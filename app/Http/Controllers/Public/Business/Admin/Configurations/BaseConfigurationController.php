<?php

namespace App\Http\Controllers\Public\Business\Admin\Configurations;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\Configurations\BaseConfiguration;
use App\Services\BusinessNavigation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** Shared secure CRUD mechanics; pages, models, routes, migrations and field definitions are separate. */
abstract class BaseConfigurationController extends Controller
{
    protected const MODEL = '';
    protected const SLUG = '';
    protected const TITLE = '';
    protected const TITLE_BN = '';
    protected const FIELDS = [];

    private function context(Request $request): array
    {
        $business = $request->attributes->get('current_business');
        abort_unless($business instanceof Business, 404);
        $logged = Auth::guard('business_admin')->user();
        abort_unless($logged && (int)$logged->business_id === (int)$business->id, 403);

        $user = BusinessUser::query()->with('role')
            ->where('business_id', $business->id)->findOrFail($logged->id);
        $modules = app(BusinessNavigation::class)->forBusiness($business, $user);
        $module = $modules->firstWhere('slug', 'configurations');
        $menu = $module?->menus->firstWhere('slug', static::SLUG);
        abort_unless($menu && $menu->children->isEmpty(), 403, 'Configuration access denied.');

        $owner = $user->isOwnerOf($business);
        $permissions = ['create' => $owner, 'update' => $owner, 'delete' => $owner];
        if (!$owner) {
            $accesses = $user->accesses()->where('module_id', $module->id)
                ->where('menu_id', $menu->id)->whereNull('child_menu_id')
                ->where('is_active', true)->where('approval_status', 'Approved')->get();
            foreach (['create', 'update', 'delete'] as $action) {
                $permissions[$action] = $accesses->contains(fn($access) =>
                    (bool)$access->can_view && (bool)$access->{'can_'.$action});
            }
        }
        return [$business, $user, $modules, $permissions];
    }

    private function model(): string
    {
        $class = static::MODEL;
        abort_unless(is_subclass_of($class, BaseConfiguration::class), 500);
        return $class;
    }

    protected function categories(Business $business)
    {
        return collect();
    }

    protected function beforeSave(array $data, ?Model $record, Business $business): void {}
    protected function beforeDelete(Model $record, Business $business): void {}
    protected function prepareInput(Request $request): void {}

    public function index(Request $request, string $business)
    {
        [$tenant, $user, $modules, $permissions] = $this->context($request);
        $model = $this->model();
        $query = $model::query()->where('business_id', $tenant->id);
        $fields = static::FIELDS;
        $search = mb_substr(trim((string)$request->query('search', '')), 0, 100);
        if ($search !== '') {
            $searchable = array_keys(array_filter($fields, fn($v) =>
                in_array($v['type'], ['text','phone','email','textarea','currency','hex'], true)));
            $query->where(function ($q) use ($search, $searchable) {
                $q->where('code', 'like', '%'.$search.'%');
                foreach ($searchable as $col) $q->orWhere($col, 'like', '%'.$search.'%');
            });
        }

        $columns = ['id' => ['label'=>'ID','type'=>'integer'],
                    'code' => ['label'=>'Code','type'=>'text']];
        foreach ($fields as $column => $meta) $columns[$column] = $meta;
        $columns['is_active'] = ['label'=>'Status','type'=>'status'];
        $columns['created_at'] = ['label'=>'Created','type'=>'date'];
        $columns['updated_at'] = ['label'=>'Updated','type'=>'date'];

        $filters = $request->query('f', []);
        if (!is_array($filters)) $filters = [];
        $filters = array_intersect_key($filters, $columns);
        foreach ($filters as $column => $value) {
            if (!is_scalar($value)) continue;
            $value = trim(mb_substr((string)$value, 0, 120));
            if ($value === '') continue;
            $type = $columns[$column]['type'];
            if (in_array($type, ['status','boolean'], true)) {
                if (in_array($value, ['0','1'], true)) $query->where($column, (int)$value);
            } elseif (in_array($type, ['integer','category'], true)) {
                if (ctype_digit($value)) $query->where($column, (int)$value);
            } elseif ($type === 'decimal') {
                if (is_numeric($value)) $query->where($column, (float)$value);
            } elseif ($type === 'date') {
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) $query->whereDate($column, $value);
            } elseif ($type === 'select') {
                if (isset($columns[$column]['options'][$value])) $query->where($column, $value);
            } else {
                $query->where($column, 'like', '%'.$value.'%');
            }
        }
        $sort = (string)$request->query('sort', 'id');
        if (!array_key_exists($sort, $columns)) $sort = 'id';
        $dir = strtolower((string)$request->query('dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $perPage = (int)$request->query('per_page', 15);
        if (!in_array($perPage, [10,15,25,50,100], true)) $perPage = 15;
        $records = $query->orderBy($sort, $dir)->orderBy('id', 'desc')
            ->paginate($perPage)->withQueryString();

        return view('public.business.admin.configurations.'.static::SLUG.'.index', [
            'business' => $tenant, 'user' => $user, 'modules' => $modules,
            'title' => static::TITLE, 'titleBn'=>static::TITLE_BN,
            'fields'=>$fields, 'columns'=>$columns, 'records'=>$records,
            'filters'=>$filters, 'search'=>$search, 'sort'=>$sort, 'dir'=>$dir,
            'perPage'=>$perPage, 'permissions'=>$permissions,
            'categories'=>$this->categories($tenant),
            'routePrefix'=>'store.admin.configurations.'.str_replace('-', '_', static::SLUG),
        ]);
    }

    private function validated(Request $request, Business $tenant, ?Model $record): array
    {
        $rules = [];
        foreach (static::FIELDS as $name=>$meta) {
            $required = $meta['required'] ? 'required' : 'nullable';
            $rules[$name] = match($meta['type']) {
                'text', 'phone' => [$required,'string','max:255'],
                'email' => [$required,'email:rfc','max:255'],
                'textarea' => [$required,'string','max:2000'],
                'currency' => [$required,'string','size:3','regex:/^[A-Z]{3}$/'],
                'hex' => [$required,'regex:/^#[0-9a-fA-F]{6}$/'],
                'select' => [$required,Rule::in(array_keys($meta['options']))],
                'category' => [$required,'integer', Rule::exists('config_categories','id')
                    ->where('business_id',$tenant->id)->whereNull('deleted_at')->where('is_active',true)],
                'decimal' => [$required,'numeric','min:0','max:'.($name==='rate'?'100':'999999999')],
                'integer' => [$required,'integer','min:0','max:1000000'],
                'boolean' => ['required','boolean'],
                default => throw new \LogicException('Invalid configuration field type'),
            };
        }
        $rules['is_active'] = ['required','boolean'];
        if (isset($rules['currency_code'])) {
            $model = $this->model();
            $rules['currency_code'][] = Rule::unique((new $model)->getTable(), 'currency_code')
                ->where('business_id', $tenant->id)->whereNull('deleted_at')->ignore($record?->id);
        }
        $data = $request->validate($rules);
        if (isset($data['currency_code'])) $data['currency_code'] = strtoupper($data['currency_code']);
        $data['is_active'] = (bool)$data['is_active'];
        if (array_key_exists('is_default',$data)) $data['is_default'] = (bool)$data['is_default'];
        return $data;
    }

    private function ensureWrite(array $permissions, string $action): void
    {
        abort_unless($permissions[$action] ?? false, 403, 'You do not have permission to '.$action);
    }

    public function store(Request $request, string $business)
    {
        [$tenant,,, $permissions] = $this->context($request);
        $this->ensureWrite($permissions,'create');
        $this->prepareInput($request);
        $data = $this->validated($request,$tenant,null);
        $model = $this->model();
        DB::transaction(function () use ($data, $model, $tenant) {
            $this->beforeSave($data, null, $tenant);
            $model::query()->create($data + [
                'business_id'=>$tenant->id, 'approval_status'=>'Approved','approved_at'=>now(),
            ]);
        });
        return redirect()->route('store.admin.configurations.'.str_replace('-', '_',static::SLUG).'.index',
            ['business'=>$request->route('business')])->with('success','Created successfully.');
    }

    public function update(Request $request, string $business, int $record)
    {
        [$tenant,,, $permissions] = $this->context($request);
        $this->ensureWrite($permissions,'update');
        $model = $this->model();
        $row = $model::query()->where('business_id',$tenant->id)->findOrFail($record);
        $this->prepareInput($request);
        $data = $this->validated($request,$tenant,$row);
        DB::transaction(function () use ($data,$row,$tenant) {
            $this->beforeSave($data, $row, $tenant);
            $row->update($data);
        });
        return back()->with('success','Updated successfully.');
    }

    public function destroy(Request $request, string $business, int $record)
    {
        [$tenant,,, $permissions] = $this->context($request);
        $this->ensureWrite($permissions,'delete');
        $model = $this->model();
        $row = $model::query()->where('business_id',$tenant->id)->findOrFail($record);
        $this->beforeDelete($row,$tenant);
        $row->delete();
        return back()->with('success','Deleted successfully.');
    }
}

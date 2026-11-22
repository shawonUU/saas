<?php

namespace App\Http\Controllers\Public\Business\Admin\Configurations;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\Configurations\Supplier;
use App\Services\BusinessNavigation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{
    protected const SLUG = 'suppliers';
    protected const TITLE = 'Suppliers';
    protected const TITLE_BN = 'সরবরাহকারী';
    protected const FIELDS = [
        'name' => [
            'label' => 'Supplier Name',
            'label_bn' => 'সরবরাহকারীর নাম',
            'type' => 'text',
            'required' => true,
            'options' => [

            ],
        ],
        'contact_person' => [
            'label' => 'Contact Person',
            'label_bn' => 'যোগাযোগের ব্যক্তি',
            'type' => 'text',
            'required' => false,
            'options' => [

            ],
        ],
        'phone' => [
            'label' => 'Phone',
            'label_bn' => 'ফোন',
            'type' => 'phone',
            'required' => false,
            'options' => [

            ],
        ],
        'email' => [
            'label' => 'Email',
            'label_bn' => 'ইমেইল',
            'type' => 'email',
            'required' => false,
            'options' => [

            ],
        ],
        'address' => [
            'label' => 'Address',
            'label_bn' => 'ঠিকানা',
            'type' => 'textarea',
            'required' => false,
            'options' => [

            ],
        ],
        'notes' => [
            'label' => 'Notes',
            'label_bn' => 'নোট',
            'type' => 'textarea',
            'required' => false,
            'options' => [

            ],
        ],
    ];

    private function context(Request $request): array
    {
        $tenant = $request->attributes->get('current_business');
        abort_unless($tenant instanceof Business, 404);
        $logged = Auth::guard('business_admin')->user();
        abort_unless($logged && (int) $logged->business_id === (int) $tenant->id, 403);
        $user = BusinessUser::with('role')->where('business_id', $tenant->id)->findOrFail($logged->id);
        $modules = app(BusinessNavigation::class)->forBusiness($tenant, $user);
        $module = $modules->firstWhere('slug', 'configurations');
        $menu = $module?->menus->firstWhere('slug', self::SLUG);
        abort_unless($menu && $menu->children->isEmpty(), 403);

        $owner = $user->isOwnerOf($tenant);
        $permissions = ['create' => $owner, 'update' => $owner, 'delete' => $owner];
        if (!$owner) {
            $accesses = $user->accesses()->where('module_id', $module->id)
                ->where('menu_id', $menu->id)->whereNull('child_menu_id')
                ->where('is_active', true)->where('approval_status', 'Approved')->get();
            foreach (array_keys($permissions) as $action) {
                $permissions[$action] = $accesses->contains(fn ($access) =>
                    $access->can_view && $access->{'can_'.$action});
            }
        }
        return [$tenant, $user, $modules, $permissions];
    }

    public function index(Request $request, string $business)
    {
        [$tenant, $user, $modules, $permissions] = $this->context($request);
        $fields = self::FIELDS;
        $columns = ['id' => ['label' => 'ID', 'label_bn' => 'আইডি', 'type' => 'integer'],
            'code' => ['label' => 'Code', 'label_bn' => 'কোড', 'type' => 'text']]
            + $fields + ['is_active' => ['label' => 'Status', 'label_bn' => 'অবস্থা', 'type' => 'status'],
                'created_at' => ['label' => 'Created', 'label_bn' => 'তৈরির তারিখ', 'type' => 'date'],
                'updated_at' => ['label' => 'Updated', 'label_bn' => 'সংশোধনের তারিখ', 'type' => 'date']];
        $query = Supplier::query()->where('business_id', $tenant->id);
        $search = mb_substr(trim((string) $request->query('search', '')), 0, 100);
        if ($search !== '') {
            $searchable = array_keys(array_filter($fields, fn ($field) => in_array(
                $field['type'], ['text', 'phone', 'email', 'textarea', 'currency', 'hex'], true)));
            $query->where(function ($q) use ($search, $searchable) {
                $q->where('code', 'like', '%'.$search.'%');
                foreach ($searchable as $column) $q->orWhere($column, 'like', '%'.$search.'%');
            });
        }
        $filters = $request->query('f', []);
        if (!is_array($filters)) $filters = [];
        $filters = array_intersect_key($filters, $columns);
        foreach ($filters as $column => $value) {
            if (!is_scalar($value)) continue;
            $value = trim(mb_substr((string) $value, 0, 120));
            if ($value === '') continue;
            $type = $columns[$column]['type'];
            if (in_array($type, ['status', 'boolean'], true)) {
                if (in_array($value, ['0', '1'], true)) $query->where($column, (int) $value);
            } elseif (in_array($type, ['integer', 'category'], true)) {
                if (ctype_digit($value)) $query->where($column, (int) $value);
            } elseif ($type === 'decimal') {
                if (is_numeric($value)) $query->where($column, (float) $value);
            } elseif ($type === 'date') {
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) $query->whereDate($column, $value);
            } elseif ($type === 'select') {
                if (isset($columns[$column]['options'][$value])) $query->where($column, $value);
            } else {
                $query->where($column, 'like', '%'.$value.'%');
            }
        }
        $sort = (string) $request->query('sort', 'id');
        if (!isset($columns[$sort])) $sort = 'id';
        $dir = strtolower((string) $request->query('dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $perPage = (int) $request->query('per_page', 15);
        if (!in_array($perPage, [10, 15, 25, 50, 100], true)) $perPage = 15;
        $records = $query->orderBy($sort, $dir)->paginate($perPage)->withQueryString();

        return view('public.business.admin.configurations.'.self::SLUG.'.index', [
            'business' => $tenant, 'user' => $user, 'modules' => $modules,
            'title' => self::TITLE, 'titleBn' => self::TITLE_BN,
            'fields' => $fields, 'columns' => $columns, 'records' => $records,
            'filters' => $filters, 'search' => $search, 'sort' => $sort, 'dir' => $dir,
            'perPage' => $perPage, 'permissions' => $permissions,
            'categories' => collect(),
            'routePrefix' => 'business.admin.configurations.'.str_replace('-', '_', self::SLUG),
        ]);
    }

    private function validated(Request $request, Business $tenant, ?Supplier $record = null): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email:rfc|max:255',
            'address' => 'nullable|string|max:2000',
            'notes' => 'nullable|string|max:2000',
            'is_active' => 'required|boolean',
        ]);
        $data['is_active'] = (bool) $data['is_active'];

        return $data;
    }

    public function store(Request $request, string $business)
    {
        [$tenant, , , $permissions] = $this->context($request);
        abort_unless($permissions['create'], 403);
        $data = $this->validated($request, $tenant);
        Supplier::create($data + ['business_id' => $tenant->id,
                'approval_status' => 'Approved', 'approved_at' => now()]);
        return redirect()->route('business.admin.configurations.'.str_replace('-', '_', self::SLUG).'.index',
            ['business' => $business])->with('success', t('Created successfully.', 'সফলভাবে তৈরি হয়েছে।'));
    }

    public function update(Request $request, string $business, int $record)
    {
        [$tenant, , , $permissions] = $this->context($request);
        abort_unless($permissions['update'], 403);
        $row = Supplier::where('business_id', $tenant->id)->findOrFail($record);
        $data = $this->validated($request, $tenant, $row);
        $row->update($data);
        return back()->with('success', t('Updated successfully.', 'সফলভাবে হালনাগাদ হয়েছে।'));
    }

    public function destroy(Request $request, string $business, int $record)
    {
        [$tenant, , , $permissions] = $this->context($request);
        abort_unless($permissions['delete'], 403);
        $row = Supplier::where('business_id', $tenant->id)->findOrFail($record);
        $row->delete();
        return back()->with('success', t('Deleted successfully.', 'সফলভাবে মুছে ফেলা হয়েছে।'));
    }
}

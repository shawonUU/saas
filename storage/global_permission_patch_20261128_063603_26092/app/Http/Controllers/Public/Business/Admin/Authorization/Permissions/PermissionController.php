<?php
namespace App\Http\Controllers\Public\Business\Admin\Authorization\Permissions;

use App\Http\Controllers\Controller;
use App\Models\{Business, BusinessPermission, BusinessUser, BusinessUserPermission};
use App\Services\Public\Business\Admin\Settings\NavigationManagement\BusinessNavigation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PermissionController extends Controller
{
    private function context(Request $request): array
    {
        $tenant = $request->attributes->get('current_business');
        abort_unless($tenant instanceof Business, 404);
        $logged = Auth::guard('business_admin')->user();
        abort_unless($logged && (int)$logged->business_id === (int)$tenant->id, 403);
        $actor = BusinessUser::with('role')->where('business_id',$tenant->id)->findOrFail($logged->id);
        $owner = $actor->isOwnerOf($tenant);
        $admin = !$actor->role?->is_owner && strtolower(trim((string)$actor->role?->name)) === 'admin';
        abort_unless($owner || $admin, 403);
        $tree = app(BusinessNavigation::class)->forBusiness($tenant, $actor);
        $menu = $tree->firstWhere('slug','authorization')?->menus->firstWhere('slug','permissions');
        abort_unless($menu && $menu->children->isEmpty(), 403);
        return [$tenant,$actor,$tree];
    }

    public function index(Request $request, string $business)
    {
        [$tenant,$actor,$modules] = $this->context($request);
        $tree = app(BusinessNavigation::class)->forBusiness($tenant);
        $query = BusinessPermission::where(fn($q) => $q->whereNull('business_id')->orWhere('business_id',$tenant->id));
        $search = trim(mb_substr((string)$request->query('search',''),0,100));
        if ($search !== '') $query->where(fn($q) => $q->where('name','like','%'.$search.'%')->orWhere('key','like','%'.$search.'%'));
        $records = $query->orderByRaw('CASE WHEN business_id IS NULL THEN 1 ELSE 0 END')->latest('id')->paginate(15)->withQueryString();
        return view('public.business.admin.authorization.permissions.index',compact('tenant','actor','modules','tree','records','search'));
    }

    private function validatePayload(Request $request, Business $tenant): array
    {
        $data = $request->validate([
            'name'=>['required','string','max:255'],
            'key'=>['required','string','max:255','regex:/^[a-z][a-z0-9._:-]*$/'],
            'description'=>['nullable','string','max:2000'],
            'module_id'=>['required','integer'],
            'menu_id'=>['nullable','integer'],
            'child_menu_id'=>['nullable','integer'],
            'is_active'=>['required','boolean'],
        ]);
        $tree = app(BusinessNavigation::class)->forBusiness($tenant);
        $module = $tree->first(fn($m) => (int)$m->id === (int)$data['module_id']);
        if (!$module) throw ValidationException::withMessages(['module_id'=>'Choose an available module.']);
        $menu = null;
        if (!empty($data['menu_id'])) {
            $menu = $module->menus->first(fn($m) => (int)$m->id === (int)$data['menu_id']);
            if (!$menu) throw ValidationException::withMessages(['menu_id'=>'Menu does not belong to this module or is unavailable.']);
        }
        if (!empty($data['child_menu_id'])) {
            if (!$menu || !$menu->children->contains(fn($c) => (int)$c->id === (int)$data['child_menu_id'])) {
                throw ValidationException::withMessages(['child_menu_id'=>'Child menu does not belong to this menu or is unavailable.']);
            }
        }
        $data['menu_id'] = $menu?->id;
        $data['child_menu_id'] = !empty($data['child_menu_id']) ? (int)$data['child_menu_id'] : null;
        return $data;
    }

    public function store(Request $request)
    {
        [$tenant] = $this->context($request);
        $data = $this->validatePayload($request,$tenant);
        if (BusinessPermission::where('key',$data['key'])->exists()) throw ValidationException::withMessages(['key'=>'Permission key already exists.']);
        BusinessPermission::create($data + ['business_id'=>$tenant->id,'approval_status'=>'Approved']);
        return back()->with('success',t('Permission created.','পারমিশন তৈরি হয়েছে।'));
    }

    private function editable(Business $tenant, int $id): BusinessPermission
    {
        return BusinessPermission::where('business_id',$tenant->id)->findOrFail($id);
    }

    public function update(Request $request, string $business, int $record)
    {
        [$tenant] = $this->context($request);
        $permission = $this->editable($tenant,$record);
        $data = $this->validatePayload($request,$tenant);
        if (BusinessPermission::where('key',$data['key'])->whereKeyNot($record)->exists()) {
            throw ValidationException::withMessages(['key'=>'Permission key already exists.']);
        }
        $permission->update($data);
        return back()->with('success',t('Permission updated.','পারমিশন আপডেট হয়েছে।'));
    }

    public function destroy(Request $request, string $business, int $record)
    {
        [$tenant] = $this->context($request);
        $permission = $this->editable($tenant,$record);
        if (BusinessUserPermission::where('permission_id',$permission->id)->exists()) {
            return back()->withErrors(['delete'=>t('Remove this permission from users first.','আগে ইউজারদের কাছ থেকে এই পারমিশন সরান।')]);
        }
        $permission->delete();
        return back()->with('success',t('Permission deleted.','পারমিশন মুছে ফেলা হয়েছে।'));
    }
}

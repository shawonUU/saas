<?php
namespace App\Http\Controllers\Public\Business\Admin\Authorization\Roles;

use App\Http\Controllers\Controller;
use App\Models\{Business, BusinessRole, BusinessUser};
use App\Services\Public\Business\Admin\Settings\NavigationManagement\BusinessNavigation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    private function context(Request $request): array
    {
        $business = $request->attributes->get('current_business');
        abort_unless($business instanceof Business, 404);
        $logged = Auth::guard('business_admin')->user();
        abort_unless($logged && (int)$logged->business_id === (int)$business->id, 403);
        $actor = BusinessUser::with('role')->where('business_id', $business->id)->findOrFail($logged->id);
        $owner = $actor->isOwnerOf($business);
        $admin = !$actor->role?->is_owner && strtolower(trim((string)$actor->role?->name)) === 'admin';
        abort_unless($owner || $admin, 403);
        $modules = app(BusinessNavigation::class)->forBusiness($business, $actor);
        $menu = $modules->firstWhere('slug', 'authorization')?->menus->firstWhere('slug', 'roles');
        abort_unless($menu && $menu->children->isEmpty(), 403);
        return [$business, $actor, $owner, $modules];
    }

    public function index(Request $request, string $business)
    {
        [$tenant, $actor, $owner, $modules] = $this->context($request);
        $query = BusinessRole::withCount('users')->where('business_id', $tenant->id);
        $search = trim(mb_substr((string)$request->query('search', ''), 0, 100));
        if ($search !== '') $query->where('name', 'like', '%'.$search.'%');
        $status = (string)$request->query('status', '');
        if (in_array($status, ['0','1'], true)) $query->where('is_active', (bool)$status);
        $records = \App\Services\Public\Business\Admin\Shared\ListPage::page($query->orderBy('is_owner', 'desc')->orderBy('name'), $request, ['id'=>'number','name'=>'text','is_active'=>'number'], 'Business Roles');
        return view('public.business.admin.authorization.roles.index', compact('tenant','actor','owner','modules','records','search','status'));
    }

    private function payload(Request $request, int $businessId, ?BusinessRole $role = null): array
    {
        $request->merge(['name' => trim((string)$request->input('name'))]);
        $unique = Rule::unique('business_roles', 'name')->where('business_id', $businessId);
        if ($role) $unique->ignore($role->id);
        $data = $request->validate([
            'name' => ['required','string','max:255',$unique],
            'is_active' => ['required','boolean'],
        ]);
        if (in_array(strtolower($data['name']), ['owner','admin'], true)) {
            throw ValidationException::withMessages(['name' => 'Owner and Admin are reserved roles.']);
        }
        $exists = BusinessRole::withTrashed()->where('business_id', $businessId)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($data['name'])])
            ->when($role, fn($q) => $q->whereKeyNot($role->id))->exists();
        if ($exists) throw ValidationException::withMessages(['name' => 'This role name already exists (including archived roles).']);
        return $data;
    }

    public function store(Request $request)
    {
        [$tenant] = $this->context($request);
        $data = $this->payload($request, (int)$tenant->id);
        BusinessRole::create(['business_id'=>$tenant->id, 'name'=>$data['name'], 'is_owner'=>false,
            'is_active'=>(bool)$data['is_active'], 'approval_status'=>'Approved']);
        return back()->with('success', t('Role created.','রোল তৈরি হয়েছে।'));
    }

    private function editable(Business $business, int $id): BusinessRole
    {
        $role = BusinessRole::where('business_id', $business->id)->findOrFail($id);
        abort_if($role->is_owner || in_array(strtolower(trim($role->name)), ['owner','admin'], true), 403);
        return $role;
    }

    public function update(Request $request, string $business, int $record)
    {
        [$tenant] = $this->context($request);
        $role = $this->editable($tenant, $record);
        $data = $this->payload($request, (int)$tenant->id, $role);
        $role->update(['name'=>$data['name'],'is_active'=>(bool)$data['is_active']]);
        return back()->with('success', t('Role updated.','রোল আপডেট হয়েছে।'));
    }

    public function destroy(Request $request, string $business, int $record)
    {
        [$tenant] = $this->context($request);
        $role = $this->editable($tenant, $record);
        if ($role->users()->exists()) {
            return back()->withErrors(['delete'=>t('Move assigned users to another role first.','আগে এই রোলের ইউজারদের অন্য রোলে দিন।')]);
        }
        $role->delete();
        return back()->with('success', t('Role deleted.','রোল মুছে ফেলা হয়েছে।'));
    }
}

<?php
namespace App\Http\Controllers\Public\Business\Admin\Authorization\Permissions;

use App\Http\Controllers\Controller;
use App\Models\{Business, BusinessPermission, BusinessUser};
use App\Services\Public\Business\Admin\Settings\NavigationManagement\BusinessNavigation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermissionController extends Controller
{
    public function index(Request $request, string $business)
    {
        $tenant = $request->attributes->get('current_business');
        abort_unless($tenant instanceof Business, 404);
        $logged = Auth::guard('business_admin')->user();
        abort_unless($logged && (int) $logged->business_id === (int) $tenant->id, 403);
        $actor = BusinessUser::with('role')->where('business_id', $tenant->id)->findOrFail($logged->id);
        $owner = $actor->isOwnerOf($tenant);
        $admin = ! $actor->role?->is_owner && strtolower(trim((string) $actor->role?->name)) === 'admin';
        abort_unless($owner || $admin, 403);
        $modules = app(BusinessNavigation::class)->forBusiness($tenant, $actor);
        $menu = $modules->firstWhere('slug', 'authorization')?->menus->firstWhere('slug', 'permissions');
        abort_unless($menu && $menu->children->isEmpty(), 403);
        $search = trim(mb_substr((string) $request->query('search',''), 0, 100));
        $query = BusinessPermission::with(['module','menu','childMenu']);
        if ($search !== '') $query->where(fn ($q) => $q->where('name', 'like', '%'.$search.'%')->orWhere('key', 'like', '%'.$search.'%'));
        $columns = $request->query('f', []);
        $columns = is_array($columns) ? $columns : [];
        $identity = trim(mb_substr(is_scalar($columns['identity'] ?? null) ? (string)$columns['identity'] : '', 0, 100));
        if ($identity !== '') $query->where(fn($q) => $q->where('name', 'like', '%'.$identity.'%')
            ->orWhere('key', 'like', '%'.$identity.'%')->orWhere('description', 'like', '%'.$identity.'%'));
        $feature = trim(mb_substr(is_scalar($columns['feature'] ?? null) ? (string)$columns['feature'] : '', 0, 100));
        if ($feature !== '') $query->where(fn($q) => $q->whereHas('module', fn($r) => $r->where('name', 'like', '%'.$feature.'%')
            ->orWhere('name_bn', 'like', '%'.$feature.'%'))->orWhereHas('menu', fn($r) => $r->where('name', 'like', '%'.$feature.'%')
            ->orWhere('name_bn', 'like', '%'.$feature.'%'))->orWhereHas('childMenu', fn($r) => $r->where('name', 'like', '%'.$feature.'%')
            ->orWhere('name_bn', 'like', '%'.$feature.'%')));
        $records = \App\Services\Public\Business\Admin\Shared\ListPage::page($query->orderBy('name'), $request, ['id'=>'number','name'=>'text','key'=>'text','is_active'=>'number','module_id'=>'number'], 'Global Permissions');
        return view('public.business.admin.authorization.permissions.index', compact('tenant','actor','modules','records','search'));
    }
}

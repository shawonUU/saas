<?php
namespace App\Http\Controllers\Public\Business\Admin\Authorization\Users;

use App\Http\Controllers\Controller;
use App\Models\{Business, BusinessRole, BusinessUser, User};
use App\Services\Public\Business\Admin\Settings\NavigationManagement\BusinessNavigation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class BusinessUserController extends Controller
{
    private function context(Request $request): array
    {
        $business = $request->attributes->get('current_business');
        abort_unless($business instanceof Business, 404);
        $auth = Auth::guard('business_admin')->user();
        abort_unless($auth && (int)$auth->business_id === (int)$business->id, 403);
        $actor = BusinessUser::with('role')->where('business_id', $business->id)->findOrFail($auth->id);
        $isOwner = $actor->isOwnerOf($business);
        abort_unless($isOwner || (!(bool)$actor->role?->is_owner && strtolower(trim((string)$actor->role?->name)) === 'admin'), 403);
        $tree = app(BusinessNavigation::class)->forBusiness($business, $actor);
        $authorization = $tree->firstWhere('slug', 'authorization');
        abort_unless($authorization && $authorization->menus->contains('slug', 'users'), 403);
        return [$business, $actor, $isOwner];
    }

    private function roles(Business $business, bool $isOwner)
    {
        return BusinessRole::query()->where('business_id', $business->id)
            ->where('is_owner', false)->where('is_active', true)
            ->where('approval_status', 'Approved')
            ->when(!$isOwner, fn($q) => $q->whereRaw('LOWER(name) != ?', ['admin']))
            ->orderBy('name');
    }

    private function roleFor(Business $business, bool $isOwner, string $requested): BusinessRole
    {
        if ($requested === 'staff') {
            $role = BusinessRole::firstOrCreate(
                ['business_id' => $business->id, 'name' => 'Staff'],
                ['is_owner' => false, 'is_active' => true, 'approval_status' => 'Approved']
            );
            if ($role->is_owner || !$role->is_active || $role->approval_status !== 'Approved') {
                throw ValidationException::withMessages(['role_id' => 'Staff role is not available.']);
            }
            return $role;
        }
        $role = $this->roles($business, $isOwner)->whereKey((int)$requested)->first();
        if (!$role) throw ValidationException::withMessages(['role_id' => 'Please select an allowed role.']);
        return $role;
    }

    private function editable(Business $business, BusinessUser $actor, bool $isOwner, int $id): BusinessUser
    {
        $target = BusinessUser::with('role')->where('business_id', $business->id)->findOrFail($id);
        abort_if($target->isOwnerOf($business) || (bool)$target->role?->is_owner, 403, 'Owner cannot be edited.');
        abort_if(!$isOwner && strtolower(trim((string)$target->role?->name)) === 'admin', 403);
        abort_if((int)$target->id === (int)$actor->id, 403, 'You cannot edit your own admin account here.');
        return $target;
    }

    public function index(Request $request, BusinessNavigation $navigation)
    {
        [$business, $actor, $isOwner] = $this->context($request);
        $query = BusinessUser::with('role')->where('business_id', $business->id);
        $search = trim((string)$request->query('search', ''));
        $status = (string)$request->query('status', 'all');
        $roleId = $request->query('role_id');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('code', 'like', '%'.$search.'%');
            });
        }
        if ($status === 'active') $query->where('is_active', true)->where('approval_status', 'Approved');
        elseif ($status === 'inactive') $query->where(fn($q) => $q->where('is_active', false)->orWhere('approval_status', '!=', 'Approved'));
        if (ctype_digit((string)$roleId)) $query->where('role_id', (int)$roleId);
        $base = BusinessUser::where('business_id', $business->id);
        return view('public.business.admin.users.index', [
            'title' => t('Team users', 'টিম ইউজার'),
            'business' => $business, 'user' => $actor, 'modules' => $navigation->forBusiness($business, $actor),
            'members' => $query->latest('id')->paginate(15)->withQueryString(),
            'roles' => $this->roles($business, $isOwner)->get(),
            'filterRoles' => BusinessRole::where('business_id', $business->id)->orderBy('name')->get(),
            'totalMembers' => (clone $base)->count(),
            'activeMembers' => (clone $base)->where('is_active', true)->where('approval_status', 'Approved')->count(),
            'inactiveMembers' => (clone $base)->where(fn($q) => $q->where('is_active', false)->orWhere('approval_status', '!=', 'Approved'))->count(),
            'isOwner' => $isOwner, 'search' => $search, 'status' => $status,
        ]);
    }

    public function store(Request $request)
    {
        [$business, $actor, $isOwner] = $this->context($request);
        $request->merge(['email' => strtolower(trim((string)$request->input('email')))]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email'), Rule::unique('business_users', 'email')->where('business_id', $business->id)],
            'role_id' => ['required', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'is_active' => ['required', 'boolean'],
        ]);
        DB::transaction(function () use ($data, $business, $isOwner) {
            $role = $this->roleFor($business, $isOwner, (string)$data['role_id']);
            $globalUser = User::create([
                'name' => trim($data['name']), 'email' => $data['email'],
                'is_active' => true, 'approval_status' => 'Approved',
            ]);
            BusinessUser::create([
                'user_id' => $globalUser->id, 'business_id' => $business->id,
                'role_id' => $role->id, 'name' => trim($data['name']),
                'email' => $data['email'], 'password' => $data['password'],
                'is_active' => (bool)$data['is_active'], 'approval_status' => 'Approved',
            ]);
        });
        return back()->with('success', t('User created successfully.', 'ইউজার তৈরি হয়েছে।'));
    }

    public function update(Request $request, string $business, string $memberId)
    {
        [$tenant, $actor, $isOwner] = $this->context($request);
        $target = $this->editable($tenant, $actor, $isOwner, (int)$memberId);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role_id' => ['required', 'string', 'max:30'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'is_active' => ['required', 'boolean'],
        ]);
        DB::transaction(function () use ($data, $tenant, $isOwner, $target) {
            $role = $this->roleFor($tenant, $isOwner, (string)$data['role_id']);
            $target->name = trim($data['name']);
            $target->role_id = $role->id;
            $target->is_active = (bool)$data['is_active'];
            if (filled($data['password'] ?? null)) $target->password = $data['password'];
            $target->save();
        });
        return back()->with('success', t('User updated successfully.', 'ইউজার আপডেট হয়েছে।'));
    }
}

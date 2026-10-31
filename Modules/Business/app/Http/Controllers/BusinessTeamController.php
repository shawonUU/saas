<?php

namespace Modules\Business\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Business\App\Models\Business;

class BusinessTeamController extends Controller
{
    private const ROLES = ['Admin', 'Manager', 'Cashier', 'Accountant', 'Staff'];
    private const ACTIONS = ['can_view', 'can_create', 'can_update', 'can_delete'];

    private function context(Request $request, int $businessId): array
    {
        $business = Business::query()
            ->whereKey($businessId)
            ->where('status', 'Active')
            ->firstOrFail();

        $actor = DB::table('business_members')
            ->where('business_id', $business->id)
            ->where('user_id', $request->user()->id)
            ->where('status', 'Active')
            ->first();

        abort_unless($actor, 403, 'You do not have access to this business.');

        $isOwner = $business->owner_id == $request->user()->id
            && $actor->role === 'Owner';

        $isAdmin = $actor->role === 'Admin';

        abort_unless($isOwner || $isAdmin, 403, 'Only Owner or Admin can manage shop users.');

        return [$business, $actor, $isOwner];
    }

    private function member(Business $business, int $memberId): object
    {
        $member = DB::table('business_members')
            ->where('business_id', $business->id)
            ->where('id', $memberId)
            ->first();

        abort_unless($member, 404, 'Business member not found.');

        return $member;
    }

    private function assertEditable(object $member, bool $isOwner): void
    {
        if ($member->role === 'Owner' || (!$isOwner && $member->role === 'Admin')) {
            abort(403, 'You cannot modify this member.');
        }
    }

    private function allowedModules(Business $business)
    {
        return DB::table('business_category_modules as cm')
            ->join('app_modules as m', 'm.id', '=', 'cm.app_module_id')
            ->where('cm.business_category_id', $business->category_id)
            ->where('m.is_active', true)
            ->select('m.id', 'm.name', 'm.slug', 'm.icon', 'm.sort_order')
            ->orderBy('m.sort_order')
            ->orderBy('m.id')
            ->get();
    }

    private function flags($record): array
    {
        return collect(self::ACTIONS)
            ->mapWithKeys(fn ($key) => [$key => (bool) ($record?->$key ?? false)])
            ->all();
    }

    private function permissionsFor(int $memberId, string $table, string $column)
    {
        return DB::table($table)
            ->where('business_member_id', $memberId)
            ->get()
            ->keyBy($column);
    }

    public function index(Request $request, int $businessId): JsonResponse
    {
        [$business, $actor, $isOwner] = $this->context($request, $businessId);

        $members = DB::table('business_members as bm')
            ->join('users as u', 'u.id', '=', 'bm.user_id')
            ->where('bm.business_id', $business->id)
            ->select(
                'bm.id', 'bm.user_id', 'bm.business_id', 'bm.role',
                'bm.status', 'bm.created_at', 'u.name', 'u.email'
            )
            ->orderByRaw("CASE WHEN bm.role = 'Owner' THEN 0 WHEN bm.role = 'Admin' THEN 1 ELSE 2 END")
            ->orderBy('u.name')
            ->get()
            ->map(function ($member) use ($isOwner) {
                $member->can_manage = $member->role !== 'Owner'
                    && ($isOwner || $member->role !== 'Admin');

                return $member;
            });

        return response()->json([
            'data' => [
                'business' => [
                    'id' => $business->id,
                    'name' => $business->name,
                ],
                'current_member_id' => $actor->id,
                'can_assign_admin' => $isOwner,
                'roles' => $isOwner ? self::ROLES : array_values(array_diff(self::ROLES, ['Admin'])),
                'members' => $members,
            ],
        ]);
    }

    public function store(Request $request, int $businessId): JsonResponse
    {
        [$business, , $isOwner] = $this->context($request, $businessId);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', Rule::in(self::ROLES)],
        ]);

        if ($validated['role'] === 'Admin' && !$isOwner) {
            abort(403, 'Only Owner can assign an Admin.');
        }

        $user = User::query()
            ->where('email', $validated['email'])
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['No registered user was found with this email.'],
            ]);
        }

        $memberId = DB::transaction(function () use ($business, $user, $validated) {
            DB::table('businesses')
                ->where('id', $business->id)
                ->lockForUpdate()
                ->first();

            $existing = DB::table('business_members')
                ->where('business_id', $business->id)
                ->where('user_id', $user->id)
                ->first();

            if ($existing) {
                throw ValidationException::withMessages([
                    'email' => ['This user already belongs to this business.'],
                ]);
            }

            return DB::table('business_members')->insertGetId([
                'business_id' => $business->id,
                'user_id' => $user->id,
                'role' => $validated['role'],
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return response()->json([
            'message' => 'User added to business successfully.',
            'member_id' => $memberId,
        ], 201);
    }

    public function update(Request $request, int $businessId, int $memberId): JsonResponse
    {
        [$business, , $isOwner] = $this->context($request, $businessId);

        $validated = $request->validate([
            'role' => ['required', Rule::in(self::ROLES)],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
        ]);

        if ($validated['role'] === 'Admin' && !$isOwner) {
            abort(403, 'Only Owner can assign an Admin.');
        }

        DB::transaction(function () use ($business, $memberId, $validated, $isOwner) {
            $member = DB::table('business_members')
                ->where('business_id', $business->id)
                ->where('id', $memberId)
                ->lockForUpdate()
                ->first();

            abort_unless($member, 404, 'Business member not found.');
            $this->assertEditable($member, $isOwner);

            DB::table('business_members')
                ->where('id', $memberId)
                ->update([
                    'role' => $validated['role'],
                    'status' => $validated['status'],
                    'updated_at' => now(),
                ]);
        });

        return response()->json(['message' => 'Member updated successfully.']);
    }

    public function destroy(Request $request, int $businessId, int $memberId): JsonResponse
    {
        [$business, , $isOwner] = $this->context($request, $businessId);

        DB::transaction(function () use ($business, $memberId, $isOwner) {
            $member = DB::table('business_members')
                ->where('business_id', $business->id)
                ->where('id', $memberId)
                ->lockForUpdate()
                ->first();

            abort_unless($member, 404, 'Business member not found.');
            $this->assertEditable($member, $isOwner);

            // Disable access, preserve membership and audit history.
            DB::table('business_members')
                ->where('id', $memberId)
                ->update([
                    'status' => 'Inactive',
                    'updated_at' => now(),
                ]);
        });

        return response()->json(['message' => 'Member access disabled successfully.']);
    }

    public function permissions(Request $request, int $businessId, int $memberId): JsonResponse
    {
        [$business, , $isOwner] = $this->context($request, $businessId);
        $member = $this->member($business, $memberId);
        $this->assertEditable($member, $isOwner);

        $modulePermissions = $this->permissionsFor(
            $memberId, 'business_member_permissions', 'app_module_id'
        );
        $menuPermissions = $this->permissionsFor(
            $memberId, 'business_member_menu_permissions', 'app_menu_id'
        );
        $childPermissions = $this->permissionsFor(
            $memberId, 'business_member_child_menu_permissions', 'app_child_menu_id'
        );

        $modules = $this->allowedModules($business);

        $moduleIds = $modules->pluck('id')->all();

        $menus = DB::table('app_menus')
            ->whereIn('app_module_id', $moduleIds)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy('app_module_id');

        $menuIds = $menus->flatten(1)->pluck('id')->all();

        $children = DB::table('app_child_menus')
            ->whereIn('app_menu_id', $menuIds)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy('app_menu_id');

        $tree = $modules->map(function ($module) use (
            $menus, $children, $modulePermissions, $menuPermissions, $childPermissions
        ) {
            return [
                'id' => $module->id,
                'name' => $module->name,
                'slug' => $module->slug,
                'icon' => $module->icon,
                'permissions' => $this->flags($modulePermissions->get($module->id)),
                'menus' => ($menus->get($module->id) ?? collect())->map(function ($menu) use (
                    $children, $menuPermissions, $childPermissions
                ) {
                    return [
                        'id' => $menu->id,
                        'name' => $menu->name,
                        'slug' => $menu->slug,
                        'permissions' => $this->flags($menuPermissions->get($menu->id)),
                        'children' => ($children->get($menu->id) ?? collect())->map(function ($child) use ($childPermissions) {
                            return [
                                'id' => $child->id,
                                'name' => $child->name,
                                'slug' => $child->slug,
                                'permissions' => $this->flags($childPermissions->get($child->id)),
                            ];
                        })->values()->all(),
                    ];
                })->values()->all(),
            ];
        })->values();

        return response()->json([
            'data' => [
                'member' => [
                    'id' => $member->id,
                    'role' => $member->role,
                    'status' => $member->status,
                ],
                'modules' => $tree,
            ],
        ]);
    }

    public function savePermissions(Request $request, int $businessId, int $memberId): JsonResponse
    {

    // dd($request->all());

        [$business, , $isOwner] = $this->context($request, $businessId);



        $validated = $request->validate([
            'modules' => ['required', 'array'],
            'modules.*' => ['required', 'array'],
            'modules.*.id' => ['required', 'integer', 'distinct'],
            'modules.*.permissions' => ['required', 'array'],
            'modules.*.menus' => ['required', 'array'],
            'modules.*.menus.*.id' => ['required', 'integer'],
            'modules.*.menus.*.permissions' => ['required', 'array'],
            'modules.*.menus.*.children' => ['nullable', 'array'],
            'modules.*.menus.*.children.*.id' => ['required', 'integer'],
            'modules.*.menus.*.children.*.permissions' => ['required', 'array'],
        ]);

        $moduleIds = $this->allowedModules($business)->pluck('id')->all();

        $allowedMenus = DB::table('app_menus')
            ->whereIn('app_module_id', $moduleIds)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        $allowedChildren = DB::table('app_child_menus')
            ->whereIn('app_menu_id', $allowedMenus->keys())
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        $rows = [
            'business_member_permissions' => [],
            'business_member_menu_permissions' => [],
            'business_member_child_menu_permissions' => [],
        ];

        $seenMenus = [];
        $seenChildren = [];

        $normalize = function ($input): array {
            if (!is_array($input)) {
                throw ValidationException::withMessages([
                    'modules' => ['Invalid permission values.'],
                ]);
            }

            foreach (self::ACTIONS as $action) {
                if (!array_key_exists($action, $input) || !is_bool($input[$action])) {
                    throw ValidationException::withMessages([
                        'modules' => ['Every permission action must be a boolean.'],
                    ]);
                }
            }

            $view = $input['can_view'];

            return [
                'can_view' => $view,
                'can_create' => $view && $input['can_create'],
                'can_update' => $view && $input['can_update'],
                'can_delete' => $view && $input['can_delete'],
            ];
        };

        foreach ($validated['modules'] as $module) {
            $moduleId = $module['id'];

            if (!in_array($moduleId, $moduleIds, true)) {
                throw ValidationException::withMessages([
                    'modules' => ['Invalid module for this business category.'],
                ]);
            }

            $moduleFlags = $normalize($module['permissions']);
            $rows['business_member_permissions'][] = [
                'app_module_id' => $moduleId,
                'flags' => $moduleFlags,
            ];

            foreach ($module['menus'] as $menu) {
                $menuId = $menu['id'];
                $allowedMenu = $allowedMenus->get($menuId);

                if (!$allowedMenu || (int) $allowedMenu->app_module_id !== (int) $moduleId || isset($seenMenus[$menuId])) {
                    throw ValidationException::withMessages([
                        'modules' => ['Invalid or duplicate menu.'],
                    ]);
                }

                $seenMenus[$menuId] = true;
                $menuFlags = $normalize($menu['permissions']);

                foreach (self::ACTIONS as $action) {
                    $menuFlags[$action] = $menuFlags[$action] && $moduleFlags[$action];
                }

                $rows['business_member_menu_permissions'][] = [
                    'app_menu_id' => $menuId,
                    'flags' => $menuFlags,
                ];

                foreach ($menu['children'] as $child) {
                    $childId = $child['id'];
                    $allowedChild = $allowedChildren->get($childId);

                    if (!$allowedChild || (int) $allowedChild->app_menu_id !== (int) $menuId || isset($seenChildren[$childId])) {
                        throw ValidationException::withMessages([
                            'modules' => ['Invalid or duplicate child menu.'],
                        ]);
                    }

                    $seenChildren[$childId] = true;
                    $childFlags = $normalize($child['permissions']);

                    foreach (self::ACTIONS as $action) {
                        $childFlags[$action] = $childFlags[$action] && $menuFlags[$action];
                    }

                    $rows['business_member_child_menu_permissions'][] = [
                        'app_child_menu_id' => $childId,
                        'flags' => $childFlags,
                    ];
                }
            }
        }

        DB::transaction(function () use ($business, $memberId, $isOwner, $rows) {
            $member = DB::table('business_members')
                ->where('business_id', $business->id)
                ->where('id', $memberId)
                ->lockForUpdate()
                ->first();

            abort_unless($member, 404, 'Member not found.');
            $this->assertEditable($member, $isOwner);

            foreach ($rows as $table => $entries) {
                DB::table($table)->where('business_member_id', $memberId)->delete();

                if (!$entries) continue;

                $now = now();
                $column = match ($table) {
                    'business_member_permissions' => 'app_module_id',
                    'business_member_menu_permissions' => 'app_menu_id',
                    default => 'app_child_menu_id',
                };

                $insert = array_map(fn ($item) => array_merge([
                    'business_member_id' => $memberId,
                    $column => $item[$column],
                    'created_at' => $now,
                    'updated_at' => $now,
                ], $item['flags']), $entries);

                DB::table($table)->insert($insert);
            }
        });

        return response()->json([
            'message' => 'Member permissions saved successfully.',
        ]);
    }
}

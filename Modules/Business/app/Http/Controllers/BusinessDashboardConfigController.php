<?php

namespace Modules\Business\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Business\App\Models\Business;

class BusinessDashboardConfigController extends Controller
{
    private const ACTIONS = [
        'can_view',
        'can_create',
        'can_update',
        'can_delete',
    ];

    private function permissionFlags(
        ?object $permission,
        bool $fullAccess,
        ?array $parent = null
    ): array {
        $result = [];

        foreach (self::ACTIONS as $action) {
            $allowed = $fullAccess || (bool) ($permission?->{$action} ?? false);
            $result[$action] = $allowed && ($parent[$action] ?? true);
        }

        if (!$result['can_view']) {
            foreach (self::ACTIONS as $action) {
                $result[$action] = false;
            }
        }

        return $result;
    }

    public function show(Request $request, int $businessId): JsonResponse
    {
        $business = Business::query()
            ->with([
                'category',
                'branches' => function ($query) {
                    $query->where('status', 'Active')
                        ->orderByDesc('is_default')
                        ->orderBy('name');
                },
            ])
            ->whereKey($businessId)
            ->where('status', 'Active')
            ->firstOrFail();

        abort_unless(
            $business->category && $business->category->is_active,
            404,
            'Business category is unavailable.'
        );

        $member = DB::table('business_members')
            ->where('business_id', $business->id)
            ->where('user_id', $request->user()->id)
            ->where('status', 'Active')
            ->first();

        abort_unless($member, 403, 'Business access denied.');

        $isOwner = $member->role === 'Owner'
            && (int) $business->owner_id === (int) $request->user()->id;

        $isAdmin = $member->role === 'Admin';
        $fullAccess = $isOwner || $isAdmin;

        $modulePermissions = DB::table('business_member_permissions')
            ->where('business_member_id', $member->id)
            ->get()
            ->keyBy('app_module_id');

        $menuPermissions = DB::table('business_member_menu_permissions')
            ->where('business_member_id', $member->id)
            ->get()
            ->keyBy('app_menu_id');

        $childPermissions = DB::table('business_member_child_menu_permissions')
            ->where('business_member_id', $member->id)
            ->get()
            ->keyBy('app_child_menu_id');

        $modules = DB::table('business_category_modules as bcm')
            ->join('app_modules as am', 'am.id', '=', 'bcm.app_module_id')
            ->where('bcm.business_category_id', $business->category_id)
            ->where('am.is_active', true)
            ->select(
                'am.id',
                'am.name',
                'am.slug',
                'am.icon',
                'am.route',
                'am.sort_order'
            )
            ->orderBy('am.sort_order')
            ->orderBy('am.id')
            ->get();

        $moduleIds = $modules->pluck('id')->all();

        $menus = DB::table('app_menus')
            ->whereIn('app_module_id', $moduleIds)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $menuIds = $menus->pluck('id')->all();

        $children = DB::table('app_child_menus')
            ->whereIn('app_menu_id', $menuIds)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $menusByModule = $menus->groupBy('app_module_id');
        $childrenByMenu = $children->groupBy('app_menu_id');

        $navigation = $modules->map(function ($module) use (
            $modulePermissions,
            $menuPermissions,
            $childPermissions,
            $menusByModule,
            $childrenByMenu,
            $fullAccess
        ) {
            $moduleFlags = $this->permissionFlags(
                $modulePermissions->get($module->id),
                $fullAccess
            );

            if (!$moduleFlags['can_view']) {
                return null;
            }

            $moduleMenus = ($menusByModule->get($module->id) ?? collect())
                ->map(function ($menu) use (
                    $menuPermissions,
                    $childPermissions,
                    $childrenByMenu,
                    $moduleFlags,
                    $fullAccess
                ) {
                    $menuFlags = $this->permissionFlags(
                        $menuPermissions->get($menu->id),
                        $fullAccess,
                        $moduleFlags
                    );

                    if (!$menuFlags['can_view']) {
                        return null;
                    }

                    $menuChildren = ($childrenByMenu->get($menu->id) ?? collect())
                        ->map(function ($child) use (
                            $childPermissions,
                            $menuFlags,
                            $fullAccess
                        ) {
                            $childFlags = $this->permissionFlags(
                                $childPermissions->get($child->id),
                                $fullAccess,
                                $menuFlags
                            );

                            if (!$childFlags['can_view']) {
                                return null;
                            }

                            return [
                                'id' => $child->id,
                                'app_menu_id' => $child->app_menu_id,
                                'name' => $child->name,
                                'slug' => $child->slug,
                                'icon' => $child->icon,
                                'route' => $child->route,
                                'sort_order' => $child->sort_order,
                                'is_active' => true,
                                'permissions' => $childFlags,
                            ];
                        })
                        ->filter()
                        ->values();

                    // A group without a direct route needs visible children.
                    if (!$menu->route && $menuChildren->isEmpty()) {
                        return null;
                    }

                    return [
                        'id' => $menu->id,
                        'app_module_id' => $menu->app_module_id,
                        'name' => $menu->name,
                        'slug' => $menu->slug,
                        'icon' => $menu->icon,
                        'route' => $menu->route,
                        'sort_order' => $menu->sort_order,
                        'is_active' => true,
                        'permissions' => $menuFlags,
                        'children' => $menuChildren->all(),
                    ];
                })
                ->filter()
                ->values();

            // Keep permitted modules, including modules without menus.
            return [
                'id' => $module->id,
                'name' => $module->name,
                'slug' => $module->slug,
                'icon' => $module->icon,
                'route' => $module->route,
                'sort_order' => $module->sort_order,
                'permissions' => $moduleFlags,
                'menus' => $moduleMenus->all(),
            ];
        })
        ->filter()
        ->values();

        return response()->json([
            'message' => 'Dashboard configuration loaded successfully.',
            'data' => [
                'business' => [
                    'id' => $business->id,
                    'name' => $business->name,
                    'slug' => $business->slug,
                    'country' => $business->country,
                    'currency' => $business->currency,
                    'timezone' => $business->timezone,
                    'phone' => $business->phone,
                    'email' => $business->email,
                    'status' => $business->status,
                ],
                'category' => [
                    'id' => $business->category->id,
                    'name' => $business->category->name,
                    'slug' => $business->category->slug,
                ],
                'membership' => [
                    'id' => $member->id,
                    'role' => $member->role,
                    'status' => $member->status,
                ],
                'branches' => $business->branches,
                'modules' => $navigation,
                'can_manage_team' => $fullAccess,
            ],
        ]);
    }
}

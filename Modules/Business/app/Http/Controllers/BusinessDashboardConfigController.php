<?php

namespace Modules\Business\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Business\App\Models\Business;
use Modules\Business\App\Models\BusinessMemberPermission;

class BusinessDashboardConfigController extends Controller
{
    public function show(
        Request $request,
        int $businessId
    ): JsonResponse {
        $user = $request->user();

        $business = Business::query()
            ->whereKey($businessId)
            ->where('status', 'Active')
            ->whereHas('members', function ($query) use ($user) {
                $query
                    ->where('user_id', $user->id)
                    ->where('status', 'Active');
            })
            ->with([
                'category',
                'branches' => function ($query) {
                    $query->where('status', 'Active');
                },
            ])
            ->first();

        if (! $business || ! $business->category?->is_active) {
            return response()->json([
                'message' => 'Business not found or you do not have access to it.',
            ], 404);
        }

        $membership = $business->members()
            ->where('user_id', $user->id)
            ->where('status', 'Active')
            ->first();

        $isOwner = $membership->role === 'Owner';

        $categoryModules = $business->category
            ->categoryModules()
            ->whereHas('module', function ($query) {
                $query->where('is_active', true);
            })
            ->with([
                'module.menus' => function ($query) {
                    $query->where('is_active', true);
                },
                'module.menus.childMenus' => function ($query) {
                    $query->where('is_active', true);
                },
            ])
            ->get();

        $permissions = BusinessMemberPermission::query()
            ->where('business_member_id', $membership->id)
            ->get()
            ->keyBy('app_module_id');

        $modules = $categoryModules
            ->filter(fn ($categoryModule) => $categoryModule->module !== null)
            ->map(function ($categoryModule) use ($isOwner, $permissions) {
                $module = $categoryModule->module;
                $permission = $permissions->get($module->id);

                $canView = $isOwner || (bool) ($permission?->can_view ?? false);
                $canCreate = $isOwner || (bool) ($permission?->can_create ?? false);
                $canUpdate = $isOwner || (bool) ($permission?->can_update ?? false);
                $canDelete = $isOwner || (bool) ($permission?->can_delete ?? false);

                if (! $canView) {
                    return null;
                }

                $menus = $module->menus
                    ->map(function ($menu) {
                        return [
                            'id' => $menu->id,
                            'app_module_id' => $menu->app_module_id,
                            'name' => $menu->name,
                            'slug' => $menu->slug,
                            'icon' => $menu->icon,
                            'route' => $menu->route,
                            'sort_order' => $menu->sort_order,
                            'is_active' => $menu->is_active,
                            'children' => $menu->childMenus
                                ->map(function ($childMenu) {
                                    return [
                                        'id' => $childMenu->id,
                                        'app_menu_id' => $childMenu->app_menu_id,
                                        'name' => $childMenu->name,
                                        'slug' => $childMenu->slug,
                                        'icon' => $childMenu->icon,
                                        'route' => $childMenu->route,
                                        'sort_order' => $childMenu->sort_order,
                                        'is_active' => $childMenu->is_active,
                                    ];
                                })
                                ->values(),
                        ];
                    })
                    ->values();

                return [
                    'id' => $module->id,
                    'name' => $module->name,
                    'slug' => $module->slug,
                    'icon' => $module->icon,
                    'route' => $module->route,
                    'sort_order' => $module->sort_order,
                    'permissions' => [
                        'can_view' => $canView,
                        'can_create' => $canCreate,
                        'can_update' => $canUpdate,
                        'can_delete' => $canDelete,
                    ],
                    'menus' => $menus,
                ];
            })
            ->filter()
            ->sortBy('sort_order')
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
                    'id' => $membership->id,
                    'role' => $membership->role,
                    'status' => $membership->status,
                ],
                'branches' => $business->branches,
                'modules' => $modules,
            ],
        ]);
    }
}

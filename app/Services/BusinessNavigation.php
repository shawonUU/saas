<?php

namespace App\Services;

use App\Models\{
    Business, BusinessUser, BusinessCategoryModule, BusinessCategoryMenu,
    BusinessCategoryChildMenu, BusinessModule, BusinessMenu, BusinessChildMenu,
    BusinessSubscription, Module, SubscriptionPlanModule,
    SubscriptionPlanMenu, SubscriptionPlanChildMenu
};
use Illuminate\Support\Collection;

/**
 * Intersection of Global ∩ Category ∩ Active Subscription ∩ Business Overrides
 * ∩ User Access. A later layer is never allowed to re-enable a denied parent.
 */
class BusinessNavigation
{
    public function forBusiness(Business $business, ?BusinessUser $user = null): Collection
    {
        if (!$business->is_active || $business->approval_status !== 'Approved') return collect();
        if ($user && ((int) $user->business_id !== (int) $business->id
            || !$user->is_active || $user->approval_status !== 'Approved')) return collect();

        $category = $business->category;
        if (!$category || !$category->is_active || $category->approval_status !== 'Approved') return collect();

        // No active subscription (or wrong category, expired plan) means no navigation.
        $now = now();
        $subscription = BusinessSubscription::query()
            ->with('plan')
            ->where('business_id', $business->id)
            ->where('subscription_status', 'active')
            ->where('is_active', true)
            ->where(fn($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', $now))
            ->whereNull('canceled_at')
            ->latest('starts_at')->latest('id')
            ->get()->first(fn($row) => $row->plan
                && (int) $row->plan->business_category_id === (int) $business->category_id
                && $row->plan->is_active
                && $row->plan->approval_status === 'Approved');
        if (!$subscription) return collect();

        $categoryId = $category->id;
        $planId = $subscription->plan_id;

        $allowed = [];
        foreach ([
            'module' => [BusinessCategoryModule::class, SubscriptionPlanModule::class, 'module_id'],
            'menu' => [BusinessCategoryMenu::class, SubscriptionPlanMenu::class, 'menu_id'],
            'child' => [BusinessCategoryChildMenu::class, SubscriptionPlanChildMenu::class, 'child_menu_id'],
        ] as $type => [$categoryModel, $planModel, $column]) {
            $categoryIds = $categoryModel::query()
                ->where('business_category_id', $categoryId)->pluck($column)->all();
            $planIds = $planModel::query()
                ->where('subscription_plan_id', $planId)
                ->where('business_category_id', $categoryId)
                ->where('is_enabled', true)->where('is_active', true)
                ->where('approval_status', 'Approved')->pluck($column)->all();
            $allowed[$type] = array_fill_keys(array_map('intval', array_intersect($categoryIds, $planIds)), true);
        }

        $overrides = [
            'module' => $this->overrides(BusinessModule::class, 'module_id', $business->id, $subscription->id, $now),
            'menu' => $this->overrides(BusinessMenu::class, 'menu_id', $business->id, $subscription->id, $now),
            'child' => $this->overrides(BusinessChildMenu::class, 'child_menu_id', $business->id, $subscription->id, $now),
        ];
        $owner = $user && $user->isOwnerOf($business);
        $access = $user && !$owner ? $user->accesses()->where('can_view', true)->get() : collect();

        return Module::query()->with([
            'menus' => fn($q) => $q->where('is_active', true)
                ->where('approval_status', 'Approved')->withCount('children'),
            'menus.children' => fn($q) => $q->where('is_active', true)->where('approval_status', 'Approved'),
        ])
            ->where('is_active', true)->where('approval_status', 'Approved')
            ->whereIn('id', array_keys($allowed['module']))
            ->orderBy('sort_order')->orderBy('id')->get()
            ->filter(function ($module) use ($allowed, $overrides, $user, $owner, $access) {
                if (($overrides['module'][(int)$module->id] ?? true) === false) return false;

                $menus = $module->menus->filter(function ($menu) use ($module, $allowed, $overrides, $user, $owner, $access) {
                    if (!isset($allowed['menu'][(int)$menu->id])
                        || ($overrides['menu'][(int)$menu->id] ?? true) === false) return false;

                    // A parent with children must never become a clickable leaf just
                    // because all of its child permissions were filtered out.
                    if ((int)$menu->children_count === 0) {
                        $menu->setRelation('children', collect());
                        if (!$user || $owner) return true;
                        return $access->contains(fn($permission) =>
                            (int)$permission->module_id === (int)$module->id
                            && (int)$permission->menu_id === (int)$menu->id
                            && $permission->child_menu_id === null);
                    }

                    $children = $menu->children->filter(function ($child) use ($module, $menu, $allowed, $overrides, $user, $owner, $access) {
                        if (!isset($allowed['child'][(int)$child->id])
                            || ($overrides['child'][(int)$child->id] ?? true) === false) return false;
                        if (!$user || $owner) return true;

                        return $access->contains(fn($permission) =>
                            (int)$permission->module_id === (int)$module->id
                            && (int)$permission->menu_id === (int)$menu->id
                            && ($permission->child_menu_id === null
                                || (int)$permission->child_menu_id === (int)$child->id));
                    })->values();
                    $menu->setRelation('children', $children);
                    return $children->isNotEmpty();
                })->values();
                $module->setRelation('menus', $menus);
                return $menus->isNotEmpty();
            })->values();
    }

    private function overrides(string $model, string $column, int $businessId, int $subscriptionId, $now): array
    {
        return $model::query()->where('business_id', $businessId)
            // New overrides are subscription independent. Old ones may be scoped.
            ->where(fn($q) => $q->whereNull('business_subscription_id')
                ->orWhere('business_subscription_id', $subscriptionId))
            ->where(fn($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', $now))
            ->orderBy('id')->pluck('is_enabled', $column)
            ->mapWithKeys(fn($value, $id) => [(int)$id => (bool)$value])->all();
    }

    public function canView(Business $business, BusinessUser $user, int $childId): bool
    {
        return $this->forBusiness($business, $user)->contains(fn($module) =>
            $module->menus->contains(fn($menu) => $menu->children->contains('id', $childId)));
    }

    public function canViewMenu(Business $business, BusinessUser $user, int $menuId): bool
    {
        return $this->forBusiness($business, $user)->contains(fn($module) =>
            $module->menus->contains('id', $menuId));
    }
}

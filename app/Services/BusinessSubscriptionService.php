<?php

namespace App\Services;

use App\Models\{
    Business, BusinessCategory, BusinessCategoryModule, BusinessCategoryMenu,
    BusinessCategoryChildMenu, BusinessSubscription,
    SubscriptionPlan, SubscriptionPlanModule, SubscriptionPlanMenu, SubscriptionPlanChildMenu, Module
};
use Illuminate\Support\Facades\DB;

/**
 * Basic is category-specific, free and non-expiring. Navigation reads
 * the current plan mappings directly; no per-business feature snapshot.
 */
class BusinessSubscriptionService
{
    private const TYPES = [
        'module' => [BusinessCategoryModule::class, SubscriptionPlanModule::class, 'module_id'],
        'menu' => [BusinessCategoryMenu::class, SubscriptionPlanMenu::class, 'menu_id'],
        'child' => [BusinessCategoryChildMenu::class, SubscriptionPlanChildMenu::class, 'child_menu_id'],
    ];

    public function syncBasicPlan(BusinessCategory $category): SubscriptionPlan
    {
        return DB::transaction(function () use ($category) {
            // Configurations is a mandatory common module for every category.
            // This also covers categories added after ModuleMenuSeeder was run.
            $configurations = Module::query()->where('slug', 'configurations')->first();
            if ($configurations) {
                BusinessCategoryModule::withTrashed()->updateOrCreate(
                    ['business_category_id' => $category->id, 'module_id' => $configurations->id],
                    ['deleted_at' => null, 'approval_status' => 'Approved', 'is_active' => true]
                );
                foreach ($configurations->menus as $menu) {
                    BusinessCategoryMenu::withTrashed()->updateOrCreate(
                        ['business_category_id' => $category->id, 'menu_id' => $menu->id],
                        ['deleted_at' => null, 'approval_status' => 'Approved', 'is_active' => true]
                    );
                }
            }

            $plan = SubscriptionPlan::withTrashed()->updateOrCreate(
                ['slug' => $category->slug . '-basic'],
                [
                    'business_category_id' => $category->id,
                    'name' => 'Basic',
                    'description' => 'Free Basic plan: all features assigned to this business category.',
                    'price' => 0,
                    'currency' => 'BDT',
                    'billing_cycle' => 'Monthly',
                    'billing_interval' => 1,
                    'trial_days' => 0,
                    'sort_order' => 1,
                    'is_active' => true,
                    'approval_status' => 'Approved',
                    'deleted_at' => null,
                ]
            );

            foreach (self::TYPES as [$categoryModel, $planModel, $column]) {
                $ids = $categoryModel::query()
                    ->where('business_category_id', $category->id)
                    ->pluck($column)->unique()->values()->all();

                // Remove only entries belonging to this Basic plan; do not touch paid plans.
                $stale = $planModel::withTrashed()
                    ->where('subscription_plan_id', $plan->id)
                    ->where('business_category_id', $category->id);
                if ($ids) $stale->whereNotIn($column, $ids);
                $stale->forceDelete();

                foreach ($ids as $id) {
                    $planModel::withTrashed()->updateOrCreate(
                        ['subscription_plan_id' => $plan->id, 'business_category_id' => $category->id, $column => $id],
                        ['is_enabled' => true, 'is_active' => true, 'approval_status' => 'Approved', 'deleted_at' => null]
                    );
                }
            }

            return $plan;
        });
    }

    public function activateBasic(Business $business): BusinessSubscription
    {
        return DB::transaction(function () use ($business) {
            $category = $business->category()->firstOrFail();
            $plan = SubscriptionPlan::query()
                ->where('business_category_id', $category->id)
                ->where('slug', $category->slug . '-basic')
                ->where('is_active', true)
                ->where('approval_status', 'Approved')
                ->first();

            if (!$plan) {
                // Covers newly added categories without requiring a manual re-seed.
                $plan = $this->syncBasicPlan($category);
            }

            // Avoid creating a second subscription if an activation is retried.
            $subscription = BusinessSubscription::firstOrCreate(
                ['business_id' => $business->id, 'plan_id' => $plan->id],
                [
                    'subscription_status' => 'active',
                    'starts_at' => now(),
                    'ends_at' => null,
                    'auto_renew' => false,
                    'notes' => 'Free Basic plan activated automatically on business registration.',
                    'approval_status' => 'Approved',
                ]
            );

            return $subscription;
        });
    }

}

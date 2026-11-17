<?php

namespace App\Services;

use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\BusinessCategoryModule;
use App\Models\BusinessCategoryMenu;
use App\Models\BusinessCategoryChildMenu;
use App\Models\BusinessModule;
use App\Models\BusinessMenu;
use App\Models\BusinessChildMenu;
use App\Models\BusinessSubscription;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionPlanModule;
use App\Models\SubscriptionPlanMenu;
use App\Models\SubscriptionPlanChildMenu;
use Illuminate\Support\Facades\DB;

class BusinessSubscriptionService
{
    // The category mappings are the sole source for the free Basic plan.
    private const FEATURES = [
        [BusinessCategoryModule::class, SubscriptionPlanModule::class, BusinessModule::class, 'module_id'],
        [BusinessCategoryMenu::class, SubscriptionPlanMenu::class, BusinessMenu::class, 'menu_id'],
        [BusinessCategoryChildMenu::class, SubscriptionPlanChildMenu::class, BusinessChildMenu::class, 'child_menu_id'],
    ];

    public function syncBasicPlan(BusinessCategory $category): SubscriptionPlan
    {
        return DB::transaction(function () use ($category) {
            $plan = SubscriptionPlan::withTrashed()->updateOrCreate(
                ['slug' => $category->slug . '-basic'],
                [
                    'business_category_id' => $category->id,
                    'name' => 'Basic',
                    'description' => 'Free Basic plan with all features mapped to this business category.',
                    'price' => 0,
                    'currency' => 'BDT',
                    'billing_cycle' => 'Monthly',
                    'billing_interval' => 1,
                    'trial_days' => 0,
                    'sort_order' => 1,
                    'is_active' => $category->is_active && $category->approval_status === 'Approved',
                    'approval_status' => 'Approved',
                    'deleted_at' => null,
                ]
            );

            foreach (self::FEATURES as [$categoryModel, $planModel, , $column]) {
                $ids = $categoryModel::query()
                    ->where('business_category_id', $category->id)
                    ->where('is_active', true)
                    ->pluck($column)->unique()->values()->all();

                $stale = $planModel::withTrashed()
                    ->where('subscription_plan_id', $plan->id);
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
            abort_unless($category->is_active && $category->approval_status === 'Approved', 422);
            // Always use current category access, even if the seeder ran earlier.
            $plan = $this->syncBasicPlan($category);

            $subscription = BusinessSubscription::firstOrCreate(
                ['business_id' => $business->id, 'plan_id' => $plan->id],
                [
                    'subscription_status' => 'active',
                    'starts_at' => now(),
                    'ends_at' => null,
                    'auto_renew' => false,
                    'notes' => 'Free Basic plan activated automatically on business registration.',
                    'is_active' => true,
                    'approval_status' => 'Approved',
                ]
            );

            foreach (self::FEATURES as [, $planModel, $businessModel, $column]) {
                $ids = $planModel::query()
                    ->where('subscription_plan_id', $plan->id)
                    ->where('business_category_id', $category->id)
                    ->where('is_enabled', true)
                    ->where('is_active', true)
                    ->where('approval_status', 'Approved')
                    ->pluck($column)->all();

                // Only clean records created from a plan; never erase manual overrides.
                $stale = $businessModel::withTrashed()
                    ->where('business_id', $business->id)->where('source', 'Plan');
                if ($ids) $stale->whereNotIn($column, $ids);
                $stale->forceDelete();

                foreach ($ids as $id) {
                    $record = $businessModel::withTrashed()->firstOrNew([
                        'business_id' => $business->id, $column => $id,
                    ]);
                    if ($record->exists && $record->source === 'Override') continue;
                    $record->fill([
                        'business_subscription_id' => $subscription->id,
                        'source' => 'Plan',
                        'is_enabled' => true,
                        'is_active' => true,
                        'approval_status' => 'Approved',
                        'starts_at' => $subscription->starts_at,
                        'ends_at' => null,
                        'deleted_at' => null,
                    ])->save();
                }
            }
            return $subscription;
        });
    }
}

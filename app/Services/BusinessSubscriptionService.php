<?php

namespace App\Services;

use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\BusinessCategoryChildMenu;
use App\Models\BusinessCategoryMenu;
use App\Models\BusinessCategoryModule;
use App\Models\BusinessSubscription;
use App\Models\Module;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionPlanChildMenu;
use App\Models\SubscriptionPlanMenu;
use App\Models\SubscriptionPlanModule;
use Illuminate\Support\Facades\DB;

use App\Models\BusinessModule;
use App\Models\BusinessMenu;
use App\Models\BusinessChildMenu;
class BusinessSubscriptionService
{
    private const TYPES = [
        'module' => [ BusinessCategoryModule::class, SubscriptionPlanModule::class, 'module_id' ],
        'menu' => [ BusinessCategoryMenu::class, SubscriptionPlanMenu::class, 'menu_id' ],
        'child' => [ BusinessCategoryChildMenu::class, SubscriptionPlanChildMenu::class, 'child_menu_id' ],
    ];

    public function syncBasicPlan(BusinessCategory $category): SubscriptionPlan
    {
        return DB::transaction(function () use ($category) {

            // Ensure Configurations module is assigned to the category.
            $configurations = Module::query()
                ->where('slug', 'configurations')
                ->first();

            if ($configurations) {

                BusinessCategoryModule::withTrashed()->updateOrCreate(
                    [
                        'business_category_id' => $category->id,
                        'module_id' => $configurations->id,
                    ],
                    [
                        'deleted_at' => null,
                        'approval_status' => 'Approved',
                        'is_active' => true,
                    ]
                );

                foreach ($configurations->menus as $menu) {

                    BusinessCategoryMenu::withTrashed()->updateOrCreate(
                        [
                            'business_category_id' => $category->id,
                            'menu_id' => $menu->id,
                        ],
                        [
                            'deleted_at' => null,
                            'approval_status' => 'Approved',
                            'is_active' => true,
                        ]
                    );

                    foreach ($menu->childMenus as $childMenu) {

                        BusinessCategoryChildMenu::withTrashed()->updateOrCreate(
                            [
                                'business_category_id' => $category->id,
                                'child_menu_id' => $childMenu->id,
                            ],
                            [
                                'deleted_at' => null,
                                'approval_status' => 'Approved',
                                'is_active' => true,
                            ]
                        );
                    }
                }
            }

            // Create or update Basic Plan.
            $plan = SubscriptionPlan::withTrashed()->updateOrCreate(
                [
                    'slug' => $category->slug . '-basic'
                ],
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

            // Sync Modules, Menus and Child Menus.
            foreach (self::TYPES as [$categoryModel, $planModel, $column]) {

                $ids = $categoryModel::query()
                    ->where('business_category_id', $category->id)
                    ->where('is_active', true)
                    ->where('approval_status', 'Approved')
                    ->pluck($column)
                    ->unique()
                    ->values()
                    ->all();

                // Remove stale entries belonging to this Basic plan only.
                $stale = $planModel::withTrashed()
                    ->where('subscription_plan_id', $plan->id)
                    ->where('business_category_id', $category->id);

                if (!empty($ids)) {
                    $stale->whereNotIn($column, $ids);
                }

                $stale->forceDelete();

                // Insert or update plan features.
                foreach ($ids as $id) {

                    $planModel::withTrashed()->updateOrCreate(
                        [
                            'subscription_plan_id' => $plan->id,
                            'business_category_id' => $category->id,
                            $column => $id,
                        ],
                        [
                            'is_enabled' => true,
                            'is_active' => true,
                            'approval_status' => 'Approved',
                            'deleted_at' => null,
                        ]
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

            if (!$plan) { $plan = $this->syncBasicPlan($category);}

            $businessSubscription = BusinessSubscription::firstOrCreate(
                [
                    'business_id' => $business->id,
                    'plan_id' => $plan->id,
                ],
                [
                    'subscription_status' => 'active',
                    'starts_at' => now(),
                    'ends_at' => null,
                    'auto_renew' => false,
                    'notes' => 'Free Basic plan activated automatically on business registration.',
                    'approval_status' => 'Approved',
                ]
            );


            // Sync Business Modules
            foreach (SubscriptionPlanModule::query()
                ->where('subscription_plan_id', $plan->id)
                ->where('is_enabled', true)
                ->get() as $item) {

                BusinessModule::updateOrCreate(
                    [
                        'business_id' => $business->id,
                        'module_id' => $item->module_id,
                    ],
                    [
                        'business_subscription_id' => $subscription->id,
                        'source' => 'Plan',
                        'is_enabled' => true,
                        'starts_at' => $subscription->starts_at,
                        'ends_at' => null,
                    ]
                );
            }

            // Sync Business Menus
            foreach (SubscriptionPlanMenu::query()
                ->where('subscription_plan_id', $plan->id)
                ->where('is_enabled', true)
                ->get() as $item) {

                BusinessMenu::updateOrCreate(
                    [
                        'business_id' => $business->id,
                        'menu_id' => $item->menu_id,
                    ],
                    [
                        'business_subscription_id' => $subscription->id,
                        'source' => 'Plan',
                        'is_enabled' => true,
                        'starts_at' => $subscription->starts_at,
                        'ends_at' => null,
                    ]
                );
            }

            // Sync Business Child Menus
            foreach (SubscriptionPlanChildMenu::query()
                ->where('subscription_plan_id', $plan->id)
                ->where('is_enabled', true)
                ->get() as $item) {

                BusinessChildMenu::updateOrCreate(
                    [
                        'business_id' => $business->id,
                        'child_menu_id' => $item->child_menu_id,
                    ],
                    [
                        'business_subscription_id' => $subscription->id,
                        'source' => 'Plan',
                        'is_enabled' => true,
                        'starts_at' => $subscription->starts_at,
                        'ends_at' => null,
                    ]
                );
            }

            return $businessSubscription;

        });
    }
}

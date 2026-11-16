<?php

namespace Database\Seeders;

use App\Models\BusinessCategory;
use App\Models\BusinessCategoryModule;
use App\Models\BusinessCategoryMenu;
use App\Models\BusinessCategoryChildMenu;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionPlanFeature;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $categories = BusinessCategory::where(
                'approval_status',
                'Approved'
            )->get();

            foreach ($categories as $category) {

                $plan = SubscriptionPlan::updateOrCreate(
                    [
                        'slug' => $category->slug . '-basic',
                    ],
                    [
                        'code' => getGenerateCode(SubscriptionPlan::class, 'code', 'SUBP' ),
                        'business_category_id' => $category->id,
                        'name' => 'Basic',
                        'description' => 'Free Basic Subscription Plan with all modules, menus and features enabled.',
                        'price' => 0.00,
                        'currency' => 'BDT',
                        'billing_cycle' => 'Monthly',
                        'billing_interval' => 1,
                        'trial_days' => 0,
                        'sort_order' => 1,
                    ]
                );

                $moduleIds = BusinessCategoryModule::where(
                    'business_category_id',
                    $category->id
                )->pluck('module_id')->unique();

                $menuIds = BusinessCategoryMenu::where(
                    'business_category_id',
                    $category->id
                )->pluck('menu_id')->unique();

                $childMenuIds = BusinessCategoryChildMenu::where(
                    'business_category_id',
                    $category->id
                )->pluck('child_menu_id')->unique();

                // Remove old plan features before syncing.
                SubscriptionPlanFeature::where(
                    'plan_id',
                    $plan->id
                )->delete();

                // Enable all assigned modules.
                foreach ($moduleIds as $moduleId) {
                    SubscriptionPlanFeature::create([
                        'code' => getGenerateCode(SubscriptionPlanFeature::class, 'code', 'SUBPF' ),
                        'plan_id' => $plan->id,
                        'module_id' => $moduleId,
                        'menu_id' => null,
                        'child_menu_id' => null,
                        'feature_key' => 'module:' . $moduleId,
                    ]);
                }

                // Enable all assigned menus.
                foreach ($menuIds as $menuId) {
                    SubscriptionPlanFeature::create([
                        'code' => getGenerateCode(SubscriptionPlanFeature::class, 'code', 'SUBPF' ),
                        'plan_id' => $plan->id,
                        'module_id' => null,
                        'menu_id' => $menuId,
                        'child_menu_id' => null,
                        'feature_key' => 'menu:' . $menuId,
                    ]);
                }

                // Enable all assigned child menus.
                foreach ($childMenuIds as $childMenuId) {
                    SubscriptionPlanFeature::create([
                        'code' => getGenerateCode(SubscriptionPlanFeature::class, 'code', 'SUBPF' ),
                        'plan_id' => $plan->id,
                        'module_id' => null,
                        'menu_id' => null,
                        'child_menu_id' => $childMenuId,
                        'feature_key' => 'child-menu:' . $childMenuId,
                    ]);
                }
            }
        });
    }
}

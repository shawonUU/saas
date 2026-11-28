<?php

use App\Models\BusinessCategoryChildMenu;
use App\Models\BusinessCategoryMenu;
use App\Models\BusinessCategoryModule;
use App\Models\ChildMenu;
use App\Models\Menu;
use App\Models\Module;
use App\Models\SubscriptionPlanChildMenu;
use App\Models\SubscriptionPlanMenu;
use App\Models\SubscriptionPlanModule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // This updates only navigation/entitlements; existing branding data is not moved or duplicated.
    public function up(): void
    {
        DB::transaction(function (): void {
            $settings = Module::where('slug', 'settings')->first();
            $ecommerce = Module::where('slug', 'ecommerce')->first();
            if (! $settings || ! $ecommerce) {
                return; // A fresh install's ModuleMenuSeeder registers everything.
            }
            $businessMenu = Menu::where('module_id', $settings->id)->where('slug', 'business')->first();
            $businessGeneral = $businessMenu ? ChildMenu::where('menu_id', $businessMenu->id)
                ->where('slug', 'general-settings')->first() : null;
            if (! $businessGeneral) {
                return;
            }

            $menu = Menu::withTrashed()->firstOrCreate(
                ['module_id' => $settings->id, 'slug' => 'ecommerce'],
                ['name' => 'E-commerce', 'name_bn' => 'ই-কমার্স', 'icon' => 'shopping-bag',
                    'sort_order' => 2, 'is_active' => true, 'approval_status' => 'Approved']
            );
            $child = ChildMenu::withTrashed()->firstOrCreate(
                ['menu_id' => $menu->id, 'slug' => 'general-settings'],
                ['name' => 'General Settings', 'name_bn' => 'সাধারণ সেটিংস',
                    'sort_order' => 0, 'is_active' => true, 'approval_status' => 'Approved']
            );

            // Do not grant a storefront setting to a category that lacks E-commerce.
            $settingsCategoryIds = BusinessCategoryModule::where('module_id', $settings->id)
                ->where('is_active', true)->where('approval_status', 'Approved')->pluck('business_category_id');
            $ecommerceCategoryIds = BusinessCategoryModule::where('module_id', $ecommerce->id)
                ->where('is_active', true)->where('approval_status', 'Approved')->pluck('business_category_id')->all();
            $businessCategoryIds = BusinessCategoryMenu::where('menu_id', $businessMenu->id)
                ->where('is_active', true)->where('approval_status', 'Approved')
                ->pluck('business_category_id')->all();
            $generalCategoryIds = BusinessCategoryChildMenu::where('child_menu_id', $businessGeneral->id)
                ->where('is_active', true)->where('approval_status', 'Approved')
                ->pluck('business_category_id')->all();

            foreach ($settingsCategoryIds->intersect($ecommerceCategoryIds)
                ->intersect($businessCategoryIds)->intersect($generalCategoryIds) as $categoryId) {
                $categoryId = (int) $categoryId;
                BusinessCategoryMenu::firstOrCreate(
                    ['business_category_id' => $categoryId, 'menu_id' => $menu->id],
                    ['is_active' => true, 'approval_status' => 'Approved']
                );
                BusinessCategoryChildMenu::firstOrCreate(
                    ['business_category_id' => $categoryId, 'child_menu_id' => $child->id],
                    ['is_active' => true, 'approval_status' => 'Approved']
                );

                // Honor existing plan restrictions: grant only plans that already
                // include Business Settings and E-commerce, never override disabled plans.
                $settingsPlans = SubscriptionPlanMenu::where('business_category_id', $categoryId)
                    ->where('menu_id', $businessMenu->id)->where('is_enabled', true)
                    ->where('is_active', true)->where('approval_status', 'Approved')
                    ->pluck('subscription_plan_id');
                $generalPlans = SubscriptionPlanChildMenu::where('business_category_id', $categoryId)
                    ->where('child_menu_id', $businessGeneral->id)->where('is_enabled', true)
                    ->where('is_active', true)->where('approval_status', 'Approved')
                    ->pluck('subscription_plan_id')->all();
                $ecommercePlans = SubscriptionPlanModule::where('business_category_id', $categoryId)
                    ->where('module_id', $ecommerce->id)->where('is_enabled', true)
                    ->where('is_active', true)->where('approval_status', 'Approved')
                    ->pluck('subscription_plan_id')->all();

                foreach ($settingsPlans->intersect($generalPlans)->intersect($ecommercePlans) as $planId) {
                    SubscriptionPlanMenu::withTrashed()->firstOrCreate(
                        ['subscription_plan_id' => (int) $planId, 'business_category_id' => $categoryId, 'menu_id' => $menu->id],
                        ['is_enabled' => true, 'is_active' => true, 'approval_status' => 'Approved']
                    );
                    SubscriptionPlanChildMenu::withTrashed()->firstOrCreate(
                        ['subscription_plan_id' => (int) $planId, 'business_category_id' => $categoryId, 'child_menu_id' => $child->id],
                        ['is_enabled' => true, 'is_active' => true, 'approval_status' => 'Approved']
                    );
                }
            }
        });
    }

    public function down(): void
    {
        // The menu may already have tenant assignments or administrator changes.
        // Do not delete shared navigation/plan data during rollback.
    }
};

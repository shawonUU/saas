<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $module = DB::table('modules')->where('slug', 'settings')->first();
        if (! $module) return;
        $menu = DB::table('menus')->where('module_id', $module->id)->where('slug', 'business')->first();
        if (! $menu) return;
        $child = DB::table('child_menus')->where('menu_id', $menu->id)->where('slug', 'business-date')->first();
        if (! $child) {
            $id = DB::table('child_menus')->insertGetId([
                'menu_id' => $menu->id, 'slug' => 'business-date', 'name' => 'Business Date',
                'name_bn' => 'বিজনেস তারিখ', 'sort_order' => 2,
                'code' => 'SET-BUS-DATE', 'is_active' => true, 'approval_status' => 'Approved',
                'created_at' => now(), 'updated_at' => now(),
            ]);
        } else $id = $child->id;
        // Existing business/category/subscription gates stay authoritative.
        $categories = DB::table('business_category_menus')->where('menu_id', $menu->id)->pluck('business_category_id');
        foreach ($categories as $categoryId) {
            DB::table('business_category_child_menus')->updateOrInsert(
                ['business_category_id' => $categoryId, 'child_menu_id' => $id],
                ['code' => 'BD-CAT-'.$categoryId, 'is_active' => true, 'approval_status' => 'Approved', 'updated_at' => now()]
            );
        }
        $plans = DB::table('subscription_plan_menus')->where('menu_id', $menu->id)
            ->where('is_enabled', true)->where('is_active', true)->where('approval_status', 'Approved')
            ->select('subscription_plan_id', 'business_category_id')->get();
        foreach ($plans as $plan) {
            if (! $categories->contains($plan->business_category_id)) continue;
            DB::table('subscription_plan_child_menus')->updateOrInsert([
                'subscription_plan_id' => $plan->subscription_plan_id,
                'business_category_id' => $plan->business_category_id, 'child_menu_id' => $id,
            ], [
                'code' => 'BD-PLAN-'.$plan->subscription_plan_id.'-'.$plan->business_category_id,
                'is_active' => true, 'is_enabled' => true,
                'approval_status' => 'Approved', 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Navigation history is intentionally retained. Roll back code only if needed.
    }
};

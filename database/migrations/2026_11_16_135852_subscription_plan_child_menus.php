<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plan_child_menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_plan_id')->constrained('subscription_plans')->cascadeOnDelete();
            $table->foreignId('business_category_id')->constrained('business_categories')->cascadeOnDelete();
            $table->foreignId('child_menu_id')->constrained('child_menus')->cascadeOnDelete();
            $table->boolean('is_enabled')->default(true);
            $table->unique(['subscription_plan_id', 'business_category_id', 'child_menu_id'], 'sp_child_unique');
            $table->erpCommonColumns();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plan_child_menus');
    }
};

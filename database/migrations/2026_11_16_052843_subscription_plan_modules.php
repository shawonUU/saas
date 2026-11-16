<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plan_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_plan_id')->constrained('subscription_plans')->cascadeOnDelete();
            $table->foreignId('business_category_id')->constrained('business_categories')->cascadeOnDelete();
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
            $table->boolean('is_enabled')->default(true);
            $table->unique(['subscription_plan_id', 'business_category_id', 'module_id'], 'sp_mod_unique');
            $table->erpCommonColumns();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plan_modules');
    }
};

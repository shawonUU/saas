<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscription_plan_features', function (Blueprint $table) {
            $table->id();

            $table->foreignId('plan_id')
                ->constrained('subscription_plans')
                ->cascadeOnDelete();

            $table->foreignId('module_id')
                ->nullable()
                ->constrained('modules')
                ->cascadeOnDelete();

            $table->foreignId('menu_id')
                ->nullable()
                ->constrained('menus')
                ->cascadeOnDelete();

            $table->foreignId('child_menu_id')
                ->nullable()
                ->constrained('child_menus')
                ->cascadeOnDelete();

            $table->string('feature_key');

            $table->unique(
                ['plan_id', 'feature_key'],
                'spf_plan_feature_unique'
            );

            $table->erpCommonColumns();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};

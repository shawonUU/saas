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
        Schema::create('business_subscription_features', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_subscription_id')
                ->constrained('business_subscriptions')
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

            $table->string('source')->default('plan');
            $table->boolean('is_enabled')->default(true);

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->text('notes')->nullable();

            $table->unique(
                ['business_subscription_id', 'feature_key'],
                'bsf_subscription_feature_unique'
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

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
        Schema::create('business_child_menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_subscription_id')
                ->constrained('business_subscriptions')
                ->cascadeOnDelete();

            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignId('child_menu_id')->constrained('child_menus')->cascadeOnDelete();

            $table->enum('source', ['Plan', 'Override'])->default('Plan');
            $table->boolean('is_enabled')->default(true);

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->text('notes')->nullable();
            $table->unique(['business_subscription_id', 'business_id', 'child_menu_id'], 'bcm_unique');
            $table->erpCommonColumns();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_child_menus');
    }
};

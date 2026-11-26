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
        if (Schema::hasTable('restaurant_productions')) {
            return;
        }

        Schema::create('restaurant_productions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('business_id')->index();
            $table->unsignedBigInteger('food_variant_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->decimal('quantity', 18, 3);
            $table->decimal('total_cost', 18, 2);
            $table->json('ingredients');
            $table->uuid('request_key');
            $table->text('notes')->nullable();
            $table->timestamp('posted_at');
            $table->erpCommonColumns();
            $table->unique(['business_id', 'request_key'], 'productions_request_unique');
            $table->index(['business_id', 'food_variant_id', 'posted_at'], 'productions_food_date_idx');
            $table->index(['business_id', 'warehouse_id'], 'productions_warehouse_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Posted production records are referenced by the shared stock ledger.
    }
};

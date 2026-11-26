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
        Schema::table('order_items', function (Blueprint $table): void {
            $table->decimal('cost', 22, 5)->change();

            if (! Schema::hasColumn('order_items', 'kitchen_status')) {
                $table->string('kitchen_status', 20)->nullable();
            }
            if (! Schema::hasColumn('order_items', 'kitchen_station_id')) {
                $table->unsignedBigInteger('kitchen_station_id')->nullable();
            }
            if (! Schema::hasColumn('order_items', 'kitchen_batch')) {
                $table->unsignedInteger('kitchen_batch')->default(0);
            }
            if (! Schema::hasColumn('order_items', 'kitchen_note')) {
                $table->text('kitchen_note')->nullable();
            }
            foreach (['sent_at', 'preparing_at', 'ready_at', 'served_at'] as $column) {
                if (! Schema::hasColumn('order_items', $column)) {
                    $table->timestamp($column)->nullable();
                }
            }
            if (! Schema::hasColumn('order_items', 'void_reason')) {
                $table->string('void_reason', 500)->nullable();
            }
            foreach (['modifier_snapshot', 'recipe_snapshot'] as $column) {
                if (! Schema::hasColumn('order_items', $column)) {
                    $table->json($column)->nullable();
                }
            }
            if (! Schema::hasColumn('order_items', 'base_price')) {
                $table->decimal('base_price', 18, 2)->nullable();
            }
            if (! Schema::hasIndex('order_items', 'order_items_kitchen_idx')) {
                $table->index(['business_id', 'order_id', 'kitchen_status'], 'order_items_kitchen_idx');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kitchen history and price snapshots must survive an application rollback.
    }
};

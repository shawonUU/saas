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
        if (Schema::hasTable('restaurant_wastages')) {
            return;
        }

        Schema::create('restaurant_wastages', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('business_id')->index();
            $table->unsignedBigInteger('variant_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->decimal('quantity', 18, 3);
            $table->decimal('unit_cost', 18, 2);
            $table->text('reason');
            $table->uuid('request_key');
            $table->timestamp('posted_at');
            $table->erpCommonColumns();
            $table->unique(['business_id', 'request_key'], 'wastages_request_unique');
            $table->index(['business_id', 'variant_id', 'posted_at'], 'wastages_variant_date_idx');
            $table->index(['business_id', 'warehouse_id'], 'wastages_warehouse_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Posted wastage records are referenced by shared inventory and journals.
    }
};

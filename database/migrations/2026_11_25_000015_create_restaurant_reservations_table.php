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
        if (Schema::hasTable('restaurant_reservations')) {
            return;
        }

        Schema::create('restaurant_reservations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('business_id')->index();
            $table->unsignedBigInteger('dining_table_id');
            $table->string('customer_name');
            $table->string('customer_phone', 40)->nullable();
            $table->unsignedSmallInteger('guest_count')->default(1);
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->string('status', 20)->default('confirmed');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->uuid('request_key');
            $table->erpCommonColumns();
            $table->unique(['business_id', 'request_key'], 'reservations_request_unique');
            $table->index(['business_id', 'dining_table_id', 'starts_at', 'ends_at'], 'reservations_table_time_idx');
            $table->index(['business_id', 'order_id'], 'reservations_order_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reservation history must survive an application rollback.
    }
};

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
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('channel', 24)->default('admin')->change();
            $table->string('status', 24)->default('confirmed')->change();

            if (! Schema::hasColumn('orders', 'posted_at')) {
                $table->timestamp('posted_at')->nullable();
            }

            if (! Schema::hasColumn('orders', 'service_type')) {
                $table->string('service_type', 20)->nullable();
            }
            if (! Schema::hasColumn('orders', 'dining_table_id')) {
                $table->unsignedBigInteger('dining_table_id')->nullable();
            }
            if (! Schema::hasColumn('orders', 'guest_count')) {
                $table->unsignedSmallInteger('guest_count')->default(1);
            }
            if (! Schema::hasColumn('orders', 'restaurant_notes')) {
                $table->text('restaurant_notes')->nullable();
            }
            if (! Schema::hasColumn('orders', 'restaurant_version')) {
                $table->unsignedInteger('restaurant_version')->default(1);
            }
            if (! Schema::hasColumn('orders', 'restaurant_tax_id')) {
                $table->unsignedBigInteger('restaurant_tax_id')->nullable();
            }
            if (! Schema::hasColumn('orders', 'restaurant_closed_at')) {
                $table->timestamp('restaurant_closed_at')->nullable();
            }
            if (! Schema::hasColumn('orders', 'restaurant_tendered')) {
                $table->decimal('restaurant_tendered', 18, 2)->default(0);
            }
            if (! Schema::hasColumn('orders', 'restaurant_change')) {
                $table->decimal('restaurant_change', 18, 2)->default(0);
            }
            if (! Schema::hasIndex('orders', 'orders_restaurant_status_idx')) {
                $table->index(['business_id', 'channel', 'status'], 'orders_restaurant_status_idx');
            }
            if (! Schema::hasIndex('orders', 'orders_business_posted_idx')) {
                $table->index(['business_id', 'posted_at'], 'orders_business_posted_idx');
            }
            if (! Schema::hasIndex('orders', 'orders_restaurant_table_idx')) {
                $table->index(['business_id', 'dining_table_id', 'restaurant_closed_at'], 'orders_restaurant_table_idx');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Preserve existing restaurant orders when rolling application code back.
    }
};

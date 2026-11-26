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
        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'name_bn')) {
                $table->string('name_bn')->nullable();
            }
            if (! Schema::hasColumn('products', 'kitchen_station_id')) {
                $table->unsignedBigInteger('kitchen_station_id')->nullable();
            }
            if (! Schema::hasIndex('products', 'products_kitchen_station_idx')) {
                $table->index(['business_id', 'kitchen_station_id'], 'products_kitchen_station_idx');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Preserve translations and station assignments when rolling code back.
    }
};

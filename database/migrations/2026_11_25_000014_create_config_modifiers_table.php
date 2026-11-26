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
        if (Schema::hasTable('config_modifiers')) {
            return;
        }

        Schema::create('config_modifiers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('business_id')->index();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('name');
            $table->string('name_bn')->nullable();
            $table->decimal('price', 18, 2)->default(0);
            $table->erpCommonColumns();
            $table->index(['business_id', 'product_id', 'is_active'], 'modifiers_business_product_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Modifier definitions are retained to protect existing configuration.
    }
};

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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('business_category_id')->nullable();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->string('currency', 3)->default('BDT');
            $table->enum('billing_cycle', ['Monthly', 'Yearly'] )->default('Monthly');
            $table->unsignedSmallInteger('billing_interval')->default(1);
            $table->unsignedSmallInteger('trial_days')->default(0);
            $table->unsignedInteger('sort_order')->default(0);

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

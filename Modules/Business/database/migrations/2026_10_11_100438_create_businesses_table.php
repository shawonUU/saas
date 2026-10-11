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
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('business_categories')->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('country')->default('Bangladesh');
            $table->string('currency', 3)->default('BDT');
            $table->string('timezone')->default('Asia/Dhaka');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('status')->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};

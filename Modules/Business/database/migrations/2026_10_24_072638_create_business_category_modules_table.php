<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_category_modules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_category_id')
                ->constrained('business_categories')
                ->cascadeOnDelete();

            $table->foreignId('app_module_id')
                ->constrained('app_modules')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(
                ['business_category_id', 'app_module_id'],
                'cat_module_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_category_modules');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_menus', function (Blueprint $table) {
            $table->id();

            $table->foreignId('app_module_id')
                ->constrained('app_modules')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('name_bn');
            $table->string('slug');
            $table->string('icon')->nullable();
            $table->string('route')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['app_module_id', 'slug']);
            $table->index(['app_module_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_menus');
    }
};
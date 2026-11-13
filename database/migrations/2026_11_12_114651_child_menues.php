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
        Schema::create('child_menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id');
            $table->string('name');
            $table->string('slug');
            $table->string('route')->nullable();
            $table->string('permission')->nullable();
            $table->string('icon')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->unique(['menu_id', 'slug']);
            $table->index(['menu_id', 'sort_order',]);
            $table->erpCommonColumns();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('child_menus');
    }
};

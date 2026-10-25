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
    Schema::create('app_child_menus', function (Blueprint $table) {
        $table->id();

        $table->foreignId('app_menu_id')
            ->constrained('app_menus')
            ->cascadeOnDelete();

        $table->string('name');
        $table->string('slug');
        $table->string('icon')->nullable();
        $table->string('route')->nullable();
        $table->unsignedInteger('sort_order')->default(0);
        $table->boolean('is_active')->default(true);

        $table->timestamps();

        $table->unique(['app_menu_id', 'slug']);
        $table->index(['app_menu_id', 'sort_order']);
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_child_menus');
    }
};

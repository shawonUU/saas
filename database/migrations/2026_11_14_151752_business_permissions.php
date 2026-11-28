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
        Schema::create('business_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('key')->unique();
            $table->bigInteger('module_id')->nullable();
            $table->bigInteger('menu_id')->nullable();
            $table->bigInteger('child_menu_id')->nullable();
            $table->text('description')->nullable();
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

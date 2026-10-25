<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_member_permissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_member_id')
                ->constrained('business_members')
                ->cascadeOnDelete();

            $table->foreignId('app_module_id')
                ->constrained('app_modules')
                ->cascadeOnDelete();

            $table->boolean('can_view')->default(false);
            $table->boolean('can_create')->default(false);
            $table->boolean('can_update')->default(false);
            $table->boolean('can_delete')->default(false);

            $table->timestamps();

            $table->unique(
                ['business_member_id', 'app_module_id'],
                'member_module_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_member_permissions');
    }
};
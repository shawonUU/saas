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
        Schema::create('business_user_permissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id');
            $table->foreignId('user_id');
            $table->foreignId('permission_id');
            $table->erpCommonColumns();
            $table->unique([ 'user_id', 'permission_id', 'business_id', ],'unique_user_permission');
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

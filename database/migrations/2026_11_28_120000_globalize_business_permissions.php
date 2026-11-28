<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('business_permissions') || !Schema::hasColumn('business_permissions', 'business_id')) return;
        $indexes = collect(Schema::getIndexes('business_permissions'));
        if ($indexes->contains(fn ($index) => ($index['name'] ?? '') === 'unique_key_business')) {
            Schema::table('business_permissions', fn (Blueprint $table) => $table->dropUnique('unique_key_business'));
        }
        Schema::table('business_permissions', fn (Blueprint $table) => $table->dropColumn('business_id'));
    }

    public function down(): void
    {
        if (!Schema::hasTable('business_permissions') || Schema::hasColumn('business_permissions', 'business_id')) return;
        Schema::table('business_permissions', fn (Blueprint $table) => $table->unsignedBigInteger('business_id')->nullable());
        Schema::table('business_permissions', fn (Blueprint $table) => $table->unique(['key', 'business_id'], 'unique_key_business'));
        // Previous business_id values cannot be reconstructed by rollback; use a DB backup if needed.
    }
};

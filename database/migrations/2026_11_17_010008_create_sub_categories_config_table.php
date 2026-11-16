<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Existing installs may already have this table from the legacy combined migration.
        if (Schema::hasTable('config_sub_categories')) return;

        Schema::create('config_sub_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->string('name');
            $table->foreignId('category_id')->constrained('config_categories')->restrictOnDelete();
            $table->text('description')->nullable();
            $table->index(['business_id', 'category_id']);
            $table->erpCommonColumns();
            $table->index(['business_id', 'name']);
            $table->index(['business_id', 'is_active']);
        });
    }

    public function down(): void
    {
        // Intentionally non-destructive: an older migration may own this table and its data.
        // Drop only via an explicit, reviewed data-removal migration.
    }
};

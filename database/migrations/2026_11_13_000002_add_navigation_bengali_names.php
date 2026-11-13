<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        foreach (['business_categories','modules','menus','child_menus'] as $table) {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->string('name_bn')->nullable()->after('name'));
        }
    }
    public function down(): void {
        foreach (['business_categories','modules','menus','child_menus'] as $table) {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropColumn('name_bn'));
        }
    }
};

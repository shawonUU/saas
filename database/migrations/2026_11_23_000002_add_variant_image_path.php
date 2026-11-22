<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { if (Schema::hasTable('variants') && !Schema::hasColumn('variants','image_path')) Schema::table('variants',fn(Blueprint $t)=>$t->string('image_path')->nullable()); }
 public function down(): void { if (Schema::hasColumn('variants','image_path')) Schema::table('variants',fn(Blueprint $t)=>$t->dropColumn('image_path')); }
};

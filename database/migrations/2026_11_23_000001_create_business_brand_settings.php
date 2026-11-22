<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  if (!Schema::hasTable('business_brand_settings')) Schema::create('business_brand_settings', function(Blueprint $t){
   $t->id();$t->foreignId('business_id')->unique()->constrained('businesses')->cascadeOnDelete();
   $t->string('favicon')->nullable();$t->string('banner')->nullable();$t->string('primary_color',7)->default('#5145e5');
   $t->string('tagline',255)->nullable();$t->text('about')->nullable();$t->timestamps();
  });
 }
 public function down(): void { Schema::dropIfExists('business_brand_settings'); }
};

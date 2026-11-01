<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('retail_categories', function(Blueprint $t){$t->id();$t->foreignId('business_id')->index();$t->string('name');$t->string('name_bn')->nullable();$t->string('slug');$t->unsignedInteger('sort_order')->default(0);$t->boolean('is_active')->default(true);$t->timestamps();$t->unique(['business_id','slug']);});
  Schema::create('retail_products', function(Blueprint $t){$t->id();$t->foreignId('business_id')->index();$t->foreignId('category_id')->nullable()->index();$t->string('name');$t->string('name_bn')->nullable();$t->string('slug');$t->text('description')->nullable();$t->string('image_url')->nullable();$t->string('sku')->nullable();$t->decimal('price',14,2)->default(0);$t->unsignedInteger('stock')->default(0);$t->boolean('has_variants')->default(false);$t->boolean('is_active')->default(true);$t->timestamps();$t->unique(['business_id','slug']);});
  Schema::create('retail_product_variants', function(Blueprint $t){$t->id();$t->foreignId('business_id')->index();$t->foreignId('product_id')->index();$t->string('sku');$t->string('size')->nullable();$t->string('color')->nullable();$t->string('color_hex',7)->nullable();$t->decimal('price',14,2);$t->unsignedInteger('stock')->default(0);$t->timestamps();$t->unique(['business_id','sku']);});
 }
 public function down(): void {Schema::dropIfExists('retail_product_variants');Schema::dropIfExists('retail_products');Schema::dropIfExists('retail_categories');}
};

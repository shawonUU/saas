<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {Schema::create('recipe_items',function(Blueprint $t){$t->id();$t->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();$t->foreignId('food_variant_id')->constrained('variants');$t->foreignId('ingredient_variant_id')->constrained('variants');$t->decimal('quantity',16,3);$t->unique(['business_id','food_variant_id','ingredient_variant_id'],'recipe_item_unique');$t->erpCommonColumns();});}
 public function down():void {Schema::dropIfExists('recipe_items');}
};

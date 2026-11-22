<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  if(!Schema::hasColumn('products','compare_at_price')) Schema::table('products',function(Blueprint $t){$t->decimal('compare_at_price',16,2)->nullable()->after('price');});
 }
 public function down():void {
  if(Schema::hasColumn('products','compare_at_price')) Schema::table('products',fn(Blueprint $t)=>$t->dropColumn('compare_at_price'));
 }
};

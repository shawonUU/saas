<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  foreach(['products','variants'] as $table){
   Schema::table($table,function(Blueprint $t) use($table){$t->string('barcode',100)->nullable()->after('sku');$t->unique(['business_id','barcode'],$table.'_business_barcode_unique');});
  }
 }
 public function down(): void {
  foreach(['variants','products'] as $table){Schema::table($table,function(Blueprint $t) use($table){$t->dropUnique($table.'_business_barcode_unique');$t->dropColumn('barcode');});}
 }
};

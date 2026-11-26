<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {Schema::table('products',function(Blueprint $t){$t->boolean('is_ingredient')->default(false);$t->index(['business_id','is_ingredient']);});}
 public function down():void {Schema::table('products',function(Blueprint $t){$t->dropIndex(['business_id','is_ingredient']);$t->dropColumn('is_ingredient');});}
};

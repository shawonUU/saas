<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {if(Schema::hasTable('products')&&!Schema::hasColumn('products','sub_category_id'))Schema::table('products',fn(Blueprint $t)=>$t->unsignedBigInteger('sub_category_id')->nullable()->after('category_id')->index());}
 public function down():void {if(Schema::hasTable('products')&&Schema::hasColumn('products','sub_category_id'))Schema::table('products',fn(Blueprint $t)=>$t->dropColumn('sub_category_id'));}
};

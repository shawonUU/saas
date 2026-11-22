<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {if(Schema::hasTable('products')&&!Schema::hasColumn('products','specifications'))Schema::table('products',fn(Blueprint $t)=>$t->json('specifications')->nullable());}
 public function down():void {if(Schema::hasTable('products')&&Schema::hasColumn('products','specifications'))Schema::table('products',fn(Blueprint $t)=>$t->dropColumn('specifications'));}
};

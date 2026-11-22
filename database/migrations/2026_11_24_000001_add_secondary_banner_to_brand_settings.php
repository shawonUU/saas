<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {if(Schema::hasTable('business_brand_settings')&&!Schema::hasColumn('business_brand_settings','secondary_banner'))Schema::table('business_brand_settings',fn(Blueprint $t)=>$t->string('secondary_banner')->nullable()->after('banner'));}
 public function down():void {if(Schema::hasTable('business_brand_settings')&&Schema::hasColumn('business_brand_settings','secondary_banner'))Schema::table('business_brand_settings',fn(Blueprint $t)=>$t->dropColumn('secondary_banner'));}
};

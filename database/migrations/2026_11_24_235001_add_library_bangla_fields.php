<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 private const FIELDS = ['config_payment_methods'=>['name'],'config_suppliers'=>['name','address'],'config_categories'=>['name'],'config_shipping_methods'=>['name'],'config_taxes'=>['name'],'business_customers'=>['name','address'],'config_units'=>['name'],'config_sizes'=>['name'],'config_brands'=>['name'],'config_sub_categories'=>['name'],'config_colors'=>['name'],'config_currencies'=>['name'],'accounts'=>['name']];
 public function up():void {foreach(self::FIELDS as $table=>$fields){foreach($fields as $field){if(Schema::hasTable($table)&&!Schema::hasColumn($table,$field.'_bn'))Schema::table($table,function(Blueprint $t)use($field){if($field==='name')$t->string('name_bn')->nullable();else $t->text($field.'_bn')->nullable();});}}}
 public function down():void {foreach(self::FIELDS as $table=>$fields){foreach($fields as $field){if(Schema::hasTable($table)&&Schema::hasColumn($table,$field.'_bn'))Schema::table($table,fn(Blueprint $t)=>$t->dropColumn($field.'_bn'));}}}
};

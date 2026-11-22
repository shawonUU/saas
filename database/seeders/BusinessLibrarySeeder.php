<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
class BusinessLibrarySeeder extends Seeder {
 public function run():void {
  if(!Schema::hasTable('businesses'))return;
  $libraries=[
   'config_brands'=>[['UNBRANDED','Unbranded',[]]],
   'config_categories'=>[['GENERAL','General',[]]],
   'config_taxes'=>[['NO-TAX','No Tax',['rate'=>0,'calculation_type'=>'exclusive']]],
   'config_units'=>[['PCS','Piece',['symbol'=>'pcs']],['KG','Kilogram',['symbol'=>'kg']],['GM','Gram',['symbol'=>'g']],['LTR','Liter',['symbol'=>'L']],['BOX','Box',['symbol'=>'box']],['PACK','Pack',['symbol'=>'pack']]],
   'config_sizes'=>[['SMALL','Small',[]],['MEDIUM','Medium',[]],['LARGE','Large',[]],['XL','Extra Large',[]]],
   'config_colors'=>[['BLACK','Black',['hex_code'=>'#000000']],['WHITE','White',['hex_code'=>'#FFFFFF']],['BLUE','Blue',['hex_code'=>'#2563EB']],['RED','Red',['hex_code'=>'#DC2626']]],
   'config_currencies'=>[['BDT','Bangladeshi Taka',['currency_code'=>'BDT','symbol'=>'৳','exchange_rate'=>1,'is_default'=>1]]],
   'config_payment_methods'=>[['COD','Cash on Delivery',['method_type'=>'cash']],['CASH','Cash',['method_type'=>'cash']],['BANK','Bank Transfer',['method_type'=>'bank']]],
   'config_shipping_methods'=>[['STD','Standard Delivery',['charge'=>0,'estimated_days'=>3]]],
  ];
  foreach(DB::table('businesses')->select('id')->cursor() as $business){
   foreach($libraries as $table=>$rows){
    if(!Schema::hasTable($table))continue;
    foreach($rows as [$code,$name,$extra]){
     $values=array_merge(['name'=>$name,'is_active'=>1,'approval_status'=>'Approved'],$extra);
     // Never overwrite customized merchant records.
     // Schema macro code is globally unique across businesses.
     $tenantCode='B'.$business->id.'-'.$code;
     if(DB::table($table)->where('business_id',$business->id)->where('code',$tenantCode)->exists())continue;
     DB::table($table)->insert(array_merge(['business_id'=>$business->id,'code'=>$tenantCode,'created_at'=>now(),'updated_at'=>now()],$values));
    }
   }
  }
 }
}

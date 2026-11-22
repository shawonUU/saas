<?php
namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
class EcommerceDemoTaxonomySeeder {
 private function config(int $businessId,string $table,string $code,string $name,array $extra=[]):int {
  $query=DB::table($table)->where('business_id',$businessId)->where('name',$name);
  if($table==='config_sub_categories')$query->where('category_id',$extra['category_id']);
  $row=$query->first();if($row)return (int)$row->id;
  $row=DB::table($table)->where('code',$code)->first();if($row){
   if((int)$row->business_id!==$businessId)throw new \RuntimeException('Catalog seed code collision: '.$code);
   return (int)$row->id;
  }
  return (int)DB::table($table)->insertGetId(array_merge([
   'business_id'=>$businessId,'code'=>$code,'name'=>$name,'is_active'=>1,
   'approval_status'=>'Approved','created_at'=>now(),'updated_at'=>now(),
  ],$extra));
 }
 public function seed(int $businessId,array $catalog):array {
  $ref=['categories'=>[],'subcategories'=>[]];
  $ref['brand']=$this->config($businessId,'config_brands',"D{$businessId}-BRAND",'Demo Collection');
  $ref['unit']=$this->config($businessId,'config_units',"D{$businessId}-PCS",'Piece',['symbol'=>'pcs']);
  foreach(['Black'=>'#222222','White'=>'#FFFFFF','Pink'=>'#E98EAC','Blue'=>'#4777B9'] as $name=>$hex){
   $ref['colors'][$name]=$this->config($businessId,'config_colors',"D{$businessId}-COLOR-".strtoupper($name),$name,['hex_code'=>$hex]);
  }
  foreach(['Small','Medium','Large','XL'] as $name){
   $ref['sizes'][$name]=$this->config($businessId,'config_sizes',"D{$businessId}-SIZE-".strtoupper($name),$name);
  }
  foreach($catalog['categories'] as $i=>$category){
   $cid=$this->config($businessId,'config_categories',"D{$businessId}-CAT-".($i+1),$category['name'],['description'=>'Marketplace catalog category']);
   $ref['categories'][$i]=$cid;
   foreach($category['subcategories'] as $j=>$subcategory){
    $ref['subcategories'][$i][$j]=$this->config($businessId,'config_sub_categories',"D{$businessId}-SUB-".($i+1).'-'.($j+1),$subcategory['name'],['category_id'=>$cid]);
   }
  }
  return $ref;
 }
}

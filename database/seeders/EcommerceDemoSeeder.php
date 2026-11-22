<?php
namespace Database\Seeders;
use App\Models\Business;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
class EcommerceDemoSeeder extends Seeder {
 public function run():void {
  $id=(int)(getenv('ECOMMERCE_DEMO_BUSINESS_ID')?:0);
  if(!$id)throw new RuntimeException('Choose one business: php artisan ecommerce:seed-demo BUSINESS_ID (or set ECOMMERCE_DEMO_BUSINESS_ID).');
  $result=$this->seedBusiness($id);
  $this->command?->info(json_encode($result));
 }
 public function catalog():array {
  $file=database_path('seeders/data/ecommerce_demo/catalog.json');
  $data=json_decode(file_get_contents($file),true,512,JSON_THROW_ON_ERROR);
  if(!is_array($data['categories']??null))throw new RuntimeException('Invalid demo catalog manifest.');
  return $data;
 }
 public function seedBusiness(int $businessId):array {
  $business=Business::findOrFail($businessId);
  foreach(['config_categories','config_sub_categories','config_brands','config_units','config_sizes','config_colors','products','variants','warehouses','warehouse_stocks','stock_movements','accounts','journals','journal_lines'] as $table){
   if(!Schema::hasTable($table))throw new RuntimeException('Missing table '.$table.'. Run php artisan migrate --force first.');
  }
  foreach(['products'=>['sub_category_id','specifications','compare_at_price'],'variants'=>['image_path']] as $table=>$columns){
   foreach($columns as $column)if(!Schema::hasColumn($table,$column))throw new RuntimeException("Missing {$table}.{$column}. Apply marketplace V2 patch and run migrations.");
  }
  $catalog=$this->catalog();
  $media=app(EcommerceDemoMediaSeeder::class)->install($businessId);
  $refs=app(EcommerceDemoTaxonomySeeder::class)->seed($businessId,$catalog);
  return app(EcommerceDemoProductsSeeder::class)->seed($business,$catalog,$refs,$media)+[
   'business_id'=>$businessId,'categories_in_manifest'=>count($catalog['categories']),
   'subcategories_in_manifest'=>array_sum(array_map(fn($c)=>count($c['subcategories']),$catalog['categories'])),
   'image_files_in_bundle'=>count(glob(database_path('seeders/data/ecommerce_demo/images/*.jpg'))?:[]),
  ];
 }
}

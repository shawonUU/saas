<?php
namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
class EcommerceDemoMediaSeeder {
 private function assets():string {return database_path('seeders/data/ecommerce_demo');}
 public function install(int $businessId):string {
  $base='businesses/'.$businessId.'/products/demo';
  $source=$this->assets().'/images';
  if(!is_dir($source))throw new RuntimeException('Demo image assets are missing. Reapply the PHP installer.');
  $disk=Storage::disk('public');
  foreach(glob($source.'/*.jpg')?:[] as $file){
   $relative=$base.'/'.basename($file);
   if(!$disk->exists($relative)){
    $bytes=file_get_contents($file);
    if($bytes===false||!$disk->put($relative,$bytes))throw new RuntimeException('Cannot install demo image: '.$relative);
   }
  }
  if(Schema::hasTable('business_brand_settings')){
   $existing=DB::table('business_brand_settings')->where('business_id',$businessId)->first();
   $update=[];
   foreach(['banner'=>'banner-main.jpg','secondary_banner'=>'banner-side.jpg'] as $column=>$file){
    if(!Schema::hasColumn('business_brand_settings',$column))continue;
    if($existing && filled($existing->{$column}))continue;
    $relative='businesses/'.$businessId.'/demo-banners/'.$file;
    if(!$disk->exists($relative)){
     $bytes=file_get_contents($this->assets().'/'.$file);
     if($bytes===false||!$disk->put($relative,$bytes))throw new RuntimeException('Cannot install demo banner: '.$file);
    }
    $update[$column]=$relative;
   }
   if($update){
    if($existing)DB::table('business_brand_settings')->where('business_id',$businessId)->update($update+['updated_at'=>now()]);
    else DB::table('business_brand_settings')->insert($update+['business_id'=>$businessId,'primary_color'=>'#E82964','tagline'=>'Explore fresh finds every day','created_at'=>now(),'updated_at'=>now()]);
   }
  }
  return $base;
 }
}

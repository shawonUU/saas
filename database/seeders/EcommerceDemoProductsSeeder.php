<?php
namespace Database\Seeders;
use App\Models\Business;
use App\Models\Configurations\Product;
use App\Models\Configurations\Variant;
use App\Services\Public\Business\Admin\Inventory\Movements\Inventory;
use App\Services\Public\Business\Admin\Finance\Ledger\JournalEntries\Accounting;
use Illuminate\Support\Facades\DB;
class EcommerceDemoProductsSeeder {
 public function seed(Business $business,array $catalog,array $ids,string $base):array {
  $new=0;$skipped=0;$newVariants=0;
  foreach($catalog['categories'] as $i=>$category){
   foreach($category['subcategories'] as $j=>$subcategory){
    for($n=0;$n<2;$n++){
     $sku=sprintf('DEMO-C%02d-S%02d-%02d',$i+1,$j+1,$n+1);
     if(Product::where('business_id',$business->id)->where('sku',$sku)->withTrashed()->exists()){$skipped++;continue;}
     $price=round((float)$category['base_price']+$j*63+$n*175,2);
     $cost=round($price*0.48,2);
     $label=$subcategory['name'];
     $name=$label.' — '.($n===0?'Classic Edition':'Premium Collection');
     $image=$base.'/'.$subcategory['images'][$n];
     $sizeId=in_array($i,[0,3,4,5,11],true)?$ids['sizes'][$n?'Large':'Medium']:null;
     $colorId=$ids['colors'][$n?'Blue':'Black'];
     $stock=$n===0?24:16;
     DB::transaction(function()use($business,$ids,$i,$j,$n,$sku,$name,$image,$price,$cost,$sizeId,$colorId,$stock,&$newVariants){
      $product=Product::create([
       'business_id'=>$business->id,'category_id'=>$ids['categories'][$i],
       'sub_category_id'=>$ids['subcategories'][$i][$j],
       'brand_id'=>$ids['brand'],'unit_id'=>$ids['unit'],
       'name'=>$name,'sku'=>$sku,
       'description'=>'Sample product for NexaBiz storefront preview and testing only. Not genuine sellable inventory. Replace with your own photos, prices and stock before launch.',
       'specifications'=>json_encode([['name'=>'Collection','value'=>'Demo'],['name'=>'Type','value'=>'Sample product'],['name'=>'Availability','value'=>'Test inventory only']],JSON_UNESCAPED_UNICODE),
       'image_url'=>$image,'price'=>$price,'compare_at_price'=>round($price*1.22,2),
       'cost'=>$cost,'track_stock'=>true,'is_active'=>true,
      ]);
      // Give every Classic item two selectable variants; Premium items have one.
      $variantCount=$n===0?2:1;
      for($v=1;$v<=$variantCount;$v++){
       $variantPrice=$price+($v===2?95:0);
       $variantCost=$cost+($v===2?46:0);
       $variantSize=$v===1?$sizeId:($sizeId?$ids['sizes']['Large']:null);
       $variantColor=$v===1?$colorId:$ids['colors']['Pink'];
       $variantStock=$stock-($v===2?7:0);
       $variant=Variant::create([
        'business_id'=>$business->id,'product_id'=>$product->id,'sku'=>$sku.'-V'.$v,
        'size_id'=>$variantSize,'color_id'=>$variantColor,'price'=>$variantPrice,'cost'=>$variantCost,
        'stock'=>0,'image_path'=>$image,'is_active'=>true,
       ]);
       app(Inventory::class)->move($business,$variant,$variantStock,'opening',$variantCost,'opening',$variant->id,'Demo catalog opening stock');
       $amount=round($variantStock*$variantCost,2);
       app(Accounting::class)->post($business,'opening',$variant->id,'Demo catalog opening '.$sku.'-'.$v,[
        '1300'=>[$amount,0],'3000'=>[0,$amount],
       ]);
       $newVariants++;
      }
     });
     $new++;
    }
   }
  }
  return ['products_created'=>$new,'products_skipped'=>$skipped,'variants_created'=>$newVariants];
 }
}

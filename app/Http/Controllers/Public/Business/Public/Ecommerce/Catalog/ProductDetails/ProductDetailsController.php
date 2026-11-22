<?php
namespace App\Http\Controllers\Public\Business\Public\Ecommerce\Catalog\ProductDetails;
use App\Http\Controllers\Controller;
use App\Services\Public\Business\Public\Ecommerce\Catalog\Products\CatalogService;
use Illuminate\Http\Request;
class ProductDetailsController extends Controller {
 public function show(Request $r,CatalogService $catalog,string $business,int $product){
  $business=$r->attributes->get('current_business');
  $item=$catalog->query($business)->findOrFail($product);
  $related=$catalog->query($business)->where('id','!=',$item->id)->when($item->category_id,fn($q)=>$q->where('category_id',$item->category_id))->limit(10)->get();
  return view('public.business.public.ecommerce.catalog.show',[
   'business'=>$business,'item'=>$item,'related'=>$related,'categories'=>$catalog->categories($business),
   'soldCounts'=>$catalog->soldCounts($business,$related),
  ]);
 }
}

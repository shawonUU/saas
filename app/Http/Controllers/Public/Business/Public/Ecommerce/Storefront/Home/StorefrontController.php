<?php
namespace App\Http\Controllers\Public\Business\Public\Ecommerce\Storefront\Home;
use App\Http\Controllers\Controller;
use App\Services\Public\Business\Public\Ecommerce\Catalog\Products\CatalogService;
use Illuminate\Http\Request;
class StorefrontController extends Controller {
 public function index(Request $r,CatalogService $catalog){
  $business=$r->attributes->get('current_business');
  $categories=$catalog->categories($business);
  $products=$catalog->query($business)->latest('id')->limit(240)->get();
  $homeSections=$categories->map(function($category)use($products){
    return ['category'=>$category,'products'=>$products->where('category_id',$category->id)->take(8)];
   })->filter(fn($item)=>$item['products']->isNotEmpty())->take(12)->values();
  return view('public.business.public.home',compact('business','categories','products','homeSections')+['soldCounts'=>$catalog->soldCounts($business,$products)]);
 }
}

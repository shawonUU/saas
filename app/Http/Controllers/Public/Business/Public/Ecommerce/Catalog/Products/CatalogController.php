<?php
namespace App\Http\Controllers\Public\Business\Public\Ecommerce\Catalog\Products;
use App\Http\Controllers\Controller;
use App\Services\Public\Business\Public\Ecommerce\Catalog\Products\CatalogService;
use Illuminate\Http\Request;
class CatalogController extends Controller {
 public function index(Request $r,CatalogService $catalog){
  $business=$r->attributes->get('current_business');$categories=$catalog->categories($business);
  $products=$catalog->search($business,$r);
  return view('public.business.public.ecommerce.catalog.index',compact('business','categories','products')+['soldCounts'=>$catalog->soldCounts($business,$products->getCollection())]);
 }
}

<?php
namespace App\Http\Controllers\Public\Business\Public\Ecommerce\Checkout\Cart;
use App\Http\Controllers\Controller;
use App\Services\Public\Business\Public\Ecommerce\Catalog\Products\CatalogService;
use Illuminate\Http\Request;
class CartController extends Controller {
 public function index(Request $r,CatalogService $catalog){
  $business=$r->attributes->get('current_business');
  return view('public.business.public.ecommerce.checkout.cart',[ 'business'=>$business,
   'categories'=>$catalog->categories($business),'shippings'=>$catalog->shippings($business),'onlinePaymentEnabled'=>$catalog->onlinePayment($business) ]);
 }
}

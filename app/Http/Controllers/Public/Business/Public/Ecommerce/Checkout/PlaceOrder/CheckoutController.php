<?php
namespace App\Http\Controllers\Public\Business\Public\Ecommerce\Checkout\PlaceOrder;
use App\Http\Controllers\Controller;
use App\Services\Public\Business\Public\Ecommerce\Catalog\Products\CatalogService;
use Illuminate\Http\Request;
class CheckoutController extends Controller {
 public function index(Request $request,CatalogService $catalog){
  $business=$request->attributes->get('current_business');
  return view('public.business.public.ecommerce.checkout.place-order',[
   'business'=>$business,'categories'=>$catalog->categories($business),
   'shippings'=>$catalog->shippings($business),'onlinePaymentEnabled'=>$catalog->onlinePayment($business),
  ]);
 }
}

<?php
namespace App\Http\Controllers\Public\Business\Public\Ecommerce\Account\Wishlist;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Public\Business\Public\Ecommerce\Catalog\Products\CatalogService;
class WishlistController extends Controller {
 public function index(Request $r,CatalogService $catalog){return view('public.business.public.ecommerce.account.wishlist',['business'=>$r->attributes->get('current_business'),'categories'=>$catalog->categories($r->attributes->get('current_business'))]);}
}

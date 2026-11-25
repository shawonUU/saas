<?php
namespace App\Http\Controllers\Public\Business\Public\Ecommerce\Account\Dashboard;
use App\Http\Controllers\Controller;
use App\Services\Public\Business\Admin\Ecommerce\OnlineOrders\OnlineOrderList\OnlineOrderService;
use Illuminate\Http\Request;
class DashboardController extends Controller {
 public function index(Request $r,OnlineOrderService $service){
 $customer=$r->user('business_customer');$business=$r->attributes->get('current_business');if(!in_array($business->category?->slug,['retail','grocery','ecommerce','online-store'],true)) return view('portals.dashboard',['title'=>$business->name.' · Customer','user'=>$customer,'subtitle'=>t('My customer account','আমার কাস্টমার অ্যাকাউন্ট'),'logoutUrl'=>route('business.customer.logout'),'links'=>[[t('Store home','স্টোর হোম'),route('business.customer.home')]]]);
 $q=$service->customer($business,$customer->id);
 $stats=['orders'=>(clone $q)->count(),'processing'=>(clone $q)->where('status','confirmed')->whereIn('delivery_status',['pending','packed','shipped'])->count(),'delivered'=>(clone $q)->where('delivery_status','delivered')->where('status','confirmed')->count()];
 $purchaseStats=['delivered_total'=>(clone $q)->where('status','confirmed')->where('delivery_status','delivered')->sum('total'),'return_credit'=>(clone $q)->where('status','confirmed')->sum('credited')];$orders=$q->latest('id')->limit(5)->get();return view('public.business.public.ecommerce.account.dashboard',compact('customer','business','stats','orders','purchaseStats'));
 }
}

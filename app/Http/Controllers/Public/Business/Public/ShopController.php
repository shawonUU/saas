<?php
namespace App\Http\Controllers\Public\Business\Public;
use App\Http\Controllers\Controller;
use App\Models\Retail\{Product,Variant};
use App\Services\Retail\Sales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class ShopController extends Controller {
 public function index(Request $r){$b=$r->attributes->get('current_business');$query=Product::where('business_id',$b->id)->where('is_active',true)->whereHas('variants',fn($q)=>$q->where('is_active',true));
  if($q=trim((string)$r->query('q','')))$query->where('name','like','%'.mb_substr($q,0,100).'%');
  $products=$query->with(['variants'=>fn($q)=>$q->where('is_active',true)->with(['size','color'])])->latest()->paginate(24)->withQueryString();
  $shippings=DB::table('config_shipping_methods')->where('business_id',$b->id)->where('is_active',true)->orderBy('name')->get();
  $campaigns=\App\Models\Retail\Campaign::where('business_id',$b->id)->where('is_active',true)->where(fn($q)=>$q->whereNull('starts_at')->orWhere('starts_at','<=',now()))->where(fn($q)=>$q->whereNull('ends_at')->orWhere('ends_at','>=',now()))->get();
  $onlinePaymentEnabled=\App\Models\Retail\Integration::where('business_id',$b->id)->where('provider','sslcommerz')->where('is_active',true)->exists();
  return view('public.business.public.retail.shop',compact('b','products','shippings','campaigns','onlinePaymentEnabled'));}
 public function checkout(Request $r,Sales $sales){$b=$r->attributes->get('current_business');$d=$r->validate(['request_key'=>'required|uuid','customer_name'=>'required|string|max:255','customer_phone'=>'required|string|max:40','customer_email'=>'nullable|required_if:payment_choice,sslcommerz|email|max:255','payment_choice'=>'required|in:cod,sslcommerz','marketing_opt_in'=>'nullable|boolean','customer_address'=>'required|string|max:2000','coupon_code'=>'nullable|string|max:50','shipping_method_id'=>'nullable|integer',
   'items'=>'required|array|min:1|max:100','items.*.variant_id'=>'required|integer','items.*.quantity'=>'required|integer|min:1|max:999']);
  if($d['payment_choice']==='sslcommerz')app(\App\Services\Retail\SslCommerz::class)->integration($b);
  $order=$sales->create($b,$d,'online');
  if(!empty($d['request_key']))\App\Models\Retail\AbandonedCart::where('business_id',$b->id)->where('cart_key',$d['request_key'])->update(['status'=>'converted']);
  if($d['payment_choice']==='sslcommerz')return redirect()->to(\Illuminate\Support\Facades\URL::temporarySignedRoute('store.customer.gateway.start',now()->addMinutes(15),['business'=>$b->domain,'order'=>$order->order_no]));
  return redirect()->route('store.customer.shop.success',['order'=>$order->order_no])->with('success',t('Order placed successfully.','অর্ডার সফলভাবে গ্রহণ করা হয়েছে।'));}
 public function saveCart(Request $r){$b=$r->attributes->get('current_business');$d=$r->validate(['request_key'=>'required|uuid','marketing_opt_in'=>'accepted','customer_name'=>'nullable|string|max:255','customer_phone'=>'required|string|max:40','items'=>'required|array|min:1|max:100','items.*.variant_id'=>'required|integer','items.*.quantity'=>'required|integer|min:1|max:999']);$subtotal=0;$items=[];foreach($d['items'] as $line){$variant=\App\Models\Retail\Variant::where('business_id',$b->id)->where('is_active',true)->with('product')->findOrFail($line['variant_id']);if(!$variant->product?->is_active)abort(422);$subtotal+=round((float)$variant->price*(int)$line['quantity'],2);$items[]=['variant_id'=>$variant->id,'quantity'=>(int)$line['quantity']];}\App\Models\Retail\AbandonedCart::updateOrCreate(['business_id'=>$b->id,'cart_key'=>$d['request_key']],['customer_name'=>$d['customer_name']??null,'customer_phone'=>$d['customer_phone'],'items'=>json_encode($items),'subtotal'=>round($subtotal,2),'status'=>'abandoned']);return response()->json(['saved'=>true]);}
 public function success(Request $r,string $order){$b=$r->attributes->get('current_business');
  // Never expose another visitor's order details: confirmation only shows an order code.
  return view('public.business.public.retail.success',['b'=>$b,'orderNo'=>$order]);}
}

<?php
namespace App\Services\Retail;
use App\Models\Business;
use App\Models\Retail\{Order,Integration,GatewayPayment};
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
class SslCommerz {
 public function integration(Business $b):Integration {
  $i=Integration::where('business_id',$b->id)->where('provider','sslcommerz')->where('is_active',true)->first();
  if(!$i || empty($i->settings['store_id']) || empty($i->settings['store_password']))throw ValidationException::withMessages(['payment'=>'Online payment is not configured.']);return $i;
 }
 private function base(Integration $i):string{return !empty($i->settings['sandbox'])?'https://sandbox.sslcommerz.com':'https://securepay.sslcommerz.com';}
 public function start(Business $b,Order $order):string {
  $i=$this->integration($b);if(!filter_var($order->customer_email,FILTER_VALIDATE_EMAIL))throw ValidationException::withMessages(['payment'=>'A valid email is required for online payment.']);$amount=round((float)$order->total-(float)$order->paid-(float)$order->credited,2);
  if($amount<1)throw ValidationException::withMessages(['payment'=>'No payable balance or below minimum gateway amount.']);
  $tran='SAAS'.Str::upper(Str::random(22));
  $transaction=GatewayPayment::create(['business_id'=>$b->id,'order_id'=>$order->id,'tran_id'=>$tran,'status'=>'initiated','amount'=>$amount]);
  $callback=route('store.customer.gateway.callback',['business'=>$b->domain]);
  $response=Http::asForm()->timeout(18)->post($this->base($i).'/gwprocess/v4/api.php',[
   'store_id'=>$i->settings['store_id'],'store_passwd'=>$i->settings['store_password'],'total_amount'=>number_format($amount,2,'.',''),
   'currency'=>'BDT','tran_id'=>$tran,'success_url'=>$callback,'fail_url'=>$callback,'cancel_url'=>$callback,'ipn_url'=>$callback,
   'cus_name'=>$order->customer_name?:'Customer','cus_email'=>$order->customer_email,'cus_phone'=>$order->customer_phone?:'01000000000',
   'cus_add1'=>$order->customer_address?:'Bangladesh','cus_city'=>'Dhaka','cus_country'=>'Bangladesh',
   'shipping_method'=>'YES','ship_name'=>$order->customer_name?:'Customer','ship_add1'=>$order->customer_address?:'Bangladesh',
   'ship_city'=>'Dhaka','ship_country'=>'Bangladesh','product_name'=>'Order '.$order->order_no,'product_category'=>'Retail','product_profile'=>'general',
  ]);
  $url=$response->json('GatewayPageURL');
  if(!$response->successful() || !is_string($url) || !str_starts_with($url,'https://')){$transaction->update(['status'=>'init_failed','response'=>mb_substr($response->body(),0,2000)]);throw ValidationException::withMessages(['payment'=>'Payment gateway initialization failed. Try COD or contact the shop.']);}
  // Prevent redirection to arbitrary third-party URLs supplied by an upstream compromise.
  $host=parse_url($url,PHP_URL_HOST);if(!in_array($host,['sandbox.sslcommerz.com','securepay.sslcommerz.com'],true))throw ValidationException::withMessages(['payment'=>'Invalid payment gateway redirect host.']);
  return $url;
 }
 public function confirm(Business $b,string $tran,string $valId):bool {
  $i=$this->integration($b);
  $verified=Http::timeout(18)->get($this->base($i).'/validator/api/validationserverAPI.php',['val_id'=>$valId,'store_id'=>$i->settings['store_id'],'store_passwd'=>$i->settings['store_password'],'format'=>'json','v'=>'1']);
  if(!$verified->successful())return false;
  $data=$verified->json();if(!is_array($data)||!in_array($data['status']??null,['VALID','VALIDATED'],true)||($data['tran_id']??null)!==$tran||($data['currency']??null)!=='BDT'||(string)($data['risk_level']??'0')!=='0')return false;
  return DB::transaction(function()use($b,$tran,$valId,$data){$gp=GatewayPayment::where('business_id',$b->id)->where('tran_id',$tran)->lockForUpdate()->firstOrFail();if($gp->status==='paid')return true;
   if(!in_array($gp->status,['initiated','init_failed'],true)||abs((float)$data['amount']-(float)$gp->amount)>.01)return false;
   $order=Order::where('business_id',$b->id)->lockForUpdate()->findOrFail($gp->order_id);
   if(round((float)$order->total-(float)$order->paid-(float)$order->credited,2)<(float)$gp->amount-.01)return false;
   app(Payments::class)->gatewayOrder($b,$order,(float)$gp->amount,$valId);
   $gp->update(['status'=>'paid','val_id'=>$valId,'response'=>json_encode(['status'=>$data['status'],'bank_tran_id'=>$data['bank_tran_id']??null])]);return true;
  });
 }
}

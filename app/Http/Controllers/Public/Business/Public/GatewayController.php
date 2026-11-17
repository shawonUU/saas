<?php
namespace App\Http\Controllers\Public\Business\Public;
use App\Http\Controllers\Controller;
use App\Models\Retail\{Order,GatewayPayment};
use App\Services\Retail\SslCommerz;
use Illuminate\Http\Request;
class GatewayController extends Controller {
 public function start(Request $r,string $order,SslCommerz $api){$b=$r->attributes->get('current_business');$o=Order::where('business_id',$b->id)->where('channel','online')->where('order_no',$order)->firstOrFail();return redirect()->away($api->start($b,$o));}
 public function callback(Request $r,SslCommerz $api){$b=$r->attributes->get('current_business');$tran=(string)$r->input('tran_id','');$val=(string)$r->input('val_id','');$gp=GatewayPayment::where('business_id',$b->id)->where('tran_id',$tran)->first();if(!$gp||!$val||strlen($val)>150)return response('Unable to verify transaction',422);$valid=$api->confirm($b,$tran,$val);if(!$valid)return response('Payment not verified. Please contact support with your transaction ID.',422);$order=Order::where('business_id',$b->id)->findOrFail($gp->order_id);return redirect()->route('store.customer.shop.success',['business'=>$b->domain,'order'=>$order->order_no])->with('success',t('Payment verified.','পেমেন্ট যাচাই হয়েছে।'));}
}

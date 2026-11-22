<?php
namespace App\Http\Controllers\Public\Business\Public\Ecommerce\Checkout\PlaceOrder;
use App\Http\Controllers\Controller;
use App\Models\Configurations\Variant;
use App\Models\Sales\Discount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class QuoteController extends Controller {
 public function quote(Request $request){
  $business=$request->attributes->get('current_business');
  $data=$request->validate(['items'=>'required|array|min:1|max:100','items.*.variant_id'=>'required|integer','items.*.quantity'=>'required|integer|min:1|max:999','shipping_method_id'=>'nullable|integer','coupon_code'=>'nullable|string|max:50']);
  $subtotal=0;
  foreach($data['items'] as $line){
   $v=Variant::where('business_id',$business->id)->where('is_active',true)->with('product')->findOrFail($line['variant_id']);
   if(!$v->product||!$v->product->is_active||($v->product->track_stock&&(float)$v->stock<(int)$line['quantity']))throw ValidationException::withMessages(['items'=>'Product unavailable or stock insufficient.']);
   $subtotal+=round((float)$v->price*(int)$line['quantity'],2);
  }
  $subtotal=round($subtotal,2);$shipping=0;
  if(!empty($data['shipping_method_id'])){
   $method=DB::table('config_shipping_methods')->where('business_id',$business->id)->where('is_active',true)->find($data['shipping_method_id']);
   if(!$method)throw ValidationException::withMessages(['shipping_method_id'=>'Invalid delivery method.']);
   $shipping=round((float)$method->charge,2);
  }
  $discount=0;$code=strtoupper(trim((string)($data['coupon_code']??'')));
  if($code!==''){
   $coupon=Discount::where('business_id',$business->id)->where('code',$code)->where('is_active',true)->first();
   if(!$coupon||($coupon->starts_at&&now()->lt($coupon->starts_at))||($coupon->ends_at&&now()->gt($coupon->ends_at)))throw ValidationException::withMessages(['coupon_code'=>'Coupon is invalid or expired.']);
   $discount=$coupon->type==='percent'?round($subtotal*min((float)$coupon->value,100)/100,2):(float)$coupon->value;
  }
  $discount=max(0,min($subtotal,$discount));
  return response()->json(['subtotal'=>$subtotal,'discount'=>$discount,'shipping'=>$shipping,'total'=>round($subtotal-$discount+$shipping,2)]);
 }
}

<?php
namespace App\Services\Public\Business\Admin\Finance;
use App\Services\Public\Business\Admin\Finance\Ledger\JournalEntries\Accounting;
use App\Models\Accounts\Payment;
use App\Models\Business;
use App\Models\Purchase\Purchase;
use App\Models\Purchase\PurchaseReturn;
use App\Models\Sales\Order;
use App\Models\Sales\SaleReturn;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Payments {
 private function validateAmount(float $amount,string $method):void {
  if(!in_array($method,['cash','bank'],true) || $amount<=0)throw ValidationException::withMessages(['amount'=>'Select a method and enter a positive amount.']);
 }
 private function record(Business $business,string $type,int $id,string $method,string $direction,float $amount,string $ref):Payment {
  return Payment::create(['business_id'=>$business->id,'reference_type'=>$type,'reference_id'=>$id,'method'=>$method,'direction'=>$direction,'amount'=>$amount,'reference_no'=>$ref?:null]);
 }
 public function payOrder(Business $business,Order $order,float $amount,string $method,string $ref=''):void {
  $this->validateAmount($amount,$method);$order=Order::where('business_id',$business->id)->lockForUpdate()->findOrFail($order->id);
  $amount=round($amount,2);if($amount>round((float)$order->total-(float)$order->paid-(float)$order->credited,2)+.00001)throw ValidationException::withMessages(['amount'=>'Amount exceeds order due.']);
  $payment=$this->record($business,'order',$order->id,$method,'in',$amount,$ref);$order->increment('paid',$amount);
  app(Accounting::class)->post($business,'payment',$payment->id,'Customer payment '.$order->order_no,[($method==='bank'?'1100':'1000')=>[$amount,0],'1200'=>[0,$amount]]);
 }
 public function gatewayOrder(Business $business,Order $order,float $amount,string $providerReference):void {
  $order=Order::where('business_id',$business->id)->lockForUpdate()->findOrFail($order->id);
  $amount=round($amount,2);if($amount<=0 || $amount>round((float)$order->total-(float)$order->paid-(float)$order->credited,2)+.00001)throw ValidationException::withMessages(['payment'=>'Gateway amount exceeds order due.']);
  $payment=$this->record($business,'order',$order->id,'bank','in',$amount,'SSL:'.$providerReference);
  $order->increment('paid',$amount);
  app(Accounting::class)->post($business,'payment',$payment->id,'Gateway validated (awaiting settlement) '.$order->order_no,['1150'=>[$amount,0],'1200'=>[0,$amount]]);
 }
 public function payPurchase(Business $business,Purchase $purchase,float $amount,string $method,string $ref=''):void {
  $this->validateAmount($amount,$method);$purchase=Purchase::where('business_id',$business->id)->lockForUpdate()->findOrFail($purchase->id);
  $amount=round($amount,2);if($amount>round((float)$purchase->total-(float)$purchase->paid-(float)PurchaseReturn::where('business_id',$business->id)->where('purchase_id',$purchase->id)->sum('ap_credit'),2)+.00001)throw ValidationException::withMessages(['amount'=>'Amount exceeds supplier due.']);
  $payment=$this->record($business,'purchase',$purchase->id,$method,'out',$amount,$ref);$purchase->increment('paid',$amount);
  app(Accounting::class)->post($business,'payment',$payment->id,'Supplier payment '.$purchase->purchase_no,['2000'=>[$amount,0],($method==='bank'?'1100':'1000')=>[0,$amount]]);
 }
 public function refund(Business $business,SaleReturn $return,float $amount,string $method,string $ref=''):void {
  $this->validateAmount($amount,$method);$return=SaleReturn::where('business_id',$business->id)->lockForUpdate()->findOrFail($return->id);
  $amount=round($amount,2);
  $order=Order::where('business_id',$business->id)->lockForUpdate()->findOrFail($return->order_id);
  $totalRefunded=SaleReturn::where('business_id',$business->id)->where('order_id',$order->id)->sum('refunded');
  $refundable=max(0,round((float)$order->paid-(float)$totalRefunded-max(0,(float)$order->total-(float)$order->credited),2));
  if($amount>round((float)$return->amount-(float)$return->refunded,2)+.00001 || $amount>$refundable+.00001)
   throw ValidationException::withMessages(['amount'=>'Refund exceeds return credit or the paid amount after remaining customer dues.']);
  $payment=$this->record($business,'return',$return->id,$method,'out',$amount,$ref);$return->increment('refunded',$amount);
  app(Accounting::class)->post($business,'payment',$payment->id,'Customer refund',[ '1200'=>[$amount,0],($method==='bank'?'1100':'1000')=>[0,$amount]]);
 }
}

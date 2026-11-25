<?php
namespace App\Services\Public\Business\Admin\Ecommerce\OnlineOrders\OnlineOrderList;
use App\Services\Public\Business\Admin\Sales\Orders\OrderDetails\CancelSale;
use App\Models\Business;
use App\Models\Sales\Order;
use App\Models\Ecommerce\CancellationRequest;
use App\Models\Retail\CourierShipment;
use Illuminate\Support\Facades\DB;
class CancellationRequestService {
 public function eligible(Order $o):bool {return $o->status==='confirmed' && (float)$o->paid<=0 && (float)$o->credited<=0 && $o->delivery_status==='pending' && !CourierShipment::where('business_id',$o->business_id)->where('order_id',$o->id)->exists();}
 public function submit(Business $b,int $customer,int $id,string $reason):void {DB::transaction(function()use($b,$customer,$id,$reason){$o=Order::where('business_id',$b->id)->where('customer_id',$customer)->where('channel','online')->lockForUpdate()->findOrFail($id);abort_unless($this->eligible($o),422,t('This order cannot be cancelled online.','এই অর্ডার অনলাইনে বাতিল করা যাবে না।'));abort_if(CancellationRequest::where('business_id',$b->id)->where('order_id',$id)->where('status','pending')->exists(),422,t('Request already pending.','অনুরোধ অপেক্ষমাণ আছে।'));CancellationRequest::create(['business_id'=>$b->id,'customer_id'=>$customer,'order_id'=>$id,'reason'=>$reason]);});}
 public function review(Business $b,int $id,int $admin,array $data,CancelSale $cancel):void {DB::transaction(function()use($b,$id,$admin,$data,$cancel){$request=CancellationRequest::where('business_id',$b->id)->findOrFail($id);$o=Order::where('business_id',$b->id)->where('channel','online')->where('customer_id',$request->customer_id)->lockForUpdate()->findOrFail($request->order_id);$request=CancellationRequest::where('business_id',$b->id)->lockForUpdate()->findOrFail($id);abort_unless($request->status==='pending',422);if($data['decision']==='approved'){abort_unless($this->eligible($o),422,t('Order no longer eligible.','অর্ডারটি এখন বাতিলযোগ্য নয়।'));$cancel->cancel($b,$o);}$request->update(['status'=>$data['decision'],'admin_note'=>$data['admin_note']??null,'reviewed_by'=>$admin,'reviewed_at'=>now()]);});}
}

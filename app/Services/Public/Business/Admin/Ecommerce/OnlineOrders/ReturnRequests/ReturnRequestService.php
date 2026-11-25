<?php
namespace App\Services\Public\Business\Admin\Ecommerce\OnlineOrders\ReturnRequests;
use App\Models\Ecommerce\ReturnRequest;
use App\Models\Sales\Order;
use App\Models\Business;
use App\Services\Public\Business\Admin\Sales\Returns\NewReturn\Returns;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class ReturnRequestService {
 public function submit(Business $b,int $customer,int $orderId,array $data){
 return DB::transaction(function()use($b,$customer,$orderId,$data){
 $order=Order::where('business_id',$b->id)->where('customer_id',$customer)->where('channel','online')->lockForUpdate()->findOrFail($orderId);
 abort_unless($order->status==='confirmed' && $order->delivery_status==='delivered',422,t('Only delivered orders can be returned.','শুধু ডেলিভারি হওয়া অর্ডার ফেরত দেওয়া যাবে।'));
 $item=$order->items()->where('business_id',$b->id)->findOrFail($data['order_item_id']);
 $pending=ReturnRequest::where('business_id',$b->id)->where('order_item_id',$item->id)->where('status','pending')->sum('quantity');
 if(round((float)$data['quantity'],3)>round($item->quantity-$item->returned_quantity-$pending,3))throw ValidationException::withMessages(['quantity'=>t('Quantity exceeds the remaining returnable quantity.','ফেরতযোগ্য পরিমাণের চেয়ে বেশি।')]);
 return ReturnRequest::create(['business_id'=>$b->id,'customer_id'=>$customer,'order_id'=>$order->id,'order_item_id'=>$item->id,'quantity'=>$data['quantity'],'reason'=>$data['reason']]);
 });}
 public function review(Business $b,int $id,int $admin,array $data,Returns $returns){
 return DB::transaction(function()use($b,$id,$admin,$data,$returns){
 $record=ReturnRequest::where('business_id',$b->id)->findOrFail($id);
 $order=Order::where('business_id',$b->id)->where('channel','online')->lockForUpdate()->findOrFail($record->order_id);
 $record=ReturnRequest::where('business_id',$b->id)->lockForUpdate()->findOrFail($id);
 abort_unless($record->status==='pending',422,t('Request already reviewed.','অনুরোধ ইতিমধ্যে পর্যালোচিত।'));
 if($data['decision']==='posted'){
 abort_unless($order->status==='confirmed' && $order->delivery_status==='delivered' && (int)$order->customer_id===(int)$record->customer_id,422);
 $result=$returns->create($b,['order_id'=>$order->id,'reason'=>$record->reason,'items'=>[['order_item_id'=>$record->order_item_id,'quantity'=>$record->quantity]]]);
 $record->sale_return_id=$result->id;
 }
 $record->status=$data['decision'];$record->admin_note=$data['admin_note']??null;$record->reviewed_by=$admin;$record->reviewed_at=now();$record->save();return $record;
 });}
}

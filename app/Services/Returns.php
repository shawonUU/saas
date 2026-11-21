<?php
namespace App\Services;
use App\Models\Business;
use App\Models\Configurations\Variant;
use App\Models\Sales\Order;
use App\Models\Sales\OrderItem;
use App\Models\Sales\ReturnItem;
use App\Models\Sales\SaleReturn;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Returns {
 public function create(Business $business,array $data):SaleReturn {
  return DB::transaction(function()use($business,$data){
   $order=Order::where('business_id',$business->id)->lockForUpdate()->findOrFail($data['order_id']);
   if($order->status==='cancelled')throw ValidationException::withMessages(['order_id'=>'Cancelled order cannot be returned.']);
   $returnLines=[];$sales=0;$cost=0;$subtotal=(float)$order->subtotal;
   // Invoice net revenue excludes shipping and VAT in both inclusive and exclusive tax modes.
   $invoiceNet=round((float)$order->total-(float)$order->shipping-(float)$order->tax,2);
   foreach($data['items'] as $row){$item=OrderItem::where('business_id',$business->id)->where('order_id',$order->id)->lockForUpdate()->findOrFail($row['order_item_id']);
    $qty=round((float)$row['quantity'],3);if($qty===0.0)continue;if($qty<0 || $qty>round((float)$item->quantity-(float)$item->returned_quantity,3))throw ValidationException::withMessages(['items'=>'Return quantity exceeds the quantity sold.']);
    // Round each line; final return carries any remaining discount rounding difference.
    $net=round($qty*(float)$item->price*($subtotal>0?$invoiceNet/$subtotal:1),2);
    $sales+=$net;$lineCost=$item->variant?->product?->track_stock?round($qty*(float)$item->cost,2):0;$cost+=$lineCost;$returnLines[]=compact('item','qty','net','lineCost');
   }
   if(!$returnLines)throw ValidationException::withMessages(['items'=>'Select return items.']);
   $sales=round($sales,2);$cost=round($cost,2);
   $previous=SaleReturn::where('business_id',$business->id)->where('order_id',$order->id)->sum('amount');
   $allReturning=true;
   foreach($order->items()->where('business_id',$business->id)->get() as $item){$requested=collect($returnLines)->first(fn($x)=>$x['item']->id===$item->id)['qty']??0;
    if(round((float)$item->returned_quantity+$requested,3)<round((float)$item->quantity,3)){$allReturning=false;break;}}
   // Treat delivery charge as non-refundable; tax is allocated based on net item value.
   $tax=round((float)$order->tax*($subtotal>0?array_sum(array_map(fn($x)=>$x['qty']*(float)$x['item']->price,$returnLines))/$subtotal:0),2);
   $previousTax=DB::table('retail_journals')->where('retail_journals.business_id',$business->id)->where('source_type','return')->whereIn('source_id',SaleReturn::where('business_id',$business->id)->where('order_id',$order->id)->pluck('id'))
     ->join('retail_journal_lines','retail_journal_lines.journal_id','=','retail_journals.id')->join('retail_accounts','retail_accounts.id','=','retail_journal_lines.account_id')->where('retail_accounts.code','2100')->sum('retail_journal_lines.debit');
   if($allReturning)$tax=round((float)$order->tax-$previousTax,2);
   if($allReturning)$sales=round($invoiceNet-($previous-$previousTax),2);
   $amount=round($sales+$tax,2);
   if($amount<=0)throw ValidationException::withMessages(['items'=>'Invalid return amount.']);
   $return=SaleReturn::create(['business_id'=>$business->id,'order_id'=>$order->id,'amount'=>$amount,'reason'=>$data['reason']??null]);
   foreach($returnLines as $line){$item=$line['item'];ReturnItem::create(['business_id'=>$business->id,'return_id'=>$return->id,'order_item_id'=>$item->id,'quantity'=>$line['qty'],'amount'=>$line['net']]);$item->increment('returned_quantity',$line['qty']);
    $variant=Variant::where('business_id',$business->id)->findOrFail($item->variant_id);
    if($variant->product?->track_stock)app(Inventory::class)->move($business,$variant,$line['qty'],'return',(float)$item->cost,'return',$return->id,'Customer return');
   }
   $entries=['4200'=>[$sales,0],'1200'=>[0,$amount]];
   if($tax>0)$entries['2100']=[$tax,0];if($cost>0){$entries['1300']=[$cost,0];$entries['5000']=[0,$cost];}
   app(Accounting::class)->post($business,'return',$return->id,'Sales return '.$order->order_no,$entries);
   $order->increment('credited',$amount);if($allReturning)$order->update(['status'=>'returned']);
   return $return;
  });
 }
}

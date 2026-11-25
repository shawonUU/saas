<?php
namespace App\Services\Public\Business\Admin\Sales\Orders\OrderDetails;
use App\Services\Public\Business\Admin\Finance\Ledger\JournalEntries\Accounting;
use App\Services\Public\Business\Admin\Inventory\Movements\Inventory;
use App\Models\Accounts\Account;
use App\Models\Accounts\GatewayPayment;
use App\Models\Accounts\Journal;
use App\Models\Accounts\JournalLine;
use App\Models\Business;
use App\Models\Configurations\Variant;
use App\Models\Sales\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CancelSale {
 public function cancel(Business $b,Order $order):void {
  DB::transaction(function()use($b,$order){$order=Order::where('business_id',$b->id)->lockForUpdate()->findOrFail($order->id);
   if($order->status!=='confirmed'||$order->paid>0||$order->credited>0||in_array($order->delivery_status,['shipped','delivered'],true))throw ValidationException::withMessages(['order'=>'Only unpaid, unshipped and unreturned orders can be cancelled.']);
   if(GatewayPayment::where('business_id',$b->id)->where('order_id',$order->id)->whereIn('status',['paid','initiated'])->exists())throw ValidationException::withMessages(['order'=>'Gateway payment already recorded.']);
   foreach($order->items()->where('business_id',$b->id)->get() as $item){$variant=Variant::where('business_id',$b->id)->findOrFail($item->variant_id);if($variant->product?->track_stock)app(Inventory::class)->move($b,$variant,(float)$item->quantity,'sale_cancel',(float)$item->cost,'order_cancel',$order->id,'Cancel unpaid order');}
   $journal=Journal::where('business_id',$b->id)->where('source_type','order')->where('source_id',$order->id)->firstOrFail();$entries=[];
   foreach(JournalLine::where('business_id',$b->id)->where('journal_id',$journal->id)->get() as $line){$code=Account::where('business_id',$b->id)->findOrFail($line->account_id)->code;$entries[$code]=[(float)$line->credit,(float)$line->debit];}
   app(Accounting::class)->post($b,'order_cancel',$order->id,'Cancel '.$order->order_no,$entries);
   $order->update(['status'=>'cancelled']);
  });
 }
}

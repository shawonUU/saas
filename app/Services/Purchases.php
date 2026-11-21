<?php
namespace App\Services;
use App\Models\Business;
use App\Models\Configurations\Variant;
use App\Models\Purchase\Purchase;
use App\Models\Purchase\PurchaseItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Purchases {
 public function create(Business $business,array $data):Purchase {
  return DB::transaction(function()use($business,$data){$total=0;$items=[];
   foreach($data['items'] as $row){$variant=Variant::where('business_id',$business->id)->lockForUpdate()->findOrFail($row['variant_id']);if(!$variant->product?->track_stock)throw \Illuminate\Validation\ValidationException::withMessages(['items'=>'Goods receipts require stock-tracked variants.']);$qty=round((float)$row['quantity'],3);$cost=round((float)$row['cost'],2);$amount=round($qty*$cost,2);
    $total+=$amount;$items[]=compact('variant','qty','cost','amount');}
   $total=round($total,2);$purchase=Purchase::create(['business_id'=>$business->id,'supplier_id'=>$data['supplier_id']??null,'purchase_no'=>'GRN-'.Str::upper(Str::random(9)),'total'=>$total,'notes'=>$data['notes']??null]);
   foreach($items as $item){PurchaseItem::create(['business_id'=>$business->id,'purchase_id'=>$purchase->id,'variant_id'=>$item['variant']->id,'quantity'=>$item['qty'],'cost'=>$item['cost'],'total'=>$item['amount']]);
    $oldQty=(float)$item['variant']->stock;$oldCost=(float)$item['variant']->cost;$avg=round(($oldQty*$oldCost+$item['qty']*$item['cost'])/max(.001,$oldQty+$item['qty']),2);$item['variant']->update(['cost'=>$avg]);app(Inventory::class)->move($business,$item['variant'],$item['qty'],'purchase',$item['cost'],'purchase',$purchase->id,$purchase->purchase_no);}
   app(Accounting::class)->post($business,'purchase',$purchase->id,$purchase->purchase_no,['1300'=>[$total,0],'2000'=>[0,$total]]);
   if(!empty($data['pay_amount']))app(Payments::class)->payPurchase($business,$purchase,(float)$data['pay_amount'],(string)($data['payment_method']??'cash'));
   return $purchase->fresh();
  });
 }
}

<?php
namespace App\Services\Public\Business\Admin\Sales\Pos;

use App\Models\Business;
use App\Models\Configurations\Variant;
use App\Models\Sales\Discount;
use App\Models\Sales\Order;
use App\Services\Public\Business\Admin\Sales\Orders\NewOrder\Sales;
use App\Services\Public\Business\Admin\Finance\Payments;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PosService
{
    public function quote(Business $business,array $data):array
    {
        $subtotal=0;
        foreach($data['items'] as $item){
            $variant=Variant::with('product')->where('business_id',$business->id)->where('is_active',true)->findOrFail($item['variant_id']);
            if(!$variant->product?->is_active) $this->fail('items',t('Product is unavailable.','পণ্যটি পাওয়া যাচ্ছে না।'));
            $qty=round((float)$item['quantity'],3);
            if($qty<=0) $this->fail('items',t('Invalid quantity.','পরিমাণ সঠিক নয়।'));
            if($variant->product->track_stock){
                $stock=DB::table('warehouse_stocks as s')->join('warehouses as w','w.id','=','s.warehouse_id')->where('s.business_id',$business->id)->where('w.business_id',$business->id)->where('w.code','MAIN')->where('s.variant_id',$variant->id)->value('s.quantity')??0;
                if($qty>min((float)$stock,(float)$variant->stock)) $this->fail('items',t('Insufficient stock: ','পর্যাপ্ত স্টক নেই: ').$variant->sku);
            }
            $subtotal+=round($qty*(float)$variant->price,2);
        }
        $subtotal=round($subtotal,2);
        if($subtotal<=0) $this->fail('items',t('Select a priced item.','মূল্যযুক্ত পণ্য নির্বাচন করুন।'));
        $discount=(float)($data['discount']??0);
        $code=strtoupper(trim((string)($data['coupon_code']??'')));
        if($code!==''){
            $coupon=Discount::where('business_id',$business->id)->where('code',$code)->where('is_active',true)->first();
            if(!$coupon || ($coupon->starts_at && now()->lt($coupon->starts_at)) || ($coupon->ends_at && now()->gt($coupon->ends_at))) $this->fail('coupon_code',t('Coupon is invalid or expired.','কুপনটি ভুল অথবা মেয়াদ শেষ।'));
            $discount=$coupon->type==='percent'?round($subtotal*min((float)$coupon->value,100)/100,2):(float)$coupon->value;
        }
        $discount=max(0,min(round($discount,2),$subtotal));$base=round($subtotal-$discount,2);$rate=0;$inclusive=false;
        if(!empty($data['tax_id'])){
            $tax=DB::table('config_taxes')->where('business_id',$business->id)->where('is_active',true)->where('id',$data['tax_id'])->first();
            if(!$tax) $this->fail('tax_id',t('Invalid tax.','কর সঠিক নয়।'));
            $rate=(float)$tax->rate;$inclusive=$tax->calculation_type==='inclusive';
        }
        $tax=$inclusive&&$rate>0?round($base-$base/(1+$rate/100),2):round($base*$rate/100,2);
        $total=round($base+($inclusive?0:$tax),2);
        return compact('subtotal','discount','tax','total','inclusive');
    }

    public function checkout(Business $business,array $data,Sales $sales,Payments $payments):Order
    {
        return DB::transaction(function()use($business,$data,$sales,$payments){
            Business::whereKey($business->id)->lockForUpdate()->firstOrFail();
            $existing=Order::where('business_id',$business->id)->where('request_key',$data['request_key'])->first();
            if($existing){if($existing->channel!=='pos') $this->fail('request_key',t('Invalid sale reference.','বিক্রয় রেফারেন্স সঠিক নয়।'));return $existing;}
            if(!empty($data['customer_id'])){
                $customer=DB::table('business_customers')->where('business_id',$business->id)->where('is_active',true)->where('id',$data['customer_id'])->first();
                if(!$customer) $this->fail('customer_id',t('Customer is unavailable.','কাস্টমার পাওয়া যাচ্ছে না।'));
                $data['customer_name']=$customer->name;$data['customer_phone']=$customer->phone;
            }
            // Sales owns authoritative price, inventory and journal posting. The outer transaction rolls all of it back if checkout cannot proceed.
            $data['shipping']=0;$data['pay_amount']=0;
            $order=$sales->create($business,$data,'pos');
            $total=round((float)$order->total,2);
            if(abs($total-(float)$data['expected_total'])>.001) $this->fail('expected_total',t('Price changed. Review the cart and try again.','মূল্য পরিবর্তন হয়েছে। কার্ট যাচাই করে আবার চেষ্টা করুন।'));
            $tendered=round((float)($data['tendered']??0),2);$mode=$data['payment_mode'];$method=$data['payment_method']??'cash';
            $paid=$mode==='due'?0:($mode==='full'?$total:$tendered);
            if($mode==='full' && ($tendered<$total || ($method==='bank' && $tendered!==$total))) $this->fail('tendered',t('Check the received amount.','গৃহীত টাকা যাচাই করুন।'));
            if($mode==='partial' && ($paid<=0 || $paid>=$total)) $this->fail('tendered',t('Partial payment must be below total.','আংশিক পেমেন্ট মোট টাকার চেয়ে কম হতে হবে।'));
            if($paid<$total && empty($data['customer_id'])) $this->fail('customer_id',t('Select a registered customer for credit sales.','বাকিতে বিক্রয়ের জন্য নিবন্ধিত কাস্টমার নির্বাচন করুন।'));
            if($paid>0) $payments->payOrder($business,$order,$paid,$method,(string)($data['payment_reference']??''));
            return $order->fresh();
        });
    }
    private function fail(string $field,string $message):never {throw ValidationException::withMessages([$field=>$message]);}
}

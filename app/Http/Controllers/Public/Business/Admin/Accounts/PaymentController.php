<?php
namespace App\Http\Controllers\Public\Business\Admin\Accounts;
use App\Http\Controllers\Controller;
use App\Models\Accounts\Payment;
use App\Models\Purchase\Purchase;
use App\Models\Sales\Order;
use App\Models\Sales\SaleReturn;
use App\Services\Retail\Access;
use App\Services\Retail\Lists;
use App\Services\Retail\Payments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentController extends Controller {
 public function index(Request $r,Access $a,string $kind='customer'){$params=$kind==='supplier'?['finance','payables','vendor-payments']:['finance','receivables','collections'];$v=$a->check($r,...$params);$v['title']=$kind==='supplier'?'Supplier Payments':'Customer Collections';$v['kind']=$kind;
  $v['records']=Lists::page(Payment::where('business_id',$v['business']->id)->where('reference_type',$kind==='supplier'?'purchase':'order'),$r,['id'=>'number','reference_id'=>'number','method'=>'text','direction'=>'text','amount'=>'number','reference_no'=>'text','created_at'=>'date']);
  $v['docs']=$kind==='supplier'?Purchase::where('business_id',$v['business']->id)->whereColumn('paid','<','total')->latest()->limit(150)->get():Order::where('business_id',$v['business']->id)->whereRaw('paid + credited < total')->latest()->limit(150)->get();
  return view('public.business.admin.retail.payments.index',$v);}
 public function store(Request $r,Access $a,Payments $payments,string $kind='customer'){$p=$kind==='supplier'?['finance','payables','vendor-payments']:['finance','receivables','collections'];$v=$a->check($r,...[...$p,'create']);$b=$v['business'];$d=$r->validate(['document_id'=>'required|integer','amount'=>'required|numeric|gt:0','method'=>'required|in:cash,bank','reference_no'=>'nullable|string|max:255']);
  DB::transaction(function()use($b,$d,$payments,$kind){if($kind==='supplier')$payments->payPurchase($b,Purchase::where('business_id',$b->id)->findOrFail($d['document_id']),(float)$d['amount'],$d['method'],$d['reference_no']??'');
   else $payments->payOrder($b,Order::where('business_id',$b->id)->findOrFail($d['document_id']),(float)$d['amount'],$d['method'],$d['reference_no']??'');});
  return back()->with('success',t('Payment posted.','পেমেন্ট পোস্ট হয়েছে।'));}
 public function refund(Request $r,Access $a,Payments $payments){$v=$a->check($r,'sales','returns','return-list','update');$b=$v['business'];$d=$r->validate(['return_id'=>'required|integer','amount'=>'required|numeric|gt:0','method'=>'required|in:cash,bank','reference_no'=>'nullable|string|max:255']);
  DB::transaction(fn()=> $payments->refund($b,SaleReturn::where('business_id',$b->id)->findOrFail($d['return_id']),(float)$d['amount'],$d['method'],$d['reference_no']??''));return back()->with('success',t('Refund posted.','রিফান্ড পোস্ট হয়েছে।'));}
}

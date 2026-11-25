<?php
namespace App\Http\Controllers\Public\Business\Admin\Purchase\PurchaseReturns\PurchaseReturnList;
use App\Http\Controllers\Controller;
use App\Models\Purchase\PurchaseReturn;
use App\Models\Purchase\Purchase;
use App\Models\Purchase\PurchaseItem;
use App\Models\Configurations\Variant;
use App\Models\Accounts\Payment;
use App\Services\Public\Business\Admin\Finance\Ledger\JournalEntries\Accounting;
use App\Services\Public\Business\Admin\Authorization\AccessControl\Access;
use App\Services\Public\Business\Admin\Inventory\Movements\Inventory;
use App\Services\Public\Business\Admin\Shared\Lists;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PurchaseReturnController extends Controller {
 public function index(Request $r,Access $a){$v=$a->check($r,'purchase','purchase-returns','purchase-return-list');$b=$v['business'];$v['records']=Lists::page(PurchaseReturn::where('business_id',$b->id),$r,['id'=>'number','purchase_id'=>'number','variant_id'=>'number','quantity'=>'number','amount'=>'number','created_at'=>'date']);$v['purchases']=Purchase::with('items.variant')->where('business_id',$b->id)->latest()->limit(100)->get();return view('public.business.admin.retail.purchase_returns.index',$v);}
 public function store(Request $r,Access $a){$v=$a->check($r,'purchase','purchase-returns','purchase-return-list','create');$b=$v['business'];$d=$r->validate(['purchase_item_id'=>'required|integer','quantity'=>'required|numeric|gt:0|max:999999','reason'=>'nullable|string|max:255','refund_method'=>'required|in:cash,bank']);DB::transaction(function()use($b,$d){$item=PurchaseItem::where('business_id',$b->id)->lockForUpdate()->findOrFail($d['purchase_item_id']);$p=Purchase::where('business_id',$b->id)->lockForUpdate()->findOrFail($item->purchase_id);$returned=(float)PurchaseReturn::where('business_id',$b->id)->where('purchase_item_id',$item->id)->sum('quantity');$qty=round((float)$d['quantity'],3);if($qty>round((float)$item->quantity-$returned,3))throw ValidationException::withMessages(['quantity'=>'Return exceeds purchased quantity.']);$variant=Variant::where('business_id',$b->id)->lockForUpdate()->findOrFail($item->variant_id);$amount=round($qty*(float)$item->cost,2);if($amount<=0)throw ValidationException::withMessages(['quantity'=>'Return value must be positive.']);
 $pReturnTotal=(float)PurchaseReturn::where('business_id',$b->id)->where('purchase_id',$p->id)->sum('amount');$previousCredit=(float)PurchaseReturn::where('business_id',$b->id)->where('purchase_id',$p->id)->sum('ap_credit');$unpaid=max(0,round((float)$p->total-(float)$p->paid-$previousCredit,2));$apCredit=min($unpaid,$amount);$refund=round($amount-$apCredit,2);if($pReturnTotal+$amount>(float)$p->total+.001)throw ValidationException::withMessages(['quantity'=>'Return exceeds purchase total.']);
 $ret=PurchaseReturn::create(['business_id'=>$b->id,'purchase_id'=>$p->id,'purchase_item_id'=>$item->id,'variant_id'=>$variant->id,'quantity'=>$qty,'amount'=>$amount,'ap_credit'=>$apCredit,'refund_amount'=>$refund,'reason'=>$d['reason']??null]);app(Inventory::class)->move($b,$variant,-$qty,'purchase_return',(float)$variant->cost,'purchase_return',$ret->id,'Supplier return');$inventoryValue=round($qty*(float)$variant->cost,2);$difference=round($inventoryValue-$amount,2);
 $entries=['1300'=>[0,$inventoryValue]];if($difference>0)$entries['6100']=[$difference,0];elseif($difference<0)$entries['6100']=[0,-$difference];if($apCredit>0)$entries['2000']=[$apCredit,0];if($refund>0){$cashCode=$d['refund_method']==='bank'?'1100':'1000';$entries[$cashCode]=[$refund,0];Payment::create(['business_id'=>$b->id,'reference_type'=>'purchase_return','reference_id'=>$ret->id,'method'=>$d['refund_method'],'direction'=>'in','amount'=>$refund,'reference_no'=>'Supplier refund']);}app(Accounting::class)->post($b,'purchase_return',$ret->id,'Supplier stock return',$entries);});return back()->with('success',t('Purchase returned; stock and accounts posted.','ক্রয় রিটার্ন ও হিসাব পোস্ট হয়েছে।'));}
}

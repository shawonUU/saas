<?php
namespace App\Http\Controllers\Public\Business\Admin\Inventory;
use App\Http\Controllers\Controller;
use App\Models\Configurations\Variant;
use App\Models\Inventory\StockMovement;
use App\Services\Access;
use App\Services\Lists;
use App\Services\Inventory;
use App\Services\Accounting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockController extends Controller {
 public function report(Request $r,Access $a){$v=$a->check($r,'inventory','stock','stock-report');$v['title']='Stock Report';$v['records']=Lists::page(Variant::with('product')->where('business_id',$v['business']->id),$r,['id'=>'number','sku'=>'text','price'=>'number','cost'=>'number','stock'=>'number','created_at'=>'date']);return view('public.business.admin.retail.stock.report',$v);}
 public function low(Request $r,Access $a){$v=$a->check($r,'inventory','stock','low-stock');$v['title']='Low Stock';$v['records']=Lists::page(Variant::with('product')->where('business_id',$v['business']->id)->where('stock','<=',5),$r,['id'=>'number','sku'=>'text','stock'=>'number']);return view('public.business.admin.retail.stock.low',$v);}
 public function ledger(Request $r,Access $a){$v=$a->check($r,'inventory','stock','stock-ledger');$v['title']='Stock Ledger';$v['records']=Lists::page(StockMovement::where('business_id',$v['business']->id)->with('variant'),$r,['id'=>'number','variant_id'=>'number','kind'=>'text','quantity'=>'number','unit_cost'=>'number','source_type'=>'text','created_at'=>'date']);return view('public.business.admin.retail.stock.ledger',$v);}
 public function adjustment(Request $r,Access $a){$v=$a->check($r,'inventory','movements','stock-adjustment');$v['title']='Stock Adjustment';$v['variants']=Variant::with('product')->where('business_id',$v['business']->id)->orderBy('sku')->get();return view('public.business.admin.retail.stock.adjustment',$v);}
 public function adjust(Request $r,Access $a){$v=$a->check($r,'inventory','movements','stock-adjustment','create');$b=$v['business'];$d=$r->validate(['variant_id'=>'required|integer','quantity'=>'required|numeric|not_in:0|between:-99999999,99999999','note'=>'required|string|max:255']);
  DB::transaction(function()use($b,$d){$variant=Variant::where('business_id',$b->id)->lockForUpdate()->findOrFail($d['variant_id']);$qty=(float)$d['quantity'];$cost=(float)$variant->cost;
   app(Inventory::class)->move($b,$variant,$qty,'adjustment',$cost,'adjustment',null,$d['note']);$movement=StockMovement::where('business_id',$b->id)->where('variant_id',$variant->id)->latest('id')->firstOrFail();$movement->update(['source_id'=>$movement->id]);
   $amount=round(abs($qty)*$cost,2);if($amount>0){$entries=$qty>0?['1300'=>[$amount,0],'3000'=>[0,$amount]]:['6000'=>[$amount,0],'1300'=>[0,$amount]];
    app(Accounting::class)->post($b,'adjustment',$movement->id,'Stock adjustment: '.$d['note'],$entries);}
  });return back()->with('success',t('Stock adjusted.','স্টক সমন্বয় হয়েছে।'));}
}

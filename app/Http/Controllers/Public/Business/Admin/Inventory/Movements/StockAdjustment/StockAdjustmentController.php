<?php
namespace App\Http\Controllers\Public\Business\Admin\Inventory\Movements\StockAdjustment;
use App\Http\Controllers\Controller;
use App\Models\Configurations\Variant;
use App\Models\Inventory\StockMovement;
use App\Services\Public\Business\Admin\Authorization\AccessControl\Access;
use App\Services\Public\Business\Admin\Inventory\Movements\Inventory;
use App\Services\Public\Business\Admin\Finance\Ledger\JournalEntries\Accounting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller {
 public function adjustment(Request $r,Access $a){$v=$a->check($r,'inventory','movements','stock-adjustment');$v['title']='Stock Adjustment';$v['variants']=Variant::with('product')->where('business_id',$v['business']->id)->orderBy('sku')->get();return view('public.business.admin.retail.stock.adjustment',$v);}
 public function adjust(Request $r,Access $a){$v=$a->check($r,'inventory','movements','stock-adjustment','create');$b=$v['business'];$d=$r->validate(['variant_id'=>'required|integer','quantity'=>'required|numeric|not_in:0|between:-99999999,99999999','note'=>'required|string|max:255']);
  DB::transaction(function()use($b,$d){$variant=Variant::where('business_id',$b->id)->lockForUpdate()->findOrFail($d['variant_id']);$qty=(float)$d['quantity'];$cost=(float)$variant->cost;
   app(Inventory::class)->move($b,$variant,$qty,'adjustment',$cost,'adjustment',null,$d['note']);$movement=StockMovement::where('business_id',$b->id)->where('variant_id',$variant->id)->latest('id')->firstOrFail();$movement->update(['source_id'=>$movement->id]);
   $amount=round(abs($qty)*$cost,2);if($amount>0){$entries=$qty>0?['1300'=>[$amount,0],'3000'=>[0,$amount]]:['6000'=>[$amount,0],'1300'=>[0,$amount]];
    app(Accounting::class)->post($b,'adjustment',$movement->id,'Stock adjustment: '.$d['note'],$entries);}
  });return back()->with('success',t('Stock adjusted.','স্টক সমন্বয় হয়েছে।'));}
}

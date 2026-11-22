<?php
namespace App\Http\Controllers\Public\Business\Admin\Inventory;
use App\Http\Controllers\Controller;
use App\Models\Configurations\Warehouse;
use App\Models\Inventory\WarehouseStock;
use App\Models\Configurations\Variant;
use App\Models\Inventory\StockTransfer;
use App\Models\Inventory\StockTransferItem;
use App\Services\Access;
use App\Services\Lists;
use App\Services\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class WarehouseController extends Controller {
 public function index(Request $r,Access $a){$v=$a->check($r,'settings','locations','warehouses');$b=$v['business'];app(Inventory::class)->main($b);$v['title']='Warehouses';$v['records']=Lists::page(Warehouse::where('business_id',$b->id),$r,['id'=>'number','code'=>'text','name'=>'text','is_default'=>'number','is_active'=>'number']);return view('public.business.admin.retail.warehouses.index',$v);}
 public function store(Request $r,Access $a){$v=$a->check($r,'settings','locations','warehouses','create');$b=$v['business'];$d=$r->validate(['code'=>['required','alpha_dash','max:30',Rule::unique('warehouses')->where('business_id',$b->id)],'name'=>'required|string|max:255']);Warehouse::create(['business_id'=>$b->id,'code'=>strtoupper($d['code']),'name'=>$d['name'],'is_default'=>false,'is_active'=>true]);return back()->with('success',t('Warehouse created.','গুদাম তৈরি হয়েছে।'));}
 public function transfers(Request $r,Access $a){$v=$a->check($r,'inventory','movements','stock-transfer');$b=$v['business'];$v['title']='Stock Transfers';$v['records']=Lists::page(StockTransfer::where('business_id',$b->id),$r,['id'=>'number','transfer_no'=>'text','from_warehouse_id'=>'number','to_warehouse_id'=>'number','created_at'=>'date']);$v['warehouses']=Warehouse::where('business_id',$b->id)->where('is_active',true)->get();$v['variants']=Variant::where('business_id',$b->id)->orderBy('sku')->get();$v['stocks']=WarehouseStock::where('business_id',$b->id)->get()->groupBy('warehouse_id');return view('public.business.admin.retail.warehouses.transfers',$v);}
 public function transfer(Request $r,Access $a){$v=$a->check($r,'inventory','movements','stock-transfer','create');$b=$v['business'];$d=$r->validate(['from_warehouse_id'=>'required|integer','to_warehouse_id'=>'required|integer|different:from_warehouse_id','variant_id'=>'required|integer','quantity'=>'required|numeric|gt:0|max:999999','note'=>'nullable|string|max:255']);DB::transaction(function()use($b,$d){$from=Warehouse::where('business_id',$b->id)->where('is_active',true)->findOrFail($d['from_warehouse_id']);$to=Warehouse::where('business_id',$b->id)->where('is_active',true)->findOrFail($d['to_warehouse_id']);$variant=Variant::where('business_id',$b->id)->lockForUpdate()->findOrFail($d['variant_id']);$t=StockTransfer::create(['business_id'=>$b->id,'transfer_no'=>'TR-'.Str::upper(Str::random(9)),'from_warehouse_id'=>$from->id,'to_warehouse_id'=>$to->id,'note'=>$d['note']??null]);$qty=round((float)$d['quantity'],3);$stock=app(Inventory::class);$stock->move($b,$variant,-$qty,'transfer_out',(float)$variant->cost,'transfer',$t->id,'Warehouse transfer',$from->id);$stock->move($b,$variant,$qty,'transfer_in',(float)$variant->cost,'transfer',$t->id,'Warehouse transfer',$to->id);StockTransferItem::create(['business_id'=>$b->id,'stock_transfer_id'=>$t->id,'variant_id'=>$variant->id,'quantity'=>$qty]);});return back()->with('success',t('Stock transferred.','স্টক স্থানান্তর হয়েছে।'));}
}

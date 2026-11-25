<?php
namespace App\Http\Controllers\Public\Business\Admin\Inventory\Stock\StockLedger;
use App\Http\Controllers\Controller;
use App\Models\Inventory\StockMovement;
use App\Services\Public\Business\Admin\Authorization\AccessControl\Access;
use App\Services\Public\Business\Admin\Shared\Lists;
use Illuminate\Http\Request;

class StockLedgerController extends Controller {
 public function ledger(Request $r,Access $a){$v=$a->check($r,'inventory','stock','stock-ledger');$v['title']='Stock Ledger';$v['records']=Lists::page(StockMovement::where('business_id',$v['business']->id)->with('variant'),$r,['id'=>'number','variant_id'=>'number','kind'=>'text','quantity'=>'number','unit_cost'=>'number','source_type'=>'text','created_at'=>'date']);return view('public.business.admin.retail.stock.ledger',$v);}
}

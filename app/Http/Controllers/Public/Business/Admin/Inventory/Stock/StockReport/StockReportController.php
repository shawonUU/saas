<?php
namespace App\Http\Controllers\Public\Business\Admin\Inventory\Stock\StockReport;
use App\Http\Controllers\Controller;
use App\Models\Configurations\Variant;
use App\Services\Public\Business\Admin\Authorization\AccessControl\Access;
use App\Services\Public\Business\Admin\Shared\Lists;
use Illuminate\Http\Request;

class StockReportController extends Controller {
 public function report(Request $r,Access $a){$v=$a->check($r,'inventory','stock','stock-report');$v['title']='Stock Report';$v['records']=Lists::page(Variant::with('product')->where('business_id',$v['business']->id),$r,['id'=>'number','sku'=>'text','price'=>'number','cost'=>'number','stock'=>'number','created_at'=>'date']);return view('public.business.admin.retail.stock.report',$v);}
}

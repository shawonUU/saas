<?php
namespace App\Http\Controllers\Public\Business\Admin\Inventory\Stock\LowStock;
use App\Http\Controllers\Controller;
use App\Models\Configurations\Variant;
use App\Services\Public\Business\Admin\Authorization\AccessControl\Access;
use App\Services\Public\Business\Admin\Shared\Lists;
use Illuminate\Http\Request;

class LowStockController extends Controller {
 public function low(Request $r,Access $a){$v=$a->check($r,'inventory','stock','low-stock');$v['title']='Low Stock';$v['records']=Lists::page(Variant::with('product')->where('business_id',$v['business']->id)->where('stock','<=',5),$r,['id'=>'number','sku'=>'text','stock'=>'number']);return view('public.business.admin.retail.stock.low',$v);}
}

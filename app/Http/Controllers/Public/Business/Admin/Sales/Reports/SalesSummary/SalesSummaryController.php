<?php
namespace App\Http\Controllers\Public\Business\Admin\Sales\Reports\SalesSummary;
use App\Http\Controllers\Controller;
use App\Models\Sales\Order;
use App\Models\Sales\SaleReturn;
use App\Services\Public\Business\Admin\Authorization\AccessControl\Access;
use App\Services\Public\Business\Admin\Shared\Lists;
use Illuminate\Http\Request;
class SalesSummaryController extends Controller {
 public function salesSummary(Request $r,Access $a){$v=$a->check($r,'sales','reports','sales-summary');$b=$v['business'];$v['title']='Sales Summary';
  $query=Order::where('business_id',$b->id);$returns=SaleReturn::where('business_id',$b->id);$payments=\App\Models\Accounts\Payment::where('business_id',$b->id)->where('direction','in');
  $from=$r->query('from');$to=$r->query('to');if($from&&preg_match('/^\d{4}-\d{2}-\d{2}$/',$from)){$query->whereDate('created_at','>=',$from);$returns->whereDate('created_at','>=',$from);$payments->whereDate('created_at','>=',$from);}
  if($to&&preg_match('/^\d{4}-\d{2}-\d{2}$/',$to)){$query->whereDate('created_at','<=',$to);$returns->whereDate('created_at','<=',$to);$payments->whereDate('created_at','<=',$to);}
  $v['counts']=['orders'=>(clone $query)->count(),'gross'=>(float)(clone $query)->sum('subtotal'),'discount'=>(float)(clone $query)->sum('discount'),
   'tax'=>(float)(clone $query)->sum('tax'),'delivery'=>(float)(clone $query)->sum('shipping'),'invoiced'=>(float)(clone $query)->sum('total'),'returns'=>(float)$returns->sum('amount'),'collected'=>(float)$payments->sum('amount')];
  $v['records']=Lists::page($query,$r,['id'=>'number','order_no'=>'text','channel'=>'text','subtotal'=>'number','discount'=>'number','shipping'=>'number','tax'=>'number','total'=>'number','paid'=>'number','created_at'=>'date']);
  return view('public.business.admin.retail.reports.sales',$v);}
}

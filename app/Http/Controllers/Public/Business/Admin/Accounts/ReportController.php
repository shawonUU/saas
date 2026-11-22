<?php
namespace App\Http\Controllers\Public\Business\Admin\Accounts;
use App\Http\Controllers\Controller;
use App\Models\Sales\Order;
use App\Models\Purchase\Purchase;
use App\Models\Sales\SaleReturn;
use App\Models\Accounts\Journal;
use App\Models\Accounts\JournalLine;
use App\Models\Accounts\Account;
use App\Services\Access;
use App\Services\Lists;
use App\Services\Accounting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class ReportController extends Controller {
 public function ledger(Request $r,Access $a){$v=$a->check($r,'finance','ledger','general-ledger');$v['title']='General Ledger';
  $v['records']=Lists::page(JournalLine::with(['account','journal'])->where('business_id',$v['business']->id),$r,['id'=>'number','journal_id'=>'number','account_id'=>'number','debit'=>'number','credit'=>'number','created_at'=>'date']);return view('public.business.admin.retail.reports.ledger',$v);}
 public function cash(Request $r,Access $a){$v=$a->check($r,'finance','financial-reports','cash-flow');$v['title']='Cash Flow';
  $v['records']=Lists::page(JournalLine::with(['account','journal'])->where('business_id',$v['business']->id)->whereHas('account',fn($q)=>$q->whereIn('code',['1000','1100'])),$r,['id'=>'number','journal_id'=>'number','account_id'=>'number','debit'=>'number','credit'=>'number','created_at'=>'date']);return view('public.business.admin.retail.reports.cash',$v);}
 public function salesSummary(Request $r,Access $a){$v=$a->check($r,'sales','reports','sales-summary');$b=$v['business'];$v['title']='Sales Summary';
  $query=Order::where('business_id',$b->id);$returns=SaleReturn::where('business_id',$b->id);$payments=\App\Models\Accounts\Payment::where('business_id',$b->id)->where('direction','in');
  $from=$r->query('from');$to=$r->query('to');if($from&&preg_match('/^\d{4}-\d{2}-\d{2}$/',$from)){$query->whereDate('created_at','>=',$from);$returns->whereDate('created_at','>=',$from);$payments->whereDate('created_at','>=',$from);}
  if($to&&preg_match('/^\d{4}-\d{2}-\d{2}$/',$to)){$query->whereDate('created_at','<=',$to);$returns->whereDate('created_at','<=',$to);$payments->whereDate('created_at','<=',$to);}
  $v['counts']=['orders'=>(clone $query)->count(),'gross'=>(float)(clone $query)->sum('subtotal'),'discount'=>(float)(clone $query)->sum('discount'),
   'tax'=>(float)(clone $query)->sum('tax'),'delivery'=>(float)(clone $query)->sum('shipping'),'invoiced'=>(float)(clone $query)->sum('total'),'returns'=>(float)$returns->sum('amount'),'collected'=>(float)$payments->sum('amount')];
  $v['records']=Lists::page($query,$r,['id'=>'number','order_no'=>'text','channel'=>'text','subtotal'=>'number','discount'=>'number','shipping'=>'number','tax'=>'number','total'=>'number','paid'=>'number','created_at'=>'date']);
  return view('public.business.admin.retail.reports.sales',$v);}
 public function journals(Request $r,Access $a){$v=$a->check($r,'finance','ledger','journal-entries');$v['title']='Journal Entries';$v['records']=Lists::page(Journal::where('business_id',$v['business']->id)->with('lines'),$r,['id'=>'number','source_type'=>'text','source_id'=>'number','memo'=>'text','posted_at'=>'date']);return view('public.business.admin.retail.reports.journals',$v);}
 public function trial(Request $r,Access $a){$v=$a->check($r,'finance','financial-reports','trial-balance');$v['title']='Trial Balance';return $this->balance($v,'trial');}
 public function profit(Request $r,Access $a){$v=$a->check($r,'finance','financial-reports','profit-loss');$v['title']='Profit & Loss';return $this->balance($v,'profit');}
 public function sheet(Request $r,Access $a){$v=$a->check($r,'finance','financial-reports','balance-sheet');$v['title']='Balance Sheet';return $this->balance($v,'sheet');}
 private function balance(array $v,string $mode){app(Accounting::class)->ensure($v['business']);$v['mode']=$mode;
  $v['accounts']=Account::where('accounts.business_id',$v['business']->id)->leftJoin('journal_lines',function($j){$j->on('journal_lines.account_id','=','accounts.id')->on('journal_lines.business_id','=','accounts.business_id');})
    ->groupBy('accounts.id','accounts.code','accounts.name','accounts.type')
    ->select('accounts.id','accounts.code','accounts.name','accounts.type',DB::raw('COALESCE(SUM(journal_lines.debit),0) AS debit'),DB::raw('COALESCE(SUM(journal_lines.credit),0) AS credit'))->orderBy('accounts.code')->get();
  return view('public.business.admin.retail.reports.balance',$v);}
 public function dues(Request $r,Access $a){$v=$a->check($r,'finance','receivables','customer-dues');$v['title']='Customer Dues';$v['records']=Lists::page(Order::where('business_id',$v['business']->id)->whereRaw('paid + credited < total'),$r,['id'=>'number','order_no'=>'text','customer_name'=>'text','total'=>'number','paid'=>'number','created_at'=>'date']);return view('public.business.admin.retail.reports.dues',$v);}
 public function bills(Request $r,Access $a){$v=$a->check($r,'finance','payables','vendor-bills');$v['title']='Vendor Bills';$v['records']=Lists::page(Purchase::where('business_id',$v['business']->id)->whereRaw('(total - paid) > COALESCE((SELECT SUM(ap_credit) FROM purchase_returns WHERE purchase_returns.purchase_id=purchases.id AND purchase_returns.business_id=purchases.business_id),0)'),$r,['id'=>'number','purchase_no'=>'text','supplier_id'=>'number','total'=>'number','paid'=>'number','created_at'=>'date']);return view('public.business.admin.retail.reports.bills',$v);}
 public function sales(Request $r,Access $a){$v=$a->check($r,'ecommerce','online-orders','online-order-list');$v['title']='Online Orders';$v['records']=Lists::page(Order::where('business_id',$v['business']->id)->where('channel','online'),$r,['id'=>'number','order_no'=>'text','customer_name'=>'text','customer_phone'=>'text','total'=>'number','paid'=>'number','delivery_status'=>'text','created_at'=>'date']);return view('public.business.admin.retail.reports.online',$v);}
}

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
 public function journals(Request $r,Access $a){
  $v=$a->check($r,'finance','ledger','journal-entries');$b=$v['business'];
  $filters=$r->validate(['type'=>'nullable|in:SV,PUV,RV,PV,CV,JV,CN,DN,OV,AV,GS','from'=>'nullable|date_format:Y-m-d','to'=>'nullable|date_format:Y-m-d','q'=>'nullable|string|max:100','origin'=>'nullable|in:manual,automatic','voucher'=>'nullable|integer|min:1']);
  $types=['SV'=>['Sales Voucher','বিক্রয় ভাউচার'],'PUV'=>['Purchase Voucher','ক্রয় ভাউচার'],'RV'=>['Receipt Voucher','প্রাপ্তি ভাউচার'],'PV'=>['Payment Voucher','প্রদান ভাউচার'],'CV'=>['Contra Voucher','স্থানান্তর ভাউচার'],'JV'=>['Journal Voucher','জার্নাল ভাউচার'],'CN'=>['Credit Note','ক্রেডিট নোট'],'DN'=>['Debit Note','ডেবিট নোট'],'OV'=>['Opening Voucher','প্রারম্ভিক ভাউচার'],'AV'=>['Adjustment Voucher','সমন্বয় ভাউচার'],'GS'=>['Gateway Settlement','গেটওয়ে সেটেলমেন্ট']];
  $case="CASE journals.source_type WHEN 'order' THEN 'SV' WHEN 'purchase' THEN 'PUV' WHEN 'payment' THEN CASE payments.direction WHEN 'in' THEN 'RV' WHEN 'out' THEN 'PV' ELSE 'JV' END WHEN 'expense' THEN 'PV' WHEN 'cash_transfer' THEN 'CV' WHEN 'return' THEN 'CN' WHEN 'order_cancel' THEN 'CN' WHEN 'purchase_return' THEN 'DN' WHEN 'opening' THEN 'OV' WHEN 'adjustment' THEN 'AV' WHEN 'gateway_settlement' THEN 'GS' WHEN 'manual' THEN COALESCE(manual_journals.voucher_type,'JV') ELSE 'JV' END";
  $query=Journal::query()->where('journals.business_id',$b->id)->leftJoin('payments',function($j){$j->on('payments.id','=','journals.source_id')->on('payments.business_id','=','journals.business_id')->where('journals.source_type','=','payment');})->leftJoin('manual_journals',function($j){$j->on('manual_journals.id','=','journals.source_id')->on('manual_journals.business_id','=','journals.business_id')->where('journals.source_type','=','manual');})->select('journals.*','manual_journals.reference_no as manual_reference')->selectRaw($case.' AS voucher_type')->with(['lines'=>fn($q)=>$q->where('business_id',$b->id)->with(['account'=>fn($q)=>$q->where('business_id',$b->id)])]);
  $v['types']=$types;$v['title']=t('All Vouchers','সকল ভাউচার');
  if(!empty($filters['voucher'])){$v['voucher']=(clone $query)->where('journals.id',$filters['voucher'])->firstOrFail();}
  if(!empty($filters['type']))$query->whereRaw("($case) = ?",[$filters['type']]);
  if(!empty($filters['from']))$query->whereDate('journals.posted_at','>=',$filters['from']);
  if(!empty($filters['to']))$query->whereDate('journals.posted_at','<=',$filters['to']);
  if(!empty($filters['origin']))$query->where('journals.source_type',$filters['origin']==='manual'?'=':'!=','manual');
  if(!empty($filters['q'])){$search=$filters['q'];$query->where(function($q)use($search,$case){$q->where('journals.memo','like','%'.$search.'%')->orWhere('manual_journals.reference_no','like','%'.$search.'%')->orWhereRaw("CONCAT(($case), '-', LPAD(journals.id, GREATEST(8, CHAR_LENGTH(journals.id)), '0')) LIKE ?",['%'.$search.'%']);if(ctype_digit($search))$q->orWhere('journals.source_id',$search);});}
  $v['records']=$query->orderByDesc('journals.id')->paginate(20)->withQueryString();
  return view('public.business.admin.retail.reports.journals',$v);
 }
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

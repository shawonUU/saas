<?php
namespace App\Http\Controllers\Public\Business\Admin\Finance\FinancialReports;
use App\Http\Controllers\Controller;
use App\Models\Accounts\JournalLine;
use App\Models\Accounts\Account;
use App\Services\Public\Business\Admin\Authorization\AccessControl\Access;
use App\Services\Public\Business\Admin\Shared\Lists;
use App\Services\Public\Business\Admin\Finance\Ledger\JournalEntries\Accounting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class FinancialReportController extends Controller {
 public function cash(Request $r,Access $a){$v=$a->check($r,'finance','financial-reports','cash-flow');$v['title']='Cash Flow';
  $v['records']=Lists::page(JournalLine::with(['account','journal'])->where('business_id',$v['business']->id)->whereHas('account',fn($q)=>$q->whereIn('code',['1000','1100'])),$r,['id'=>'number','journal_id'=>'number','account_id'=>'number','debit'=>'number','credit'=>'number','created_at'=>'date']);return view('public.business.admin.retail.reports.cash',$v);}
 public function trial(Request $r,Access $a){$v=$a->check($r,'finance','financial-reports','trial-balance');$v['title']='Trial Balance';return $this->balance($v,'trial');}
 public function profit(Request $r,Access $a){$v=$a->check($r,'finance','financial-reports','profit-loss');$v['title']='Profit & Loss';return $this->balance($v,'profit');}
 public function sheet(Request $r,Access $a){$v=$a->check($r,'finance','financial-reports','balance-sheet');$v['title']='Balance Sheet';return $this->balance($v,'sheet');}
 private function balance(array $v,string $mode){app(Accounting::class)->ensure($v['business']);$v['mode']=$mode;
  $v['accounts']=Account::where('accounts.business_id',$v['business']->id)->leftJoin('journal_lines',function($j){$j->on('journal_lines.account_id','=','accounts.id')->on('journal_lines.business_id','=','accounts.business_id');})
    ->groupBy('accounts.id','accounts.code','accounts.name','accounts.type')
    ->select('accounts.id','accounts.code','accounts.name','accounts.type',DB::raw('COALESCE(SUM(journal_lines.debit),0) AS debit'),DB::raw('COALESCE(SUM(journal_lines.credit),0) AS credit'))->orderBy('accounts.code')->get();
  return view('public.business.admin.retail.reports.balance',$v);}
}

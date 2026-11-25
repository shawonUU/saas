<?php
namespace App\Http\Controllers\Public\Business\Admin\Finance\Ledger\GeneralLedger;
use App\Http\Controllers\Controller;
use App\Models\Accounts\JournalLine;
use App\Services\Public\Business\Admin\Authorization\AccessControl\Access;
use App\Services\Public\Business\Admin\Shared\Lists;
use Illuminate\Http\Request;
class GeneralLedgerController extends Controller {
 public function ledger(Request $r,Access $a){$v=$a->check($r,'finance','ledger','general-ledger');$v['title']='General Ledger';
  $v['records']=Lists::page(JournalLine::with(['account','journal'])->where('business_id',$v['business']->id),$r,['id'=>'number','journal_id'=>'number','account_id'=>'number','debit'=>'number','credit'=>'number','created_at'=>'date']);return view('public.business.admin.retail.reports.ledger',$v);}
}

<?php
namespace App\Http\Controllers\Public\Business\Admin\Finance\Ledger\JournalEntries;
use App\Http\Controllers\Controller;
use App\Models\Accounts\Account;
use App\Models\Accounts\ManualJournal;
use App\Models\Accounts\Journal;
use App\Services\Public\Business\Admin\Finance\Ledger\JournalEntries\Accounting;
use App\Services\Public\Business\Admin\Authorization\AccessControl\Access;
use App\Services\Public\Business\Admin\Shared\Lists;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Validation\ValidationException;
class ManualJournalController extends Controller {
 public function index(Request $r,Access $a){$v=$a->check($r,'finance','ledger','journal-entries');$b=$v['business'];app(Accounting::class)->ensure($b);$v['accounts']=Account::where('business_id',$b->id)->where('is_active',true)->orderBy('code')->get();$v['records']=Lists::page(ManualJournal::where('business_id',$b->id),$r,['id'=>'number','reference_no'=>'text','voucher_type'=>'text','description'=>'text','created_at'=>'date']);return view('public.business.admin.retail.manual_journals.index',$v);}
 public function store(Request $r,Access $a){$v=$a->check($r,'finance','ledger','journal-entries','create');$b=$v['business'];$d=$r->validate(['voucher_type'=>'required|in:PV,RV,CV,JV','description'=>'required|string|max:255','lines'=>'required|array|min:2|max:30','lines.*.account_id'=>'required|integer','lines.*.debit'=>'required|numeric|min:0','lines.*.credit'=>'required|numeric|min:0']);DB::transaction(function()use($b,$d){$entries=[];foreach($d['lines'] as $line){$acc=Account::where('business_id',$b->id)->where('is_active',true)->findOrFail($line['account_id']);if(isset($entries[$acc->code]))throw ValidationException::withMessages(['lines'=>'Select each account only once.']);$entries[$acc->code]=[(float)$line['debit'],(float)$line['credit']];}if($d['voucher_type']!=='JV'){ $cashDebit=0;$cashCredit=0;foreach($entries as $code=>[$debit,$credit]){if(in_array((string)$code,['1000','1010','1100','1110'],true)){$cashDebit+=$debit;$cashCredit+=$credit;}elseif($d['voucher_type']==='CV')throw ValidationException::withMessages(['lines'=>t('Contra vouchers require cash or bank accounts only.','স্থানান্তর ভাউচারে শুধু নগদ বা ব্যাংক হিসাব ব্যবহার করুন।')]);} $valid=match($d['voucher_type']){'PV'=>$cashCredit>0&&$cashDebit==0,'RV'=>$cashDebit>0&&$cashCredit==0,'CV'=>$cashDebit>0&&$cashCredit>0};if(!$valid)throw ValidationException::withMessages(['lines'=>t('Invalid cash or bank direction for this voucher.','ভাউচারের নগদ বা ব্যাংক হিসাবের দিক সঠিক নয়।')]);} $doc=ManualJournal::create(['business_id'=>$b->id,'voucher_type'=>$d['voucher_type'],'reference_no'=>getGenerateCode(ManualJournal::where('business_id',$b->id),'reference_no',$d['voucher_type']),'description'=>$d['description']]);app(Accounting::class)->post($b,'manual',$doc->id,$doc->description,$entries);$doc->update(['journal_id'=>Journal::where('business_id',$b->id)->where('source_type','manual')->where('source_id',$doc->id)->value('id')]);});return back()->with('success',t('Journal posted.','জার্নাল পোস্ট হয়েছে।'));}
}

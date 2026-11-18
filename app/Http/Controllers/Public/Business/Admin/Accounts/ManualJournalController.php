<?php
namespace App\Http\Controllers\Public\Business\Admin\Accounts;
use App\Http\Controllers\Controller;
use App\Models\Retail\{Account,ManualJournal,Journal};
use App\Services\Retail\{Accounting,Access,Lists};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
class ManualJournalController extends Controller {
 public function index(Request $r,Access $a){$v=$a->check($r,'finance','ledger','journal-entries');$b=$v['business'];app(Accounting::class)->ensure($b);$v['accounts']=Account::where('business_id',$b->id)->where('is_active',true)->orderBy('code')->get();$v['records']=Lists::page(ManualJournal::where('business_id',$b->id),$r,['id'=>'number','reference_no'=>'text','description'=>'text','created_at'=>'date']);return view('public.business.admin.retail.manual_journals.index',$v);}
 public function store(Request $r,Access $a){$v=$a->check($r,'finance','ledger','journal-entries','create');$b=$v['business'];$d=$r->validate(['description'=>'required|string|max:1000','lines'=>'required|array|min:2|max:30','lines.*.account_id'=>'required|integer','lines.*.debit'=>'required|numeric|min:0','lines.*.credit'=>'required|numeric|min:0']);DB::transaction(function()use($b,$d){$entries=[];foreach($d['lines'] as $line){$acc=Account::where('business_id',$b->id)->where('is_active',true)->findOrFail($line['account_id']);if(isset($entries[$acc->code]))throw ValidationException::withMessages(['lines'=>'Select each account only once.']);$entries[$acc->code]=[(float)$line['debit'],(float)$line['credit']];}$doc=ManualJournal::create(['business_id'=>$b->id,'reference_no'=>'MJ-'.Str::upper(Str::random(9)),'description'=>$d['description']]);app(Accounting::class)->post($b,'manual',$doc->id,$doc->description,$entries);$doc->update(['journal_id'=>Journal::where('business_id',$b->id)->where('source_type','manual')->where('source_id',$doc->id)->value('id')]);});return back()->with('success',t('Journal posted.','জার্নাল পোস্ট হয়েছে।'));}
}

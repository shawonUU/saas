<?php
namespace App\Http\Controllers\Public\Business\Admin\Retail;
use App\Http\Controllers\Controller;
use App\Models\Retail\CashTransfer;
use App\Services\Retail\{Access,Accounting,Lists};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class BankTransferController extends Controller {
 public function index(Request $r,Access $a){$v=$a->check($r,'finance','banking','bank-transfers');$v['records']=Lists::page(CashTransfer::where('business_id',$v['business']->id),$r,['id'=>'number','direction'=>'text','amount'=>'number','reference_no'=>'text','created_at'=>'date']);return view('public.business.admin.retail.bank_transfers.index',$v);}
 public function store(Request $r,Access $a){$v=$a->check($r,'finance','banking','bank-transfers','create');$b=$v['business'];$d=$r->validate(['direction'=>'required|in:cash_to_bank,bank_to_cash','amount'=>'required|numeric|gt:0|max:99999999','reference_no'=>'required|string|max:100']);DB::transaction(function()use($b,$d){$amount=round((float)$d['amount'],2);if($amount<=0)throw ValidationException::withMessages(['amount'=>'Amount too small.']);$record=CashTransfer::create([...$d,'business_id'=>$b->id]);$entries=$d['direction']==='cash_to_bank'?['1000'=>[0,$amount],'1100'=>[$amount,0]]:['1000'=>[$amount,0],'1100'=>[0,$amount]];app(Accounting::class)->post($b,'cash_transfer',$record->id,'Cash/bank transfer: '.$d['reference_no'],$entries);});return back()->with('success',t('Transfer posted.','হিসাব পোস্ট হয়েছে।'));}
}

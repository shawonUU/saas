<?php
namespace App\Http\Controllers\Public\Business\Admin\Retail;
use App\Http\Controllers\Controller;
use App\Models\Retail\Expense;
use App\Services\Retail\{Accounting,Access,Lists};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class ExpenseController extends Controller {
 public function index(Request $r,Access $a){$v=$a->check($r,'finance','expenses','expense-list');$v['records']=Lists::page(Expense::where('business_id',$v['business']->id),$r,['id'=>'number','description'=>'text','amount'=>'number','method'=>'text','expense_date'=>'date']);return view('public.business.admin.retail.expenses.index',$v);}
 public function store(Request $r,Access $a){$v=$a->check($r,'finance','expenses','new-expense','create');$b=$v['business'];$d=$r->validate(['description'=>'required|string|max:255','amount'=>'required|numeric|gt:0|max:99999999','method'=>'required|in:cash,bank','expense_date'=>'required|date']);DB::transaction(function()use($b,$d){$e=Expense::create([...$d,'business_id'=>$b->id]);$amount=round((float)$d['amount'],2);app(Accounting::class)->post($b,'expense',$e->id,$e->description,['6000'=>[$amount,0],$d['method']==='cash'?'1000':'1100'=>[0,$amount]]);});return redirect()->route('store.admin.retail.expenses.index')->with('success',t('Expense posted.','খরচ পোস্ট হয়েছে।'));}
}

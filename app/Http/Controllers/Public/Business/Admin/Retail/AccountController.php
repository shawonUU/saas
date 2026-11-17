<?php
namespace App\Http\Controllers\Public\Business\Admin\Retail;
use App\Http\Controllers\Controller;
use App\Models\Retail\Account;
use App\Services\Retail\{Access,Lists,Accounting};
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class AccountController extends Controller {
 public function index(Request $r,Access $a){$v=$a->check($r,'finance','chart-of-accounts','account-list');app(Accounting::class)->ensure($v['business']);$v['title']='Chart of Accounts';$v['records']=Lists::page(Account::where('business_id',$v['business']->id),$r,['id'=>'number','code'=>'text','name'=>'text','type'=>'text','is_system'=>'number','is_active'=>'number']);return view('public.business.admin.retail.accounts.index',$v);}
 public function store(Request $r,Access $a){$v=$a->check($r,'finance','chart-of-accounts','account-list','create');$d=$r->validate(['code'=>['required','regex:/^[0-9]{4,8}$/',Rule::unique('retail_accounts')->where('business_id',$v['business']->id)],'name'=>'required|string|max:255','type'=>'required|in:asset,liability,equity,income,expense']);if((int)$d['code']<8000)return back()->withErrors(['code'=>'Custom account codes must be 8000 or above.']);$d['is_system']=false;Account::create([...$d,'business_id'=>$v['business']->id]);return back()->with('success',t('Account created.','হিসাব খাত তৈরি।'));}
 public function update(Request $r,int $record,Access $a){$v=$a->check($r,'finance','chart-of-accounts','account-list','update');$acc=Account::where('business_id',$v['business']->id)->findOrFail($record);abort_if($acc->is_system,403,'System accounts cannot be edited.');$d=$r->validate(['name'=>'required|string|max:255','is_active'=>'required|boolean']);$acc->update($d);return back()->with('success',t('Account updated.','হিসাব আপডেট।'));}
}

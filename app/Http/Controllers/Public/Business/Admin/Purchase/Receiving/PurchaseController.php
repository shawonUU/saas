<?php
namespace App\Http\Controllers\Public\Business\Admin\Purchase\Receiving;
use App\Http\Controllers\Controller;
use App\Models\Purchase\Purchase;
use App\Models\Configurations\Variant;
use App\Services\Public\Business\Admin\Authorization\AccessControl\Access;
use App\Services\Public\Business\Admin\Shared\Lists;
use App\Services\Public\Business\Admin\Purchase\Receiving\NewGrn\Purchases;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
class PurchaseController extends Controller {
 public function index(Request $r,Access $a){$v=$a->check($r,'purchase','receiving','grn-list');$v['title']='Goods Receipts';$v['records']=Lists::page(Purchase::where('business_id',$v['business']->id),$r,['id'=>'number','purchase_no'=>'text','supplier_id'=>'number','total'=>'number','paid'=>'number','created_at'=>'date']);return view('public.business.admin.retail.purchases.index',$v);}
 public function create(Request $r,Access $a){$v=$a->check($r,'purchase','receiving','new-grn');$v['title']='Receive Inventory';$v['variants']=Variant::with('product')->where('business_id',$v['business']->id)->get();$v['suppliers']=DB::table('config_suppliers')->where('business_id',$v['business']->id)->where('is_active',true)->get();return view('public.business.admin.retail.purchases.form',$v);}
 public function store(Request $r,Access $a,Purchases $service){$v=$a->check($r,'purchase','receiving','new-grn','create');$d=$r->validate(['supplier_id'=>['nullable',Rule::exists('config_suppliers','id')->where('business_id',$v['business']->id)],'notes'=>'nullable|string|max:255','items'=>'required|array|min:1|max:100','items.*.variant_id'=>'required|integer','items.*.quantity'=>'required|numeric|gt:0|max:999999','items.*.cost'=>'required|numeric|min:0','pay_amount'=>'nullable|numeric|min:0','payment_method'=>'nullable|in:cash,bank']);
  $p=$service->create($v['business'],$d);return redirect()->route('business.admin.purchase.receiving.grn-list.index')->with('success',t('Inventory received and accounts posted.','স্টক গ্রহণ এবং হিসাব পোস্ট হয়েছে।'));}
}

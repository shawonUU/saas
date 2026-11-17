<?php
namespace App\Http\Controllers\Public\Business\Admin\Retail;
use App\Http\Controllers\Controller;
use App\Models\Retail\{SaleReturn,Order};
use App\Services\Retail\{Access,Lists,Returns};
use Illuminate\Http\Request;
class ReturnController extends Controller {
 public function index(Request $r,Access $a){$v=$a->check($r,'sales','returns','return-list');$v['title']='Sales Returns';$v['records']=Lists::page(SaleReturn::where('business_id',$v['business']->id),$r,['id'=>'number','order_id'=>'number','amount'=>'number','refunded'=>'number','reason'=>'text','created_at'=>'date']);return view('public.business.admin.retail.returns.index',$v);}
 public function create(Request $r,Access $a){$v=$a->check($r,'sales','returns','new-return');$v['title']='New Sales Return';$v['orders']=Order::with('items')->where('business_id',$v['business']->id)->where('status','confirmed')->latest()->limit(200)->get();return view('public.business.admin.retail.returns.form',$v);}
 public function store(Request $r,Access $a,Returns $service){$v=$a->check($r,'sales','returns','new-return','create');$data=$r->validate(['order_id'=>'required|integer','reason'=>'nullable|string|max:255','items'=>'required|array|min:1|max:100','items.*.order_item_id'=>'required|integer','items.*.quantity'=>'required|numeric|min:0']);$service->create($v['business'],$data);return redirect()->route('store.admin.retail.returns.index')->with('success',t('Return posted. Stock and accounts updated.','রিটার্ন পোস্ট হয়েছে। স্টক ও হিসাব আপডেট।'));}
}

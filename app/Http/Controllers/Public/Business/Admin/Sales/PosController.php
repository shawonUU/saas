<?php
namespace App\Http\Controllers\Public\Business\Admin\Sales;
use App\Http\Controllers\Controller;
use App\Models\Retail\Variant;
use App\Services\Retail\{Access,Sales};
use Illuminate\Http\Request;
class PosController extends Controller {
 public function index(Request $r,Access $a){$v=$a->check($r,'pos','counter','new-sale');$v['title']='POS Counter';$v['variants']=Variant::with('product')->where('business_id',$v['business']->id)->where('is_active',true)->get()->filter(fn($x)=>$x->product?->is_active);
  return view('public.business.admin.retail.pos.index',$v);}
 public function store(Request $r,Access $a,Sales $sales){$v=$a->check($r,'pos','counter','new-sale','create');$d=OrderController::rules($r,$v['business']->id);$d['pay_amount']=$r->input('pay_amount',0);
  $o=$sales->create($v['business'],$d,'pos');return redirect()->route('business.admin.retail.orders.show',['order'=>$o->id])->with('success',t('POS sale saved.','পস বিক্রয় সংরক্ষিত।'));}
}

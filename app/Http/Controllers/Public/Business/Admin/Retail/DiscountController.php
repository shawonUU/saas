<?php
namespace App\Http\Controllers\Public\Business\Admin\Retail;
use App\Http\Controllers\Controller;
use App\Models\Retail\Discount;
use App\Services\Retail\{Access,Lists};
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class DiscountController extends Controller {
 public function index(Request $r,Access $a){$v=$a->check($r,'sales','discounts','coupons');$v['title']='Discount Coupons';$v['records']=Lists::page(Discount::where('business_id',$v['business']->id),$r,['id'=>'number','code'=>'text','type'=>'text','value'=>'number','is_active'=>'number','created_at'=>'date']);return view('public.business.admin.retail.discounts.index',$v);}
 public function store(Request $r,Access $a){$v=$a->check($r,'sales','discounts','coupons','create');$d=$r->validate(['code'=>['required','string','max:50',Rule::unique('retail_discounts')->where('business_id',$v['business']->id)],'type'=>'required|in:fixed,percent','value'=>'required|numeric|gt:0','starts_at'=>'nullable|date','ends_at'=>'nullable|date|after:starts_at','is_active'=>'required|boolean']);if($d['type']==='percent' && $d['value']>100)return back()->withErrors(['value'=>'Percentage must be within 100.']);$d['code']=strtoupper(trim($d['code']));Discount::create([...$d,'business_id'=>$v['business']->id]);return back()->with('success',t('Coupon created.','কুপন তৈরি।'));}
 public function update(Request $r,int $record,Access $a){$v=$a->check($r,'sales','discounts','coupons','update');$discount=Discount::where('business_id',$v['business']->id)->findOrFail($record);$d=$r->validate(['is_active'=>'required|boolean']);$discount->update($d);return back()->with('success',t('Coupon updated.','কুপন আপডেট।'));}
}

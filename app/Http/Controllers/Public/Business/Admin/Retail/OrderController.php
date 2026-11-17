<?php
namespace App\Http\Controllers\Public\Business\Admin\Retail;
use App\Http\Controllers\Controller;
use App\Models\Retail\{Order,Variant};
use App\Services\Retail\{Access,Lists,Sales};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class OrderController extends Controller {
 public function index(Request $r,Access $a){$v=$a->check($r,'sales','orders','order-list');$v['title']='Orders';$v['records']=Lists::page(Order::where('business_id',$v['business']->id),$r,['id'=>'number','order_no'=>'text','channel'=>'text','customer_name'=>'text','customer_phone'=>'text','subtotal'=>'number','discount'=>'number','total'=>'number','paid'=>'number','status'=>'text','delivery_status'=>'text','created_at'=>'date']);return view('public.business.admin.retail.orders.index',$v);}
 public function create(Request $r,Access $a){$v=$a->check($r,'sales','orders','new-order');$v['title']='New Sale';$v['variants']=Variant::with('product')->where('business_id',$v['business']->id)->where('is_active',true)->get()->filter(fn($x)=>$x->product?->is_active);$v['customers']=DB::table('business_customers')->where('business_id',$v['business']->id)->where('is_active',true)->get();$v['taxes']=DB::table('config_taxes')->where('business_id',$v['business']->id)->where('is_active',true)->get();$v['channel']='admin';return view('public.business.admin.retail.orders.form',$v);}
 public function store(Request $r,Access $a,Sales $service){$v=$a->check($r,'sales','orders','new-order','create');$b=$v['business'];$d=$this->rules($r,$b->id);$order=$service->create($b,$d,'admin');return redirect()->route('store.admin.retail.orders.show',['order'=>$order->id])->with('success',t('Sale posted with accounting entries.','বিক্রয় ও হিসাব পোস্ট হয়েছে।'));}
 public static function rules(Request $r,int $businessId):array {
  return $r->validate(['request_key'=>'nullable|string|max:90','customer_id'=>['nullable',\Illuminate\Validation\Rule::exists('business_customers','id')->where('business_id',$businessId)],
    'customer_name'=>'nullable|string|max:255','customer_phone'=>'nullable|string|max:40','customer_address'=>'nullable|string|max:2000',
    'items'=>'required|array|min:1|max:100','items.*.variant_id'=>'required|integer','items.*.quantity'=>'required|numeric|gt:0|max:999999',
    'discount'=>'nullable|numeric|min:0','coupon_code'=>'nullable|string|max:50','tax_id'=>'nullable|integer','shipping'=>'nullable|numeric|min:0',
    'pay_amount'=>'nullable|numeric|min:0','payment_method'=>'nullable|in:cash,bank','payment_reference'=>'nullable|string|max:255']);
 }
 public function show(Request $r,Access $a,?int $order=null){$v=$a->check($r,'sales','orders','order-details');$v['title']='Order Details';$v['order']=$order?Order::where('business_id',$v['business']->id)->with('items.variant')->findOrFail($order):null;
  $v['orders']=Order::where('business_id',$v['business']->id)->latest()->limit(100)->get();return view('public.business.admin.retail.orders.show',$v);}
 public function cancel(Request $r,int $order,Access $a,\App\Services\Retail\CancelSale $service){$v=$a->check($r,'sales','orders','order-details','delete');$record=Order::where('business_id',$v['business']->id)->findOrFail($order);$service->cancel($v['business'],$record);return back()->with('success',t('Unpaid order cancelled and stock/accounts reversed.','অপরিশোধিত অর্ডার বাতিল; স্টক ও হিসাব ফেরত হয়েছে।'));}
}

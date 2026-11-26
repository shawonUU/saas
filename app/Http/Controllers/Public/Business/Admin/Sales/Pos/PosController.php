<?php
namespace App\Http\Controllers\Public\Business\Admin\Sales\Pos;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Public\Business\Admin\Sales\Orders\OrderController;
use App\Models\Configurations\Variant;
use App\Models\Sales\Order;
use App\Services\Public\Business\Admin\Authorization\AccessControl\Access;
use App\Services\Public\Business\Admin\Sales\Orders\NewOrder\Sales;
use App\Services\Public\Business\Admin\Sales\Pos\PosService;
use App\Services\Public\Business\Admin\Finance\Payments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function index(Request $r,Access $a)
    {
        $v=$a->check($r,'sales','retail-pos');$b=$v['business'];$v['title']=t('Point of Sale','পয়েন্ট অব সেল');
        $v['variants']=Variant::with(['product','size','color'])->where('business_id',$b->id)->where('is_active',true)->get()->filter(fn($x)=>$x->product?->is_active && (int)$x->product->business_id===(int)$b->id)->values();
        $v['stocks']=DB::table('warehouse_stocks as s')->join('warehouses as w','w.id','=','s.warehouse_id')->where('s.business_id',$b->id)->where('w.business_id',$b->id)->where('w.code','MAIN')->pluck('s.quantity','s.variant_id');
        $v['categories']=DB::table('config_categories')->where('business_id',$b->id)->where('is_active',true)->whereNull('deleted_at')->orderBy('name')->pluck('name','id');
        $v['customers']=DB::table('business_customers')->where('business_id',$b->id)->where('is_active',true)->orderBy('name')->get(['id','name','phone']);
        $v['taxes']=DB::table('config_taxes')->where('business_id',$b->id)->where('is_active',true)->whereNull('deleted_at')->orderBy('name')->get();
        return view('public.business.admin.retail.pos.index',$v);
    }
    private function data(Request $r,int $business):array
    {
        $d=OrderController::rules($r,$business);
        $r->validate(['items.*.variant_id'=>'distinct','items.*.quantity'=>'decimal:0,3','discount'=>'nullable|numeric|min:0|decimal:0,2']);
        return $d;
    }
    public function quote(Request $r,Access $a,PosService $pos)
    {
        $v=$a->check($r,'sales','retail-pos',null,'create');return response()->json($pos->quote($v['business'],$this->data($r,$v['business']->id)));
    }
    public function store(Request $r,Access $a,PosService $pos,Sales $sales,Payments $payments)
    {
        $v=$a->check($r,'sales','retail-pos',null,'create');$d=$this->data($r,$v['business']->id);
        $d=array_merge($d,$r->validate(['request_key'=>'required|uuid','expected_total'=>'required|numeric|min:0|decimal:0,2','payment_mode'=>'required|in:full,partial,due','payment_method'=>'required|in:cash,bank','tendered'=>'required|numeric|min:0|decimal:0,2|max:999999999999']));
        $order=$pos->checkout($v['business'],$d,$sales,$payments);
        $receiptUrl=route('business.admin.sales.retail-pos.receipt',['business'=>$r->route('business'),'order'=>$order->id]);
        if($r->expectsJson()){
            $order->load('items');
            $business=$v['business'];
            $stocks=DB::table('warehouse_stocks as s')->join('warehouses as w','w.id','=','s.warehouse_id')->where('s.business_id',$business->id)->where('w.business_id',$business->id)->where('w.code','MAIN')->pluck('s.quantity','s.variant_id');
            $availability=Variant::with('product')->where('business_id',$business->id)->where('is_active',true)->get()->mapWithKeys(function($variant) use($stocks,$business){
                $product=$variant->product;
                return [$variant->id=>!$product || !$product->is_active || (int)$product->business_id!==(int)$business->id ? 0 : ($product->track_stock ? max(0,min((float)$variant->stock,(float)($stocks[$variant->id]??0))) : null)];
            });
            return response()->json(['success'=>true,'receipt_url'=>$receiptUrl,'request_key'=>(string)\Illuminate\Support\Str::uuid(),'stocks'=>(object)$availability->all(),'receipt'=>[
                'order_no'=>$order->order_no,'business'=>$business->name,'customer'=>$order->customer_name,'date'=>$order->created_at?->format('d M Y, h:i A'),
                'subtotal'=>(float)$order->subtotal,'discount'=>(float)$order->discount,'tax'=>(float)$order->tax,'total'=>(float)$order->total,'paid'=>(float)$order->paid,'due'=>max(0,(float)$order->total-(float)$order->paid-(float)$order->credited),
                'items'=>$order->items->map(fn($item)=>['name'=>$item->name,'quantity'=>(float)$item->quantity,'price'=>(float)$item->price,'total'=>(float)$item->line_total])->values(),
            ]]);
        }
        return redirect()->to($receiptUrl);
    }
    public function receipt(Request $r,Access $a,string $business,int $order)
    {
        $v=$a->check($r,'sales','retail-pos');$v['title']=t('Sale receipt','বিক্রয় রসিদ');
        $v['order']=Order::where('business_id',$v['business']->id)->where('channel','pos')->with('items')->findOrFail($order);
        return view('public.business.admin.retail.pos.receipt',$v);
    }
}

<?php
namespace App\Http\Controllers\Public\Business\Admin\Logistics\Delivery\DeliveryList;
use App\Http\Controllers\Controller;
use App\Models\Sales\Order;
use App\Services\Public\Business\Admin\Authorization\AccessControl\Access;
use App\Services\Public\Business\Admin\Shared\Lists;
use Illuminate\Http\Request;
class DeliveryController extends Controller {
 public function index(Request $r,Access $a){$v=$a->check($r,...[...($r->routeIs('business.admin.logistics.*')?['logistics','delivery','delivery-list']:['ecommerce','online-orders','fulfillment'])] );$v['title']='Order Fulfillment';$v['records']=Lists::page(Order::where('business_id',$v['business']->id)->where('channel','online'),$r,['id'=>'number','order_no'=>'text','customer_name'=>'text','customer_phone'=>'text','total'=>'number','delivery_status'=>'text','tracking_number'=>'text','created_at'=>'date']);return view('public.business.admin.retail.delivery.index',$v);}
 public function update(Request $r,string $business,int $record,Access $a){$v=$a->check($r,...[...($r->routeIs('business.admin.logistics.*')?['logistics','delivery','delivery-list']:['ecommerce','online-orders','fulfillment']),'update'] );$o=Order::where('business_id',$v['business']->id)->where('channel','online')->findOrFail($record);$data=$r->validate(['delivery_status'=>'required|in:pending,packed,shipped,delivered','tracking_number'=>'nullable|string|max:120']);
  $order=['pending'=>0,'packed'=>1,'shipped'=>2,'delivered'=>3];abort_if($order[$data['delivery_status']]<$order[$o->delivery_status],422,'Delivery status cannot go backwards.');$o->update($data);return back()->with('success',t('Delivery updated.','ডেলিভারি আপডেট।'));}
}

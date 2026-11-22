<?php
namespace App\Http\Controllers\Public\Business\Admin\Retail;
use App\Http\Controllers\Controller;
use App\Models\Retail\CourierShipment;
use App\Models\Sales\Order;
use App\Services\Access;
use App\Services\Lists;
use App\Services\Pathao;
use Illuminate\Http\Request;
class CourierController extends Controller {
 public function index(Request $r,Access $a){$v=$a->check($r,'ecommerce','online-orders','fulfillment');$v['records']=Lists::page(CourierShipment::where('business_id',$v['business']->id),$r,['id'=>'number','order_id'=>'number','provider'=>'text','consignment_id'=>'text','status'=>'text','created_at'=>'date']);$v['orders']=Order::where('business_id',$v['business']->id)->where('channel','online')->with('items')->latest()->limit(150)->get();return view('public.business.admin.retail.courier.index',$v);}
 public function book(Request $r,Access $a,Pathao $service){$v=$a->check($r,'ecommerce','online-orders','fulfillment','create');$b=$v['business'];$d=$r->validate(['order_id'=>'required|integer','recipient_city'=>'required|integer|min:1','recipient_zone'=>'required|integer|min:1','recipient_area'=>'nullable|integer|min:1','item_weight'=>'required|numeric|between:0.1,50']);$o=Order::where('business_id',$b->id)->where('channel','online')->where('status','confirmed')->findOrFail($d['order_id']);$service->book($b,$o,$d);return back()->with('success',t('Courier booking confirmed.','কুরিয়ার বুকিং সফল।'));}
 public function refresh(Request $r,int $record,Access $a,Pathao $service){$v=$a->check($r,'ecommerce','online-orders','fulfillment','update');$shipment=CourierShipment::where('business_id',$v['business']->id)->findOrFail($record);$service->refresh($v['business'],$shipment);return back()->with('success',t('Courier status refreshed.','কুরিয়ার স্ট্যাটাস আপডেট।'));}
 public function resetUnknown(Request $r,int $record,Access $a){$v=$a->check($r,'ecommerce','online-orders','fulfillment','delete');$shipment=CourierShipment::where('business_id',$v['business']->id)->findOrFail($record);abort_unless($shipment->status==='unknown' && !$shipment->consignment_id,422,'Only unknown bookings without a consignment can be cleared.');$r->validate(['provider_checked'=>'accepted']);$shipment->delete();return back()->with('success',t('Unknown booking cleared after merchant confirmation.','মার্চেন্ট যাচাইয়ের পর বুকিং রিসেট হয়েছে।'));}
}

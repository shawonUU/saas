<?php
namespace App\Services\Retail;
use App\Models\Business;
use App\Models\Retail\{Integration,Order,CourierShipment};
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
class Pathao {
 private function api(Integration $i):string{return !empty($i->settings['sandbox'])?'https://courier-api-sandbox.pathao.com':'https://api-hermes.pathao.com';}
 private function configured(Business $b):Integration {
  $i=Integration::where('business_id',$b->id)->where('provider','pathao')->where('is_active',true)->first();
  if(!$i)throw ValidationException::withMessages(['courier'=>'Configure and enable Pathao first.']);return $i;
 }
 private function token(Integration $i):string {
  $s=$i->settings;$r=Http::acceptJson()->timeout(18)->post($this->api($i).'/aladdin/api/v1/issue-token',['client_id'=>$s['client_id'],'client_secret'=>$s['client_secret'],'username'=>$s['username'],'password'=>$s['password'],'grant_type'=>'password']);
  $token=$r->json('access_token')??$r->json('data.access_token');
  if(!$r->successful()||!is_string($token)||!$token)throw ValidationException::withMessages(['courier'=>'Pathao authorization failed. Check merchant credentials.']);return $token;
 }
 public function book(Business $b,Order $o,array $details):CourierShipment {
  $i=$this->configured($b);$old=CourierShipment::where('business_id',$b->id)->where('order_id',$o->id)->where('provider','pathao')->first();
  if($old)throw ValidationException::withMessages(['courier'=>'Shipment already submitted or state unknown. Check tracking before booking again.']);
  $token=$this->token($i);
  $shipment=CourierShipment::create(['business_id'=>$b->id,'order_id'=>$o->id,'provider'=>'pathao','status'=>'processing','request_id'=>$o->order_no]);
  $payload=['store_id'=>(int)$i->settings['pathao_store_id'],'merchant_order_id'=>$o->order_no,'sender_name'=>$i->settings['sender_name'],'sender_phone'=>$i->settings['sender_phone'],'recipient_name'=>$o->customer_name,'recipient_phone'=>$o->customer_phone,'recipient_address'=>$o->customer_address,'recipient_city'=>(int)$details['recipient_city'],'recipient_zone'=>(int)$details['recipient_zone'],'recipient_area'=>isset($details['recipient_area'])?(int)$details['recipient_area']:null,'delivery_type'=>48,'item_type'=>2,'item_quantity'=>max(1,(int)$o->items()->sum('quantity')),'item_weight'=>(float)$details['item_weight'],'amount_to_collect'=>max(0,round((float)$o->total-(float)$o->paid-(float)$o->credited,2)),'item_description'=>'Order '.$o->order_no];
  try{$response=Http::withToken($token)->acceptJson()->timeout(25)->post($this->api($i).'/aladdin/api/v1/orders',array_filter($payload,fn($v)=>$v!==null));$json=$response->json();$consignment=data_get($json,'data.consignment_id');if(!$response->successful()||!$consignment){$shipment->update(['status'=>'unknown','response'=>mb_substr($response->body(),0,4000)]);throw ValidationException::withMessages(['courier'=>'Pathao did not confirm booking. Review merchant panel before retrying.']);}$shipment->update(['status'=>(string)(data_get($json,'data.order_status')??'submitted'),'consignment_id'=>(string)$consignment,'response'=>mb_substr($response->body(),0,4000)]);$o->update(['tracking_number'=>(string)$consignment,'delivery_status'=>'packed']);return $shipment;}
  catch(\Throwable $e){$shipment->refresh();if($shipment->status==='processing')$shipment->update(['status'=>'unknown']);throw $e;}
 }
 public function refresh(Business $b,CourierShipment $shipment):void {
  $i=$this->configured($b);if(!$shipment->consignment_id)throw ValidationException::withMessages(['courier'=>'No confirmed consignment ID; check provider dashboard.']);
  $r=Http::withToken($this->token($i))->acceptJson()->timeout(18)->get($this->api($i).'/aladdin/api/v1/orders/'.rawurlencode($shipment->consignment_id).'/info');
  if(!$r->successful())throw ValidationException::withMessages(['courier'=>'Unable to retrieve courier status.']);
  $shipment->update(['status'=>(string)($r->json('data.order_status')??$shipment->status),'response'=>mb_substr($r->body(),0,4000)]);
 }
}

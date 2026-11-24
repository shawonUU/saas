<?php
namespace App\Services;
use App\Models\Ecommerce\CustomerAddress;
use Illuminate\Http\Request;
class CustomerAddressService {
 public function query(Request $r){return CustomerAddress::where('business_id',$r->attributes->get('current_business')->id)->where('customer_id',$r->user('business_customer')->id);}
 public function data(Request $r):array{return $r->validate(['label'=>'required|string|max:80','recipient'=>'required|string|max:255','phone'=>'required|string|max:40','district'=>'required|string|max:100','city'=>'required|string|max:100','address_line'=>'required|string|max:1400']);}
 public function create(Request $r){return CustomerAddress::create($this->data($r)+['business_id'=>$r->attributes->get('current_business')->id,'customer_id'=>$r->user('business_customer')->id]);}
}

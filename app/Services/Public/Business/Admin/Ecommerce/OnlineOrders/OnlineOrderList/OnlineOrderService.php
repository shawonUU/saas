<?php
namespace App\Services\Public\Business\Admin\Ecommerce\OnlineOrders\OnlineOrderList;
use App\Models\Sales\Order;
use App\Models\Business;
class OnlineOrderService {
 public function query(Business $business){return Order::where('business_id',$business->id)->where('channel','online');}
 public function customer(Business $business,int $customerId){return $this->query($business)->where('customer_id',$customerId);}
}

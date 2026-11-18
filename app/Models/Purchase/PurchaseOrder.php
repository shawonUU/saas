<?php
namespace App\Models\Purchase;
use Illuminate\Database\Eloquent\Model;
class PurchaseOrder extends Model {protected $table='purchase_orders';protected $guarded=[];public function items(){return $this->hasMany(PurchaseOrderItem::class,'purchase_order_id');} public function receipts(){return $this->hasMany(PurchaseOrderReceipt::class,'purchase_order_id');}}

<?php
namespace App\Models\Retail;
use Illuminate\Database\Eloquent\Model;
class PurchaseOrder extends Model {protected $table='retail_purchase_orders';protected $guarded=[];public function items(){return $this->hasMany(PurchaseOrderItem::class,'purchase_order_id');} public function receipts(){return $this->hasMany(PurchaseOrderReceipt::class,'purchase_order_id');}}

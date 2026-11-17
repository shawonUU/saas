<?php
namespace App\Models\Retail;
use Illuminate\Database\Eloquent\Model;
class Order extends Model {  protected $table='retail_orders'; protected $guarded=[]; public function items(){return $this->hasMany(OrderItem::class,'order_id');} }

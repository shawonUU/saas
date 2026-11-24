<?php
namespace App\Models\Sales;
use Illuminate\Database\Eloquent\Model;
class Order extends Model { use \App\Models\Concerns\ErpCrud;  protected $table='orders'; protected $guarded=[]; public function items(){return $this->hasMany(OrderItem::class,'order_id');} }

<?php
namespace App\Models\Purchase;
use Illuminate\Database\Eloquent\Model;
class PurchaseOrderItem extends Model { use \App\Models\Concerns\ErpCrud;protected $table='purchase_order_items';protected $guarded=[];}

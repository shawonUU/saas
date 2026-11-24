<?php
namespace App\Models\Inventory;
use Illuminate\Database\Eloquent\Model;
class WarehouseStock extends Model { use \App\Models\Concerns\ErpCrud;protected $table='warehouse_stocks';protected $guarded=[];}

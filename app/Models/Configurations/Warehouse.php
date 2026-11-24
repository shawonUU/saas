<?php
namespace App\Models\Configurations;
use Illuminate\Database\Eloquent\Model;
class Warehouse extends Model { use \App\Models\Concerns\ErpCrud;protected $table='warehouses';protected $guarded=[];public function stocks(){return $this->hasMany(WarehouseStock::class,'warehouse_id');}}

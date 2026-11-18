<?php
namespace App\Models\Configurations;
use Illuminate\Database\Eloquent\Model;
class Warehouse extends Model {protected $table='retail_warehouses';protected $guarded=[];public function stocks(){return $this->hasMany(WarehouseStock::class,'warehouse_id');}}

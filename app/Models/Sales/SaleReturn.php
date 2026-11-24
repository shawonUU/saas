<?php
namespace App\Models\Sales;
use Illuminate\Database\Eloquent\Model;
class SaleReturn extends Model { use \App\Models\Concerns\ErpCrud;  protected $table='returns'; protected $guarded=[]; public function items(){return $this->hasMany(ReturnItem::class,'return_id');} }

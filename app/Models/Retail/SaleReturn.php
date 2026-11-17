<?php
namespace App\Models\Retail;
use Illuminate\Database\Eloquent\Model;
class SaleReturn extends Model {  protected $table='retail_returns'; protected $guarded=[]; public function items(){return $this->hasMany(ReturnItem::class,'return_id');} }

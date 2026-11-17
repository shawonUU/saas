<?php
namespace App\Models\Retail;
use Illuminate\Database\Eloquent\Model;
class Purchase extends Model {  protected $table='retail_purchases'; protected $guarded=[]; public function items(){return $this->hasMany(PurchaseItem::class,'purchase_id');} }

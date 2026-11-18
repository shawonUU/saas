<?php
namespace App\Models\Purchase;
use Illuminate\Database\Eloquent\Model;
class Purchase extends Model {  protected $table='purchases'; protected $guarded=[]; public function items(){return $this->hasMany(PurchaseItem::class,'purchase_id');} }

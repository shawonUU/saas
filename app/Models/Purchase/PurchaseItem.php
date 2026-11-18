<?php
namespace App\Models\Purchase;
use Illuminate\Database\Eloquent\Model;
class PurchaseItem extends Model {  protected $table='purchase_items'; protected $guarded=[]; public function variant(){return $this->belongsTo(Variant::class,'variant_id');} }

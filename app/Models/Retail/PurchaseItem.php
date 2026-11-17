<?php
namespace App\Models\Retail;
use Illuminate\Database\Eloquent\Model;
class PurchaseItem extends Model {  protected $table='retail_purchase_items'; protected $guarded=[]; public function variant(){return $this->belongsTo(Variant::class,'variant_id');} }

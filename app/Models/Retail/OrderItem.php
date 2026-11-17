<?php
namespace App\Models\Retail;
use Illuminate\Database\Eloquent\Model;
class OrderItem extends Model {  protected $table='retail_order_items'; protected $guarded=[]; public function variant(){return $this->belongsTo(Variant::class,'variant_id');} }

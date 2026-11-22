<?php
namespace App\Models\Sales;
use App\Models\Configurations\Variant;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model {  protected $table='order_items'; protected $guarded=[]; public function variant(){return $this->belongsTo(Variant::class,'variant_id');} }

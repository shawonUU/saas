<?php
namespace App\Models\Retail;
use Illuminate\Database\Eloquent\Model;
class StockMovement extends Model {  protected $table='retail_stock_movements'; protected $guarded=[]; public function variant(){return $this->belongsTo(Variant::class,'variant_id');} }

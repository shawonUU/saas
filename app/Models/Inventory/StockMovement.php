<?php
namespace App\Models\Inventory;
use Illuminate\Database\Eloquent\Model;
class StockMovement extends Model {  protected $table='stock_movements'; protected $guarded=[]; public function variant(){return $this->belongsTo(Variant::class,'variant_id');} }

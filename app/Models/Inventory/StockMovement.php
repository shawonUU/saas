<?php
namespace App\Models\Inventory;
use Illuminate\Database\Eloquent\Model;
class StockMovement extends Model { use \App\Models\Concerns\ErpCrud;  protected $table='stock_movements'; protected $guarded=[]; public function variant(){return $this->belongsTo(Variant::class,'variant_id');} }

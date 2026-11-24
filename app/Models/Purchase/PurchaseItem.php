<?php
namespace App\Models\Purchase;
use Illuminate\Database\Eloquent\Model;
class PurchaseItem extends Model { use \App\Models\Concerns\ErpCrud;  protected $table='purchase_items'; protected $guarded=[]; public function variant(){return $this->belongsTo(Variant::class,'variant_id');} }

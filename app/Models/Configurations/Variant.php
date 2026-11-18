<?php
namespace App\Models\Configurations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Variant extends Model {  protected $table='retail_variants'; protected $guarded=[]; public function size(){return $this->belongsTo(\App\Models\Configurations\Size::class,'size_id');} public function color(){return $this->belongsTo(\App\Models\Configurations\Color::class,'color_id');} public function product(){return $this->belongsTo(Product::class,'product_id');} }

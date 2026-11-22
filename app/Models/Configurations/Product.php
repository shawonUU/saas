<?php
namespace App\Models\Configurations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Product extends Model { use SoftDeletes; protected $table='products'; protected $guarded=[]; public function variants(){return $this->hasMany(Variant::class,'product_id');} }

<?php
namespace App\Models\Configurations;
use Illuminate\Database\Eloquent\Model;
class RecipeItem extends Model {
 use \App\Models\Concerns\ErpCrud;
 protected $table='recipe_items'; protected $guarded=[];
 public function food(){return $this->belongsTo(Variant::class,'food_variant_id');}
 public function ingredient(){return $this->belongsTo(Variant::class,'ingredient_variant_id');}
}

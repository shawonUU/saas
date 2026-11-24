<?php
namespace App\Models\Retail;
use Illuminate\Database\Eloquent\Model;
class AbandonedCart extends Model { use \App\Models\Concerns\ErpCrud;protected $table='abandoned_carts';protected $guarded=[];}

<?php
namespace App\Models\Sales;
use Illuminate\Database\Eloquent\Model;
class Discount extends Model { use \App\Models\Concerns\ErpCrud;  protected $table='discounts'; protected $guarded=[];  }

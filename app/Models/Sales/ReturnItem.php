<?php
namespace App\Models\Sales;
use Illuminate\Database\Eloquent\Model;
class ReturnItem extends Model { use \App\Models\Concerns\ErpCrud;  protected $table='return_items'; protected $guarded=[];  }

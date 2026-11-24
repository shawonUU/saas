<?php
namespace App\Models\Purchase;
use Illuminate\Database\Eloquent\Model;
class PurchaseReturn extends Model { use \App\Models\Concerns\ErpCrud;protected $table='purchase_returns';protected $guarded=[];}

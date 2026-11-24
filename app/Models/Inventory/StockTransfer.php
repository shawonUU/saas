<?php
namespace App\Models\Inventory;
use Illuminate\Database\Eloquent\Model;
class StockTransfer extends Model { use \App\Models\Concerns\ErpCrud;protected $table='stock_transfers';protected $guarded=[];}

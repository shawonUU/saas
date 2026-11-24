<?php
namespace App\Models\Inventory;
use Illuminate\Database\Eloquent\Model;
class StockTransferItem extends Model { use \App\Models\Concerns\ErpCrud;protected $table='stock_transfer_items';protected $guarded=[];}

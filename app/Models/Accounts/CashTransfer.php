<?php
namespace App\Models\Accounts;
use Illuminate\Database\Eloquent\Model;
class CashTransfer extends Model { use \App\Models\Concerns\ErpCrud;protected $table='cash_transfers';protected $guarded=[];}

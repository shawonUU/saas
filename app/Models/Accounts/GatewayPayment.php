<?php
namespace App\Models\Accounts;
use Illuminate\Database\Eloquent\Model;
class GatewayPayment extends Model { use \App\Models\Concerns\ErpCrud;protected $table='gateway_payments';protected $guarded=[];}

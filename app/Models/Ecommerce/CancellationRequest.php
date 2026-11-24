<?php
namespace App\Models\Ecommerce;
use Illuminate\Database\Eloquent\Model;
class CancellationRequest extends Model {use \App\Models\Concerns\ErpCrud;protected $table='ecommerce_cancellation_requests';protected $guarded=[];}

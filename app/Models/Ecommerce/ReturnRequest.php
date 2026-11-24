<?php
namespace App\Models\Ecommerce;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ErpCrud;
class ReturnRequest extends Model { use ErpCrud; protected $table='ecommerce_return_requests'; protected $guarded=[]; }

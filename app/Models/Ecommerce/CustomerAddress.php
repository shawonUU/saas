<?php
namespace App\Models\Ecommerce;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\ErpCrud;
class CustomerAddress extends Model { use ErpCrud,SoftDeletes; protected $table='ecommerce_customer_addresses'; protected $guarded=[]; }

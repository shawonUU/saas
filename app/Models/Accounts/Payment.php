<?php
namespace App\Models\Accounts;
use Illuminate\Database\Eloquent\Model;
class Payment extends Model { use \App\Models\Concerns\ErpCrud;  protected $table='payments'; protected $guarded=[];  }

<?php
namespace App\Models\Accounts;
use Illuminate\Database\Eloquent\Model;
class Account extends Model { use \App\Models\Concerns\ErpCrud;  protected $table='accounts'; protected $guarded=[];  }

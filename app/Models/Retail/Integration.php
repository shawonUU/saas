<?php
namespace App\Models\Retail;
use Illuminate\Database\Eloquent\Model;
class Integration extends Model { use \App\Models\Concerns\ErpCrud;protected $table='integrations';protected $guarded=[];protected $casts=['settings'=>'encrypted:array'];}

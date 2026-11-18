<?php
namespace App\Models\Retail;
use Illuminate\Database\Eloquent\Model;
class Integration extends Model {protected $table='integrations';protected $guarded=[];protected $casts=['settings'=>'encrypted:array'];}

<?php
namespace App\Models\Retail;
use Illuminate\Database\Eloquent\Model;
class Campaign extends Model { use \App\Models\Concerns\ErpCrud;protected $table='campaigns';protected $guarded=[];}

<?php
namespace App\Models\Expanse;
use Illuminate\Database\Eloquent\Model;
class Expense extends Model { use \App\Models\Concerns\ErpCrud;protected $table='expenses';protected $guarded=[];}

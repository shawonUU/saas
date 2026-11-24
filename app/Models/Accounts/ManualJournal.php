<?php
namespace App\Models\Accounts;
use Illuminate\Database\Eloquent\Model;
class ManualJournal extends Model { use \App\Models\Concerns\ErpCrud;protected $table='manual_journals';protected $guarded=[];}

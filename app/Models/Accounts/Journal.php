<?php
namespace App\Models\Accounts;
use Illuminate\Database\Eloquent\Model;
class Journal extends Model { use \App\Models\Concerns\ErpCrud;  protected $table='journals'; protected $guarded=[]; public function lines(){return $this->hasMany(JournalLine::class,'journal_id');} }

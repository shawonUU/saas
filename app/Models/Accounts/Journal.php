<?php
namespace App\Models\Accounts;
use Illuminate\Database\Eloquent\Model;
class Journal extends Model {  protected $table='journals'; protected $guarded=[]; public function lines(){return $this->hasMany(JournalLine::class,'journal_id');} }

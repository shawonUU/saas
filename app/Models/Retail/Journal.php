<?php
namespace App\Models\Retail;
use Illuminate\Database\Eloquent\Model;
class Journal extends Model {  protected $table='retail_journals'; protected $guarded=[]; public function lines(){return $this->hasMany(JournalLine::class,'journal_id');} }

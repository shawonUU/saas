<?php
namespace App\Models\Retail;
use Illuminate\Database\Eloquent\Model;
class JournalLine extends Model {  protected $table='retail_journal_lines'; protected $guarded=[]; public function journal(){return $this->belongsTo(Journal::class,'journal_id');} public function account(){return $this->belongsTo(Account::class,'account_id');}  }

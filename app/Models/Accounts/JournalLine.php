<?php
namespace App\Models\Accounts;
use Illuminate\Database\Eloquent\Model;
class JournalLine extends Model { use \App\Models\Concerns\ErpCrud;  protected $table='journal_lines'; protected $guarded=[]; public function journal(){return $this->belongsTo(Journal::class,'journal_id');} public function account(){return $this->belongsTo(Account::class,'account_id');}  }

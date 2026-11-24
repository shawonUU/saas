<?php
namespace App\Models\Retail;
use Illuminate\Database\Eloquent\Model;
class Quotation extends Model { use \App\Models\Concerns\ErpCrud;protected $table='quotations';protected $guarded=[];public function items(){return $this->hasMany(QuotationItem::class,'quotation_id');}}

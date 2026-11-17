<?php
namespace App\Models\Retail;
use Illuminate\Database\Eloquent\Model;
class Quotation extends Model {protected $table='retail_quotations';protected $guarded=[];public function items(){return $this->hasMany(QuotationItem::class,'quotation_id');}}

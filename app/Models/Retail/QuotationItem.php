<?php
namespace App\Models\Retail;
use Illuminate\Database\Eloquent\Model;
class QuotationItem extends Model { use \App\Models\Concerns\ErpCrud;protected $table='quotation_items';protected $guarded=[];}

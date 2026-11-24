<?php
namespace App\Models\Purchase;
use Illuminate\Database\Eloquent\Model;
class PurchaseOrderReceipt extends Model { use \App\Models\Concerns\ErpCrud;protected $table='po_receipts';protected $guarded=[];}

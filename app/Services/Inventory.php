<?php
namespace App\Services;
use App\Models\Business;
use App\Models\Configurations\Variant;
use App\Models\Inventory\StockMovement;
use App\Models\Configurations\Warehouse;
use App\Models\Inventory\WarehouseStock;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
class Inventory {
 public function main(Business $business):Warehouse {
  return Warehouse::firstOrCreate(['business_id'=>$business->id,'code'=>'MAIN'],['name'=>'Main Warehouse','is_default'=>true,'is_active'=>true]);
 }
 // Call within DB::transaction. All changes also update aggregate variant stock.
 public function move(Business $business,Variant $variant,float $quantity,string $kind,float $cost,string $source,?int $sourceId=null,string $note='',?int $warehouseId=null):void {
  $variant=Variant::where('business_id',$business->id)->lockForUpdate()->findOrFail($variant->id);
  $warehouse=$warehouseId ? Warehouse::where('business_id',$business->id)->where('is_active',true)->findOrFail($warehouseId) : $this->main($business);
  $location=WarehouseStock::firstOrCreate(['business_id'=>$business->id,'warehouse_id'=>$warehouse->id,'variant_id'=>$variant->id],['quantity'=>0]);
  $location=WarehouseStock::whereKey($location->id)->lockForUpdate()->firstOrFail();
  $next=round((float)$location->quantity+$quantity,3);
  if($next<-.0001)throw ValidationException::withMessages(['stock'=>"Insufficient stock for {$variant->sku} at {$warehouse->name}. Available: {$location->quantity}"]);
  $aggregate=round((float)$variant->stock+$quantity,3);
  if($aggregate<-.0001)throw ValidationException::withMessages(['stock'=>'Aggregate stock cannot be negative.']);
  $location->update(['quantity'=>$next]);$variant->update(['stock'=>$aggregate]);
  StockMovement::create(['business_id'=>$business->id,'variant_id'=>$variant->id,'warehouse_id'=>$warehouse->id,'quantity'=>$quantity,'kind'=>$kind,'unit_cost'=>$cost,'source_type'=>$source,'source_id'=>$sourceId,'note'=>$note]);
 }
}

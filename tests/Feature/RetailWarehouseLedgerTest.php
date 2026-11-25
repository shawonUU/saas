<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use App\Models\{User,Business,BusinessCategory};
use App\Models\Configurations\Product;
use App\Models\Configurations\Variant;
use App\Models\Configurations\Warehouse;
use App\Models\Inventory\WarehouseStock;
use App\Models\Inventory\StockMovement;
use App\Models\Accounts\JournalLine;
use App\Services\Public\Business\Admin\Finance\Ledger\JournalEntries\Accounting;
use App\Services\Public\Business\Admin\Inventory\Movements\Inventory;
use Illuminate\Validation\ValidationException;
class RetailWarehouseLedgerTest extends TestCase {
 use RefreshDatabase;
 private function setupShop():array {
  $u=User::create(['name'=>'Owner','email'=>'warehouse@example.test','approval_status'=>'Approved']);
  $c=BusinessCategory::create(['slug'=>'warehouse-retail','name'=>'Retail','approval_status'=>'Approved']);
  $b=Business::create(['owner_id'=>$u->id,'category_id'=>$c->id,'name'=>'Warehouse Shop','domain'=>'warehouse-shop','approval_status'=>'Approved']);
  $p=Product::create(['business_id'=>$b->id,'name'=>'Item','sku'=>'A1','price'=>200,'cost'=>50,'track_stock'=>true]);
  $v=Variant::create(['business_id'=>$b->id,'product_id'=>$p->id,'sku'=>'A1-DEFAULT','price'=>200,'cost'=>50,'stock'=>0]);
  return [$b,$v];
 }
 public function test_transfer_keeps_aggregate_stock_and_ledger_balanced():void {
  [$b,$v]=$this->setupShop();
  DB::transaction(function()use($b,$v){app(Inventory::class)->move($b,$v,10,'opening',50,'opening',$v->id);app(Accounting::class)->post($b,'opening',$v->id,'Initial Stock',['1300'=>[500,0],'3000'=>[0,500]]);});
  $main=app(Inventory::class)->main($b);$other=Warehouse::create(['business_id'=>$b->id,'name'=>'Outlet','code'=>'OUT','is_default'=>false,'is_active'=>true]);
  DB::transaction(function()use($b,$v,$main,$other){app(Inventory::class)->move($b,$v,-3,'transfer_out',50,'transfer',1,'Transfer',$main->id);app(Inventory::class)->move($b,$v,3,'transfer_in',50,'transfer',1,'Transfer',$other->id);});
  $this->assertEquals(10,(float)$v->fresh()->stock);
  $this->assertEquals(7,(float)WarehouseStock::where('warehouse_id',$main->id)->where('variant_id',$v->id)->value('quantity'));
  $this->assertEquals(3,(float)WarehouseStock::where('warehouse_id',$other->id)->where('variant_id',$v->id)->value('quantity'));
  $this->assertEqualsWithDelta((float)JournalLine::where('business_id',$b->id)->sum('debit'),(float)JournalLine::where('business_id',$b->id)->sum('credit'),0.001);
 }
 public function test_unpaid_sale_can_be_cancelled_with_reversal():void {
  [$b,$v]=$this->setupShop();
  DB::transaction(function()use($b,$v){app(Inventory::class)->move($b,$v,5,'opening',50,'opening',$v->id);app(Accounting::class)->post($b,'opening',$v->id,'Initial Stock',['1300'=>[250,0],'3000'=>[0,250]]);});
  $order=app(\App\Services\Public\Business\Admin\Sales\Orders\NewOrder\Sales::class)->create($b,['items'=>[['variant_id'=>$v->id,'quantity'=>2]]],'online');
  $this->assertEquals(3,(float)$v->fresh()->stock);
  app(\App\Services\Public\Business\Admin\Sales\Orders\OrderDetails\CancelSale::class)->cancel($b,$order);
  $this->assertEquals('cancelled',$order->fresh()->status);
  $this->assertEquals(5,(float)$v->fresh()->stock);
  $this->assertEqualsWithDelta((float)JournalLine::where('business_id',$b->id)->sum('debit'),(float)JournalLine::where('business_id',$b->id)->sum('credit'),0.001);
 }
 public function test_stock_cannot_be_consumed_from_warehouse_without_quantity():void {
  [$b,$v]=$this->setupShop();$other=Warehouse::create(['business_id'=>$b->id,'name'=>'Outlet','code'=>'OUT','is_default'=>false,'is_active'=>true]);
  $this->expectException(ValidationException::class);
  DB::transaction(fn()=>app(Inventory::class)->move($b,$v,-1,'sale',50,'order',1,'Test',$other->id));
 }
}

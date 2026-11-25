<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{User,Business,BusinessCategory};
use App\Models\Configurations\Product;
use App\Models\Configurations\Variant;
use App\Models\Sales\Order;
use App\Models\Accounts\JournalLine;
use App\Services\Public\Business\Admin\Inventory\Movements\Inventory;
use App\Services\Public\Business\Admin\Finance\Ledger\JournalEntries\Accounting;
use App\Services\Public\Business\Admin\Sales\Orders\NewOrder\Sales;
use App\Services\Public\Business\Admin\Purchase\Receiving\NewGrn\Purchases;
use App\Services\Public\Business\Admin\Sales\Returns\NewReturn\Returns;
use App\Services\Public\Business\Admin\Finance\Payments;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
class RetailAccountingFlowTest extends TestCase {
 use RefreshDatabase;
 private function shop(string $domain):Business {
  $user=User::create(['name'=>'Owner','email'=>$domain.'@example.test','approval_status'=>'Approved']);
  $category=BusinessCategory::create(['slug'=>'retail-'.$domain,'name'=>'Retail','approval_status'=>'Approved']);
  return Business::create(['owner_id'=>$user->id,'category_id'=>$category->id,'name'=>'Shop '.$domain,'domain'=>$domain,'approval_status'=>'Approved']);
 }
 private function variant(Business $b,string $sku):Variant {
  $product=Product::create(['business_id'=>$b->id,'name'=>'Test Item','sku'=>$sku,'price'=>100,'cost'=>60,'track_stock'=>true]);
  $variant=Variant::create(['business_id'=>$b->id,'product_id'=>$product->id,'sku'=>$sku.'-default','price'=>100,'cost'=>60,'stock'=>0]);
  DB::transaction(function()use($b,$variant){app(Inventory::class)->move($b,$variant,10,'opening',60,'opening',$variant->id);app(Accounting::class)->post($b,'opening',$variant->id,'Initial Stock',['1300'=>[600,0],'3000'=>[0,600]]);});
  return $variant;
 }
 public function test_purchase_sale_partial_return_payment_and_balanced_journals():void {
  $b=$this->shop('retail1');$v=$this->variant($b,'SKU-01');
  $p=app(Purchases::class)->create($b,['items'=>[['variant_id'=>$v->id,'quantity'=>5,'cost'=>50]],'pay_amount'=>100,'payment_method'=>'bank']);
  $this->assertEquals(15,$v->fresh()->stock);$this->assertEquals(150,$p->fresh()->total-$p->fresh()->paid);
  $o=app(Sales::class)->create($b,['request_key'=>'test-order-123','items'=>[['variant_id'=>$v->id,'quantity'=>2]],'pay_amount'=>200,'payment_method'=>'cash'],'pos');
  $this->assertEquals(13,$v->fresh()->stock);$this->assertEquals(0,$o->fresh()->total-$o->fresh()->paid);
  $same=app(Sales::class)->create($b,['request_key'=>'test-order-123','items'=>[['variant_id'=>$v->id,'quantity'=>2]]],'pos');$this->assertSame($o->id,$same->id);
  $r=app(Returns::class)->create($b,['order_id'=>$o->id,'items'=>[['order_item_id'=>$o->items()->first()->id,'quantity'=>1]]]);
  $this->assertEquals(100,$r->amount);$this->assertEquals(14,$v->fresh()->stock);$this->assertEquals(100,$o->fresh()->credited);
  DB::transaction(fn()=>app(Payments::class)->refund($b,$r,100,'cash'));
  $this->assertEquals(100,$r->fresh()->refunded);
  $this->assertEqualsWithDelta((float)JournalLine::where('business_id',$b->id)->sum('debit'),(float)JournalLine::where('business_id',$b->id)->sum('credit'),0.001);
 }
 public function test_cross_business_variant_is_rejected():void {
  $b=$this->shop('retail2');$other=$this->shop('retail3');$v=$this->variant($b,'SKU-02');
  $this->expectException(ModelNotFoundException::class);
  app(Sales::class)->create($other,['items'=>[['variant_id'=>$v->id,'quantity'=>1]]]);
 }
}

<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{Business,BusinessCategory,BusinessCustomer,User};
use App\Models\Sales\Order;
class EcommerceCustomerAccountTest extends TestCase {
 use RefreshDatabase;
 public function test_customer_cannot_view_or_request_return_for_another_customers_order():void {
 $category=BusinessCategory::create(['slug'=>'retail','name'=>'Retail','approval_status'=>'Approved']);
 $owner=User::create(['name'=>'Owner','email'=>'owner@ec.test','approval_status'=>'Approved']);
 $b=Business::create(['owner_id'=>$owner->id,'category_id'=>$category->id,'name'=>'Shop','domain'=>'ecshop','approval_status'=>'Approved']);
 $customers=[];
 foreach(['one','two'] as $name){$u=User::create(['name'=>$name,'email'=>$name.'@ec.test','approval_status'=>'Approved']);$customers[]=BusinessCustomer::create(['user_id'=>$u->id,'business_id'=>$b->id,'name'=>$name,'email'=>$name.'@ec.test','password'=>'password123','approval_status'=>'Approved']);}
 $order=Order::create(['business_id'=>$b->id,'customer_id'=>$customers[1]->id,'order_no'=>'SO-1','channel'=>'online','status'=>'confirmed','delivery_status'=>'delivered','subtotal'=>100,'total'=>100]);
 $this->actingAs($customers[0]->refresh(),'business_customer')->get('http://ecshop.saas.test/account/orders/'.$order->id)->assertNotFound();
 $this->post('http://ecshop.saas.test/account/orders/'.$order->id.'/return',['order_item_id'=>1,'quantity'=>1,'reason'=>'Test'])->assertNotFound();
 $this->assertDatabaseCount('ecommerce_return_requests',0);
 }
}

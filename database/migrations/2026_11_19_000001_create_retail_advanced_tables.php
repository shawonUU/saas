<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
 public function up():void {
  Schema::create('purchase_orders',function(Blueprint $t){$t->id();$t->foreignId('business_id')->index();$t->unsignedBigInteger('supplier_id')->nullable();$t->string('po_no');$t->string('status',30)->default('draft');$t->decimal('total',16,2);$t->text('notes')->nullable();$t->timestamps();$t->unique(['business_id','po_no']);});
  Schema::create('purchase_order_items',function(Blueprint $t){$t->id();$t->foreignId('business_id')->index();$t->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();$t->unsignedBigInteger('variant_id');$t->decimal('quantity',16,3);$t->decimal('received_quantity',16,3)->default(0);$t->decimal('cost',16,2);$t->decimal('total',16,2);$t->timestamps();});
  Schema::create('po_receipts',function(Blueprint $t){$t->id();$t->foreignId('business_id')->index();$t->foreignId('purchase_order_id')->constrained('purchase_orders');$t->unsignedBigInteger('purchase_id');$t->timestamps();$t->unique(['business_id','purchase_id']);});
  Schema::create('warehouses',function(Blueprint $t){$t->id();$t->foreignId('business_id')->index();$t->string('name');$t->string('code',30);$t->boolean('is_default')->default(false);$t->boolean('is_active')->default(true);$t->timestamps();$t->unique(['business_id','code']);});
  Schema::create('warehouse_stocks',function(Blueprint $t){$t->id();$t->foreignId('business_id')->index();$t->foreignId('warehouse_id')->constrained('warehouses');$t->unsignedBigInteger('variant_id');$t->decimal('quantity',16,3)->default(0);$t->timestamps();$t->unique(['warehouse_id','variant_id']);});
  Schema::create('stock_transfers',function(Blueprint $t){$t->id();$t->foreignId('business_id')->index();$t->string('transfer_no');$t->foreignId('from_warehouse_id')->constrained('warehouses');$t->foreignId('to_warehouse_id')->constrained('warehouses');$t->string('note')->nullable();$t->timestamps();$t->unique(['business_id','transfer_no']);});
  Schema::create('stock_transfer_items',function(Blueprint $t){$t->id();$t->foreignId('business_id')->index();$t->foreignId('stock_transfer_id')->constrained('stock_transfers');$t->unsignedBigInteger('variant_id');$t->decimal('quantity',16,3);$t->timestamps();});
  Schema::table('orders',function(Blueprint $t){$t->string('customer_email')->nullable();$t->string('coupon_code')->nullable();});
  Schema::table('stock_movements',fn(Blueprint $t)=>$t->unsignedBigInteger('warehouse_id')->nullable()->index());
  Schema::create('purchase_returns',function(Blueprint $t){$t->id();$t->foreignId('business_id')->index();$t->unsignedBigInteger('purchase_id');$t->unsignedBigInteger('purchase_item_id');$t->unsignedBigInteger('variant_id');$t->decimal('quantity',16,3);$t->decimal('amount',16,2);$t->decimal('ap_credit',16,2)->default(0);$t->decimal('refund_amount',16,2)->default(0);$t->string('reason')->nullable();$t->timestamps();});
  Schema::create('quotations',function(Blueprint $t){$t->id();$t->foreignId('business_id')->index();$t->string('quote_no');$t->string('customer_name');$t->string('customer_phone')->nullable();$t->string('status',20)->default('open');$t->decimal('total',16,2);$t->date('valid_until')->nullable();$t->unsignedBigInteger('order_id')->nullable();$t->timestamps();$t->unique(['business_id','quote_no']);});
  Schema::create('quotation_items',function(Blueprint $t){$t->id();$t->foreignId('business_id')->index();$t->foreignId('quotation_id')->constrained('quotations')->cascadeOnDelete();$t->unsignedBigInteger('variant_id');$t->decimal('quantity',16,3);$t->decimal('price',16,2);$t->decimal('total',16,2);$t->timestamps();});
  Schema::create('manual_journals',function(Blueprint $t){$t->id();$t->foreignId('business_id')->index();$t->string('reference_no');$t->text('description');$t->unsignedBigInteger('journal_id')->nullable();$t->timestamps();$t->unique(['business_id','reference_no']);});
  Schema::create('cash_transfers',function(Blueprint $t){$t->id();$t->foreignId('business_id')->index();$t->string('direction',30);$t->decimal('amount',16,2);$t->string('reference_no');$t->timestamps();});
  Schema::create('expenses',function(Blueprint $t){$t->id();$t->foreignId('business_id')->index();$t->string('description');$t->decimal('amount',16,2);$t->string('method',10);$t->date('expense_date');$t->timestamps();});
  Schema::create('campaigns',function(Blueprint $t){$t->id();$t->foreignId('business_id')->index();$t->string('name');$t->string('coupon_code')->nullable();$t->string('banner_text')->nullable();$t->timestamp('starts_at')->nullable();$t->timestamp('ends_at')->nullable();$t->boolean('is_active')->default(true);$t->timestamps();});
  Schema::create('abandoned_carts',function(Blueprint $t){$t->id();$t->foreignId('business_id')->index();$t->uuid('cart_key');$t->string('customer_name')->nullable();$t->string('customer_phone')->nullable();$t->json('items');$t->decimal('subtotal',16,2)->default(0);$t->string('status',20)->default('abandoned');$t->timestamps();$t->unique(['business_id','cart_key']);});
  Schema::create('integrations',function(Blueprint $t){$t->id();$t->foreignId('business_id')->index();$t->string('provider',30);$t->text('settings');$t->boolean('is_active')->default(false);$t->timestamps();$t->unique(['business_id','provider']);});
  Schema::create('gateway_payments',function(Blueprint $t){$t->id();$t->foreignId('business_id')->index();$t->unsignedBigInteger('order_id');$t->string('tran_id',100)->unique();$t->string('status',30)->default('initiated');$t->decimal('amount',16,2);$t->string('val_id')->nullable();$t->decimal('fee',16,2)->default(0);$t->timestamp('settled_at')->nullable();$t->text('response')->nullable();$t->timestamps();});
  Schema::create('courier_shipments',function(Blueprint $t){$t->id();$t->foreignId('business_id')->index();$t->unsignedBigInteger('order_id');$t->string('provider',30)->default('pathao');$t->string('consignment_id')->nullable();$t->string('status',50)->default('pending');$t->string('request_id',90)->nullable();$t->text('response')->nullable();$t->timestamps();$t->unique(['business_id','order_id','provider']);});
  // Existing stock is placed in a business default warehouse without changing aggregate quantities.
  foreach(DB::table('variants')->select('business_id')->distinct()->pluck('business_id') as $bid){
   $id=DB::table('warehouses')->insertGetId(['business_id'=>$bid,'name'=>'Main Warehouse','code'=>'MAIN','is_default'=>true,'is_active'=>true,'created_at'=>now(),'updated_at'=>now()]);
   DB::table('variants')->where('business_id',$bid)->orderBy('id')->chunk(200,function($variants)use($bid,$id){foreach($variants as $v)DB::table('warehouse_stocks')->insert(['business_id'=>$bid,'warehouse_id'=>$id,'variant_id'=>$v->id,'quantity'=>$v->stock,'created_at'=>now(),'updated_at'=>now()]);});
  }
 }
 public function down():void {
  foreach(['courier_shipments','gateway_payments','integrations','abandoned_carts','campaigns','expenses','cash_transfers','manual_journals','quotation_items','quotations','purchase_returns','stock_transfer_items','stock_transfers','warehouse_stocks','warehouses','po_receipts','purchase_order_items','purchase_orders'] as $table)Schema::dropIfExists($table);
  Schema::table('stock_movements',fn(Blueprint $t)=>$t->dropColumn('warehouse_id'));
  Schema::table('orders',fn(Blueprint $t)=>$t->dropColumn(['customer_email','coupon_code']));
 }
};

<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void { Schema::create('ecommerce_return_requests',function(Blueprint $table){
 $table->id(); $table->unsignedBigInteger('business_id'); $table->unsignedBigInteger('customer_id'); $table->unsignedBigInteger('order_id'); $table->unsignedBigInteger('order_item_id'); $table->decimal('quantity',16,3); $table->string('reason',255); $table->enum('status',['pending','rejected','posted'])->default('pending'); $table->string('admin_note',255)->nullable(); $table->unsignedBigInteger('sale_return_id')->nullable()->unique(); $table->timestamp('reviewed_at')->nullable(); $table->unsignedBigInteger('reviewed_by')->nullable(); $table->index(['business_id','status']); $table->index(['business_id','order_id']); $table->erpCommonColumns(); }); }
 public function down():void { Schema::dropIfExists('ecommerce_return_requests'); }
};

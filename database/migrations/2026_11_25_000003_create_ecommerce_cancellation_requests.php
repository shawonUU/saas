<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {Schema::create('ecommerce_cancellation_requests',function(Blueprint $table){$table->id();$table->unsignedBigInteger('business_id');$table->unsignedBigInteger('customer_id');$table->unsignedBigInteger('order_id');$table->string('reason',255);$table->string('status',20)->default('pending');$table->string('admin_note',255)->nullable();$table->unsignedBigInteger('reviewed_by')->nullable();$table->timestamp('reviewed_at')->nullable();$table->index(['business_id', 'order_id', 'status'], 'ecr_business_order_status_idx');$table->erpCommonColumns();});}
 public function down():void {Schema::dropIfExists('ecommerce_cancellation_requests');}
};

<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void { Schema::create('ecommerce_customer_addresses',function(Blueprint $table){
 $table->id(); $table->unsignedBigInteger('business_id'); $table->unsignedBigInteger('customer_id'); $table->string('label',80); $table->string('recipient',255); $table->string('phone',40); $table->string('district',100); $table->string('city',100); $table->string('address_line',1400); $table->index(['business_id','customer_id']); $table->erpCommonColumns(); }); }
 public function down():void { Schema::dropIfExists('ecommerce_customer_addresses'); }
};

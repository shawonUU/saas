<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {if(!Schema::hasColumn('manual_journals','voucher_type'))Schema::table('manual_journals',fn(Blueprint $t)=>$t->string('voucher_type',2)->default('JV'));}
 public function down():void {if(Schema::hasColumn('manual_journals','voucher_type'))Schema::table('manual_journals',fn(Blueprint $t)=>$t->dropColumn('voucher_type'));}
};

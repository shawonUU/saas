<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('shop_accounts', function(Blueprint $t) {
            $t->id(); $t->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $t->foreignId('central_user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('name'); $t->string('email'); $t->string('password');
            $t->string('role')->default('Staff'); $t->string('status')->default('Active');
            $t->rememberToken(); $t->timestamps(); $t->unique(['business_id','email']);
        });
        Schema::create('shop_customers', function(Blueprint $t) {
            $t->id(); $t->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $t->string('name'); $t->string('email'); $t->string('password');
            $t->string('status')->default('Active'); $t->rememberToken(); $t->timestamps();
            $t->unique(['business_id','email']);
        });
    }
    public function down(): void { Schema::dropIfExists('shop_customers'); Schema::dropIfExists('shop_accounts'); }
};

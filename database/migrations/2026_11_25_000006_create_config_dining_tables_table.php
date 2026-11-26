<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::create('config_dining_tables', function(Blueprint $table){
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->string('name');
            $table->string('name_bn')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('dining_area_id')->constrained('config_dining_areas')->restrictOnDelete();
            $table->unsignedInteger('capacity')->default(1);
            $table->index(['business_id','dining_area_id']);
            $table->erpCommonColumns();
            $table->index(['business_id','name']);
            $table->index(['business_id','is_active']);
        });
    }
    public function down(): void { Schema::dropIfExists('config_dining_tables'); }
};

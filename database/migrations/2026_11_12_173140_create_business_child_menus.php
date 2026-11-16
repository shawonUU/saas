<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_child_menus', function (Blueprint $table) {
            $table->id();
            // A business override is independent of a subscription record.
            // Kept nullable to remain compatible with older data.
            $table->unsignedBigInteger('business_subscription_id')->nullable();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignId('child_menu_id')->constrained('child_menus')->cascadeOnDelete();
            
            $table->enum('source', ['Plan', 'Override'])->default('Override');
            $table->boolean('is_enabled')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->text('notes')->nullable();
            $table->unique(['business_id', 'child_menu_id'], 'biz_child_unique');
            $table->erpCommonColumns();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_child_menus');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        if (! Schema::hasColumn('orders', 'restaurant_workflow')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->json('restaurant_workflow')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep saved policies so application rollback cannot alter an active order's workflow.
    }
};

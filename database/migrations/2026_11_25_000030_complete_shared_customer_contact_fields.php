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
        Schema::table('business_customers', function (Blueprint $table): void {
            // Counter customers share the tenant customer table without requiring a login.
            $table->unsignedBigInteger('user_id')->nullable()->change();
            if (! Schema::hasColumn('business_customers', 'address')) {
                $table->text('address')->nullable();
            }
            if (! Schema::hasColumn('business_customers', 'notes')) {
                $table->text('notes')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Retain contact details and customers without a login when rolling code back.
    }
};

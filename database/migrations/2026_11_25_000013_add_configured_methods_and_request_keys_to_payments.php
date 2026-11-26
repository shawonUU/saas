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
        Schema::table('payments', function (Blueprint $table): void {
            $table->string('method', 24)->change();
            if (! Schema::hasColumn('payments', 'request_key')) {
                $table->uuid('request_key')->nullable();
            }
            if (! Schema::hasColumn('payments', 'configuration_method_id')) {
                $table->unsignedBigInteger('configuration_method_id')->nullable();
            }
            if (! Schema::hasIndex('payments', 'payments_business_request_unique')) {
                $table->unique(['business_id', 'request_key'], 'payments_business_request_unique');
            }
            if (! Schema::hasIndex('payments', 'payments_config_method_idx')) {
                $table->index(['business_id', 'configuration_method_id'], 'payments_config_method_idx');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep payment identities and configured payment history intact.
    }
};

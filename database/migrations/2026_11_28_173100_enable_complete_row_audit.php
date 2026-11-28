<?php

use App\Services\Public\Business\Admin\Settings\Audit\ActivityLogs\AuditSchemaService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        app(AuditSchemaService::class)->sync();
    }

    public function down(): void
    {
        // Keep the historic business_date columns and audit_logs rows intact.
        app(AuditSchemaService::class)->dropAll();
    }
};

<?php

namespace App\Console\Commands;

use App\Services\Public\Business\Admin\Settings\Audit\ActivityLogs\AuditSchemaService;
use Illuminate\Console\Command;

class SyncBusinessAudit extends Command
{
    protected $signature = 'audit:sync-triggers';
    protected $description = 'Install/refresh MySQL row audit and business-date triggers after schema changes';

    public function handle(AuditSchemaService $schema): int
    {
        $result = $schema->sync();
        $this->info('Audit schema: '.json_encode($result));
        return isset($result['warning']) ? self::FAILURE : self::SUCCESS;
    }
}

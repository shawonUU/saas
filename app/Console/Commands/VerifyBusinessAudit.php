<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VerifyBusinessAudit extends Command
{
    protected $signature = 'audit:verify-triggers';
    protected $description = 'Verify current MySQL application tables have audit and business-date triggers';

    public function handle(): int
    {
        if (DB::getDriverName() !== 'mysql') {
            $this->error('Database-level audit is supported on MySQL/MariaDB; configured driver: '.DB::getDriverName());
            return self::FAILURE;
        }
        $excluded = [
            'audit_logs','migrations','cache','cache_locks','jobs','failed_jobs','job_batches',
            'sessions','saas_sessions','authority_sessions','business_sessions','customer_sessions',
            'notifications','password_reset_tokens','business_password_reset_tokens',
            'customer_password_reset_tokens','saas_password_reset_tokens','authority_password_reset_tokens',
        ];
        $missing = [];
        $tables = 0;
        foreach (Schema::getTableListing() as $table) {
            if (!preg_match('/^[a-zA-Z0-9_]+$/', (string)$table) || in_array($table, $excluded, true)) continue;
            $cols = Schema::getColumnListing($table);
            if (!in_array('id', $cols, true)) continue;
            $tables++;
            if (in_array('business_id', $cols, true) && !in_array('business_date', $cols, true)) $missing[] = $table.': missing business_date';
            $suffixes = ['ai', 'au', 'ad'];
            if (in_array('business_id', $cols, true)) $suffixes[] = 'bi';
            foreach ($suffixes as $suffix) {
                $name = 'saas_'.substr(sha1($table),0,20).'_'.$suffix;
                $count = DB::table('information_schema.TRIGGERS')
                    ->where('TRIGGER_SCHEMA', DB::getDatabaseName())->where('TRIGGER_NAME', $name)->count();
                if (!$count) $missing[] = $table.': missing '.$suffix.' trigger';
            }
        }
        $this->info($tables.' application tables checked; '.count($missing).' missing columns/triggers.');
        foreach ($missing as $item) $this->error($item);
        if ($missing) $this->warn('After reviewing missing items, run php artisan audit:sync-triggers and verify again.');
        return $missing ? self::FAILURE : self::SUCCESS;
    }
}

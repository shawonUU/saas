<?php

namespace App\Services\Public\Business\Admin\Settings\Audit\ActivityLogs;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** MySQL row triggers cover Eloquent, query-builder, bulk inserts, and SQL statements. */
class AuditSchemaService
{
    // Framework/ephemeral data is deliberately excluded: passwords, sessions, cache and logs of logs.
    private const EXCLUDED = [
        'audit_logs', 'migrations', 'cache', 'cache_locks', 'jobs', 'failed_jobs',
        'job_batches', 'sessions', 'saas_sessions', 'authority_sessions',
        'business_sessions', 'customer_sessions', 'notifications',
        'password_reset_tokens', 'business_password_reset_tokens',
        'customer_password_reset_tokens', 'saas_password_reset_tokens',
        'authority_password_reset_tokens',
    ];

    public function sync(): array
    {
        if (DB::getDriverName() !== 'mysql') {
            return ['tables' => 0, 'triggers' => 0, 'driver' => DB::getDriverName(), 'warning' => 'MySQL/MariaDB required for database-level auditing'];
        }
        $tables = 0;
        $triggers = 0;
        foreach (Schema::getTableListing() as $table) {
            $table = (string) $table;
            if (! preg_match('/^[a-zA-Z0-9_]+$/', $table) || in_array($table, self::EXCLUDED, true)) continue;
            $columns = Schema::getColumnListing($table);
            if (! in_array('id', $columns, true)) continue;
            $scoped = in_array('business_id', $columns, true);
            if ($scoped && ! in_array('business_date', $columns, true)) {
                Schema::table($table, function (Blueprint $t) use ($table): void {
                    $t->date('business_date')->nullable();
                    $t->index(['business_id', 'business_date'], 'bd_'.substr(sha1($table), 0, 18));
                });
                $columns = Schema::getColumnListing($table);
            }
            // All application tables are audited. Global lookup rows have NULL business_id.
            $this->rebuild($table, $columns);
            $tables++;
            $triggers += 3 + ($scoped && in_array('business_date', $columns, true) ? 1 : 0);
        }
        return compact('tables', 'triggers');
    }

    public function dropAll(): void
    {
        if (DB::getDriverName() !== 'mysql') return;
        foreach (Schema::getTableListing() as $table) {
            if (! preg_match('/^[a-zA-Z0-9_]+$/', (string) $table)) continue;
            foreach (['bi', 'ai', 'au', 'ad'] as $event) {
                DB::unprepared('DROP TRIGGER IF EXISTS '.$this->identifier($this->triggerName($event, $table)));
            }
        }
    }

    private function rebuild(string $table, array $columns): void
    {
        $scoped = in_array('business_id', $columns, true);
        $hasDate = in_array('business_date', $columns, true);
        foreach (['bi', 'ai', 'au', 'ad'] as $event) {
            DB::unprepared('DROP TRIGGER IF EXISTS '.$this->identifier($this->triggerName($event, $table)));
        }
        if ($scoped && $hasDate) {
            // Preserve an explicitly supplied historic business_date, never overwrite it.
            // Missing state can happen for a newly provisioned business or CLI import.
            $day = $this->dateExpression($table, 'NEW', $scoped);
            DB::unprepared('CREATE TRIGGER '.$this->identifier($this->triggerName('bi', $table))
                .' BEFORE INSERT ON '.$this->identifier($table).' FOR EACH ROW BEGIN '
                .'IF NEW.`business_date` IS NULL THEN SET NEW.`business_date` = '.$day.'; END IF; END');
        }
        $this->after($table, $columns, 'INSERT', 'ai', 'created');
        $this->after($table, $columns, 'UPDATE', 'au', 'updated');
        $this->after($table, $columns, 'DELETE', 'ad', 'deleted');
    }

    private function after(string $table, array $columns, string $event, string $suffix, string $action): void
    {
        $row = $event === 'DELETE' ? 'OLD' : 'NEW';
        $business = $this->businessExpression($table, $row, $columns);
        $date = $this->dateExpression($table, $row, in_array('business_id', $columns, true));
        $old = $event === 'INSERT' ? 'NULL' : $this->jsonRow($columns, 'OLD');
        $new = $event === 'DELETE' ? 'NULL' : $this->jsonRow($columns, 'NEW');
        $code = in_array('code', $columns, true) ? 'LEFT(CAST('.$row.'.`code` AS CHAR), 100)' : 'NULL';
        $verb = $event === 'UPDATE' && in_array('deleted_at', $columns, true)
            ? "IF(OLD.`deleted_at` IS NULL AND NEW.`deleted_at` IS NOT NULL, 'deleted', 'updated')"
            : "'".$action."'";
        $sql = 'CREATE TRIGGER '.$this->identifier($this->triggerName($suffix, $table))
            .' AFTER '.$event.' ON '.$this->identifier($table).' FOR EACH ROW BEGIN '
            .'INSERT INTO `audit_logs` (`code`,`business_id`,`actor_id`,`actor_type`,`actor_name`,`actor_guard`,'
            .'`action`,`subject_type`,`subject_table`,`subject_id`,`subject_code`,`old_values`,`new_values`,'
            .'`ip_address`,`user_agent`,`request_method`,`route_name`,`request_path`,`business_date`,`created_at`) VALUES ('
            ."CONCAT('AUD-', REPLACE(UUID(), '-', '')), ".$business.", @saas_actor_id, @saas_actor_type, "
            .'LEFT(@saas_actor_name,191), @saas_actor_guard, '.$verb.', '.$this->quoted($table).', '.$this->quoted($table)
            .', CAST('.$row.'.`id` AS CHAR), '.$code.', '.$old.', '.$new
            .', @saas_request_ip, @saas_request_ua, @saas_request_method, LEFT(@saas_route_name,191), @saas_request_path, '
            .$date.', CURRENT_TIMESTAMP); END';
        DB::unprepared($sql);
    }

    private function businessExpression(string $table, string $row, array $columns): string
    {
        if (in_array('business_id', $columns, true)) return $row.'.`business_id`';
        if ($table === 'businesses') return $row.'.`id`';
        return 'NULL';
    }

    private function dateExpression(string $table, string $row, bool $scoped): string
    {
        if ($table === 'business_day_settings') return 'COALESCE('.$row.'.`open_date`, CURRENT_DATE())';
        if ($table === 'business_day_closures') return 'COALESCE('.$row.'.`next_business_date`, CURRENT_DATE())';
        if (! $scoped) return $table === 'businesses' ? 'COALESCE(@saas_business_date, CURRENT_DATE())' : 'NULL';
        return 'COALESCE(@saas_business_date, '
            .'(SELECT CASE WHEN `manual_mode`=1 THEN `open_date` ELSE CURRENT_DATE() END '
            .'FROM `business_day_settings` WHERE `business_id`='.$row.'.`business_id` LIMIT 1), CURRENT_DATE())';
    }

    private function jsonRow(array $columns, string $row): string
    {
        $parts = [];
        foreach ($columns as $column) {
            if (! preg_match('/^[a-zA-Z0-9_]+$/', $column) || $this->secret($column)) continue;
            $parts[] = $this->quoted($column);
            $parts[] = $row.'.'.$this->identifier($column);
        }
        return $parts ? 'JSON_OBJECT('.implode(', ', $parts).')' : 'JSON_OBJECT()';
    }

    private function secret(string $column): bool
    {
        return (bool) preg_match('/password|token|secret|credential|api_key|private_key|session|cookie|signature|card_number|cvv|authorization|otp|pin|payload|raw_body|auth_code|hash/i', $column);
    }

    private function triggerName(string $suffix, string $table): string
    {
        return 'saas_'.substr(sha1($table), 0, 20).'_'.$suffix;
    }

    private function identifier(string $name): string { return '`'.str_replace('`','``',$name).'`'; }
    private function quoted(string $value): string { return "'".str_replace("'", "''", $value)."'"; }
}

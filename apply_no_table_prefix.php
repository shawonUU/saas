<?php
/**
 * Removes the category-specific retail_ prefix from Laravel table references and
 * individual migration filenames. Does not modify Blade, UI, routes' URLs, or
 * application behavior. Run this from the Laravel project root (beside artisan).
 *
 * Inspect: php apply_no_table_prefix.php --dry-run [--database]
 * Apply:   php apply_no_table_prefix.php --apply --database
 * Files:   php apply_no_table_prefix.php --apply
 *
 * --database renames existing DB tables if necessary and reconciles migration
 * history. Back up the DATABASE independently; this tool only backs up files.
 */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit("CLI only\n"); }
$root = __DIR__;
if (!is_file($root.'/artisan') || !is_dir($root.'/database/migrations') || !is_dir($root.'/app')) {
    fwrite(STDERR, "Run this patch from your Laravel project root, beside artisan.\n"); exit(2);
}
$apply = in_array('--apply', $argv, true);
$dbEnabled = in_array('--database', $argv, true);
if (in_array('--help', $argv, true)) {
    echo "Dry run: php apply_no_table_prefix.php --dry-run [--database]\n";
    echo "Apply files: php apply_no_table_prefix.php --apply\n";
    echo "Apply files + existing DB: php apply_no_table_prefix.php --apply --database\n";
    exit(0);
}
if (is_file($root.'/database/migrations/2026_11_18_000001_create_retail_tables.php') ||
    is_file($root.'/database/migrations/2026_11_19_000001_create_retail_advanced_tables.php')) {
    fwrite(STDERR, "ERROR: Combined migrations detected. Apply the previous individual-migration refactor first. No changes made.\n"); exit(2);
}
$prefix = 'retail_';
$dirs = ['app', 'database/migrations', 'routes', 'config', 'tests', 'bootstrap', 'scripts', 'setup'];
$operations = [];
$renamed = [];
foreach ($dirs as $dir) {
    if (!is_dir($root.'/'.$dir)) continue;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/'.$dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $entry) {
        if (!$entry->isFile() || $entry->isLink() || strtolower($entry->getExtension()) !== 'php') continue;
        $old = $entry->getPathname();
        $oldText = file_get_contents($old);
        if ($oldText === false || !str_contains($oldText, $prefix) && !str_contains($entry->getBasename(), $prefix)) continue;
        $newText = str_replace($prefix, '', $oldText);
        $new = $old;
        if (str_starts_with($old, $root.'/database/migrations/')) {
            $new = dirname($old).'/'.str_replace($prefix, '', basename($old));
        }
        if ($old === $new && $oldText === $newText) continue;
        if ($old !== $new && file_exists($new) && hash_file('sha256', $new) !== hash('sha256', $newText)) {
            fwrite(STDERR, "ERROR: Migration filename collision: ".basename($new).". No changes made.\n"); exit(2);
        }
        if ($old !== $new) $renamed[basename($old, '.php')] = basename($new, '.php');
        $operations[] = ['old'=>$old, 'new'=>$new, 'text'=>$newText];
    }
}
// These are all known tables from the two individual-migration groups.
// Include the already-unprefixed base tables for upgrading an older database.
$tables = [
    'products','variants','stock_movements','accounts','journals','journal_lines','discounts','orders',
    'order_items','purchases','purchase_items','returns','return_items','payments',
    'purchase_orders','purchase_order_items','po_receipts','warehouses','warehouse_stocks',
    'stock_transfers','stock_transfer_items','purchase_returns','quotations','quotation_items',
    'manual_journals','cash_transfers','expenses','campaigns','abandoned_carts','integrations',
    'gateway_payments','courier_shipments'
];
$tableMoves = [];
$schema = null;
$db = null;
$hasMigrations = false;
$legacyBase = false;
$legacyAdvanced = false;
if ($dbEnabled) {
    if (!is_file($root.'/vendor/autoload.php')) {
        fwrite(STDERR, "ERROR: vendor/autoload.php missing. Run composer install first, or omit --database. No changes made.\n"); exit(2);
    }
    try {
        require_once $root.'/vendor/autoload.php';
        $app = require $root.'/bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        $db = \Illuminate\Support\Facades\DB::connection();
        $schema = $db->getSchemaBuilder();
        foreach ($tables as $short) {
            $old = $prefix.$short;
            $oldExists = $schema->hasTable($old);
            $newExists = $schema->hasTable($short);
            if ($oldExists && $newExists) {
                throw new RuntimeException("Both {$old} and {$short} exist. Resolve this collision manually; data will NOT be merged or deleted.");
            }
            if ($oldExists) $tableMoves[$old] = $short;
        }
        $hasMigrations = $schema->hasTable('migrations');
        if ($hasMigrations) {
            $legacyBase = $db->table('migrations')->where('migration', '2026_11_18_000001_create_retail_tables')->exists();
            $legacyAdvanced = $db->table('migrations')->where('migration', '2026_11_19_000001_create_retail_advanced_tables')->exists();
        }
    } catch (Throwable $e) {
        fwrite(STDERR, "ERROR: Database preflight failed: {$e->getMessage()}\nNo changes made.\n"); exit(2);
    }
}
$replaceCount = 0;
foreach ($operations as $op) {
    $oldText = file_get_contents($op['old']);
    $replaceCount += substr_count((string)$oldText, $prefix);
    if (!is_writable($op['old']) || !is_writable(dirname($op['new']))) {
        fwrite(STDERR, "ERROR: Source file or destination not writable: {$op['old']}\nNo changes made.\n"); exit(2);
    }
}
echo ($apply ? "APPLY" : "DRY RUN").": ".count($operations)." source files; {$replaceCount} prefix references; ".count($renamed)." migration filenames.\n";
echo "Blade files: UNTOUCHED. Module folders, namespaces, URLs and route names: UNTOUCHED.\n";
if ($dbEnabled) echo "Database: ".count($tableMoves)." existing table(s) to rename; ".($hasMigrations?'migration journal available':'migration journal not created yet').".\n";
else echo "Database: NOT modified. If tables already exist with retail_ names, use --database.\n";
if (!$apply) { echo "No changes made. Add --apply to install.\n"; exit(0); }
if (!$operations && (!$dbEnabled || !$tableMoves)) { echo "No source/table changes required.\n"; }
// Backups are created BEFORE any schema or source changes.
$backupDir = $root.'/storage/app/table_prefix_patch_backups/'.date('Ymd_His').'_'.bin2hex(random_bytes(4));
if ($operations) {
    if (!is_dir($backupDir) && !mkdir($backupDir, 0775, true) && !is_dir($backupDir)) {
        fwrite(STDERR, "ERROR: Cannot create source backup directory. No changes made.\n"); exit(2);
    }
    foreach ($operations as $op) {
        $relative = substr($op['old'], strlen($root) + 1);
        $backup = $backupDir.'/'.$relative;
        if (!is_dir(dirname($backup)) && !mkdir(dirname($backup), 0775, true) && !is_dir(dirname($backup))) {
            fwrite(STDERR, "ERROR: Cannot create backup directory for {$relative}. No changes made.\n"); exit(2);
        }
        if (!copy($op['old'], $backup)) {
            fwrite(STDERR, "ERROR: Backup failed for {$relative}. No changes made.\n"); exit(2);
        }
    }
}
// Use the Schema builder so the database engine updates FK table references.
// IMPORTANT: MySQL DDL is not transactional; an independent SQL backup is essential.
try {
    if ($dbEnabled) {
        foreach ($tableMoves as $old=>$new) {
            $schema->rename($old, $new);
            echo "DB: {$old} -> {$new}\n";
        }
    }
    foreach ($operations as $op) {
        if (file_put_contents($op['new'], $op['text'], LOCK_EX) === false) {
            throw new RuntimeException('Could not write '.substr($op['new'], strlen($root)+1));
        }
        @chmod($op['new'], fileperms($op['old']) & 0777);
        if ($op['new'] !== $op['old']) unlink($op['old']);
    }
    if ($dbEnabled && $hasMigrations) {
        foreach ($renamed as $old=>$new) {
            if ($old === $new || !$db->table('migrations')->where('migration', $old)->exists()) continue;
            if ($db->table('migrations')->where('migration', $new)->exists()) {
                echo "NOTICE: Both migration records exist: {$old}, {$new}; preserving history.\n";
                continue;
            }
            $db->table('migrations')->where('migration', $old)->update(['migration'=>$new]);
        }
        // Projects migrated BEFORE the combined migrations were split need
        // records for the new individual migrations, or Laravel would re-create
        // existing tables. Only reconcile when an old combined migration ran.
        if ($legacyBase || $legacyAdvanced) {
            $maxBatch = (int)$db->table('migrations')->max('batch');
            $directory = $root.'/database/migrations';
            foreach (glob($directory.'/2026_11_1[89]_*.php') ?: [] as $file) {
                $name = basename($file, '.php');
                if ($db->table('migrations')->where('migration', $name)->exists()) continue;
                $text = file_get_contents($file);
                if (!preg_match("/Schema::create\\('([^']+)'/", (string)$text, $match)) continue;
                $table = $match[1];
                $isBase = str_starts_with($name, '2026_11_18_');
                if ((($isBase && $legacyBase) || (!$isBase && $legacyAdvanced)) && $schema->hasTable($table)) {
                    $db->table('migrations')->insert(['migration'=>$name, 'batch'=>max(1,$maxBatch)]);
                    echo "HISTORY: Marked existing {$table} as migrated.\n";
                }
            }
            if ($legacyAdvanced) {
                foreach (['2026_11_19_000019_add_customer_email_coupon_code_to_orders',
                          '2026_11_19_000020_add_warehouse_id_to_stock_movements'] as $name) {
                    if ($db->table('migrations')->where('migration',$name)->exists()) continue;
                    $ok = str_contains($name, 'orders')
                        ? ($schema->hasTable('orders') && $schema->hasColumn('orders','customer_email') && $schema->hasColumn('orders','coupon_code'))
                        : ($schema->hasTable('stock_movements') && $schema->hasColumn('stock_movements','warehouse_id'));
                    if ($ok) $db->table('migrations')->insert(['migration'=>$name, 'batch'=>max(1,$maxBatch)]);
                }
                $name = '2026_11_19_000021_backfill_default_warehouse_stocks';
                if (!$db->table('migrations')->where('migration',$name)->exists() &&
                    $schema->hasTable('warehouses') && $schema->hasTable('warehouse_stocks')) {
                    $db->table('migrations')->insert(['migration'=>$name,'batch'=>max(1,$maxBatch)]);
                }
            }
        }
    }
} catch (Throwable $e) {
    fwrite(STDERR, "ERROR DURING APPLY: {$e->getMessage()}\n");
    if ($operations) fwrite(STDERR, "Source backups: {$backupDir}. DB DDL may already have run. Review before retrying.\n");
    exit(1);
}
if ($operations) echo "Source backups: ".substr($backupDir, strlen($root)+1)."\n";
echo "DONE. Run: php artisan migrate\nThen: php artisan optimize:clear\n";

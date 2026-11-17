<?php
/**
 * Fix only Retail PHPUnit test fixtures for the SaaS global users schema.
 * Usage (from Laravel project root):
 *   php fix_retail_test_fixtures.php --dry-run
 *   php fix_retail_test_fixtures.php
 * No migration, database write, or application source modification.
 */
declare(strict_types=1);

$root = __DIR__;
$dryRun = in_array('--dry-run', $argv, true);
$files = [
    'tests/Feature/RetailAccountingFlowTest.php',
    'tests/Feature/RetailWarehouseLedgerTest.php',
];
if (!is_file($root . '/artisan')) {
    fwrite(STDERR, "ERROR: Put this file beside Laravel's artisan file.\n");
    exit(1);
}

$changes = [];
foreach ($files as $relative) {
    $path = $root . '/' . $relative;
    if (!is_file($path)) {
        fwrite(STDERR, "ERROR: Missing {$relative}. No files changed.\n");
        exit(1);
    }
    $original = file_get_contents($path);
    if ($original === false) {
        fwrite(STDERR, "ERROR: Cannot read {$relative}. No files changed.\n");
        exit(1);
    }
    // Target the literal argument used by our two generated PHPUnit fixtures.
    // The global users table has no password field; business_users does.
    $needle = ",'password'=>'pass1234'";
    $occurrences = substr_count($original, $needle);
    if ($occurrences > 1 || ($occurrences === 0 && strpos($original, 'User::create(') !== false && preg_match('/User::create\([^;]+[\'\"]password[\'\"]\s*=>/s', $original))) {
        fwrite(STDERR, "ERROR: Unexpected test fixture version in {$relative}. No files changed.\n");
        exit(1);
    }
    if ($occurrences === 1) {
        $changes[$relative] = [
            'before' => $original,
            'after' => str_replace($needle, '', $original),
        ];
    }
}

if ($changes === []) {
    echo "Already fixed. Neither test contains the invalid global user password fixture.\n";
    exit(0);
}

foreach (array_keys($changes) as $relative) {
    echo ($dryRun ? '[DRY RUN] Would fix: ' : 'Fixing: ') . $relative . "\n";
}
if ($dryRun) {
    echo "No changes written.\n";
    exit(0);
}

$backupRoot = $root . '/storage/app/retail_test_fix_' . date('Ymd_His') . '_' . getmypid();
foreach ($changes as $relative => $pair) {
    $backupPath = $backupRoot . '/' . $relative;
    if (!is_dir(dirname($backupPath)) && !mkdir(dirname($backupPath), 0775, true) && !is_dir(dirname($backupPath))) {
        fwrite(STDERR, "ERROR: Cannot create backup folder. No files changed.\n");
        exit(1);
    }
    if (file_put_contents($backupPath, $pair['before']) === false) {
        fwrite(STDERR, "ERROR: Cannot back up {$relative}. No files changed.\n");
        exit(1);
    }
}

$written = [];
foreach ($changes as $relative => $pair) {
    $path = $root . '/' . $relative;
    if (file_put_contents($path, $pair['after'], LOCK_EX) === false) {
        foreach ($written as $restore) {
            file_put_contents($root . '/' . $restore, $changes[$restore]['before'], LOCK_EX);
        }
        fwrite(STDERR, "ERROR: Cannot write {$relative}; previously written changes restored.\n");
        exit(1);
    }
    $written[] = $relative;
}
echo "Success: " . count($written) . " PHPUnit fixture(s) fixed.\n";
echo "Backup: {$backupRoot}\n";
echo "Now run:\n  php artisan test --filter=RetailAccountingFlowTest\n  php artisan test --filter=RetailWarehouseLedgerTest\n";

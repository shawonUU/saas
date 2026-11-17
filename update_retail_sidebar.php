<?php
/**
 * Retail / E-commerce shared Sidebar UI update. No database or route changes.
 * Usage: php update_retail_sidebar.php [--dry-run] [--force]
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only.'); }
$root = __DIR__;
$relative = 'resources/views/public/business/admin/sidebar.blade.php';
$target = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
$dryRun = in_array('--dry-run', $argv, true);
$force = in_array('--force', $argv, true);
if (!is_file($root . '/artisan') || !is_file($root . '/composer.json')) {
    fwrite(STDERR, "ERROR: Put this script in your Laravel project root (beside artisan).\n");
    exit(1);
}
if (!is_file($target)) {
    fwrite(STDERR, "ERROR: Existing Sidebar Blade not found: {$relative}\n");
    exit(1);
}
$payload = gzuncompress(base64_decode('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', true));
if ($payload === false || hash('sha256', $payload) !== 'c2ac2e13ad9d4757b04489ceb4ada8141e1d27f8ff5fc27828257fb8416cddfe') {
    fwrite(STDERR, "ERROR: Bundled UI file is damaged.\n");
    exit(1);
}
$existing = file_get_contents($target);
if ($existing === false) { fwrite(STDERR, "ERROR: Can't read Sidebar.\n"); exit(1); }
$currentHash = hash('sha256', $existing);
if ($currentHash === 'c2ac2e13ad9d4757b04489ceb4ada8141e1d27f8ff5fc27828257fb8416cddfe') {
    echo "Already updated. Nothing to change.\n";
    exit(0);
}
if ($currentHash !== '29416660fd6ef6aeb1a3eff9578d05a1cdee76348f15c1e312873bdc75a29456' && !$force) {
    fwrite(STDERR, "ERROR: Your Sidebar differs from the known project version.\n");
    fwrite(STDERR, "To keep your manual edits safe, no file was changed. Review first, or use --force (automatic backup).\n");
    exit(2);
}
if ($dryRun) {
    echo "DRY RUN: will update {$relative} (one Blade file only).\n";
    echo "No migration, seeder, route, controller or other file changes.\n";
    exit(0);
}
$backupDir = $root.'/storage/app/retail_sidebar_backups/'.date('Ymd_His').'_' . substr($currentHash,0,8);
if (!is_dir($backupDir) && !mkdir($backupDir,0775,true) && !is_dir($backupDir)) {
    fwrite(STDERR, "ERROR: Can't create backup directory.\n"); exit(1);
}
$backupPath = $backupDir . '/sidebar.blade.php';
if (!copy($target, $backupPath)) { fwrite(STDERR, "ERROR: Backup failed. Nothing changed.\n"); exit(1); }
if (file_put_contents($target, $payload, LOCK_EX) === false || hash_file('sha256', $target) !== 'c2ac2e13ad9d4757b04489ceb4ada8141e1d27f8ff5fc27828257fb8416cddfe') {
    copy($backupPath, $target);
    fwrite(STDERR, "ERROR: Update failed; restored original Sidebar.\n"); exit(1);
}
echo "SUCCESS: Retail Sidebar UI updated.\n";
echo "Backup saved: " . str_replace($root.'/', '', $backupPath) . "\n";
// Refresh compiled Blade cache if Laravel dependencies are present.
if (is_file($root.'/vendor/autoload.php')) {
    $cwd = getcwd();
    chdir($root);
    $cmd = escapeshellarg(PHP_BINARY).' '.escapeshellarg('artisan').' view:clear 2>&1';
    $output=[]; $status=0;
    exec($cmd, $output, $status);
    if ($cwd !== false) chdir($cwd);
    echo $status === 0 ? "Blade view cache cleared.\n" : "Please run: php artisan view:clear\n";
}
echo "Done. No database change. You may remove this updater file.\n";

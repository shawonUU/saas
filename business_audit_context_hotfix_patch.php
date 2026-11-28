<?php
/**
 * Fix Laravel BusinessAuditContext ArgumentCountError.
 * Place beside artisan and run:
 * php business_audit_context_hotfix_patch.php --dry-run
 * php business_audit_context_hotfix_patch.php --apply
 * Optional: php business_audit_context_hotfix_patch.php --rollback
 */
declare(strict_types=1);

$target = __DIR__.'/app/Http/Middleware/BusinessAuditContext.php';
$backupRoot = __DIR__.'/.saas_backups/business_audit_context_hotfix';
$manifestPath = $backupRoot.'/latest.json';
$mode = $argv[1] ?? '--dry-run';
$old = 'public function handle(Request $request, Closure $next, BusinessDateService $days): Response';
$new = "public function __construct(private readonly BusinessDateService \$days)\n    {\n    }\n\n    public function handle(Request \$request, Closure \$next): Response";

if (!in_array($mode, ['--dry-run', '--apply', '--rollback'], true)) {
    fwrite(STDERR, "Usage: php business_audit_context_hotfix_patch.php --dry-run|--apply|--rollback\n");
    exit(2);
}
if ($mode === '--rollback') {
    if (!is_file($manifestPath)) {
        fwrite(STDERR, "No hotfix backup manifest found. Nothing restored.\n"); exit(1);
    }
    $manifest = json_decode((string)file_get_contents($manifestPath), true);
    if (!is_array($manifest) || empty($manifest['backup']) || empty($manifest['after_sha256'])) {
        fwrite(STDERR, "Invalid rollback manifest.\n"); exit(1);
    }
    if (!is_file($target) || hash_file('sha256', $target) !== $manifest['after_sha256']) {
        fwrite(STDERR, "File changed since hotfix; refusing to overwrite your updates.\n"); exit(1);
    }
    if (!is_file($manifest['backup']) || !copy($manifest['backup'], $target)) {
        fwrite(STDERR, "Could not restore original file.\n"); exit(1);
    }
    unlink($manifestPath);
    echo "ROLLBACK OK: original BusinessAuditContext.php restored.\n";
    exit(0);
}
if (!is_file($target)) {
    fwrite(STDERR, "Missing file: {$target}\n"); exit(1);
}
$content = (string)file_get_contents($target);
$oldCount = substr_count($content, $old);
if ($oldCount === 0 && str_contains($content, 'public function handle(Request $request, Closure $next): Response')
    && str_contains($content, 'private readonly BusinessDateService $days')) {
    echo "ALREADY FIXED: constructor injection found; no changes needed.\n";
    exit(0);
}
if ($oldCount !== 1 || substr_count($content, '$days->') !== 2) {
    fwrite(STDERR, "Unexpected middleware version. No changes made. Merge the provided fixed source manually.\n");
    exit(1);
}
$fixed = str_replace($old, $new, $content, $count);
$fixed = str_replace('$days->', '$this->days->', $fixed, $references);
if ($count !== 1 || $references !== 2) {
    fwrite(STDERR, "Replacement verification failed. No changes made.\n"); exit(1);
}
if ($mode === '--dry-run') {
    echo "DRY RUN OK: one middleware file will change; no migration.\n";
    exit(0);
}
if (!is_dir($backupRoot) && !mkdir($backupRoot, 0775, true) && !is_dir($backupRoot)) {
    fwrite(STDERR, "Cannot create backup directory.\n"); exit(1);
}
$backup = $backupRoot.'/BusinessAuditContext.'.date('Ymd_His').'.'.bin2hex(random_bytes(3)).'.php';
if (!copy($target, $backup)) {
    fwrite(STDERR, "Backup failed; file unchanged.\n"); exit(1);
}
$tmp = $target.'.hotfix_'.bin2hex(random_bytes(4));
if (file_put_contents($tmp, $fixed) !== strlen($fixed) || !copy($tmp, $target)) {
    @unlink($tmp);
    copy($backup, $target);
    fwrite(STDERR, "Write failed; restored original file.\n"); exit(1);
}
@unlink($tmp);
$manifest = ['backup' => $backup, 'before_sha256' => hash_file('sha256', $backup), 'after_sha256' => hash_file('sha256', $target)];
if (file_put_contents($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) === false) {
    fwrite(STDERR, "Applied fix but could not write rollback manifest. Backup: {$backup}\n");
    exit(1);
}
echo "APPLY OK: fixed BusinessAuditContext.php; backup: {$backup}\n";

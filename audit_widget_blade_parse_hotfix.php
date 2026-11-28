<?php
/**
 * Audit Log widget Blade ParseError hotfix.
 * Run beside artisan:
 * php audit_widget_blade_parse_hotfix.php --dry-run
 * php audit_widget_blade_parse_hotfix.php --apply
 * php artisan view:clear
 * Optional: php audit_widget_blade_parse_hotfix.php --rollback
 * Only changes the audit history widget Blade view.
 */
declare(strict_types=1);

$target = __DIR__.'/resources/views/public/business/admin/settings/audit/activity-logs/partials/widget.blade.php';
$backups = __DIR__.'/.saas_backups/audit_widget_blade_parse_hotfix';
$manifest = $backups.'/latest.json';
$mode = $argv[1] ?? '--dry-run';
if (!in_array($mode, ['--dry-run', '--apply', '--rollback'], true)) {
    fwrite(STDERR, "Usage: php audit_widget_blade_parse_hotfix.php --dry-run|--apply|--rollback\n");
    exit(2);
}
if (!is_file($target)) {
    fwrite(STDERR, "Missing audit widget: {$target}\n");
    exit(1);
}
if ($mode === '--rollback') {
    if (!is_file($manifest)) {
        fwrite(STDERR, "No backup manifest found. Nothing to restore.\n");
        exit(1);
    }
    $data = json_decode((string)file_get_contents($manifest), true);
    if (!is_array($data) || !isset($data['backup'], $data['fixed_sha256']) || !is_file($data['backup'])) {
        fwrite(STDERR, "Invalid or missing backup.\n");
        exit(1);
    }
    if (hash_file('sha256', $target) !== $data['fixed_sha256']) {
        fwrite(STDERR, "The widget changed after this patch. Refusing to overwrite newer edits.\n");
        exit(1);
    }
    if (!copy($data['backup'], $target)) {
        fwrite(STDERR, "Rollback failed. Original is still in {$data['backup']}\n");
        exit(1);
    }
    @unlink($manifest);
    echo "ROLLBACK OK: Audit widget restored. Run php artisan view:clear\n";
    exit(0);
}
$source = (string)file_get_contents($target);
if (str_contains($source, 'const word=@js($auditHistoryLabels);') &&
    str_contains($source, 'const base=@js($auditHistoryRecordUrl);')) {
    echo "ALREADY FIXED: Audit widget uses simple Blade variables.\n";
    exit(0);
}
$oldBase = "const base=@js(route('business.admin.settings.audit.activity-logs.record',['business'=>request()->route('business')]));";
$oldWords = "const word=@json(['log'=>t('View Log','লগ দেখুন'),'loading'=>t('Loading history…','ইতিহাস লোড হচ্ছে…'),'empty'=>t('No history found for this record.','এই রেকর্ডের ইতিহাস পাওয়া যায়নি।'),'error'=>t('Unable to load history.','ইতিহাস লোড করা যায়নি।')]);";
if (substr_count($source, $oldBase) !== 1 || substr_count($source, $oldWords) !== 1 || substr_count($source, '<script>') !== 1) {
    fwrite(STDERR, "Unexpected widget version. Nothing changed; compare the supplied fixed Blade file before merging.\n");
    exit(1);
}
$vars = <<<'BLADE'
@php
    $auditHistoryRecordUrl = route('business.admin.settings.audit.activity-logs.record', [
        'business' => request()->route('business'),
    ]);
    $auditHistoryLabels = [
        'log' => t('View Log', 'লগ দেখুন'),
        'loading' => t('Loading history…', 'ইতিহাস লোড হচ্ছে…'),
        'empty' => t('No history found for this record.', 'এই রেকর্ডের ইতিহাস পাওয়া যায়নি।'),
        'error' => t('Unable to load history.', 'ইতিহাস লোড করা যায়নি।'),
    ];
@endphp
BLADE;
$fixed = str_replace('<script>', $vars."\n<script>", $source);
$fixed = str_replace($oldBase, 'const base=@js($auditHistoryRecordUrl);', $fixed);
$fixed = str_replace($oldWords, 'const word=@js($auditHistoryLabels);', $fixed);
if (!str_contains($fixed, 'const base=@js($auditHistoryRecordUrl);') ||
    !str_contains($fixed, 'const word=@js($auditHistoryLabels);') ||
    str_contains($fixed, $oldWords)) {
    fwrite(STDERR, "Replacement verification failed. File unchanged.\n");
    exit(1);
}
if ($mode === '--dry-run') {
    echo "DRY RUN OK: fixes @json([... t(...) ...]) parse and nested @js(route(...)) in one Blade file.\n";
    echo "No migrations, routes, models, or database changes.\n";
    exit(0);
}
if (!is_dir($backups) && !mkdir($backups, 0775, true) && !is_dir($backups)) {
    fwrite(STDERR, "Cannot create backup directory.\n");
    exit(1);
}
$backup = $backups.'/widget.'.date('Ymd_His').'.'.bin2hex(random_bytes(3)).'.blade.php';
if (!copy($target, $backup)) {
    fwrite(STDERR, "Could not back up widget. File unchanged.\n");
    exit(1);
}
$tmp = $target.'.hotfix_'.bin2hex(random_bytes(3));
if (file_put_contents($tmp, $fixed) !== strlen($fixed) || !rename($tmp, $target)) {
    @unlink($tmp);
    fwrite(STDERR, "Could not replace widget; existing original was backed up at {$backup}\n");
    exit(1);
}
$data = ['backup' => $backup, 'original_sha256' => hash_file('sha256', $backup), 'fixed_sha256' => hash_file('sha256', $target)];
if (file_put_contents($manifest, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) === false) {
    fwrite(STDERR, "Fix applied but manifest write failed; original backup: {$backup}\n");
    exit(1);
}
echo "APPLY OK: Audit widget ParseError fixed. Backup: {$backup}\nRun php artisan view:clear\n";

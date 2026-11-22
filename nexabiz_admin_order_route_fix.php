<?php
/**
 * NexaBiz Business Admin > Sales > Orders > Order Details route parameter fix.
 *
 * Usage:
 *   php nexabiz_admin_order_route_fix.php --dry-run
 *   php nexabiz_admin_order_route_fix.php --apply
 *   php nexabiz_admin_order_route_fix.php --rollback
 *
 * Why: admin.{business}.saas.test contributes a {business} route value before
 * the /sales/orders/order-details/{order?} value. Both methods must accept it.
 *
 * This patch changes only method parameter signatures. No DB/route/view edits.
 */
declare(strict_types=1);

$command = $argv[1] ?? '--dry-run';
if (!in_array($command, ['--dry-run', '--apply', '--rollback'], true)) {
    fwrite(STDERR, "Usage: php nexabiz_admin_order_route_fix.php [--dry-run|--apply|--rollback]\n");
    exit(1);
}
$root = getcwd();
if (!is_file($root.'/artisan') || !is_file($root.'/composer.json')) {
    fwrite(STDERR, "ERROR: Run from your Laravel project root (artisan + composer.json).\n");
    exit(1);
}
$relative = 'app/Http/Controllers/Public/Business/Admin/Sales/OrderController.php';
$target = $root.'/'.$relative;
$backupDir = $root.'/storage/app/nexabiz-patch-backups/admin-order-route-fix-v1';
$backupFile = $backupDir.'/OrderController.php.bak';
$manifestFile = $backupDir.'/manifest.json';
$edits = [
    'show' => [
        'before' => 'public function show(Request $r,Access $a,?int $order=null)',
        'after'  => 'public function show(Request $r,Access $a,string $business,?int $order=null)',
    ],
    'cancel' => [
        'before' => 'public function cancel(Request $r,int $order,Access $a,\App\Services\CancelSale $service)',
        'after'  => 'public function cancel(Request $r,string $business,int $order,Access $a,\App\Services\CancelSale $service)',
    ],
];
function nabWrite(string $path, string $contents): void {
    $parent = dirname($path);
    if (!is_dir($parent) && !mkdir($parent, 0775, true) && !is_dir($parent)) {
        throw new RuntimeException('Cannot create directory: '.$parent);
    }
    $tmp = tempnam($parent, '.nexabiz-order-');
    if ($tmp === false) throw new RuntimeException('Cannot create temporary file.');
    try {
        if (file_put_contents($tmp, $contents, LOCK_EX) === false) {
            throw new RuntimeException('Cannot write file: '.$path);
        }
        if (is_file($path)) @chmod($tmp, fileperms($path) & 0777);
        if (!rename($tmp, $path)) throw new RuntimeException('Cannot replace file: '.$path);
    } finally {
        if (is_file($tmp)) @unlink($tmp);
    }
}
function nabPhpLint(string $body): void {
    // The patch only alters parameter lists; validate expected class/method text
    // while leaving full syntax lint to `php -l` on the installed file.
    foreach (['namespace App\\Http\\Controllers\\Public\\Business\\Admin\\Sales;',
              'class OrderController extends Controller', 'function show(', 'function cancel('] as $needle) {
        if (!str_contains($body, $needle)) throw new RuntimeException('Unexpected controller structure: '.$needle);
    }
}
try {
    if (!is_file($target)) throw new RuntimeException('Controller not found: '.$relative);
    $original = (string)file_get_contents($target);
    nabPhpLint($original);
    if ($command === '--rollback') {
        if (!is_file($manifestFile) || !is_file($backupFile)) {
            throw new RuntimeException('Backup missing; cannot rollback.');
        }
        $manifest = json_decode((string)file_get_contents($manifestFile), true, 512, JSON_THROW_ON_ERROR);
        if (($manifest['status'] ?? '') !== 'applied') {
            throw new RuntimeException('Patch is not currently applied.');
        }
        if (hash('sha256',$original) !== ($manifest['patched_sha256'] ?? '')) {
            throw new RuntimeException('Controller edited since patch. Refusing to overwrite your changes.');
        }
        $backup = (string)file_get_contents($backupFile);
        if (hash('sha256',$backup) !== ($manifest['original_sha256'] ?? '')) {
            throw new RuntimeException('Backup integrity check failed.');
        }
        nabWrite($target, $backup);
        $manifest['status'] = 'rolled-back';
        nabWrite($manifestFile, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
        echo "RESTORED {$relative}\nRollback successful. Run: php artisan route:clear\n";
        exit(0);
    }
    $patched = $original;
    $needed = [];
    foreach ($edits as $name => $edit) {
        if (str_contains($patched, $edit['after'])) {
            echo "ALREADY FIXED: {$name}()\n";
            continue;
        }
        if (substr_count($patched, $edit['before']) !== 1) {
            throw new RuntimeException("Cannot safely identify {$name}() signature. Aborting; no files changed.");
        }
        $needed[] = $name;
        $patched = str_replace($edit['before'], $edit['after'], $patched);
    }
    if (!$needed) { echo "Already fixed; no files changed.\n"; exit(0); }
    nabPhpLint($patched);
    if ($command === '--dry-run') {
        echo "WOULD UPDATE {$relative}\nMethods: ".implode(', ', $needed)."\n";
        echo "No files changed.\n";
        exit(0);
    }
    if (is_file($manifestFile)) {
        $prev = json_decode((string)file_get_contents($manifestFile), true);
        if (($prev['status'] ?? '') === 'applied') {
            throw new RuntimeException('An active backup exists but controller is not fully patched. Inspect your last installation first.');
        }
    }
    nabWrite($backupFile, $original);
    $manifest = [
        'patch' => 'admin-order-route-fix-v1',
        'status' => 'applied',
        'created_at' => date(DATE_ATOM),
        'file' => $relative,
        'methods' => $needed,
        'original_sha256' => hash('sha256',$original),
        'patched_sha256' => hash('sha256',$patched),
    ];
    nabWrite($manifestFile, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
    nabWrite($target, $patched);
    echo "PATCHED {$relative}\nUpdated: ".implode(', ', $needed)."\n";
    echo "Next: php -l {$relative}\n      php artisan route:clear\n";
} catch (Throwable $e) {
    fwrite(STDERR, "ERROR: ".$e->getMessage()."\n");
    exit(1);
}

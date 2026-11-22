<?php
/**
 * NexaBiz eCommerce domain route parameter fix
 * Apply: php nexabiz_ecommerce_route_parameter_fix.php --apply
 * Dry-run: php nexabiz_ecommerce_route_parameter_fix.php --dry-run
 * Rollback: php nexabiz_ecommerce_route_parameter_fix.php --rollback
 *
 * Fixes domain {business} + path {product}/{order} controller argument ordering.
 */

declare(strict_types=1);

$root = getcwd();
$command = $argv[1] ?? '--dry-run';
if (!in_array($command, ['--dry-run', '--apply', '--rollback'], true)) {
    fwrite(STDERR, "Usage: php nexabiz_ecommerce_route_parameter_fix.php [--dry-run|--apply|--rollback]\n");
    exit(1);
}
if (!is_file($root.DIRECTORY_SEPARATOR.'artisan') || !is_file($root.DIRECTORY_SEPARATOR.'composer.json')) {
    fwrite(STDERR, "ERROR: Run this patch inside Laravel root (artisan and composer.json required).\n");
    exit(1);
}

$changes = [
    'app/Http/Controllers/Public/Business/Public/Ecommerce/Catalog/ProductDetails/ProductDetailsController.php' => [
        [
            'before' => 'public function show(Request $r,int $product,CatalogService $catalog)',
            'after' => 'public function show(Request $r,CatalogService $catalog,string $business,int $product)',
        ],
    ],
    'app/Http/Controllers/Public/Business/Public/ShopController.php' => [
        [
            'before' => 'public function success(Request $r,string $order)',
            'after' => 'public function success(Request $r,string $business,string $order)',
        ],
    ],
    'app/Http/Controllers/Public/Business/Public/GatewayController.php' => [
        [
            'before' => 'public function start(Request $r,string $order,SslCommerz $api)',
            'after' => 'public function start(Request $r,SslCommerz $api,string $business,string $order)',
        ],
        [
            'before' => 'use App\\Models\\Accounts\\GatewayPayment;',
            'after' => "use App\\Models\\Accounts\\GatewayPayment;\nuse App\\Models\\Sales\\Order;",
        ],
    ],
];

$backupDir = $root.'/storage/app/nexabiz-patch-backups/ecommerce-route-parameter-fix-v1';
$manifestPath = $backupDir.'/manifest.json';

function patchWrite(string $file, string $contents): void
{
    $dir = dirname($file);
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        throw new RuntimeException('Could not create directory: '.$dir);
    }
    $temp = tempnam($dir, '.nexabiz-fix-');
    if ($temp === false || file_put_contents($temp, $contents, LOCK_EX) === false) {
        throw new RuntimeException('Cannot write temporary file: '.$file);
    }
    if (is_file($file)) {
        @chmod($temp, fileperms($file) & 0777);
    }
    if (!rename($temp, $file)) {
        @unlink($temp);
        throw new RuntimeException('Cannot replace file: '.$file);
    }
}

try {
    if ($command === '--rollback') {
        if (!is_file($manifestPath)) {
            throw new RuntimeException('No backup manifest found: '.$manifestPath);
        }
        $manifest = json_decode((string) file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);
        if (empty($manifest['files']) || !is_array($manifest['files'])) {
            throw new RuntimeException('Backup manifest is invalid.');
        }
        // Preflight first: never overwrite new user edits.
        foreach ($manifest['files'] as $relative => $meta) {
            $current = $root.'/'.$relative;
            $backup = $backupDir.'/original/'.$relative;
            if (!is_file($current) || !is_file($backup)) {
                throw new RuntimeException('Cannot rollback missing file: '.$relative);
            }
            if (hash_file('sha256', $current) !== $meta['patched_sha256']) {
                throw new RuntimeException('File modified after patch, refusing rollback: '.$relative);
            }
            if (hash_file('sha256', $backup) !== $meta['original_sha256']) {
                throw new RuntimeException('Backup hash mismatch: '.$relative);
            }
        }
        foreach ($manifest['files'] as $relative => $meta) {
            patchWrite($root.'/'.$relative, (string)file_get_contents($backupDir.'/original/'.$relative));
            echo "RESTORED  {$relative}\n";
        }
        // Mark rolled back, retain backups for audit.
        patchWrite($manifestPath, json_encode($manifest+['status'=>'rolled-back'], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\n");
        echo "Rollback completed. Run: php artisan optimize:clear\n";
        exit(0);
    }
    $updates = [];
    foreach ($changes as $relative => $replacements) {
        $file = $root.'/'.$relative;
        if (!is_file($file)) {
            throw new RuntimeException('Required controller missing: '.$relative);
        }
        $original = (string)file_get_contents($file);
        $updated = $original;
        foreach ($replacements as $replacement) {
            $before = $replacement['before'];
            $after = $replacement['after'];
            if (str_contains($updated, $after)) {
                continue;
            }
            if (substr_count($updated, $before) !== 1) {
                throw new RuntimeException('Expected code not found exactly once in '.$relative.': '.$before);
            }
            $updated = str_replace($before, $after, $updated);
        }
        if ($updated === $original) {
            echo "ALREADY FIXED {$relative}\n";
        } else {
            $updates[$relative] = ['before'=>$original, 'after'=>$updated];
            echo ($command === '--dry-run'?'WOULD PATCH ':'PATCH       ').$relative."\n";
        }
    }
    if ($command === '--dry-run') {
        echo "Dry run complete. ".count($updates)." file(s) would change.\n";
        exit(0);
    }
    if (!$updates) {
        echo "Nothing to change.\n";
        exit(0);
    }
    if (is_file($manifestPath)) {
        $oldManifest = json_decode((string)file_get_contents($manifestPath), true);
        if (!empty($oldManifest['files'])) {
            throw new RuntimeException('A previous backup exists; review it before reapplying: '.$manifestPath);
        }
    }
    $manifest = ['patch'=>'ecommerce-route-parameter-fix-v1','created_at'=>date(DATE_ATOM),'files'=>[]];
    foreach ($updates as $relative => $body) {
        $manifest['files'][$relative] = [
            'original_sha256'=>hash('sha256',$body['before']),
            'patched_sha256'=>hash('sha256',$body['after']),
        ];
        patchWrite($backupDir.'/original/'.$relative,$body['before']);
    }
    patchWrite($manifestPath,json_encode($manifest,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\n");
    foreach ($updates as $relative => $body) {
        patchWrite($root.'/'.$relative,$body['after']);
    }
    echo "Patch applied successfully. ".count($updates)." controller(s) updated.\n";
    echo "Run: php artisan route:clear && php artisan view:clear\n";
} catch (Throwable $e) {
    fwrite(STDERR,"ERROR: ".$e->getMessage()."\n");
    exit(1);
}

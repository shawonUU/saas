<?php
/**
 * NexaBiz product details Blade parser fix (one-file, backup/rollback).
 * php nexabiz_product_details_blade_fix.php --dry-run
 * php nexabiz_product_details_blade_fix.php --apply
 * php nexabiz_product_details_blade_fix.php --rollback
 */
declare(strict_types=1);

$action = $argv[1] ?? '--dry-run';
if (!in_array($action, ['--dry-run', '--apply', '--rollback'], true)) {
    fwrite(STDERR, "Usage: php nexabiz_product_details_blade_fix.php [--dry-run|--apply|--rollback]\n");
    exit(1);
}
$root = getcwd();
if (!is_file($root.'/artisan') || !is_file($root.'/composer.json')) {
    fwrite(STDERR, "Run this file from your Laravel project root (artisan + composer.json).\n");
    exit(1);
}
$path = 'resources/views/public/business/public/ecommerce/catalog/show.blade.php';
$file = $root.'/'.$path;
$backupFolder = $root.'/storage/app/nexabiz-patch-backups/product-details-blade-parse-v1';
$backupFile = $backupFolder.'/original.blade.php';
$manifestFile = $backupFolder.'/manifest.json';
$expectedOriginal = 'a27188142a2e3b84da5da06f900380323d3f60a3b554a57bae89c8859970048b';
$expectedNew = 'c6d00790989b74cd1938aeb5676705507c78def722213d4fcb28483b949df124';
$replacement = <<<'BLADE'
@extends('public.business.public.ecommerce.layout')
@section('title', $item->name)
@php
    $detailBrand = app(\App\Services\Public\Business\Admin\Settings\Business\GeneralSettings\BrandingService::class);
    $selectedVariant = $item->variants->first(fn($v) => !$item->track_stock || (float) $v->stock > 0) ?? $item->variants->first();
    $detailPhoto = $detailBrand->imageUrl($selectedVariant?->image_path ?: $item->image_url);
    $detailPrice = (float) ($selectedVariant?->price ?? $item->price);
    $detailBefore = (float) ($item->compare_at_price ?? 0);
    $detailOff = $detailBefore > $detailPrice && $detailPrice > 0 ? round((1 - $detailPrice / $detailBefore) * 100) : 0;
    $gallery = collect([$item->image_url])->merge($item->variants->pluck('image_path'))->filter()->unique()->map(fn($path) => $detailBrand->imageUrl($path))->filter()->values();
    $rawSpecs = $item->specifications ?? [];
    $detailSpecs = is_array($rawSpecs) ? $rawSpecs : (json_decode((string) $rawSpecs, true) ?: []);
@endphp
@section('content')
<div class="sf-page-heading sf-page-heading-small">
    <a href="{{ route('business.customer.shop') }}" class="sf-back">‹</a>
    <h1>{{ t('Product Details','পণ্যের বিস্তারিত') }}</h1>
</div>
<div class="sf-details-title">{{ $item->name }}</div>
<div class="sf-detail-cols">
    <div class="sf-gallery">
        <div class="sf-gallery-thumbs">
            @foreach($gallery->take(12) as $photo)
                <button class="sf-gallery-thumb" data-gallery-src="{{ $photo }}" type="button">
                    <img src="{{ $photo }}" alt="{{ $item->name }}">
                </button>
            @endforeach
        </div>
        <div class="sf-main-art">
            @if($detailPhoto)
                <img id="sf-detail-image" src="{{ $detailPhoto }}" alt="{{ $item->name }}">
            @else
                <div class="sf-placeholder">▧</div>
            @endif
            @if($detailOff)
                <span class="sf-sale-badge sf-detail-sale" id="sf-pdp-discount">-{{ $detailOff }}%</span>
            @endif
        </div>
    </div>
    <section class="sf-pdp-order">
        <div class="sf-pdp-prices">
            <strong id="sf-detail-price">৳ {{ number_format($detailPrice, 2) }}</strong>
            @if($detailOff)
                <del id="sf-before-price">৳{{ number_format($detailBefore, 2) }}</del>
                <small id="sf-saving-badge">-{{ $detailOff }}%</small>
            @else
                <del id="sf-before-price" hidden></del>
                <small id="sf-saving-badge" hidden></small>
            @endif
        </div>
        <p class="sf-detail-sku">{{ t('SKU','SKU') }}: {{ $item->sku }}</p>
        <h2>{{ t('Color, Size & Variants','রং, সাইজ ও ভ্যারিয়েন্ট') }}</h2>
        <div class="sf-variant-cards" role="group" aria-label="{{ t('Choose Variant','ভ্যারিয়েন্ট বেছে নিন') }}">
            @foreach($item->variants as $variant)
                <button type="button" class="sf-variant-chip" data-variant-option="{{ $variant->id }}" @disabled($item->track_stock && $variant->stock <= 0)>
                    <span class="sf-variant-chip-photo">
                        @if($variant->image_path || $item->image_url)
                            <img src="{{ $detailBrand->imageUrl($variant->image_path ?: $item->image_url) }}" alt="">
                        @else
                            ◈
                        @endif
                    </span>
                    <span>{{ collect([$variant->color?->name, $variant->size?->name])->filter()->implode(' · ') ?: $variant->sku }}</span>
                </button>
            @endforeach
        </div>
        <label class="sf-detail-select">
            {{ t('Selected Variant','নির্বাচিত ভ্যারিয়েন্ট') }}
            <select id="sf-detail-variant" data-compare="{{ $detailBefore }}">
                @foreach($item->variants as $variant)
                    <option value="{{ $variant->id }}"
                        data-stock="{{ $item->track_stock ? $variant->stock : 99999 }}"
                        data-price="{{ $variant->price }}"
                        data-image="{{ $detailBrand->imageUrl($variant->image_path ?: $item->image_url) }}"
                        @selected($selectedVariant?->id === $variant->id)
                        @disabled($item->track_stock && $variant->stock <= 0)>
                        {{ collect([$variant->size?->name, $variant->color?->name, $variant->sku])->filter()->implode(' · ') }} · ৳{{ number_format($variant->price, 2) }}
                    </option>
                @endforeach
            </select>
        </label>
        <div class="sf-pdp-stock" id="sf-detail-stock">{{ t('Choose a variant to see availability','স্টক দেখতে ভ্যারিয়েন্ট বেছে নিন') }}</div>
        <div class="sf-detail-buttons">
            <button class="sf-btn sf-pdp-add" id="sf-detail-add" data-detail-add data-product="{{ $item->id }}" data-name="{{ $item->name }}" type="button">{{ t('Add to Cart','কার্টে যোগ করুন') }}</button>
            <button class="sf-btn sf-pdp-buy" type="button" data-buy-now>{{ t('Buy Now','এখনই কিনুন') }}</button>
            <button class="sf-pdp-wish" type="button" data-wish-toggle data-id="{{ $item->id }}" data-name="{{ $item->name }}" data-price="{{ $detailPrice }}" data-url="{{ route('business.customer.product', ['product' => $item->id]) }}" data-image="{{ $detailPhoto }}" aria-label="Wishlist">♡</button>
        </div>
    </section>
    <aside class="sf-pdp-recommend">
        <h3>{{ t('Suggestions','আরও পণ্য') }}</h3>
        @foreach($related->take(12) as $recommendation)
            @php
                $rPhoto = $detailBrand->imageUrl($recommendation->image_url ?: $recommendation->variants->first()?->image_path);
            @endphp
            <a href="{{ route('business.customer.product', ['product' => $recommendation->id]) }}">
                <span>
                    @if($rPhoto)
                        <img src="{{ $rPhoto }}" alt="">
                    @else
                        ◈
                    @endif
                </span>
                <div>
                    <small>{{ $recommendation->name }}</small>
                    <strong>৳{{ number_format((float) ($recommendation->variants->first()?->price ?? $recommendation->price), 2) }}</strong>
                </div>
            </a>
        @endforeach
    </aside>
</div>
<section class="sf-details-tabs">
    <h3>{{ t('Specifications','বিস্তারিত তথ্য') }}</h3>
    <div class="sf-spec-row"><span>{{ t('Product','পণ্য') }}</span><strong>{{ $item->name }}</strong></div>
    <div class="sf-spec-row"><span>SKU</span><strong>{{ $item->sku }}</strong></div>
    <div class="sf-spec-row"><span>{{ t('Variants','ভ্যারিয়েন্ট') }}</span><strong>{{ $item->variants->count() }}</strong></div>
    @foreach($detailSpecs as $spec)
        @if(is_array($spec) && !empty($spec['name']) && !empty($spec['value']))
            <div class="sf-spec-row">
                <span>{{ $spec['name'] }}</span>
                <strong>{{ $spec['value'] }}</strong>
            </div>
        @endif
    @endforeach
    @if($item->description)
        <div class="sf-spec-row">
            <span>{{ t('Description','বিবরণ') }}</span>
            <strong style="white-space:pre-wrap">{{ $item->description }}</strong>
        </div>
    @endif
</section>
@endsection
BLADE;
// Nowdoc ends with a newline before BLADE; preserve exactly one newline.
$replacement .= "\n";
function normalizedHash(string $value): string {
    return hash('sha256', str_replace(["\r\n", "\r"], "\n", $value));
}
function safeWrite(string $path, string $data): void {
    $folder = dirname($path);
    if (!is_dir($folder) && !mkdir($folder, 0775, true) && !is_dir($folder)) throw new RuntimeException('Cannot create directory '.$folder);
    $temp = tempnam($folder, '.nexabiz-');
    if ($temp === false || file_put_contents($temp, $data, LOCK_EX) === false) throw new RuntimeException('Cannot stage '.$path);
    if (is_file($path)) @chmod($temp, fileperms($path) & 0777);
    if (!rename($temp, $path)) { @unlink($temp); throw new RuntimeException('Cannot replace '.$path); }
}
try {
    if (!is_file($file)) throw new RuntimeException('Missing Blade file: '.$path);
    $present = (string) file_get_contents($file);
    $presentHash = normalizedHash($present);
    if (normalizedHash($replacement) !== $expectedNew) throw new RuntimeException('Embedded patch payload checksum failed.');
    if ($action === '--rollback') {
        if (!is_file($manifestFile) || !is_file($backupFile)) throw new RuntimeException('No previous backup to rollback.');
        $manifest = json_decode((string)file_get_contents($manifestFile), true, 512, JSON_THROW_ON_ERROR);
        if ($presentHash !== $manifest['new_hash']) throw new RuntimeException('Blade file changed after patch. Rollback blocked to protect edits.');
        $original = (string)file_get_contents($backupFile);
        if (hash('sha256', $original) !== $manifest['original_raw_hash']) throw new RuntimeException('Backup checksum mismatch.');
        safeWrite($file, $original);
        echo "RESTORED: $path\n";
        exit(0);
    }
    if ($presentHash === $expectedNew) { echo "ALREADY FIXED: $path\n"; exit(0); }
    if ($presentHash !== $expectedOriginal) {
        throw new RuntimeException("Source file differs from the V2 patch's expected version. No files changed. Please share the current show.blade.php to merge safely.");
    }
    if ($action === '--dry-run') { echo "READY: $path (1 Blade file will be updated; backup will be created)\n"; exit(0); }
    if (is_file($manifestFile)) {
        throw new RuntimeException('Existing patch backup found. Not replacing previous backup.');
    }
    if (!is_dir($backupFolder) && !mkdir($backupFolder, 0775, true) && !is_dir($backupFolder)) throw new RuntimeException('Cannot create backup folder.');
    safeWrite($backupFile, $present);
    $manifest = [
        'file' => $path,
        'original_raw_hash' => hash('sha256', $present),
        'original_hash' => $presentHash,
        'new_hash' => $expectedNew,
        'date' => date(DATE_ATOM),
    ];
    safeWrite($manifestFile, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    // Keep the project's existing Windows or Unix line-ending convention.
    $written = str_contains($present, "\r\n") ? str_replace("\n", "\r\n", $replacement) : $replacement;
    safeWrite($file, $written);
    echo "UPDATED: $path\nBACKUP: $backupFolder\n";
} catch (Throwable $e) {
    fwrite(STDERR, "ERROR: ".$e->getMessage()."\n");
    exit(1);
}

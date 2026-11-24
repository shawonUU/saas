
<?php
$variant = $product->variants->first(fn($v) => !$product->track_stock || (float)$v->stock > 0);
$price = (float)($variant?->price ?? $product->price);
$previous = (float)($product->compare_at_price ?? 0);
$discount = ($previous > $price && $price > 0) ? round((($previous-$price)/$previous)*100) : 0;
$photo = app(\App\Services\Public\Business\Admin\Settings\Business\GeneralSettings\BrandingService::class)->imageUrl($variant?->image_path ?: $product->image_url);
$sold = (int)($soldCounts[$product->id] ?? 0);
$detailUrl = route('business.customer.product', ['product'=>$product->id]);
?>

<?php if (! $__env->hasRenderedOnce('b90d321f-2c94-4a9c-80a5-18680bfe79b9')): $__env->markAsRenderedOnce('b90d321f-2c94-4a9c-80a5-18680bfe79b9'); ?>
<style>
/* PRODUCT GRID: MAXIMUM 6 COLUMNS */
.sf-products-grid {
    display: grid !important;
    grid-template-columns: repeat(6,minmax(0,1fr));
    gap: 12px;
    align-items: stretch;
}
.sf-products-grid > .sf-product {
    width: 100% !important;
    max-width: none !important;
    min-width: 0;
}

@media(max-width:1439px) {
    .sf-products-grid {grid-template-columns:repeat(5,minmax(0,1fr))}
}
@media(max-width:1199px) {
    .sf-products-grid {grid-template-columns:repeat(4,minmax(0,1fr))}
}
@media(max-width:991px) {
    .sf-products-grid {grid-template-columns:repeat(3,minmax(0,1fr))}
}
@media(max-width:767px) {
    .sf-products-grid {
        grid-template-columns:repeat(2,minmax(0,1fr));
        gap:9px;
    }
}

/* PREMIUM CARD */
.sf-product {
    --pc-bg:#fff;
    --pc-text:#202838;
    --pc-muted:#8892a0;
    --pc-border:#e9edf2;
    --pc-image:#f7f8fa;
    --pc-button:#202c42;
    --pc-button-hover:#34445f;
    --pc-button-text:#fff;
    --pc-heart-bg:#fff;

    display:flex;
    flex-direction:column;
    position:relative;
    width:100%;
    min-width:0;
    height:100%;
    overflow:hidden;
    border:1px solid var(--pc-border);
    border-radius:11px;
    background:var(--pc-bg);
    box-shadow:0 2px 8px rgba(20,30,50,.025);
    transition:border-color .2s,box-shadow .2s;
}

.sf-product:hover {
    border-color:#cbd4df;
    box-shadow:0 5px 18px rgba(20,30,50,.085);
    transform:none !important;
}

/* IMAGE */
.sf-product .sf-product-image-wrap {
    position:relative;
    width:100%;
    overflow:hidden;
    background:var(--pc-image);
}

.sf-product .sf-product-image {
    display:flex;
    align-items:center;
    justify-content:center;
    width:100%;
    height:auto !important;
    aspect-ratio:6/5 !important;
    overflow:hidden;
    background:var(--pc-image);
}

.sf-product .sf-product-image img {
    display:block;
    width:100%;
    height:100%;
    padding:7px;
    object-fit:contain;
    box-sizing:border-box;
    transition:transform .3s ease;
}

.sf-product:hover .sf-product-image img {
    transform:scale(1.025);
}

.sf-product .sf-placeholder {
    color:#c0c7d0;
    font-size:28px;
}

/* DISCOUNT */
.sf-product .sf-sale-badge {
    position:absolute;
    top:7px;
    left:7px;
    z-index:2;
    padding:4px 7px;
    border-radius:5px;
    background:#e64553;
    color:#fff;
    font-size:10px;
    font-weight:750;
    line-height:1.2;
}

/* WISHLIST */
.sf-product .sf-heart {
    position:absolute;
    top:6px;
    right:6px;
    z-index:3;
    display:flex !important;
    align-items:center;
    justify-content:center;
    width:29px;
    height:29px;
    padding:0;
    border:1px solid var(--pc-border);
    border-radius:50%;
    background:var(--pc-heart-bg);
    color:var(--pc-text);
    font-size:21px;
    line-height:1;
    cursor:pointer;
    opacity:1 !important;
    visibility:visible !important;
    transform:none !important;
    transition:color .2s,background .2s;
}

.sf-product .sf-heart:hover,
.sf-product .sf-heart.active,
.sf-product .sf-heart.is-active {
    color:#e64553;
}

/* CONTENT */
.sf-product .sf-product-content {
    position:relative !important;
    display:flex !important;
    flex-direction:column;
    flex:1;
    width:100%;
    min-width:0;
    height:auto !important;
    padding:9px 9px 9px !important;
    overflow:visible !important;
    background:var(--pc-bg);
    opacity:1 !important;
    visibility:visible !important;
    transform:none !important;
}

.sf-product:hover .sf-product-content {
    transform:none !important;
}

/* PRICE */
.sf-product .sf-product-price {
    display:flex;
    align-items:baseline;
    flex-wrap:wrap;
    gap:3px 6px;
    margin:0 0 5px !important;
    padding:0;
}

.sf-product .sf-product-price strong {
    color:var(--pc-text);
    font-size:16px;
    font-weight:800;
    line-height:1.2;
    letter-spacing:-.3px;
}

.sf-product .sf-product-price del {
    color:var(--pc-muted);
    font-size:10.5px;
    font-weight:400;
}

/* TITLE */
.sf-product .sf-product-title {
    display:-webkit-box;
    -webkit-box-orient:vertical;
    -webkit-line-clamp:2;
    line-clamp:2;
    overflow:hidden;
    min-height:34px;
    max-height:34px;
    margin:0 0 6px !important;
    color:var(--pc-text);
    font-size:12px;
    font-weight:650;
    line-height:17px;
    text-decoration:none;
    overflow-wrap:anywhere;
}

.sf-product .sf-product-title:hover {
    color:#5272a4;
}

/* STOCK AND SOLD */
.sf-product .sf-product-meta {
    display:flex;
    align-items:center;
    justify-content:space-between;
    flex-wrap:wrap;
    gap:3px;
    margin:0 0 8px !important;
    padding:0;
}

.sf-product .sf-product-stock {
    display:inline-flex;
    align-items:center;
    gap:4px;
    color:#16834a;
    font-size:10px;
    font-weight:600;
    white-space:nowrap;
}

.sf-product .sf-stock-dot {
    width:5px;
    height:5px;
    flex-shrink:0;
    border-radius:50%;
    background:#20ad65;
}

.sf-product .is-out-stock {
    color:#d94a53;
}
.sf-product .is-out-stock .sf-stock-dot {
    background:#d94a53;
}

.sf-product .sf-product-meta small {
    color:var(--pc-muted);
    font-size:9px;
    white-space:nowrap;
}

/* COMPACT PROGRESS */
.sf-product .sf-product-progress {
    height:2px;
    min-height:2px;
    margin:0 0 7px !important;
    border-radius:5px;
    background:var(--pc-border);
    overflow:hidden;
}
.sf-product .sf-product-progress i {
    display:block;
    height:100%;
    background:#9db4d4;
    border-radius:5px;
}

/* ALWAYS VISIBLE BUTTON */
.sf-product .sf-product-action {
    display:flex !important;
    position:relative !important;
    width:100%;
    margin-top:auto;
    opacity:1 !important;
    visibility:visible !important;
    transform:none !important;
}

.sf-product .sf-product-action .sf-add.sf-add-compact {
    position:relative !important;
    inset:auto !important;
    display:flex !important;
    align-items:center;
    justify-content:center;
    gap:5px;
    width:100%;
    height:32px !important;
    min-height:32px !important;
    max-height:none !important;
    padding:4px 7px !important;
    margin:0 !important;
    border:1px solid transparent;
    border-radius:6px;
    background:var(--pc-button);
    color:var(--pc-button-text);
    font-size:11px;
    font-weight:650;
    line-height:1.2;
    text-align:center;
    text-decoration:none;
    box-sizing:border-box;
    cursor:pointer;
    opacity:1 !important;
    visibility:visible !important;
    transform:none !important;
    translate:none !important;
    pointer-events:auto !important;
    transition:background .2s ease !important;
}

.sf-product .sf-product-action .sf-add svg {
    width:15px;
    height:15px;
    flex-shrink:0;
}

.sf-product .sf-product-action .sf-add:hover {
    background:var(--pc-button-hover);
    color:var(--pc-button-text);
    transform:none !important;
}

.sf-product .sf-product-action .sf-add.sf-disabled {
    background:var(--pc-border);
    color:var(--pc-muted);
    box-shadow:none;
}

/* DARK THEME */
:is(
    html[data-theme="dark"],
    html[data-bs-theme="dark"],
    html.dark,
    body[data-theme="dark"],
    body[data-bs-theme="dark"],
    body.dark
) .sf-product {
    --pc-bg:#1e242e;
    --pc-text:#f0f3f8;
    --pc-muted:#a2afbf;
    --pc-border:#353f4d;
    --pc-image:#252d38;
    --pc-button:#e6edf6;
    --pc-button-hover:#fff;
    --pc-button-text:#1d293b;
    --pc-heart-bg:#303946;
}

:is(
    html[data-theme="dark"],
    html[data-bs-theme="dark"],
    html.dark,
    body[data-theme="dark"],
    body[data-bs-theme="dark"],
    body.dark
) .sf-product:hover {
    border-color:#526071;
}

:is(
    html[data-theme="dark"],
    html[data-bs-theme="dark"],
    html.dark,
    body[data-theme="dark"],
    body[data-bs-theme="dark"],
    body.dark
) .sf-product .sf-heart:hover,
:is(
    html[data-theme="dark"],
    html[data-bs-theme="dark"],
    html.dark,
    body[data-theme="dark"],
    body[data-bs-theme="dark"],
    body.dark
) .sf-product .sf-heart.active {
    color:#ff707a;
}

/* MOBILE */
@media(max-width:575px) {
    .sf-product {
        border-radius:9px;
    }
    .sf-product .sf-product-image {
        aspect-ratio:1.12/1 !important;
    }
    .sf-product .sf-product-image img {
        padding:5px;
    }
    .sf-product .sf-product-content {
        padding:8px 7px !important;
    }
    .sf-product .sf-product-price strong {
        font-size:14px;
    }
    .sf-product .sf-product-price del {
        font-size:9px;
    }
    .sf-product .sf-product-title {
        font-size:11px;
        line-height:16px;
        min-height:32px;
        max-height:32px;
        margin-bottom:5px !important;
    }
    .sf-product .sf-product-meta {
        margin-bottom:6px !important;
    }
    .sf-product .sf-product-stock {
        font-size:9px;
    }
    .sf-product .sf-product-progress {
        margin-bottom:6px !important;
    }
    .sf-product .sf-product-action .sf-add.sf-add-compact {
        height:30px !important;
        min-height:30px !important;
        padding:4px !important;
        font-size:10px;
    }
    .sf-product .sf-product-action .sf-add svg {
        width:13px;
        height:13px;
    }
    .sf-product .sf-heart {
        width:26px;
        height:26px;
        font-size:19px;
    }
    .sf-product .sf-sale-badge {
        top:5px;
        left:5px;
        padding:3px 5px;
        font-size:9px;
    }
}

@media(prefers-reduced-motion:reduce) {
    .sf-product,
    .sf-product * {
        transition:none !important;
    }
}
</style>
<?php endif; ?>

<article class="sf-product" data-product-card="<?php echo e($product->id); ?>">

    <div class="sf-product-image-wrap">

        <a class="sf-product-image" href="<?php echo e($detailUrl); ?>">
            <?php if($photo): ?>
                <img src="<?php echo e($photo); ?>"
                     alt="<?php echo e($product->name); ?>"
                     loading="lazy"
                     onerror="this.style.display='none';this.nextElementSibling.hidden=false">
                <span class="sf-placeholder" hidden>▧</span>
            <?php else: ?>
                <span class="sf-placeholder">▧</span>
            <?php endif; ?>
        </a>

        <?php if($discount > 0): ?>
            <span class="sf-sale-badge">-<?php echo e($discount); ?>%</span>
        <?php endif; ?>

        <button type="button"
                class="sf-heart"
                title="<?php echo e(t('Wishlist','উইশলিস্ট')); ?>"
                aria-label="<?php echo e(t('Add to wishlist','উইশলিস্টে রাখুন')); ?>"
                data-wish-toggle
                data-id="<?php echo e($product->id); ?>"
                data-name="<?php echo e($product->name); ?>"
                data-price="<?php echo e($price); ?>"
                data-url="<?php echo e($detailUrl); ?>"
                data-image="<?php echo e($photo); ?>">♡</button>
    </div>

    <div class="sf-product-content">

        <div class="sf-product-price">
            <strong>৳ <?php echo e(number_format($price,2)); ?></strong>
            <?php if($discount > 0): ?>
                <del>৳ <?php echo e(number_format($previous,2)); ?></del>
            <?php endif; ?>
        </div>

        <a href="<?php echo e($detailUrl); ?>"
           class="sf-product-title"
           title="<?php echo e($product->name); ?>">
            <?php echo e($product->name); ?>

        </a>

        <div class="sf-product-meta">
            <span class="sf-product-stock <?php echo e($variant ? 'is-in-stock' : 'is-out-stock'); ?>">
                <span class="sf-stock-dot"></span>
                <?php echo e($variant ? t('In Stock','স্টকে আছে') : t('Out of Stock','স্টক নেই')); ?>

            </span>

            <?php if($sold > 0): ?>
                <small>
                    <?php echo e(number_format($sold)); ?>

                    <?php echo e(t('Sold','বিক্রি')); ?>

                </small>
            <?php endif; ?>
        </div>

        <div class="sf-product-progress"><i></i></div>

        <div class="sf-product-action">
            <?php if($variant): ?>
                <button type="button"
                        class="sf-add sf-add-compact"
                        data-add-to-cart
                        data-id="<?php echo e($variant->id); ?>"
                        data-product="<?php echo e($product->id); ?>"
                        data-name="<?php echo e($product->name); ?>"
                        data-stock="<?php echo e($product->track_stock ? $variant->stock : 99999); ?>"
                        data-price="<?php echo e($price); ?>"
                        data-before="<?php echo e($discount ? $previous : $price); ?>"
                        data-image="<?php echo e($photo); ?>">

                    <svg viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.8"
                         stroke-linecap="round"
                         stroke-linejoin="round"
                         aria-hidden="true">
                        <circle cx="9" cy="20" r="1"/>
                        <circle cx="18" cy="20" r="1"/>
                        <path d="M2 3h2l2.7 12h12.7l2.1-9H5"/>
                        <path d="M12 7v6M9 10h6"/>
                    </svg>

                    <span><?php echo e(t('Add to Cart','কার্টে যোগ')); ?></span>
                </button>
            <?php else: ?>
                <a class="sf-add sf-add-compact sf-disabled"
                   href="<?php echo e($detailUrl); ?>">
                    <?php echo e(t('Details','বিস্তারিত')); ?>

                </a>
            <?php endif; ?>
        </div>

    </div>
</article>
<?php /**PATH C:\laragon\www\saas\resources\views/public/business/public/ecommerce/partials/product-card.blade.php ENDPATH**/ ?>
<?php $__env->startSection('title',t('Cart','কার্ট')); ?>

<?php $__env->startSection('content'); ?>
<style>
.sf-cart-page {
    --cc-bg:#fff;
    --cc-text:#202a3a;
    --cc-muted:#84909f;
    --cc-border:#e8edf2;
    --cc-soft:#f7f9fc;
    --cc-primary:#24344e;
    --cc-primary-hover:#344968;
    --cc-primary-text:#fff;
    --cc-green:#16834a;
    --cc-red:#dc4b55;
    width:100%;
    min-width:0;
    padding:12px 0 35px;
    color:var(--cc-text);
}

.sf-cart-page * {box-sizing:border-box}

.sf-cart-page .sf-page-heading {
    display:flex;
    align-items:center;
    gap:12px;
    margin:0 0 17px;
    padding:0;
}

.sf-cart-page .sf-back {
    display:inline-flex;
    align-items:center;
    justify-content:center;
    width:34px;
    height:34px;
    min-width:34px;
    border:1px solid var(--cc-border);
    border-radius:9px;
    background:var(--cc-bg);
    color:var(--cc-text);
    text-decoration:none;
    transition:background .2s;
}

.sf-cart-page .sf-back:hover {
    background:var(--cc-soft);
}

.sf-cart-page .sf-back svg {
    width:19px;
    height:19px;
    fill:none;
    stroke:currentColor;
    stroke-width:2;
    stroke-linecap:round;
    stroke-linejoin:round;
}

.sf-cart-page .sf-cart-heading-text h1 {
    margin:0;
    font-size:22px;
    font-weight:750;
    line-height:1.25;
    color:var(--cc-text);
}

.sf-cart-page .sf-cart-heading-text p {
    margin:3px 0 0;
    color:var(--cc-muted);
    font-size:12px;
}

/* LAYOUT */
.sf-cart-page .sf-cart-layout {
    display:grid;
    grid-template-columns:minmax(0,1fr) 325px;
    align-items:start;
    gap:18px;
    width:100%;
    min-width:0;
}

.sf-cart-page .sf-cart-column {
    min-width:0;
}

/* SELECT ALL */
.sf-cart-page .sf-cart-select-all {
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    min-height:52px;
    margin-bottom:11px;
    padding:10px 14px;
    background:var(--cc-bg);
    border:1px solid var(--cc-border);
    border-radius:11px;
}

.sf-cart-page .sf-cart-select-all label {
    display:inline-flex;
    align-items:center;
    gap:9px;
    color:var(--cc-text);
    font-size:13px;
    font-weight:650;
    cursor:pointer;
}

.sf-cart-page input[type="checkbox"] {
    width:17px;
    height:17px;
    flex-shrink:0;
    margin:0;
    accent-color:var(--cc-primary);
    cursor:pointer;
}

.sf-cart-page .sf-cart-remove-btn {
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:7px;
    min-height:32px;
    padding:5px 9px;
    background:transparent;
    border:1px solid transparent;
    border-radius:7px;
    color:var(--cc-muted);
    font-size:12px;
    font-weight:600;
    cursor:pointer;
}

.sf-cart-page .sf-cart-remove-btn:hover {
    background:var(--cc-soft);
    color:var(--cc-red);
}

.sf-cart-page .sf-cart-remove-btn svg {
    width:16px;
    height:16px;
    fill:none;
    stroke:currentColor;
    stroke-width:1.8;
    stroke-linecap:round;
    stroke-linejoin:round;
}

/* DYNAMIC CART LIST */
.sf-cart-page .sf-cart-items {
    display:flex;
    flex-direction:column;
    gap:10px;
    min-width:0;
}

.sf-cart-page .sf-cart-items > * {
    min-width:0;
    margin:0;
    padding:13px;
    background:var(--cc-bg);
    border:1px solid var(--cc-border);
    border-radius:11px;
    transition:border-color .2s,box-shadow .2s;
}

.sf-cart-page .sf-cart-items > *:hover {
    border-color:var(--cc-muted);
    box-shadow:0 3px 13px rgba(25,40,65,.035);
}

.sf-cart-page .sf-cart-items img {
    width:88px;
    max-width:100%;
    height:88px;
    object-fit:contain;
    border-radius:8px;
    background:var(--cc-soft);
}

.sf-cart-page .sf-cart-items a {
    color:var(--cc-text);
    text-decoration:none;
}

.sf-cart-page .sf-cart-items a:hover {
    color:var(--cc-primary-hover);
}

.sf-cart-page .sf-cart-items input[type="number"] {
    width:52px;
    min-width:0;
    min-height:30px;
    padding:3px;
    border:1px solid var(--cc-border);
    border-radius:6px;
    background:var(--cc-bg);
    color:var(--cc-text);
    text-align:center;
    font-size:12px;
}

.sf-cart-page .sf-cart-items button {
    cursor:pointer;
}

.sf-cart-page .sf-cart-variant {
    display:inline-flex;
    align-items:center;
    gap:5px;
    max-width:100%;
    width:fit-content;
    margin-top:5px;
    padding:4px 8px;
    background:var(--cc-soft);
    border:1px solid var(--cc-border);
    border-radius:6px;
    color:var(--cc-muted);
    font-size:11px;
    font-weight:500;
    line-height:1.35;
    overflow-wrap:anywhere;
}

.sf-cart-page .sf-cart-variant strong {
    color:var(--cc-text);
    font-weight:650;
}

/* EMPTY CART */
.sf-cart-page .sf-empty {
    padding:55px 18px;
    border:1px dashed var(--cc-border);
    border-radius:12px;
    background:var(--cc-bg);
    color:var(--cc-muted);
    text-align:center;
    font-size:13px;
    line-height:1.8;
}

.sf-cart-page .sf-empty[hidden] {
    display:none !important;
}

.sf-cart-page .sf-empty a {
    display:inline-block;
    margin-left:5px;
    color:var(--cc-primary-hover);
    font-weight:700;
    text-decoration:underline;
}

/* ORDER SUMMARY */
.sf-cart-page .sf-cart-summary {
    position:sticky;
    top:88px;
    min-width:0;
    padding:19px !important;
    border:1px solid var(--cc-border);
    border-radius:13px;
    background:var(--cc-bg);
    box-shadow:0 4px 20px rgba(20,30,55,.035);
    color:var(--cc-text);
}

.sf-cart-page .sf-cart-summary-head {
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
    margin-bottom:17px;
    padding-bottom:14px;
    border-bottom:1px solid var(--cc-border);
}

.sf-cart-page .sf-cart-summary-head h2 {
    margin:0;
    font-size:16px;
    font-weight:750;
    color:var(--cc-text);
}

.sf-cart-page .sf-summary-icon {
    width:33px;
    height:33px;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius:8px;
    background:var(--cc-soft);
    color:var(--cc-text);
}

.sf-cart-page .sf-summary-icon svg {
    width:18px;
    height:18px;
    fill:none;
    stroke:currentColor;
    stroke-width:1.8;
    stroke-linecap:round;
    stroke-linejoin:round;
}

/* SUMMARY LINES */
.sf-cart-page .sf-summary {
    display:flex;
    flex-direction:column;
    gap:14px;
}

.sf-cart-page .sf-summary > div {
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
    font-size:12.5px;
    color:var(--cc-muted);
}

.sf-cart-page .sf-summary > div b {
    color:var(--cc-text);
    font-size:13px;
    font-weight:650;
    white-space:nowrap;
}

.sf-cart-page .sf-summary #sf-cart-discount {
    color:var(--cc-green);
}

.sf-cart-page .sf-summary .sf-summary-total {
    margin-top:3px;
    padding-top:15px;
    border-top:1px solid var(--cc-border);
    color:var(--cc-text);
}

.sf-cart-page .sf-summary-total span {
    font-size:14px;
    font-weight:750;
}

.sf-cart-page .sf-summary-total b#sf-est-total {
    font-size:20px;
    font-weight:800;
    letter-spacing:-.4px;
    color:var(--cc-text);
}

/* SHIPPING */
.sf-cart-page .sf-shipping-note {
    display:flex;
    align-items:flex-start;
    gap:9px;
    margin:17px 0 15px;
    padding:11px;
    border-radius:8px;
    border:1px solid var(--cc-border);
    background:var(--cc-soft);
    color:var(--cc-muted);
    font-size:11.5px;
    line-height:1.6;
}

.sf-cart-page .sf-shipping-note svg {
    width:17px;
    height:17px;
    min-width:17px;
    margin-top:1px;
    fill:none;
    stroke:currentColor;
    stroke-width:1.7;
    stroke-linecap:round;
    stroke-linejoin:round;
}

/* CHECKOUT BUTTON */
.sf-cart-page .sf-checkout-btn {
    display:flex !important;
    align-items:center;
    justify-content:center;
    gap:10px;
    width:100%;
    min-height:43px;
    padding:10px 14px;
    border:0;
    border-radius:8px;
    background:var(--cc-primary);
    color:var(--cc-primary-text);
    font-size:13px;
    font-weight:700;
    text-align:center;
    text-decoration:none;
    transition:background .2s;
}

.sf-cart-page .sf-checkout-btn:hover {
    background:var(--cc-primary-hover);
    color:var(--cc-primary-text);
}

.sf-cart-page .sf-checkout-btn svg {
    width:17px;
    height:17px;
    fill:none;
    stroke:currentColor;
    stroke-width:1.9;
    stroke-linecap:round;
    stroke-linejoin:round;
}

.sf-cart-page .sf-checkout-btn[aria-disabled="true"] {
    opacity:.5;
    pointer-events:none;
}

.sf-cart-page .sf-continue-shopping {
    display:flex;
    align-items:center;
    justify-content:center;
    gap:6px;
    margin-top:13px;
    color:var(--cc-muted);
    font-size:12px;
    font-weight:550;
    text-decoration:none;
}

.sf-cart-page .sf-continue-shopping:hover {
    color:var(--cc-text);
}

/* DARK MODE */
:is(
    html[data-theme="dark"],
    html[data-bs-theme="dark"],
    html.dark,
    body[data-theme="dark"],
    body[data-bs-theme="dark"],
    body.dark
) .sf-cart-page {
    --cc-bg:#1e242e;
    --cc-text:#f1f4f9;
    --cc-muted:#a7b3c1;
    --cc-border:#35404e;
    --cc-soft:#272f3b;
    --cc-primary:#e6edf6;
    --cc-primary-hover:#fff;
    --cc-primary-text:#1e293b;
    --cc-green:#56d492;
    --cc-red:#ff7882;
}

/* RESPONSIVE */
@media(max-width:991px) {
    .sf-cart-page .sf-cart-layout {
        grid-template-columns:minmax(0,1fr) 285px;
        gap:12px;
    }
    .sf-cart-page .sf-cart-summary {
        padding:15px !important;
    }
}

@media(max-width:767px) {
    .sf-cart-page {
        padding:7px 0 25px;
    }
    .sf-cart-page .sf-cart-layout {
        grid-template-columns:minmax(0,1fr);
        gap:14px;
    }
    .sf-cart-page .sf-cart-summary {
        position:static;
        padding:15px !important;
    }
    .sf-cart-page .sf-cart-heading-text h1 {
        font-size:19px;
    }
    .sf-cart-page .sf-cart-items > * {
        padding:10px;
    }
    .sf-cart-page .sf-cart-items img {
        width:69px;
        height:69px;
    }
    .sf-cart-page .sf-cart-select-all {
        padding:9px 11px;
    }
}

@media(prefers-reduced-motion:reduce) {
    .sf-cart-page,
    .sf-cart-page * {
        transition:none !important;
    }
}
</style>

<div class="sf-cart-page">

    <div class="sf-page-heading">
        <a class="sf-back"
           href="<?php echo e(route('business.customer.shop')); ?>"
           aria-label="<?php echo e(t('Back to shop','শপে ফিরে যান')); ?>">
            <svg viewBox="0 0 24 24">
                <path d="m15 18-6-6 6-6"/>
            </svg>
        </a>

        <div class="sf-cart-heading-text">
            <h1><?php echo e(t('Shopping Cart','শপিং কার্ট')); ?></h1>
            <p><?php echo e(t('Review your selected products before checkout.','চেকআউটের আগে নির্বাচিত পণ্যগুলো দেখে নিন।')); ?></p>
        </div>
    </div>

    <div class="sf-cart-layout">

        <section class="sf-cart-column"
                 aria-label="<?php echo e(t('Cart Items','কার্টের পণ্য')); ?>">

            <div class="sf-cart-select-all">
                <label for="sf-select-all">
                    <input type="checkbox"
                           id="sf-select-all"
                           checked>
                    <span><?php echo e(t('Select all','সবগুলো নির্বাচন করুন')); ?></span>
                </label>

                <button type="button"
                        class="sf-cart-remove-btn"
                        data-cart-remove-selected
                        title="<?php echo e(t('Remove selected','নির্বাচিত পণ্য মুছুন')); ?>">
                    <svg viewBox="0 0 24 24">
                        <path d="M4 7h16M10 3h4M6 7l1 14h10l1-14M10 11v6M14 11v6"/>
                    </svg>
                    <span><?php echo e(t('Remove','মুছুন')); ?></span>
                </button>
            </div>

            <div id="sf-cart-items"
                 class="sf-cart-items"
                 aria-live="polite"></div>

            <p id="sf-cart-empty"
               class="sf-empty"
               hidden>
                <?php echo e(t('Your cart is empty.','আপনার কার্ট খালি।')); ?>

                <a href="<?php echo e(route('business.customer.shop')); ?>">
                    <?php echo e(t('Start Shopping','কেনাকাটা করুন')); ?>

                </a>
            </p>
        </section>

        <aside class="sf-cart-summary sf-panel">

            <div class="sf-cart-summary-head">
                <h2><?php echo e(t('Order Summary','অর্ডারের হিসাব')); ?></h2>
                <span class="sf-summary-icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M8 3h8l3 4v14l-4-2-3 2-3-2-4 2V7z"/>
                        <path d="M9 10h6M9 14h6"/>
                    </svg>
                </span>
            </div>

            <div class="sf-summary">
                <div>
                    <span><?php echo e(t('Product Price','পণ্যের মূল্য')); ?></span>
                    <b id="sf-subtotal">৳ 0.00</b>
                </div>

                <div>
                    <span><?php echo e(t('Discount','ছাড়')); ?></span>
                    <b id="sf-cart-discount">৳ 0.00</b>
                </div>

                <div class="sf-summary-total">
                    <span><?php echo e(t('Grand Total','সর্বমোট')); ?></span>
                    <b id="sf-est-total">৳ 0.00</b>
                </div>
            </div>

            <div class="sf-shipping-note">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M3 6h11v11H3zM14 10h4l3 3v4h-7z"/>
                    <circle cx="7.5" cy="18" r="2"/>
                    <circle cx="17.5" cy="18" r="2"/>
                </svg>
                <span>
                    <?php echo e(t(
                        'Shipping & courier charges are calculated at checkout.',
                        'চেকআউটে ডেলিভারি ও কুরিয়ার চার্জ হিসাব করা হবে।'
                    )); ?>

                </span>
            </div>

            <a href="<?php echo e(route('business.customer.checkout')); ?>"
               class="sf-btn sf-checkout-btn"
               id="sf-go-checkout">

                <span><?php echo e(t('Proceed to Checkout','চেকআউটে এগিয়ে যান')); ?></span>

                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M5 12h14m-6-6 6 6-6 6"/>
                </svg>
            </a>

            <a href="<?php echo e(route('business.customer.shop')); ?>"
               class="sf-continue-shopping">
                <span>←</span>
                <?php echo e(t('Continue Shopping','কেনাকাটা চালিয়ে যান')); ?>

            </a>

        </aside>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('public.business.public.ecommerce.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\saas-backend\resources\views/public/business/public/ecommerce/checkout/cart.blade.php ENDPATH**/ ?>
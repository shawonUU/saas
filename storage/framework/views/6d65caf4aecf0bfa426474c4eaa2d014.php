<!doctype html>
<html lang="<?php echo e(session('portal_lang','en')); ?>" data-theme="light">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
<?php echo $__env->make('portals.partials.ui-theme-bootstrap', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php
 $b=$business;
 $brandService=app(\App\Services\Public\Business\Admin\Settings\Business\GeneralSettings\BrandingService::class);
 $brandSettings=$brandService->find($b);
 $storeLogo=$brandService->imageUrl($b->logo);
 $storeFavicon=$brandService->imageUrl($brandSettings?->favicon);
?>
<title><?php echo $__env->yieldContent('title',$b->name); ?> · <?php echo e($b->name); ?></title>
<meta name="description" content="<?php echo e($brandSettings?->tagline??$b->name); ?>">
<?php if($storeFavicon): ?><link rel="icon" href="<?php echo e($storeFavicon); ?>"><?php endif; ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/nexabiz-storefront.css')); ?>?v=20261126">
<link rel="stylesheet" href="<?php echo e(asset('assets/saas-searchable-select.css')); ?>?v=20261124">
<?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body data-business-id="<?php echo e($b->id); ?>" data-cart-url="<?php echo e(route('business.customer.cart')); ?>" data-checkout-url="<?php echo e(route('business.customer.checkout')); ?>" data-wishlist-url="<?php echo e(route('business.customer.wishlist')); ?>">
<div class="sf-app">
 <?php echo $__env->make('public.business.public.ecommerce.partials.desktop-categories', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
 <div class="sf-app-main">
  <?php echo $__env->make('public.business.public.ecommerce.partials.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  <main class="sf-shell sf-body"><?php echo $__env->yieldContent('content'); ?></main>
  <?php echo $__env->make('public.business.public.ecommerce.partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
 </div>
</div>
<?php echo $__env->make('public.business.public.ecommerce.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('public.business.public.ecommerce.partials.mobile-bar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<script src="<?php echo e(asset('assets/nexabiz-storefront.js')); ?>?v=20261126" defer></script>
<script src="<?php echo e(asset('assets/saas-searchable-select.js')); ?>?v=20261124" defer></script>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body></html>
<?php /**PATH C:\laragon\www\saas\resources\views/public/business/public/ecommerce/layout.blade.php ENDPATH**/ ?>
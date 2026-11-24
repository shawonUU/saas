<?php
 $promoService=app(\App\Services\Public\Business\Admin\Settings\Business\GeneralSettings\BrandingService::class);
 $promoSettings=$promoService->find($business);
 $mainBanner=$promoService->imageUrl($promoSettings?->banner);
 $sideBanner=$promoService->imageUrl($promoSettings?->secondary_banner);
?>
<div class="sf-promos">
<a class="sf-promo-main <?php echo e($mainBanner?'sf-promo-with-image':''); ?>" href="<?php echo e(route('business.customer.shop')); ?>" <?php if($mainBanner): ?> style="background-image:url('<?php echo e($mainBanner); ?>')" <?php endif; ?>>
<?php if(!$mainBanner): ?><div class="sf-promo-copy"><small><?php echo e(t('MEGA SHOPPING FEST','মেগা শপিং ফেস্ট')); ?></small><h1><?php echo e(t('Big deals, better shopping','দারুণ দামে সেরা কেনাকাটা')); ?></h1><p><?php echo e($promoSettings?->tagline?:t('Find something special today','আজই পছন্দের পণ্য খুঁজুন')); ?></p><span><?php echo e(t('Shop all products','সব পণ্য দেখুন')); ?> ›</span></div><div class="sf-promo-illustration" aria-hidden="true"><i>✦</i><b>SALE</b><i>★</i></div><?php endif; ?>
</a>
<a class="sf-promo-side <?php echo e($sideBanner?'sf-promo-with-image':''); ?>" href="<?php echo e(route('business.customer.shop',['sort'=>'latest'])); ?>" <?php if($sideBanner): ?> style="background-image:url('<?php echo e($sideBanner); ?>')" <?php endif; ?>>
<?php if(!$sideBanner): ?><small><?php echo e(t('NEW ARRIVALS','নতুন কালেকশন')); ?></small><h2><?php echo e(t('Explore the latest','নতুন পণ্য দেখুন')); ?></h2><span><?php echo e(t('Shop now','কেনাকাটা করুন')); ?> ›</span><div aria-hidden="true" class="sf-promo-side-art">✳</div><?php endif; ?>
</a>
</div>
<?php /**PATH C:\laragon\www\saas\resources\views/public/business/public/ecommerce/partials/promo.blade.php ENDPATH**/ ?>
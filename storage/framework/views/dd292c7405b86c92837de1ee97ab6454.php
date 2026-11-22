<?php
$variant=$product->variants->first(fn($v)=>!$product->track_stock||(float)$v->stock>0);
$price=(float)($variant?->price??$product->price);
$previous=(float)($product->compare_at_price??0);
$discount=($previous>$price&&$price>0)?round((($previous-$price)/$previous)*100):0;
$photo=app(\App\Services\Public\Business\Admin\Settings\Business\GeneralSettings\BrandingService::class)->imageUrl($variant?->image_path?:$product->image_url);
$sold=(int)($soldCounts[$product->id]??0);
$detailUrl=route('business.customer.product',['product'=>$product->id]);
?>
<article class="sf-product" data-product-card="<?php echo e($product->id); ?>">
 <div class="sf-product-image-wrap"><a class="sf-product-image" href="<?php echo e($detailUrl); ?>"><?php if($photo): ?><img src="<?php echo e($photo); ?>" alt="<?php echo e($product->name); ?>" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.hidden=false"><span class="sf-placeholder" hidden>▧</span><?php else: ?><span class="sf-placeholder">▧</span><?php endif; ?></a>
 <?php if($discount>0): ?><span class="sf-sale-badge">-<?php echo e($discount); ?>%</span><?php endif; ?>
 <button class="sf-heart" type="button" title="<?php echo e(t('Wishlist','উইশলিস্ট')); ?>" aria-label="<?php echo e(t('Add to wishlist','উইশলিস্টে রাখুন')); ?>" data-wish-toggle data-id="<?php echo e($product->id); ?>" data-name="<?php echo e($product->name); ?>" data-price="<?php echo e($price); ?>" data-url="<?php echo e($detailUrl); ?>" data-image="<?php echo e($photo); ?>">♡</button></div>
 <div class="sf-product-content"><div class="sf-product-price"><strong>৳ <?php echo e(number_format($price,2)); ?></strong><?php if($discount): ?><del>৳<?php echo e(number_format($previous,2)); ?></del><?php endif; ?></div>
 <a href="<?php echo e($detailUrl); ?>" class="sf-product-title" title="<?php echo e($product->name); ?>"><?php echo e($product->name); ?></a><div class="sf-product-meta"><span class="sf-product-stock"><?php echo e($variant?t('In Stock','স্টকে আছে'):t('Out of Stock','স্টক নেই')); ?></span><?php if($sold>0): ?><small><?php echo e(number_format($sold)); ?> <?php echo e(t('Sold','বিক্রি')); ?></small><?php endif; ?></div>
 <div class="sf-product-progress"><i></i></div>
 <?php if($variant): ?><button type="button" class="sf-add sf-add-compact" data-add-to-cart data-id="<?php echo e($variant->id); ?>" data-product="<?php echo e($product->id); ?>" data-name="<?php echo e($product->name); ?>" data-stock="<?php echo e($product->track_stock?$variant->stock:99999); ?>" data-price="<?php echo e($price); ?>" data-before="<?php echo e($discount?$previous:$price); ?>" data-image="<?php echo e($photo); ?>">+ <?php echo e(t('Add to Cart','কার্টে যোগ')); ?></button><?php else: ?><a class="sf-add sf-add-compact sf-disabled" href="<?php echo e($detailUrl); ?>"><?php echo e(t('Details','বিস্তারিত')); ?></a><?php endif; ?>
 </div>
</article>
<?php /**PATH C:\laragon\www\saas-backend\resources\views/public/business/public/ecommerce/partials/product-card.blade.php ENDPATH**/ ?>
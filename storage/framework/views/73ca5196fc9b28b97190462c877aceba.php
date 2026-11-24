<?php $__env->startSection('title', $business->name); ?>

<?php
    $homeImageService = app(
        \App\Services\Public\Business\Admin\Settings\Business\GeneralSettings\BrandingService::class
    );
?>

<?php $__env->startSection('content'); ?>

<style>
/* HOME PRODUCT GRID */
.sf-product-grid.sf-home-product-grid {
    display: grid !important;
    grid-template-columns: repeat(6,minmax(0,1fr)) !important;
    gap: 11px !important;
    align-items: stretch;
    width: 100%;
    min-width: 0;
}

.sf-home-product-grid > .sf-product {
    width: 100% !important;
    max-width: none !important;
    min-width: 0;
    height: 100%;
    margin: 0 !important;
}

.sf-home-product-grid > .sf-empty {
    grid-column: 1 / -1;
}

/* RESPONSIVE */
@media(max-width:1439px) {
    .sf-product-grid.sf-home-product-grid {
        grid-template-columns:repeat(5,minmax(0,1fr)) !important;
    }
}

@media(max-width:1199px) {
    .sf-product-grid.sf-home-product-grid {
        grid-template-columns:repeat(4,minmax(0,1fr)) !important;
    }
}

@media(max-width:991px) {
    .sf-product-grid.sf-home-product-grid {
        grid-template-columns:repeat(3,minmax(0,1fr)) !important;
    }
}

@media(max-width:767px) {
    .sf-product-grid.sf-home-product-grid {
        grid-template-columns:repeat(2,minmax(0,1fr)) !important;
        gap:9px !important;
    }
}
</style>


<?php echo $__env->make('public.business.public.ecommerce.partials.promo', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


<?php if($categories->isNotEmpty()): ?>
<section class="sf-section sf-top-categories">

    <div class="sf-section-top">
        <h2><?php echo e(t('Top Category','সেরা ক্যাটাগরি')); ?></h2>

        <a href="<?php echo e(route('business.customer.shop')); ?>">
            <?php echo e(t('View all','সব দেখুন')); ?> ›
        </a>
    </div>

    <div class="sf-category-circles">

        <?php $__currentLoopData = $categories->take(15); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

            <?php
                $categoryProduct = $products->first(
                    fn($p) =>
                        (int)$p->category_id === (int)$category->id &&
                        (
                            $p->image_url ||
                            $p->variants->first()?->image_path
                        )
                );

                $categoryPhoto = $categoryProduct
                    ? $homeImageService->imageUrl(
                        $categoryProduct->image_url
                        ?: $categoryProduct->variants->first()?->image_path
                    )
                    : null;
            ?>

            <a class="sf-category-circle"
               href="<?php echo e(route('business.customer.shop',[
                   'category'=>$category->id
               ])); ?>">

                <span class="sf-circle-image">

                    <?php if($categoryPhoto): ?>
                        <img
                            src="<?php echo e($categoryPhoto); ?>"
                            alt="<?php echo e($category->name); ?>"
                            loading="lazy"
                            onerror="this.style.display='none'">
                    <?php else: ?>
                        <span class="sf-circle-fallback">
                            <?php echo e(mb_substr($category->name,0,1)); ?>

                        </span>
                    <?php endif; ?>

                </span>

                <small><?php echo e($category->name); ?></small>

            </a>

        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    </div>
</section>
<?php endif; ?>


<?php $__empty_1 = true; $__currentLoopData = ($homeSections ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

    <section class="sf-section sf-department">

        <div class="sf-section-top">

            <h2>
                <span class="sf-heading-glyph">✿</span>
                <?php echo e($section['category']->name); ?>

            </h2>

            <a href="<?php echo e(route('business.customer.shop',[
                'category'=>$section['category']->id
            ])); ?>">
                <?php echo e(t('View More','আরও দেখুন')); ?>

                <span>›</span>
            </a>

        </div>

        <div class="sf-product-grid sf-home-grid sf-products-grid sf-home-product-grid">

            <?php $__currentLoopData = $section['products']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                <?php echo $__env->make('public.business.public.ecommerce.partials.product-card', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        </div>

    </section>

<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

    
    <section class="sf-section">

        <div class="sf-section-top">

            <h2>
                <?php echo e(t('Latest Products','নতুন পণ্য')); ?>

            </h2>

            <a href="<?php echo e(route('business.customer.shop')); ?>">
                <?php echo e(t('View More','আরও দেখুন')); ?> ›
            </a>

        </div>

        <div class="sf-product-grid sf-home-grid sf-products-grid sf-home-product-grid">

            <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                <?php echo $__env->make('public.business.public.ecommerce.partials.product-card', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                <div class="sf-empty">
                    <?php echo e(t(
                        'No published products yet. Add active products and variants from Business Admin.',
                        'এখনও পণ্য প্রকাশ হয়নি। অ্যাডমিনে পণ্য যোগ করুন।'
                    )); ?>

                </div>

            <?php endif; ?>

        </div>

    </section>

<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('public.business.public.ecommerce.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\saas\resources\views/public/business/public/home.blade.php ENDPATH**/ ?>
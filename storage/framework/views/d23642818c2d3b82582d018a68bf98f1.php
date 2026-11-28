<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('public.business.admin.configurations._workspace_styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('public.business.admin.settings.partials.form-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopPush(); ?>
<?php $__env->startSection('content'); ?>
<?php ($brandAssets = app(\App\Services\Public\Business\Admin\Settings\Business\GeneralSettings\BrandingService::class)); ?>
<div class="workspace-layout gs-page">
    <?php echo $__env->make('public.business.admin.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div>
    <main class="workspace-content">
        <div class="gs-header"><div>
            <h1><?php echo e(t('E-commerce General Settings','ই-কমার্স সাধারণ সেটিংস')); ?></h1>
            <p><?php echo e(t('Set the storefront appearance, tagline, logo and homepage banners.','স্টোরফ্রন্টের রঙ, স্লোগান, লোগো এবং হোমপেজ ব্যানার পরিবর্তন করুন।')); ?></p>
        </div></div>
        <?php if($errors->any()): ?><div class="gs-error" role="alert"><?php echo e($errors->first()); ?></div><?php endif; ?>
        <?php if(session('success')): ?><div class="gs-success" role="status"><?php echo e(session('success')); ?></div><?php endif; ?>
        <form method="POST" enctype="multipart/form-data" action="<?php echo e(route('business.admin.settings.ecommerce.general-settings.update')); ?>">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <div class="gs-form">
                <section class="gs-panel">
                    <h2><?php echo e(t('Storefront Appearance','স্টোরফ্রন্টের ডিজাইন')); ?></h2>
                    <label class="gs-field"><?php echo e(t('Theme Color','থিমের রঙ')); ?>

                        <input type="color" name="primary_color" value="<?php echo e(old('primary_color',$branding?->primary_color ?? '#5145e5')); ?>" required>
                    </label>
                    <label class="gs-field"><?php echo e(t('Storefront Tagline','স্টোরের স্লোগান')); ?>

                        <input name="tagline" maxlength="255" value="<?php echo e(old('tagline',$branding?->tagline)); ?>" placeholder="<?php echo e(t('A short message for your storefront','আপনার স্টোরের সংক্ষিপ্ত স্লোগান')); ?>">
                    </label>
                    <p class="gs-note"><?php echo e(t('The existing store, logo and banner data will be retained unless you upload new files.','নতুন ছবি আপলোড না করলে বর্তমান লোগো ও ব্যানার অপরিবর্তিত থাকবে।')); ?></p>
                </section>
                <section class="gs-panel">
                    <h2><?php echo e(t('Brand Assets','ব্র্যান্ডের ছবি')); ?></h2>
                    <label class="gs-field"><?php echo e(t('Company Logo','কোম্পানির লোগো')); ?>

                        <?php if($business->logo): ?><img class="gs-preview" alt="<?php echo e(t('Existing logo','বর্তমান লোগো')); ?>" src="<?php echo e($brandAssets->imageUrl($business->logo)); ?>"><?php endif; ?>
                        <input type="file" name="logo" accept="image/jpeg,image/png,image/webp">
                        <small class="gs-note">PNG, JPG, WebP · 3 MB</small>
                    </label>
                    <label class="gs-field"><?php echo e(t('Favicon','ফেভিকন')); ?>

                        <?php if($branding?->favicon): ?><img class="gs-preview favicon" alt="<?php echo e(t('Existing favicon','বর্তমান ফেভিকন')); ?>" src="<?php echo e($brandAssets->imageUrl($branding->favicon)); ?>"><?php endif; ?>
                        <input type="file" name="favicon" accept="image/jpeg,image/png,image/webp,image/x-icon">
                        <small class="gs-note">1 MB</small>
                    </label>
                    <label class="gs-field"><?php echo e(t('Homepage Banner','হোমপেজ ব্যানার')); ?>

                        <?php if($branding?->banner): ?><img class="gs-preview banner" alt="<?php echo e(t('Existing homepage banner','বর্তমান হোমপেজ ব্যানার')); ?>" src="<?php echo e($brandAssets->imageUrl($branding->banner)); ?>"><?php endif; ?>
                        <input type="file" name="banner" accept="image/jpeg,image/png,image/webp">
                        <small class="gs-note">5 MB · <?php echo e(t('Recommended 1200 × 330 px','প্রস্তাবিত ১২০০ × ৩৩০ পিক্সেল')); ?></small>
                    </label>
                    <label class="gs-field"><?php echo e(t('Secondary Homepage Banner','হোমপেজের দ্বিতীয় ব্যানার')); ?>

                        <?php if($branding?->secondary_banner): ?><img class="gs-preview banner" alt="<?php echo e(t('Existing secondary banner','বর্তমান দ্বিতীয় ব্যানার')); ?>" src="<?php echo e($brandAssets->imageUrl($branding->secondary_banner)); ?>"><?php endif; ?>
                        <input type="file" name="secondary_banner" accept="image/jpeg,image/png,image/webp">
                        <small class="gs-note">5 MB · <?php echo e(t('Recommended 600 × 330 px','প্রস্তাবিত ৬০০ × ৩৩০ পিক্সেল')); ?></small>
                    </label>
                </section>
            </div>
            <div class="gs-actions"><button class="gs-save" type="submit"><?php echo e(t('Save E-commerce Settings','ই-কমার্স সেটিংস সংরক্ষণ')); ?></button></div>
        </form>
    </main>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('public.business.admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\saas-backend\resources\views/public/business/admin/settings/ecommerce/general-settings/index.blade.php ENDPATH**/ ?>
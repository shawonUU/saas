<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('public.business.admin.configurations._workspace_styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('public.business.admin.settings.partials.form-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopPush(); ?>
<?php $__env->startSection('content'); ?>
<div class="workspace-layout gs-page">
    <?php echo $__env->make('public.business.admin.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div>
    <main class="workspace-content">
        <div class="gs-header"><div>
            <h1><?php echo e(t('Business General Settings','বিজনেস সাধারণ সেটিংস')); ?></h1>
            <p><?php echo e(t('Manage company information. Storefront appearance is now under E-commerce Settings.','কোম্পানির তথ্য পরিচালনা করুন। স্টোরফ্রন্টের ডিজাইন এখন ই-কমার্স সেটিংসে।')); ?></p>
        </div></div>
        <?php if($errors->any()): ?><div class="gs-error" role="alert"><?php echo e($errors->first()); ?></div><?php endif; ?>
        <?php if(session('success')): ?><div class="gs-success" role="status"><?php echo e(session('success')); ?></div><?php endif; ?>
        <form method="POST" action="<?php echo e(route('business.admin.settings.business.general-settings.update')); ?>">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <div class="gs-form gs-form--single">
                <section class="gs-panel">
                    <h2><?php echo e(t('Company Information','কোম্পানির তথ্য')); ?></h2>
                    <div class="gs-grid">
                        <label class="gs-field"><?php echo e(t('Business Name','বিজনেসের নাম')); ?><input name="name" required maxlength="255" value="<?php echo e(old('name',$business->name)); ?>"></label>
                        <label class="gs-field"><?php echo e(t('Phone','ফোন')); ?><input name="phone" maxlength="50" value="<?php echo e(old('phone',$business->phone)); ?>"></label>
                        <label class="gs-field"><?php echo e(t('Email','ইমেইল')); ?><input type="email" name="email" maxlength="255" value="<?php echo e(old('email',$business->email)); ?>"></label>
                        <label class="gs-field"><?php echo e(t('Address','ঠিকানা')); ?><input name="address" maxlength="255" value="<?php echo e(old('address',$business->address)); ?>"></label>
                    </div>
                    <label class="gs-field"><?php echo e(t('About your business','বিজনেস সম্পর্কে')); ?><textarea name="about" rows="5" maxlength="3000"><?php echo e(old('about',$branding?->about)); ?></textarea></label>
                </section>
            </div>
            <div class="gs-actions"><button class="gs-save" type="submit"><?php echo e(t('Save Settings','সেটিংস সংরক্ষণ')); ?></button></div>
        </form>
    </main>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('public.business.admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\saas-backend\resources\views/public/business/admin/settings/business/general-settings/index.blade.php ENDPATH**/ ?>
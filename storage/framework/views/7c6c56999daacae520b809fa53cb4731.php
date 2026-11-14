<?php $__env->startSection('content'); ?>
<div class="workspace-layout">
    <?php echo $__env->make('portals.partials.admin-sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <main class="workspace-content">
        <div class="ws-intro"><div><small><?php echo e($child->menu->module->name); ?> / <?php echo e($child->menu->name); ?></small><h1><?php echo e(navName($child)); ?></h1><p><?php echo e(t('Business workspace','বিজনেস ওয়ার্কস্পেস')); ?></p></div></div>
        <div class="ws-card ws-placeholder"><div class="ws-placeholder-icon">▤</div><h2><?php echo e(navName($child)); ?></h2><p><?php echo e(t('This menu is assigned and access-checked. Its business functionality has not been built in this project yet.','এই মেনুর অ্যাক্সেস যাচাই করা হয়েছে। এই প্রজেক্টে পেজটির ব্যবসায়িক ফাংশন এখনো তৈরি হয়নি।')); ?></p><a class="btn btn-outline" href="<?php echo e(route('store.admin.dashboard',['business'=>$business->code])); ?>"><?php echo e(t('Back to dashboard','ড্যাশবোর্ডে ফিরুন')); ?></a></div>
    </main>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.portal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\saas\resources\views/public/business/admin/feature.blade.php ENDPATH**/ ?>
<?php $__env->startSection('title',t('Returns','রিটার্ন')); ?>
<?php $__env->startSection('account_heading',t('My returns','আমার রিটার্ন')); ?>
<?php $__env->startSection('account_content'); ?>
<div class="ec-card"><h2><?php echo e(t('Return history','রিটার্নের ইতিহাস')); ?></h2><?php echo $__env->make('public.business.public.ecommerce.account.return-list', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <?php echo $__env->make('public.business.public.ecommerce.account.pagination',['page'=>$requests], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('public.business.public.ecommerce.account.shell', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\saas-backend\resources\views/public/business/public/ecommerce/account/returns.blade.php ENDPATH**/ ?>
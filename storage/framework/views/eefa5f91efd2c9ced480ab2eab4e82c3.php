<?php $__env->startSection('content'); ?>
<div class="ret-page">
<?php echo $__env->make('public.business.admin.retail._styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('public.business.admin.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div><main class="workspace-content">
<div class="ret-title"><div><button type="button" class="ret-btn alt ret-mobile-nav" data-ws-sidebar-toggle>☰</button><h1><?php echo e(t('Online Orders','অনলাইন অর্ডার')); ?></h1><p><?php echo e(t('Business workspace','বিজনেস ওয়ার্কস্পেস')); ?></p></div><div class="ret-actions"><a class="ret-btn alt" href="<?php echo e(route('store.admin.dashboard')); ?>"><?php echo e(t('Dashboard','ড্যাশবোর্ড')); ?></a></div></div>
<?php if(session('success')): ?><div class="ret-toast"><?php echo e(session('success')); ?></div><?php endif; ?>
<?php if($errors->any()): ?><div class="ret-error"><?php echo e($errors->first()); ?></div><?php endif; ?>
<?php $columns=['order_no'=>'Order','customer_name'=>'Customer','customer_phone'=>'Phone','total'=>'Total','paid'=>'Paid','delivery_status'=>'Delivery','created_at'=>'Date']; ?>
<div class="ret-toolbar"><a class="ret-btn" href="<?php echo e(route('store.admin.retail.delivery.index')); ?>">+ <?php echo e(t('Open / New','খুলুন / নতুন')); ?></a></div>
<?php $rowActions='public.business.admin.retail._rows.orders_show'; ?>
<?php echo $__env->make('public.business.admin.retail._table', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</main></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('public.business.admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\saas-backend\resources\views/public/business/admin/retail/reports/online.blade.php ENDPATH**/ ?>
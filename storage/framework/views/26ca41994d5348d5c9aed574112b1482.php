<?php $__env->startSection('content'); ?>
<div class="ret-page"><?php echo $__env->make('public.business.admin.retail._styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('public.business.admin.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div>
<main class="workspace-content"><div class="ret-title"><div><button type="button" class="ret-btn alt ret-mobile-nav" data-ws-sidebar-toggle>☰</button><h1><?php echo e(t("Purchase Orders","ক্রয় আদেশ")); ?></h1><p><?php echo e(t('Business workspace','বিজনেস ওয়ার্কস্পেস')); ?></p></div><div class="ret-actions"><a class="ret-btn alt" href="<?php echo e(route('business.admin.dashboard')); ?>"><?php echo e(t('Dashboard','ড্যাশবোর্ড')); ?></a></div></div>
<?php if(session('success')): ?><div class="ret-toast"><?php echo e(session('success')); ?></div><?php endif; ?>
<?php if($errors->any()): ?><div class="ret-error"><?php echo e($errors->first()); ?></div><?php endif; ?>
<div class="ret-card"><a class="ret-btn" href="<?php echo e(route('business.admin.retail.purchase_orders.create')); ?>">+ <?php echo e(t('New Purchase Order','নতুন ক্রয় আদেশ')); ?></a></div><?php $columns=['po_no'=>'Order','status'=>'Status','supplier_id'=>'Supplier ID','total'=>'Total','created_at'=>'Created'];$rowActions='public.business.admin.retail._rows.purchase_orders'; ?> <?php echo $__env->make('public.business.admin.retail._table', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</main></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('public.business.admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\saas-backend\resources\views/public/business/admin/retail/purchase_orders/index.blade.php ENDPATH**/ ?>
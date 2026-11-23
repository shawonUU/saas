<?php $__env->startSection('content'); ?>
<div class="card">
    <h1 class="title"><?php echo e($title); ?></h1><p class="sub"><?php echo e($subtitle); ?></p>
    <h3><?php echo e(t('Welcome', 'স্বাগতম')); ?>, <?php echo e($user->name); ?></h3>
    <p class="small"><?php echo e($user->email); ?></p>
    <?php if(isset($user->role)): ?><p class="small"><?php echo e(t('Role', 'পদবি')); ?>: <?php echo e(ucfirst($user->role)); ?></p><?php endif; ?>
    <div class="actions">
        <?php $__currentLoopData = $links; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><a href="<?php echo e($link[1]); ?>" class="btn btn-outline"><?php echo e($link[0]); ?></a><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <form method="post" action="<?php echo e($logoutUrl); ?>"><?php echo csrf_field(); ?><button class="btn" type="submit"><?php echo e(t('Log out', 'লগআউট')); ?></button></form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.portal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\saas-backend\resources\views/portals/dashboard.blade.php ENDPATH**/ ?>
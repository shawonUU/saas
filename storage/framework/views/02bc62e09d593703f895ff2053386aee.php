<?php $__env->startSection('content'); ?>
<div class="card auth">
    <h1 class="title"><?php echo e($title); ?></h1>
    <p class="sub"><?php echo e(t('Log in with your portal account.', 'আপনার পোর্টাল অ্যাকাউন্টে লগইন করুন।')); ?></p>
    <form action="<?php echo e($action); ?>" method="post">
        <?php echo csrf_field(); ?>
        <label for="email"><?php echo e(t('Email address', 'ইমেইল ঠিকানা')); ?></label>
        <input id="email" name="email" type="email" value="<?php echo e(old('email')); ?>" autocomplete="username" required autofocus>
        <label for="password"><?php echo e(t('Password', 'পাসওয়ার্ড')); ?></label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>
        <button type="submit" class="btn wide"><?php echo e(t('Log in', 'লগইন করুন')); ?></button>
    </form>
    <?php if($registerUrl): ?><p class="hint"><?php echo e(t('New here?', 'নতুন ব্যবহারকারী?')); ?> <a href="<?php echo e($registerUrl); ?>"><?php echo e(t('Create account', 'অ্যাকাউন্ট তৈরি করুন')); ?></a></p><?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.portal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\saas-backend\resources\views/portals/login.blade.php ENDPATH**/ ?>
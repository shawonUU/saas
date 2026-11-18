<?php $__env->startSection('content'); ?>
<div class="card auth">
    <h1 class="title"><?php echo e(t('Forgot password?', 'পাসওয়ার্ড ভুলে গেছেন?')); ?></h1>
    <p class="sub"><?php echo e(t('Enter your account email to receive a reset link.', 'রিসেট লিংক পেতে ইমেইল দিন।')); ?></p>
    <?php if(session('status')): ?><p class="hint"><?php echo e(session('status')); ?></p><?php endif; ?>
    <?php if($errors->any()): ?><p class="hint"><?php echo e($errors->first()); ?></p><?php endif; ?>
    <form method="post" action="<?php echo e($action); ?>"><?php echo csrf_field(); ?>
        <label for="email"><?php echo e(t('Email', 'ইমেইল')); ?></label>
        <input id="email" name="email" type="email" value="<?php echo e(old('email')); ?>" autocomplete="email" required>
        <button class="btn wide" type="submit"><?php echo e(t('Send reset link', 'রিসেট লিংক পাঠান')); ?></button>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make($layout, array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\saas\resources\views/portals/password-forgot.blade.php ENDPATH**/ ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <h1 class="title"><?php echo e(t('My SaaS Dashboard', 'আমার SaaS ড্যাশবোর্ড')); ?></h1>
    <p class="sub"><?php echo e(t('Hello', 'স্বাগতম')); ?>, <?php echo e($user->name); ?> · <?php echo e($user->email); ?></p>
    <div class="actions"><a href="<?php echo e(route('saas.businesss.create')); ?>" class="btn"><?php echo e(t('Create new business', 'নতুন শপ তৈরি করুন')); ?></a><form method="post" action="<?php echo e(route('saas.logout')); ?>"><?php echo csrf_field(); ?><button type="submit" class="btn btn-outline"><?php echo e(t('Log out', 'লগআউট')); ?></button></form></div>
    <?php if(session('business_created')): ?>
        <div class="alert success">
            <strong><?php echo e(t('Business created successfully', 'শপ সফলভাবে তৈরি হয়েছে')); ?></strong>
            <p class="small"><?php echo e(t('Your storefront and admin portal use the same wildcard DNS setup. No individual DNS entry is needed for each new business.', 'স্টোরফ্রন্ট ও অ্যাডমিন পোর্টাল একই ওয়াইল্ডকার্ড DNS ব্যবহার করে। প্রতিটি নতুন শপের জন্য আলাদা DNS এন্ট্রি লাগবে না।')); ?></p>
            <div class="actions">
                <a class="btn" href="<?php echo e(route('store.customer.home', ['business' => session('business_created')])); ?>" target="_blank" rel="noopener"><?php echo e(t('Open storefront', 'স্টোরফ্রন্ট খুলুন')); ?></a>
                <a class="btn btn-outline" href="<?php echo e(route('store.admin.login', ['business' => session('business_created')])); ?>" target="_blank" rel="noopener"><?php echo e(t('Open admin login', 'অ্যাডমিন লগইন খুলুন')); ?></a>
            </div>
        </div>
    <?php endif; ?>
    <h2 style="margin:35px 0 12px"><?php echo e(t('My businesss', 'আমার শপগুলো')); ?></h2>
    <?php $__empty_1 = true; $__currentLoopData = $businesss; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $business): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="business"><h3><?php echo e($business->name); ?></h3>
            <div class="small"><?php echo e($business->domain); ?> · <?php echo e($business->approval_status); ?></div>
            <div class="actions">
                <a class="btn btn-outline" target="_blank" rel="noopener" href="<?php echo e(route('store.admin.login', ['business' => $business->domain])); ?>"><?php echo e(t('Business admin login', 'শপ অ্যাডমিন লগইন')); ?></a>
                <a class="btn btn-outline" target="_blank" rel="noopener" href="<?php echo e(route('store.customer.home', ['business' => $business->domain])); ?>"><?php echo e(t('Customer storefront', 'কাস্টমার স্টোর')); ?></a>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><p class="small"><?php echo e(t('No businesss yet. Create your first business.', 'এখনো কোনো শপ নেই। প্রথম শপ তৈরি করুন।')); ?></p>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('public.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\saas-backend\resources\views/public/dashboard.blade.php ENDPATH**/ ?>
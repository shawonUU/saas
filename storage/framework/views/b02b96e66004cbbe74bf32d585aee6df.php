
<?php $__env->startSection('content'); ?>
<div class="card auth" style="max-width:560px">
    <h1 class="title"><?php echo e(t('Create business', 'শপ তৈরি করুন')); ?></h1>
    <p class="sub"><?php echo e(t('This will also create your business owner login.', 'এর সঙ্গে আপনার শপ ওনারের লগইনও তৈরি হবে।')); ?></p>
    <form method="post" action="<?php echo e(route('saas.businesss.store')); ?>">
        <?php echo csrf_field(); ?>
        <label for="name"><?php echo e(t('Business name', 'শপের নাম')); ?></label><input id="name" name="name" value="<?php echo e(old('name')); ?>" required>
        <label for="domain"><?php echo e(t('Subdomain (lowercase, no spaces)', 'সাবডোমেইন (ছোট হাতের অক্ষর, স্পেস ছাড়া)')); ?></label><input id="domain" name="domain" value="<?php echo e(old('domain')); ?>" pattern="[a-z][a-z0-9-]{2,39}" placeholder="rohimstore" required>
        <p class="small"><?php echo e(t('Automatically available after setup', 'একবার DNS সেটআপ করলে স্বয়ংক্রিয়ভাবে চালু হবে')); ?>: rohimstore.<?php echo e(config('saas.domain')); ?> · admin.rohimstore.<?php echo e(config('saas.domain')); ?></p>
        <p class="small"><?php echo e(t('Reserved: admin, authority, www, api, mail, support. admin.saas.test is reserved for SaaS Authority only.', 'সংরক্ষিত: admin, authority, www, api, mail, support। admin.saas.test শুধু SaaS Authority-এর জন্য সংরক্ষিত।')); ?></p>
        <label for="category_id"><?php echo e(t('Business category', 'শপের ক্যাটাগরি')); ?></label>
        <select id="category_id" name="category_id" required><option value=""><?php echo e(t('Select category', 'ক্যাটাগরি বাছুন')); ?></option><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($category->id); ?>" <?php if(old('category_id') == $category->id): echo 'selected'; endif; ?>><?php echo e(navName($category)); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
        <label for="admin_password"><?php echo e(t('Separate admin password (optional)', 'আলাদা অ্যাডমিন পাসওয়ার্ড (ঐচ্ছিক)')); ?></label><input id="admin_password" name="admin_password" type="password" minlength="8" autocomplete="new-password">
        <label for="admin_password_confirmation"><?php echo e(t('Confirm admin password', 'অ্যাডমিন পাসওয়ার্ড নিশ্চিত করুন')); ?></label><input id="admin_password_confirmation" name="admin_password_confirmation" type="password" minlength="8" autocomplete="new-password">
        <p class="small"><?php echo e(t('Leave blank to initially use your SaaS account password. Logins remain separate.', 'খালি রাখলে শুরুতে SaaS অ্যাকাউন্টের পাসওয়ার্ড ব্যবহার হবে। লগইন আলাদাই থাকবে।')); ?></p>
        <button type="submit" class="btn wide"><?php echo e(t('Create business', 'শপ তৈরি করুন')); ?></button>
    </form>
    <p class="hint"><a href="<?php echo e(route('saas.dashboard')); ?>"><?php echo e(t('Back to dashboard', 'ড্যাশবোর্ডে ফিরুন')); ?></a></p>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('public.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\saas-backend\resources\views/public/business/business-register.blade.php ENDPATH**/ ?>
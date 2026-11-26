<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('public.business.admin.configurations._workspace_styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('public.business.admin.sales.settings.pos-settings._styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopPush(); ?>
<?php $__env->startSection('content'); ?>
<div class="workspace-layout pos-settings-page">
    <?php echo $__env->make('public.business.admin.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div>
    <main class="workspace-content">
        <header class="pos-settings-header">
            <small><?php echo e(t('Sales / Settings','বিক্রয় / সেটিংস')); ?></small>
            <h1><?php echo e(t('POS Settings','পস সেটিংস')); ?></h1>
        </header>
        <?php if(session('success')): ?>
            <div class="pos-settings-message pos-settings-success" role="status"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if($errors->any()): ?>
            <div class="pos-settings-message pos-settings-error" role="alert"><?php echo e($errors->first()); ?></div>
        <?php endif; ?>
        <form method="POST" action="<?php echo e(route('business.admin.sales.settings.pos-settings.update', ['business' => $business->domain])); ?>" class="pos-settings-card">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
            <div class="pos-settings-heading">
                <h2><?php echo e(t('Restaurant · Quick Sale','রেস্টুরেন্ট · দ্রুত বিক্রয়')); ?></h2>
                <p><?php echo e(t('Changes apply to new orders only.','পরিবর্তন শুধু নতুন অর্ডারে প্রযোজ্য হবে।')); ?></p>
            </div>
            <fieldset class="pos-settings-fields" <?php if(!$permissions['update']): echo 'disabled'; endif; ?>>
                <legend class="pos-settings-sr-only"><?php echo e(t('Restaurant workflow','রেস্টুরেন্টের কাজের নিয়ম')); ?></legend>
                <label class="pos-settings-field" for="pos-default-service">
                    <span><?php echo e(t('Start new orders as','নতুন অর্ডারের ডিফল্ট সেবা')); ?></span>
                    <select id="pos-default-service" name="restaurant_pos[default_service]" required>
                        <option value="takeaway" <?php if(old('restaurant_pos.default_service', $restaurantPosSettings['default_service']) === 'takeaway'): echo 'selected'; endif; ?>><?php echo e(t('Counter / Takeaway','কাউন্টার / টেকঅ্যাওয়ে')); ?></option>
                        <option value="dine_in" <?php if(old('restaurant_pos.default_service', $restaurantPosSettings['default_service']) === 'dine_in'): echo 'selected'; endif; ?>><?php echo e(t('Dine-in','ডাইন-ইন')); ?></option>
                        <option value="delivery" <?php if(old('restaurant_pos.default_service', $restaurantPosSettings['default_service']) === 'delivery'): echo 'selected'; endif; ?>><?php echo e(t('Delivery','ডেলিভারি')); ?></option>
                    </select>
                    <small><?php echo e(t('Dine-in starts with table selection.','ডাইন-ইন অর্ডার শুরু হবে টেবিল নির্বাচন দিয়ে।')); ?></small>
                </label>
                <label class="pos-settings-field" for="pos-kitchen-mode">
                    <span><?php echo e(t('Kitchen tracking','কিচেন ট্র্যাকিং')); ?></span>
                    <select id="pos-kitchen-mode" name="restaurant_pos[kitchen_mode]" required>
                        <option value="off" <?php if(old('restaurant_pos.kitchen_mode', $restaurantPosSettings['kitchen_mode']) === 'off'): echo 'selected'; endif; ?>><?php echo e(t('Off','বন্ধ')); ?></option>
                        <option value="optional" <?php if(old('restaurant_pos.kitchen_mode', $restaurantPosSettings['kitchen_mode']) === 'optional'): echo 'selected'; endif; ?>><?php echo e(t('Optional','ঐচ্ছিক')); ?></option>
                        <option value="required" <?php if(old('restaurant_pos.kitchen_mode', $restaurantPosSettings['kitchen_mode']) === 'required'): echo 'selected'; endif; ?>><?php echo e(t('Required','বাধ্যতামূলক')); ?></option>
                    </select>
                    <small><?php echo e(t('Optional keeps billing and table closing independent of the kitchen.','ঐচ্ছিক থাকলে বিল বা টেবিল বন্ধ করতে কিচেনের ধাপ শেষ হওয়া লাগবে না।')); ?></small>
                </label>
                <label class="pos-settings-field" for="pos-auto-send">
                    <span><?php echo e(t('Send to kitchen when billing','বিল নিশ্চিত করলে কিচেনে পাঠান')); ?></span>
                    <select id="pos-auto-send" name="restaurant_pos[auto_send_to_kitchen]" required>
                        <option value="0" <?php if(! old('restaurant_pos.auto_send_to_kitchen', $restaurantPosSettings['auto_send_to_kitchen'])): echo 'selected'; endif; ?>><?php echo e(t('No · send manually','না · প্রয়োজন হলে পাঠাব')); ?></option>
                        <option value="1" <?php if(old('restaurant_pos.auto_send_to_kitchen', $restaurantPosSettings['auto_send_to_kitchen'])): echo 'selected'; endif; ?>><?php echo e(t('Yes · send automatically','হ্যাঁ · স্বয়ংক্রিয়ভাবে পাঠান')); ?></option>
                    </select>
                    <small><?php echo e(t('Hold only saves. Kitchen Off always disables automatic sending.','হোল্ড করলে শুধু সংরক্ষণ হয়। কিচেন বন্ধ থাকলে স্বয়ংক্রিয়ভাবে পাঠানোও বন্ধ থাকবে।')); ?></small>
                </label>
            </fieldset>
            <p class="pos-settings-note"><?php echo e(t('Close dine-in tables when guests leave. Bill and KOT printing are always available.','অতিথি চলে গেলে ডাইন-ইন টেবিল বন্ধ করুন। বিল ও KOT প্রিন্ট সবসময় করা যাবে।')); ?></p>
            <?php if($permissions['update']): ?>
                <div class="pos-settings-actions"><button type="submit" class="pos-settings-save"><?php echo e(t('Save Settings','সেটিংস সংরক্ষণ')); ?></button></div>
            <?php endif; ?>
        </form>
    </main>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('public.business.admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\saas-backend\resources\views/public/business/admin/sales/settings/pos-settings/index.blade.php ENDPATH**/ ?>
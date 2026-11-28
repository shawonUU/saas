<div class="saas-popover-anchor" id="saas-notif-anchor">
    <button type="button" class="saas-icon-trigger" id="saas-notif-trigger" aria-haspopup="true"
            aria-expanded="false" aria-controls="saas-notif-menu"
            aria-label="<?php echo e(t('Notifications', 'নোটিফিকেশন')); ?>" title="<?php echo e(t('Notifications', 'নোটিফিকেশন')); ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9ZM10 21h4"/></svg>
        <span class="saas-notification-count" id="saas-notif-count" hidden></span>
    </button>
    <section class="saas-flyout saas-notif-flyout" id="saas-notif-menu" aria-label="<?php echo e(t('Notifications', 'নোটিফিকেশন')); ?>" hidden>
        <div class="saas-notif-header"><div><h2><?php echo e(t('Notifications', 'নোটিফিকেশন')); ?></h2><p id="saas-notif-description"><?php echo e(t('Updates for your account', 'আপনার অ্যাকাউন্টের আপডেট')); ?></p></div><button type="button" id="saas-notif-read-all" class="saas-mark-read" hidden><?php echo e(t('Mark all read', 'সব পড়া হয়েছে')); ?></button></div>
        <div class="saas-notif-list" id="saas-notif-list" aria-live="polite">
            <div class="saas-notif-empty"><?php echo e(t('Loading notifications…', 'নোটিফিকেশন লোড হচ্ছে…')); ?></div>
        </div>
        <div class="saas-notif-foot"><?php echo e(t('Only notifications for this account appear here.', 'শুধু এই অ্যাকাউন্টের নোটিফিকেশন এখানে দেখা যাবে।')); ?></div>
    </section>
</div>
<?php /**PATH C:\laragon\www\saas-backend\resources\views/public/business/admin/settings/business/notifications/partials/bell.blade.php ENDPATH**/ ?>
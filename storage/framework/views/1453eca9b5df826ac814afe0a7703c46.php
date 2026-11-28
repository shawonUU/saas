<?php $dayAlert = request()->attributes->get('business_day_status'); ?>
<?php if(is_array($dayAlert) && ($dayAlert['alert'] ?? false)): ?>
<div role="alert" style="padding:10px 20px;background:rgba(241,163,58,.12);color:var(--text);border-bottom:1px solid rgba(225,155,72,.45);font-size:13px;display:flex;gap:12px;align-items:center;justify-content:center;flex-wrap:wrap">
 <strong><?php echo e($dayAlert['overdue'] ? t('Business Day Close Overdue','বিজনেস ডে ক্লোজের সময় পেরিয়েছে') : t('Business Day Close Reminder','বিজনেস ডে ক্লোজের সতর্কতা')); ?></strong>
 <span><?php echo e(t('Open date:','খোলা তারিখ:')); ?> <?php echo e($dayAlert['business_date']); ?> · <?php echo e(t('Closing deadline:','বন্ধের শেষ সময়:')); ?> <?php echo e($dayAlert['deadline_at']); ?></span>
 <a href="<?php echo e(route('business.admin.settings.business.business-date.index',['business'=>request()->route('business')])); ?>" style="color:var(--primary);font-weight:700"><?php echo e(t('Manage / Close Day','সেটিংস / দিন বন্ধ')); ?></a>
</div>
<?php endif; ?>
<?php /**PATH C:\laragon\www\saas-backend\resources\views/public/business/admin/settings/business/business-date/partials/alert.blade.php ENDPATH**/ ?>
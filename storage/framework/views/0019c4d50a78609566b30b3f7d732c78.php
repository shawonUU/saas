<?php
    $listuiCount = method_exists($paginator,'total') ? $paginator->total() : $paginator->count();
    $listuiLast = method_exists($paginator,'lastPage') ? $paginator->lastPage() : $paginator->currentPage();
?>
<div class="cfg-bottom listui-footer">
 <span><?php echo e($paginator->firstItem() ?? 0); ?>–<?php echo e($paginator->lastItem() ?? 0); ?> <?php echo e(t('of','মোট')); ?> <?php echo e($listuiCount); ?> <?php echo e(t('records','টি রেকর্ড')); ?></span>
 <nav class="cfg-pagination" aria-label="<?php echo e(t('Pagination','পেজিনেশন')); ?>">
  <?php if($paginator->previousPageUrl()): ?><a href="<?php echo e($paginator->previousPageUrl()); ?>"><?php echo e(t('Prev','আগের')); ?></a><?php else: ?><span class="is-disabled"><?php echo e(t('Prev','আগের')); ?></span><?php endif; ?>
  <span><?php echo e($paginator->currentPage()); ?> / <?php echo e(max(1, $listuiLast)); ?></span>
  <?php if($paginator->nextPageUrl()): ?><a href="<?php echo e($paginator->nextPageUrl()); ?>"><?php echo e(t('Next','পরের')); ?></a><?php else: ?><span class="is-disabled"><?php echo e(t('Next','পরের')); ?></span><?php endif; ?>
 </nav>
</div>
<?php /**PATH C:\laragon\www\saas-backend\resources\views/public/business/admin/shared/list-ui/pagination.blade.php ENDPATH**/ ?>
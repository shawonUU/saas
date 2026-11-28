<?php
 $listuiExcludeParams = array_merge(['per_page','page','export','f'], $excludeParams ?? []);
 $listuiPerPage=(int)request('per_page',15);
 if(!in_array($listuiPerPage,[10,15,25,50,100],true))$listuiPerPage=15;
?>
<div class="listui-standalone" data-listui-root>
 <form method="GET" id="listui-standard-form" class="cfg-toolbar cfg-customers-toolbar listui-toolbar" data-listui-form data-listui-filter-target="listui-extra-filter">
  <?php $__currentLoopData = request()->except($listuiExcludeParams); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $param=>$value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
   <?php if(is_scalar($value)): ?><input type="hidden" name="<?php echo e($param); ?>" value="<?php echo e($value); ?>">
   <?php elseif(is_array($value)): ?><?php $__currentLoopData = $value; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subkey=>$subvalue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(is_scalar($subvalue)): ?><input type="hidden" name="<?php echo e($param); ?>[<?php echo e($subkey); ?>]" value="<?php echo e($subvalue); ?>"><?php endif; ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> <?php endif; ?>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  <div class="cfg-toolbar-actions listui-controls">
   <select name="per_page" class="cfg-input cfg-small listui-perpage" aria-label="<?php echo e(t('Rows per page','প্রতি পৃষ্ঠায় সারি')); ?>" onchange="this.form.requestSubmit()"><?php $__currentLoopData = [10,15,25,50,100]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $size): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($size); ?>" <?php if($listuiPerPage===$size): echo 'selected'; endif; ?>><?php echo e($size); ?> <?php echo e(t('per page','প্রতি পৃষ্ঠায়')); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
   <button type="button" class="cfg-btn cfg-action-icon cfg-filter-toggle" data-listui-toggle data-show-label="<?php echo e(t('Show Column Filters','কলাম ফিল্টার দেখান')); ?>" data-hide-label="<?php echo e(t('Hide Column Filters','কলাম ফিল্টার লুকান')); ?>" aria-expanded="true" title="<?php echo e(t('Show or hide filters','ফিল্টার দেখান বা লুকান')); ?>"><span data-listui-toggle-label><?php echo e(t('Hide Column Filters','কলাম ফিল্টার লুকান')); ?></span></button>
   <a class="cfg-btn cfg-action-icon" href="<?php echo e(url()->current()); ?>" title="<?php echo e(t('Reset all filters','সব ফিল্টার রিসেট')); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 11a9 9 0 1 1 3.2 6.9"/><path d="M3 4v7h7"/></svg><?php echo e(t('Reset','রিসেট')); ?></a>
   <span class="cfg-toolbar-separator" aria-hidden="true"></span>
   <a class="cfg-btn cfg-action-icon cfg-export-excel" data-listui-export="excel" href="<?php echo e(request()->fullUrlWithQuery(['export'=>'excel','page'=>null])); ?>" title="<?php echo e(t('Download filtered Excel','ফিল্টার করা এক্সেল ডাউনলোড')); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="m8 13 5 6m0-6-5 6"/></svg>Excel</a>
   <a class="cfg-btn cfg-action-icon cfg-export-pdf" data-listui-export="pdf" href="<?php echo e(request()->fullUrlWithQuery(['export'=>'pdf','page'=>null])); ?>" title="<?php echo e(t('Download filtered PDF','ফিল্টার করা পিডিএফ ডাউনলোড')); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 13h6M9 17h6"/></svg>PDF</a>
  </div>
 </form>
</div>
<?php /**PATH C:\laragon\www\saas-backend\resources\views/public/business/admin/shared/list-ui/standalone-toolbar.blade.php ENDPATH**/ ?>
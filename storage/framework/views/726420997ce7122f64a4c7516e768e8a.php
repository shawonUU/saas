<aside class="sf-category-rail" aria-label="<?php echo e(t('Categories','ক্যাটাগরি')); ?>">
 <div class="sf-rail-list">
 <?php $__currentLoopData = ($categories??collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php $kids=$category->children??collect(); $active=(int)request('category')===(int)$category->id; ?>
  <div class="sf-rail-group <?php echo e($active?'is-active':''); ?>">
  <div class="sf-rail-row"><a href="<?php echo e(route('business.customer.shop',['category'=>$category->id])); ?>" class="sf-rail-item"><span class="sf-rail-emoji"><?php echo e(['♧','♙','◇','♢','✿','♥','♤','✧','♧','✦','◈','♢'][$loop->index%12]); ?></span><span class="sf-rail-label"><?php echo e($category->name); ?></span></a>
  <?php if($kids->count()): ?><button class="sf-rail-expand" aria-expanded="<?php echo e($active?'true':'false'); ?>" type="button" data-category-toggle title="<?php echo e(t('Show subcategories','সাবক্যাটাগরি দেখুন')); ?>">⌄</button><?php else: ?><span class="sf-rail-arrow">›</span><?php endif; ?></div>
  <?php if($kids->count()): ?><div class="sf-rail-children" <?php if(!$active): ?> hidden <?php endif; ?>><?php $__currentLoopData = $kids; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kid): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><a href="<?php echo e(route('business.customer.shop',['category'=>$category->id,'sub_category'=>$kid->id])); ?>" class="<?php echo \Illuminate\Support\Arr::toCssClasses(['active'=>(int)request('sub_category')===(int)$kid->id]); ?>"><?php echo e($kid->name); ?></a><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div><?php endif; ?>
  </div>
 <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
 </div>
 <div class="sf-rail-bottom"><a href="<?php echo e(route('business.customer.shop')); ?>"><strong>⌕</strong><span><?php echo e(t('All Products','সব পণ্য')); ?></span></a><a href="<?php echo e(route('business.customer.wishlist')); ?>"><strong>♡</strong><span><?php echo e(t('Wishlist','উইশলিস্ট')); ?></span></a></div>
</aside>
<?php /**PATH C:\laragon\www\saas-backend\resources\views/public/business/public/ecommerce/partials/desktop-categories.blade.php ENDPATH**/ ?>
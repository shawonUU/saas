<?php
    $sidebarLogoPath=data_get($business,'logo') ?? data_get($business,'logo_path') ?? data_get($business,'business_logo');
    $sidebarLogo=$sidebarLogoPath ? (preg_match('~^https?://~i',$sidebarLogoPath) ? $sidebarLogoPath : (str_starts_with($sidebarLogoPath,'/') ? url($sidebarLogoPath) : (str_starts_with($sidebarLogoPath,'storage/') || str_starts_with($sidebarLogoPath,'images/') ? asset($sidebarLogoPath) : asset('storage/'.$sidebarLogoPath)))) : null;
    $currentChildParam=request()->route('childMenu');
    $currentChildId=is_object($currentChildParam) ? $currentChildParam->id : $currentChildParam;
?>
<aside class="workspace-sidebar" aria-label="<?php echo e(t('Business navigation','বিজনেস নেভিগেশন')); ?>">
    <div class="ws-side-top">
        <div class="ws-side-brand">
            
            <button class="ws-close-sidebar" data-ws-sidebar-toggle type="button" aria-label="<?php echo e(t('Close menu','মেনু বন্ধ করুন')); ?>">
                <svg class="ws-svg"><use href="#ws-i-x"/></svg>
            </button>
        </div>
        <label class="ws-side-search">
            <svg class="ws-svg"><use href="#ws-i-search"/></svg>
            <input type="search" id="ws-sidebar-search" placeholder="<?php echo e(t('Search menu...','মেনু খুঁজুন...')); ?>" autocomplete="off" aria-label="<?php echo e(t('Search menu','মেনু খুঁজুন')); ?>">
        </label>
    </div>
    <nav class="ws-side-scroll"><div class="ws-nav-label"><?php echo e(t('MAIN MENU','প্রধান মেনু')); ?></div><a class="ws-nav-dashboard <?php echo e(request()->routeIs('store.admin.dashboard') ? 'active' : ''); ?>" href="<?php echo e(route('store.admin.dashboard',['business'=>$business->code])); ?>"><svg class="ws-svg"><use href="#ws-i-grid"/></svg><?php echo e(t('Dashboard','ড্যাশবোর্ড')); ?></a><div class="ws-nav-separator"></div><div class="ws-nav-label"><?php echo e(t('WORKSPACE MODULES','ওয়ার্কস্পেস মডিউল')); ?></div>
        <?php $__empty_1 = true; $__currentLoopData = $modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><div class="ws-nav-group"><div class="ws-module-label"><span><?php echo e(navName($module)); ?></span><em><?php echo e($module->menus->count()); ?></em></div><?php $__currentLoopData = $module->menus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $menu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php ($expanded=$menu->children->contains(fn($child)=>(string)$child->id===(string)$currentChildId)); ?><details class="ws-menu" <?php echo e($expanded ? 'open' : ''); ?>><summary><svg class="ws-svg"><use href="#ws-i-folder"/></svg><span class="ws-menu-text"><?php echo e(navName($menu)); ?></span><svg class="ws-svg ws-chevron"><use href="#ws-i-chevron"/></svg></summary><div class="ws-children"><?php $__currentLoopData = $menu->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><a class="<?php echo e((string)$currentChildId===(string)$child->id ? 'active' : ''); ?>" href="<?php echo e($child->route==='/dashboard' ? route('store.admin.dashboard',['business'=>$business->code]) : route('store.admin.feature',['business'=>$business->code,'childMenu'=>$child->id])); ?>"><span><?php echo e(navName($child)); ?></span></a><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div></details><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><p class="ws-search-empty"><?php echo e(t('No menu access assigned yet.','এখনও কোনো মেনু অ্যাক্সেস দেওয়া হয়নি।')); ?></p><?php endif; ?>
        <p class="ws-search-empty" id="ws-search-empty" hidden><?php echo e(t('No matching menus found.','কোনো মেনু পাওয়া যায়নি।')); ?></p>
    </nav>
    <div class="ws-side-bottom"><div class="ws-side-user"><span class="ws-user-avatar"><?php echo e(mb_strtoupper(mb_substr($user->name ?? 'A',0,1))); ?></span><div class="ws-user-text"><strong><?php echo e($user->name); ?></strong><small><?php echo e(t('Store administrator','স্টোর অ্যাডমিন')); ?></small></div><form method="POST" action="<?php echo e(route('store.admin.logout',['business'=>$business->code])); ?>"><?php echo csrf_field(); ?><button class="ws-signout" type="submit" title="<?php echo e(t('Log out','লগআউট')); ?>" aria-label="<?php echo e(t('Log out','লগআউট')); ?>"><svg class="ws-svg"><use href="#ws-i-logout"/></svg></button></form></div></div>
</aside>
<?php /**PATH C:\laragon\www\saas-backend\resources\views/public/business/admin/sidebar.blade.php ENDPATH**/ ?>
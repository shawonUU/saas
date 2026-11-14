
<?php
    $sidebarLogoPath=data_get($business,'logo') ?? data_get($business,'logo_path') ?? data_get($business,'business_logo');
    $sidebarLogo=$sidebarLogoPath ? (preg_match('~^https?://~i',$sidebarLogoPath) ? $sidebarLogoPath : (str_starts_with($sidebarLogoPath,'/') ? url($sidebarLogoPath) : (str_starts_with($sidebarLogoPath,'storage/') || str_starts_with($sidebarLogoPath,'images/') ? asset($sidebarLogoPath) : asset('storage/'.$sidebarLogoPath)))) : null;
    $currentModuleSlug=(string)request()->route('moduleSlug');
    $currentMenuSlug=(string)request()->route('menuSlug');
    $currentChildSlug=(string)request()->route('childSlug');
?>
<style>
.workspace-sidebar .ws-menu-direct{display:flex;align-items:center;gap:12px;width:100%;padding:11px 14px;border-radius:10px;color:inherit;text-decoration:none;transition:background .2s,color .2s;cursor:pointer}
.workspace-sidebar .ws-menu-direct:hover{background:rgba(128,128,128,.1);color:inherit;text-decoration:none}
.workspace-sidebar .ws-menu-direct.active{background:rgba(128,128,128,.15);font-weight:600}
.workspace-sidebar .ws-menu-direct .ws-svg{width:18px;height:18px;flex-shrink:0}
.workspace-sidebar .ws-menu-direct .ws-menu-text{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap; font-size:10px}
.workspace-sidebar .ws-nav-group[hidden],.workspace-sidebar .ws-menu[hidden],.workspace-sidebar .ws-menu-direct[hidden]{display:none!important}
</style>
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
    <nav class="ws-side-scroll">
        <div class="ws-nav-label"><?php echo e(t('MAIN MENU','প্রধান মেনু')); ?></div>
        <a class="ws-nav-dashboard <?php echo e(request()->routeIs('store.admin.dashboard') ? 'active' : ''); ?>" href="<?php echo e(route('store.admin.dashboard')); ?>">
            <svg class="ws-svg"><use href="#ws-i-grid"/></svg>
            <?php echo e(t('Dashboard','ড্যাশবোর্ড')); ?>

        </a>
        <div class="ws-nav-separator"></div>
        <div class="ws-nav-label"><?php echo e(t('WORKSPACE MODULES','ওয়ার্কস্পেস মডিউল')); ?></div>
        <?php $__empty_1 = true; $__currentLoopData = $modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="ws-nav-group">
                <div class="ws-module-label">
                    <span><?php echo e(navName($module)); ?></span>
                    <em><?php echo e($module->menus->count()); ?></em>
                </div>
                <?php $__currentLoopData = $module->menus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $menu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $hasChildren=$menu->children->isNotEmpty();
                        $menuActive=$currentModuleSlug===(string)$module->slug && $currentMenuSlug===(string)$menu->slug;
                        $menuParams=['moduleSlug'=>$module->slug,'menuSlug'=>$menu->slug];
                    ?>
                    <?php if($hasChildren): ?>
                        <details class="ws-menu" <?php echo e($menuActive ? 'open' : ''); ?>>
                            <summary class="<?php echo e($menuActive ? 'active' : ''); ?>">
                                <svg class="ws-svg"><use href="#ws-i-folder"/></svg>
                                <span class="ws-menu-text"><?php echo e(navName($menu)); ?></span>
                                <svg class="ws-svg ws-chevron"><use href="#ws-i-chevron"/></svg>
                            </summary>
                            <div class="ws-children">
                                <?php $__currentLoopData = $menu->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $childActive=$menuActive && $currentChildSlug===(string)$child->slug;
                                        $childParams=[...$menuParams,'childSlug'=>$child->slug];
                                    ?>
                                    <a class="<?php echo e($childActive ? 'active' : ''); ?>" href="<?php echo e(route('store.admin.feature',$childParams)); ?>">
                                        <span><?php echo e(navName($child)); ?></span>
                                    </a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </details>
                    <?php else: ?>
                        <a class="ws-menu-direct <?php echo e($menuActive && $currentChildSlug==='' ? 'active' : ''); ?>" href="<?php echo e(route('store.admin.feature',$menuParams)); ?>">
                            <svg class="ws-svg"><use href="#ws-i-folder"/></svg>
                            <span class="ws-menu-text"><?php echo e(navName($menu)); ?></span>
                        </a>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="ws-search-empty"><?php echo e(t('No menu access assigned yet.','এখনও কোনো মেনু অ্যাক্সেস দেওয়া হয়নি।')); ?></p>
        <?php endif; ?>
        <p class="ws-search-empty" id="ws-search-empty" hidden><?php echo e(t('No matching menus found.','কোনো মেনু পাওয়া যায়নি।')); ?></p>
    </nav>
    <div class="ws-side-bottom">
        <div class="ws-side-user">
            <span class="ws-user-avatar"><?php echo e(mb_strtoupper(mb_substr($user->name ?? 'A',0,1))); ?></span>
            <div class="ws-user-text">
                <strong><?php echo e($user->name); ?></strong>
                <small><?php echo e(t('Store administrator','স্টোর অ্যাডমিন')); ?></small>
            </div>
            <form method="POST" action="<?php echo e(route('store.admin.logout')); ?>">
                <?php echo csrf_field(); ?>
                <button class="ws-signout" type="submit" title="<?php echo e(t('Log out','লগআউট')); ?>" aria-label="<?php echo e(t('Log out','লগআউট')); ?>">
                    <svg class="ws-svg"><use href="#ws-i-logout"/></svg>
                </button>
            </form>
        </div>
    </div>
</aside>
<script>
document.addEventListener('DOMContentLoaded',function(){
    const sidebar=document.querySelector('.workspace-sidebar');
    if(!sidebar)return;
    const search=sidebar.querySelector('#ws-sidebar-search');
    const empty=sidebar.querySelector('#ws-search-empty');
    if(!search)return;
    const groups=[...sidebar.querySelectorAll('.ws-nav-group')];
    search.addEventListener('input',function(){
        const q=this.value.trim().toLocaleLowerCase();
        let found=false;
        groups.forEach(group=>{
            let groupFound=false;
            const moduleName=group.querySelector('.ws-module-label span')?.textContent.toLocaleLowerCase() || '';
            const moduleMatch=!!q && moduleName.includes(q);
            group.querySelectorAll('.ws-menu,.ws-menu-direct').forEach(item=>{
                const menuName=item.querySelector('.ws-menu-text')?.textContent.toLocaleLowerCase() || '';
                const menuMatch=!q || moduleMatch || menuName.includes(q);
                let childMatch=false;
                if(item.matches('details')){
                    item.querySelectorAll('.ws-children a').forEach(child=>{
                        const match=!q || moduleMatch || menuMatch || child.textContent.toLocaleLowerCase().includes(q);
                        child.hidden=!match;
                        if(match)childMatch=true;
                    });
                    item.hidden=!(menuMatch || childMatch);
                    if(q && !item.hidden)item.open=true;
                    if(!q)item.open=item.querySelector('.ws-children a.active')!==null;
                }else{
                    item.hidden=!menuMatch;
                }
                if(!item.hidden)groupFound=true;
            });
            group.hidden=!groupFound;
            if(groupFound)found=true;
        });
        if(empty)empty.hidden=found || !q;
    });
});
</script>
<?php /**PATH C:\laragon\www\saas-backend\resources\views/public/business/admin/sidebar.blade.php ENDPATH**/ ?>
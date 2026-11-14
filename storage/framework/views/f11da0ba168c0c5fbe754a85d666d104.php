
<?php
    $currentModuleSlug=(string)request()->route('module');
    $currentMenuSlug=(string)request()->route('menu');
    $currentChildSlug=(string)request()->route('child');
    $businessRoute=request()->route('business');
    $businessKey=is_object($businessRoute)?(string)($businessRoute->subdomain ?? $businessRoute->slug ?? $businessRoute->domain ?? ''):(string)$businessRoute;
?>
<style>
.workspace-sidebar .ws-module{margin-bottom:4px}
.workspace-sidebar .ws-module[hidden],.workspace-sidebar .ws-menu[hidden],.workspace-sidebar .ws-menu-direct[hidden],.workspace-sidebar .ws-children a[hidden]{display:none!important}
.workspace-sidebar .ws-module>.ws-module-label{display:flex;align-items:center;gap:10px;cursor:pointer;list-style:none;user-select:none;border-radius:10px;padding:11px 12px;transition:background .2s,color .2s}
.workspace-sidebar .ws-module>.ws-module-label::-webkit-details-marker{display:none}
.workspace-sidebar .ws-module>.ws-module-label::marker{content:""}
.workspace-sidebar .ws-module>.ws-module-label:hover{background:rgba(128,128,128,.1)}
.workspace-sidebar .ws-module>.ws-module-label.active{background:rgba(128,128,128,.12);font-weight:600}
.workspace-sidebar .ws-module>.ws-module-label .ws-module-icon{width:18px;height:18px;flex-shrink:0}
.workspace-sidebar .ws-module>.ws-module-label .ws-module-name{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:12px}
.workspace-sidebar .ws-module>.ws-module-label em{font-style:normal;font-size:11px;min-width:20px;text-align:center}
.workspace-sidebar .ws-module>.ws-module-label .ws-module-chevron{width:15px;height:15px;flex-shrink:0;transition:transform .2s}
.workspace-sidebar .ws-module[open]>.ws-module-label .ws-module-chevron{transform:rotate(90deg)}
.workspace-sidebar .ws-module-content{padding:4px 0 6px 12px}
.workspace-sidebar .ws-module-content .ws-menu{margin-bottom:2px}
.workspace-sidebar .ws-menu>summary{cursor:pointer}
.workspace-sidebar .ws-menu-direct{display:flex;align-items:center;gap:12px;width:100%;padding:11px 14px;border-radius:10px;color:inherit;text-decoration:none;transition:background .2s,color .2s;cursor:pointer}
.workspace-sidebar .ws-menu-direct:hover{background:rgba(128,128,128,.1);color:inherit;text-decoration:none}
.workspace-sidebar .ws-menu-direct.active{background:rgba(128,128,128,.15);font-weight:600}
.workspace-sidebar .ws-menu-direct .ws-svg{width:18px;height:18px;flex-shrink:0}
.workspace-sidebar .ws-menu-direct .ws-menu-text{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:12px}
.workspace-sidebar .ws-menu .ws-menu-text,.workspace-sidebar .ws-children a{font-size:12px}
.workspace-sidebar .ws-children{padding-left:10px}
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
        <a class="ws-nav-dashboard <?php echo e(request()->routeIs('store.admin.dashboard')?'active':''); ?>" href="<?php echo e(route('store.admin.dashboard',['business'=>$businessKey])); ?>">
            <svg class="ws-svg"><use href="#ws-i-grid"/></svg><?php echo e(t('Dashboard','ড্যাশবোর্ড')); ?>

        </a>
        <div class="ws-nav-separator"></div>
        <div class="ws-nav-label"><?php echo e(t('WORKSPACE MODULES','ওয়ার্কস্পেস মডিউল')); ?></div>
        <?php $__empty_1 = true; $__currentLoopData = $modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php $moduleActive=$currentModuleSlug===(string)$module->slug; ?>
            <details class="ws-nav-group ws-module" <?php echo e($moduleActive?'open':''); ?>>
                <summary class="ws-module-label <?php echo e($moduleActive?'active':''); ?>">
                    <svg class="ws-svg ws-module-icon"><use href="#ws-i-folder"/></svg>
                    <span class="ws-module-name"><?php echo e(navName($module)); ?></span>
                    <em><?php echo e($module->menus->count()); ?></em>
                    <svg class="ws-svg ws-module-chevron"><use href="#ws-i-chevron"/></svg>
                </summary>
                <div class="ws-module-content">
                    <?php $__currentLoopData = $module->menus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $menu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $hasChildren=$menu->children->isNotEmpty();
                            $menuActive=$moduleActive && $currentMenuSlug===(string)$menu->slug;
                            $menuParams=['business'=>$businessKey,'module'=>$module->slug,'menu'=>$menu->slug];
                        ?>
                        <?php if($hasChildren): ?>
                            <details class="ws-menu" <?php echo e($menuActive?'open':''); ?>>
                                <summary class="<?php echo e($menuActive?'active':''); ?>">
                                    <svg class="ws-svg"><use href="#ws-i-folder"/></svg>
                                    <span class="ws-menu-text"><?php echo e(navName($menu)); ?></span>
                                    <svg class="ws-svg ws-chevron"><use href="#ws-i-chevron"/></svg>
                                </summary>
                                <div class="ws-children">
                                    <?php $__currentLoopData = $menu->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $childActive=$menuActive && $currentChildSlug===(string)$child->slug;
                                            $childParams=[...$menuParams,'child'=>$child->slug];
                                        ?>
                                        <a class="<?php echo e($childActive?'active':''); ?>" href="<?php echo e(route('store.admin.feature',$childParams)); ?>">
                                            <span><?php echo e(navName($child)); ?></span>
                                        </a>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </details>
                        <?php else: ?>
                            <a class="ws-menu-direct <?php echo e($menuActive && $currentChildSlug===''?'active':''); ?>" href="<?php echo e(route('store.admin.feature',$menuParams)); ?>">
                                <svg class="ws-svg"><use href="#ws-i-folder"/></svg>
                                <span class="ws-menu-text"><?php echo e(navName($menu)); ?></span>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </details>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="ws-search-empty"><?php echo e(t('No menu access assigned yet.','এখনও কোনো মেনু অ্যাক্সেস দেওয়া হয়নি।')); ?></p>
        <?php endif; ?>
        <p class="ws-search-empty" id="ws-search-empty" hidden><?php echo e(t('No matching menus found.','কোনো মেনু পাওয়া যায়নি।')); ?></p>
    </nav>
    <div class="ws-side-bottom">
        <div class="ws-side-user">
            <span class="ws-user-avatar"><?php echo e(mb_strtoupper(mb_substr($user->name??'A',0,1))); ?></span>
            <div class="ws-user-text">
                <strong><?php echo e($user->name); ?></strong>
                <small><?php echo e(t('Store administrator','স্টোর অ্যাডমিন')); ?></small>
            </div>
            <form method="POST" action="<?php echo e(route('store.admin.logout',['business'=>$businessKey])); ?>">
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
    const groups=[...sidebar.querySelectorAll('.ws-module')];
    const search=sidebar.querySelector('#ws-sidebar-search');
    const empty=sidebar.querySelector('#ws-search-empty');
    let searching=false;
    const moduleStates=new Map(),menuStates=new Map();
    groups.forEach(group=>{
        group.querySelector(':scope > summary')?.addEventListener('click',()=>{
            if(searching || group.open)return;
            groups.forEach(other=>{
                if(other!==group){other.open=false;other.querySelectorAll('.ws-menu').forEach(menu=>menu.open=false);}
            });
        });
        group.querySelectorAll('.ws-menu').forEach(menu=>{
            menu.querySelector(':scope > summary')?.addEventListener('click',()=>{
                if(searching || menu.open)return;
                group.querySelectorAll('.ws-menu').forEach(other=>{if(other!==menu)other.open=false;});
            });
        });
    });
    if(!search)return;
    search.addEventListener('input',function(){
        const q=this.value.trim().toLocaleLowerCase();
        if(q && !searching){
            searching=true;
            groups.forEach(group=>{
                moduleStates.set(group,group.open);
                group.querySelectorAll('.ws-menu').forEach(menu=>menuStates.set(menu,menu.open));
            });
        }
        if(!q){
            groups.forEach(group=>{
                group.hidden=false;
                group.open=searching?(moduleStates.get(group)??false):group.open;
                group.querySelectorAll('.ws-menu,.ws-menu-direct').forEach(item=>{
                    item.hidden=false;
                    if(item.matches('details'))item.open=searching?(menuStates.get(item)??false):item.open;
                });
                group.querySelectorAll('.ws-children a').forEach(child=>child.hidden=false);
            });
            searching=false;
            moduleStates.clear();menuStates.clear();
            if(empty)empty.hidden=true;
            return;
        }
        let found=false;
        groups.forEach(group=>{
            const moduleName=group.querySelector('.ws-module-name')?.textContent.toLocaleLowerCase()||'';
            const moduleMatch=moduleName.includes(q);
            let groupFound=false;
            group.querySelectorAll('.ws-menu,.ws-menu-direct').forEach(item=>{
                const menuName=item.querySelector('.ws-menu-text')?.textContent.toLocaleLowerCase()||'';
                const menuMatch=moduleMatch||menuName.includes(q);
                let childMatch=false;
                if(item.matches('details')){
                    item.querySelectorAll('.ws-children a').forEach(child=>{
                        const match=menuMatch||child.textContent.toLocaleLowerCase().includes(q);
                        child.hidden=!match;
                        if(match)childMatch=true;
                    });
                    item.hidden=!(menuMatch||childMatch);
                    if(!item.hidden)item.open=true;
                }else item.hidden=!menuMatch;
                if(!item.hidden)groupFound=true;
            });
            group.hidden=!groupFound;
            if(groupFound){group.open=true;found=true;}
        });
        if(empty)empty.hidden=found;
    });
});
</script>
<?php /**PATH C:\laragon\www\saas-backend\resources\views/public/business/admin/sidebar.blade.php ENDPATH**/ ?>
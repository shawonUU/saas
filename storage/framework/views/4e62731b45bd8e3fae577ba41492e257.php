

<?php $__env->startPush('styles'); ?>

<style>

.workspace-layout{--ws-panel:#fff;--ws-bg:#f6f8fc;--ws-text:#172138;--ws-muted:#7d879b;--ws-line:#e9edf5;--ws-accent:#635bdf;display:grid;grid-template-columns:276px minmax(0,1fr);min-height:calc(100vh - 76px);background:var(--ws-bg);color:var(--ws-text)}

.workspace-layout *{box-sizing:border-box}.workspace-layout a{text-decoration:none}.ws-svg{width:19px;height:19px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round;flex-shrink:0}.workspace-content{min-width:0;padding:32px clamp(20px,3.2vw,54px) 60px;max-width:1800px;width:100%;margin:auto}.ws-toolbar,.ws-header-actions,.ws-heading-row,.ws-section-head,.ws-side-brand,.ws-side-user,.ws-section-title,.ws-link,.ws-quick-link,.ws-module-name{display:flex;align-items:center}.ws-toolbar{justify-content:space-between;gap:16px;margin-bottom:28px}.ws-breadcrumb{display:flex;align-items:center;gap:9px;font-size:12px;color:var(--ws-muted);font-weight:700}.ws-breadcrumb strong{color:var(--ws-text)}.ws-breadcrumb .ws-svg{width:15px;height:15px}.ws-header-actions{gap:10px}.ws-button{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:40px;padding:0 15px;border:1px solid var(--ws-line);border-radius:11px;background:var(--ws-panel);color:var(--ws-text);font-size:12px;font-weight:800;cursor:pointer;transition:.2s}.ws-button:hover{border-color:#bfc3ed;color:var(--ws-accent)}.ws-button .ws-svg{width:16px;height:16px}.ws-button-primary{background:#645ce2;color:#fff;border-color:#645ce2}.ws-button-primary:hover{background:#5149cb;color:#fff}.ws-mobile-menu{display:none}.ws-hero{position:relative;display:grid;grid-template-columns:minmax(0,1fr) 300px;gap:25px;align-items:center;overflow:hidden;padding:37px 42px;min-height:244px;border-radius:22px;background:linear-gradient(113deg,#171f3e 0%,#252751 64%,#363477 100%);color:#fff;box-shadow:0 16px 35px rgba(31,39,76,.08)}.ws-hero:after{content:"";position:absolute;width:370px;height:370px;right:-110px;top:-180px;border:1px solid rgba(255,255,255,.12);border-radius:50%;pointer-events:none}.ws-hero-copy{position:relative;z-index:2}.ws-eyebrow{display:inline-flex;align-items:center;gap:8px;color:#c7cafa;font-size:11px;letter-spacing:1.5px;font-weight:800;text-transform:uppercase}.ws-eyebrow:before{content:"";width:7px;height:7px;border-radius:50%;background:#9ee5cb;box-shadow:0 0 0 4px rgba(158,229,203,.13)}.ws-hero h1{font-size:clamp(26px,3vw,38px);line-height:1.18;letter-spacing:-1.5px;margin:15px 0 12px;font-family:Manrope,'DM Sans',sans-serif;font-weight:800}.ws-hero p{color:#c6cbe5;font-size:13px;line-height:1.85;max-width:560px}.ws-hero-meta{display:flex;flex-wrap:wrap;gap:9px;margin-top:22px}.ws-hero-chip{display:inline-flex;gap:7px;align-items:center;padding:8px 11px;border:1px solid rgba(255,255,255,.16);background:rgba(255,255,255,.07);border-radius:9px;color:#e8eafd;font-size:11px;font-weight:700}.ws-hero-chip .ws-svg{width:14px;height:14px}.ws-hero-art{position:relative;height:160px;display:flex;align-items:center;justify-content:center}.ws-art-circle{position:relative;width:156px;height:156px;border-radius:36px;display:grid;place-items:center;background:linear-gradient(140deg,rgba(255,255,255,.2),rgba(255,255,255,.05));border:1px solid rgba(255,255,255,.22);box-shadow:0 17px 50px rgba(0,0,0,.13);transform:rotate(-10deg)}.ws-art-circle .ws-svg{width:72px;height:72px;color:#dce1ff;stroke-width:1.2;transform:rotate(10deg)}.ws-art-float{position:absolute;background:#fff;color:#252a57;display:flex;align-items:center;justify-content:center;width:57px;height:57px;box-shadow:0 10px 34px rgba(11,16,48,.18);border-radius:18px}.ws-art-float .ws-svg{width:26px;height:26px}.ws-art-float.one{top:0;right:2px;transform:rotate(12deg);color:#655ce1}.ws-art-float.two{bottom:0;left:0;transform:rotate(-12deg);color:#17a985}.ws-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin:22px 0 32px}.ws-stat{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;background:var(--ws-panel);border:1px solid var(--ws-line);border-radius:17px;padding:23px 23px 20px;min-height:145px;box-shadow:0 5px 18px rgba(29,40,75,.025)}.ws-stat small{color:var(--ws-muted);font-weight:700;font-size:12px}.ws-stat strong{display:block;font-family:Manrope,'DM Sans',sans-serif;font-size:32px;line-height:1.2;letter-spacing:-1.2px;margin:12px 0 7px;font-weight:800}.ws-stat p{color:var(--ws-muted);font-size:11px;line-height:1.5}.ws-stat-icon{width:45px;height:45px;border-radius:13px;display:grid;place-items:center;flex-shrink:0}.ws-stat-icon.violet{background:#efedff;color:#6057d9}.ws-stat-icon.teal{background:#e3f7f3;color:#159981}.ws-stat-icon.orange{background:#fff2e6;color:#e39542}.ws-lower-grid{display:grid;grid-template-columns:minmax(0,1.35fr) minmax(290px,.85fr);gap:20px;align-items:start}.ws-panel{background:var(--ws-panel);border:1px solid var(--ws-line);border-radius:18px;padding:25px;min-width:0}.ws-panel + .ws-panel{margin-top:0}.ws-section-head{justify-content:space-between;gap:16px;margin-bottom:20px}.ws-section-title{gap:10px}.ws-title-icon{width:34px;height:34px;display:grid;place-items:center;border-radius:10px;background:#f0efff;color:#6157d8}.ws-title-icon .ws-svg{width:17px;height:17px}.ws-section-head h2{font-family:Manrope,'DM Sans',sans-serif;font-size:16px;letter-spacing:-.4px}.ws-section-head p{font-size:11px;color:var(--ws-muted);margin-top:5px;line-height:1.5}.ws-mini-count{background:#f5f6fc;border:1px solid var(--ws-line);border-radius:9px;padding:7px 10px;font-size:11px;color:var(--ws-muted);font-weight:800;white-space:nowrap}.ws-quick-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.ws-quick-link{gap:12px;min-width:0;border:1px solid var(--ws-line);background:var(--ws-panel);padding:14px 12px;border-radius:12px;transition:.2s}.ws-quick-link:hover{border-color:#bbb5f5;background:rgba(99,91,223,.035);transform:translateY(-2px)}.ws-quick-symbol{width:38px;height:38px;display:grid;place-items:center;border-radius:11px;background:#f0efff;color:#635bdf;flex-shrink:0}.ws-quick-symbol .ws-svg{width:18px;height:18px}.ws-quick-info{min-width:0;flex:1}.ws-quick-info strong{display:block;white-space:nowrap;text-overflow:ellipsis;overflow:hidden;font-size:12px;color:var(--ws-text)}.ws-quick-info small{display:block;margin-top:5px;color:var(--ws-muted);font-size:10px;white-space:nowrap;text-overflow:ellipsis;overflow:hidden}.ws-quick-link>.ws-svg{width:14px;height:14px;color:#a9b0c0}.ws-module-list{display:flex;flex-direction:column;gap:10px}.ws-module-card{display:flex;align-items:center;gap:12px;border:1px solid var(--ws-line);border-radius:13px;padding:13px 12px;min-width:0;transition:.2s}.ws-module-card:hover{border-color:#c7c4ec}.ws-module-avatar{display:grid;place-items:center;width:42px;height:42px;flex-shrink:0;border-radius:12px;background:#eae9ff;color:#6259d7;font-size:16px;font-weight:800}.ws-module-card:nth-child(3n+2) .ws-module-avatar{background:#e3f8f3;color:#16987f}.ws-module-card:nth-child(3n+3) .ws-module-avatar{background:#fff0e5;color:#d68a3b}.ws-module-info{flex:1;min-width:0}.ws-module-info strong{display:block;font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.ws-module-info span{display:block;font-size:11px;color:var(--ws-muted);margin-top:6px}.ws-module-arrow{display:grid;place-items:center;width:28px;height:28px;border:1px solid var(--ws-line);border-radius:8px;color:#8d95a7;flex-shrink:0}.ws-module-arrow .ws-svg{width:15px;height:15px}.ws-module-arrow.disabled{opacity:.45}.ws-note{display:flex;gap:11px;align-items:flex-start;margin-top:17px;background:#f7f8ff;border-radius:12px;padding:14px;color:#70798f;font-size:11px;line-height:1.7}.ws-note .ws-svg{width:17px;height:17px;color:#635bdf;margin-top:1px}.ws-empty-state{text-align:center;border:1px dashed var(--ws-line);border-radius:13px;padding:34px 20px;color:var(--ws-muted);font-size:12px;line-height:1.8}.ws-empty-state .ws-svg{width:30px;height:30px;margin-bottom:10px;color:#a6a9c1}.ws-empty-state strong{display:block;color:var(--ws-text);font-size:13px;margin-bottom:3px}.ws-footer-note{display:flex;justify-content:space-between;gap:15px;margin-top:24px;color:var(--ws-muted);font-size:11px}.ws-footer-note strong{color:var(--ws-text)}

/* Sidebar */

.workspace-sidebar{position:sticky;top:76px;height:calc(100vh - 76px);background:var(--ws-panel);border-right:1px solid var(--ws-line);display:flex;flex-direction:column;min-width:0;z-index:30}.ws-side-top{padding:25px 18px 17px;border-bottom:1px solid var(--ws-line)}.ws-side-brand{gap:11px;min-width:0}.ws-side-logo{display:grid;place-items:center;width:43px;height:43px;border-radius:13px;background:#eeeaff;color:#655bdd;font-size:20px;font-weight:800;flex-shrink:0;object-fit:contain}.ws-side-brand-text{min-width:0;flex:1}.ws-side-brand-text strong{display:block;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.ws-side-brand-text small{display:block;font-size:10px;margin-top:5px;color:var(--ws-muted);font-weight:700}.ws-close-sidebar{display:none;border:0;background:transparent;color:var(--ws-muted);padding:5px;cursor:pointer}.ws-side-search{position:relative;margin-top:24px}.ws-side-search .ws-svg{position:absolute;top:12px;left:13px;width:17px;height:17px;color:#98a0b0}.ws-side-search input{width:100%;height:41px;border:1px solid var(--ws-line);outline:0;background:var(--ws-bg);color:var(--ws-text);border-radius:11px;padding:0 12px 0 40px;font-family:inherit;font-size:12px}.ws-side-search input:focus{border-color:#a49efa;box-shadow:0 0 0 3px rgba(99,91,223,.09)}.ws-side-scroll{flex:1;overflow:auto;scrollbar-width:thin;padding:18px 12px 14px}.ws-nav-label{padding:0 11px;margin:2px 0 9px;color:#a0a7b8;font-weight:800;font-size:10px;letter-spacing:1.2px;text-transform:uppercase}.ws-nav-dashboard{display:flex;gap:12px;align-items:center;padding:12px 13px;border-radius:11px;font-size:12px;font-weight:800;color:#596276;transition:.2s}.ws-nav-dashboard .ws-svg{width:18px;height:18px}.ws-nav-dashboard:hover,.ws-nav-dashboard.active{background:#eeeefe;color:#5b53d8}.ws-nav-separator{height:1px;background:var(--ws-line);margin:16px 10px}.ws-nav-group{margin:0 0 11px}.ws-module-label{display:flex;justify-content:space-between;align-items:center;gap:8px;padding:11px 12px 8px;font-size:10px;color:#a0a7b8;letter-spacing:.8px;text-transform:uppercase;font-weight:800}.ws-module-label span:first-child{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.ws-module-label em{font-style:normal;font-size:10px;color:#a4a9b8}.ws-menu{margin:2px 0}.ws-menu summary{list-style:none;display:flex;gap:10px;align-items:center;cursor:pointer;padding:11px 13px;color:#596276;font-size:12px;font-weight:700;border-radius:10px;transition:.2s}.ws-menu summary::-webkit-details-marker{display:none}.ws-menu summary:hover{background:var(--ws-bg);color:var(--ws-accent)}.ws-menu summary>.ws-svg:first-child{width:16px;height:16px;color:#929bb0}.ws-menu summary .ws-menu-text{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.ws-menu summary .ws-chevron{width:14px;height:14px;color:#a1a8b6;transition:transform .2s}.ws-menu[open] summary .ws-chevron{transform:rotate(180deg)}.ws-children{display:flex;flex-direction:column;gap:2px;padding:3px 0 5px 30px}.ws-children a{display:flex;align-items:center;gap:9px;border-radius:9px;padding:9px 10px;font-size:11px;color:#7c859a;min-width:0;transition:.2s}.ws-children a:before{content:"";width:5px;height:5px;background:#cad0de;border-radius:50%;flex-shrink:0}.ws-children a span{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.ws-children a:hover,.ws-children a.active{color:#5b54d6;background:#f0efff;font-weight:800}.ws-children a.active:before{background:#665de2}.ws-menu[hidden],.ws-nav-group[hidden],.ws-children a[hidden]{display:none!important}.ws-search-empty{font-size:11px;color:var(--ws-muted);padding:15px;text-align:center}.ws-side-bottom{border-top:1px solid var(--ws-line);padding:15px 17px 18px}.ws-side-user{gap:10px;min-width:0}.ws-user-avatar{display:grid;place-items:center;width:38px;height:38px;border-radius:11px;background:#ebeafd;color:#6259d7;font-size:15px;font-weight:800;flex-shrink:0}.ws-user-text{flex:1;min-width:0}.ws-user-text strong{display:block;font-size:11px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.ws-user-text small{display:block;font-size:10px;color:var(--ws-muted);margin-top:5px}.ws-signout{width:31px;height:31px;display:grid;place-items:center;border:1px solid var(--ws-line);border-radius:9px;background:var(--ws-panel);color:var(--ws-muted);cursor:pointer}.ws-signout:hover{color:#d45a69;border-color:#f3cbd0}.ws-signout .ws-svg{width:16px;height:16px}.ws-sidebar-overlay{display:none}

html[data-theme="dark"] .workspace-layout{--ws-panel:#1c2435;--ws-bg:#111827;--ws-text:#eef1fb;--ws-muted:#a0aec0;--ws-line:#344055;--ws-accent:#aca7ff}html[data-theme="dark"] .ws-nav-dashboard.active,html[data-theme="dark"] .ws-nav-dashboard:hover,html[data-theme="dark"] .ws-children a.active,html[data-theme="dark"] .ws-children a:hover{background:#34305e;color:#dedaff}html[data-theme="dark"] .ws-title-icon,html[data-theme="dark"] .ws-quick-symbol,html[data-theme="dark"] .ws-side-logo,html[data-theme="dark"] .ws-user-avatar{background:#34305e;color:#d8d5ff}html[data-theme="dark"] .ws-mini-count,html[data-theme="dark"] .ws-note{background:#222b40}html[data-theme="dark"] .ws-stat-icon.violet{background:#37305b}html[data-theme="dark"] .ws-stat-icon.teal{background:#163f3c}html[data-theme="dark"] .ws-stat-icon.orange{background:#4c3526}html[data-theme="dark"] .ws-module-avatar{background:#34305e}html[data-theme="dark"] .ws-menu summary:hover{background:#252f43}

@media(max-width:1200px){.ws-lower-grid{grid-template-columns:1fr}.ws-quick-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}

@media(max-width:1050px){.workspace-layout{display:block}.workspace-sidebar{position:fixed;top:0;left:0;width:min(310px,86vw);height:100dvh;z-index:220;transform:translateX(-105%);transition:transform .25s}.ws-sidebar-open .workspace-sidebar{transform:translateX(0)}.ws-sidebar-open{overflow:hidden}.ws-sidebar-open .ws-sidebar-overlay{display:block;position:fixed;inset:0;background:rgba(12,18,37,.57);z-index:210}.ws-close-sidebar,.ws-mobile-menu{display:inline-flex}.workspace-content{padding:26px 23px 45px}}

@media(max-width:680px){.workspace-content{padding:18px 14px 36px}.ws-toolbar{margin-bottom:17px}.ws-header-actions{gap:7px}.ws-header-actions .ws-store-label{display:none}.ws-header-actions .ws-button{padding:0 11px;min-height:38px}.ws-breadcrumb{font-size:11px}.ws-hero{grid-template-columns:1fr;padding:27px 25px;min-height:0;border-radius:17px}.ws-hero h1{font-size:27px}.ws-hero-art{display:none}.ws-stats{gap:10px;margin:14px 0 19px;grid-template-columns:repeat(2,minmax(0,1fr))}.ws-stat{padding:16px;min-height:129px}.ws-stat:nth-child(3){grid-column:1 / -1}.ws-stat strong{font-size:26px}.ws-stat-icon{width:37px;height:37px}.ws-stat-icon .ws-svg{width:17px;height:17px}.ws-panel{padding:17px;border-radius:15px}.ws-lower-grid{gap:15px}.ws-quick-grid{grid-template-columns:1fr}.ws-footer-note{flex-direction:column;gap:6px}}

</style>

<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>





<?php
    $menuCount=$modules->sum(fn($module)=>$module->menus->count());
    $pageCount=$modules->sum(fn($module)=>$module->menus->sum(fn($menu)=>$menu->children->count()));
    $quickPages=$modules->flatMap(fn($module)=>$module->menus->flatMap(fn($menu)=>$menu->children->map(fn($child)=>['child'=>$child,'module'=>navName($module),'menu'=>navName($menu)])))->take(8);
    $storefrontUrl=request()->getScheme().'://'.preg_replace('/^admin\./i','',request()->getHttpHost());
    $businessKey=request()->route('business');
    $businessKey=is_object($businessKey)?(string)($businessKey->domain ?? $businessKey->slug ?? ''):(string)$businessKey;
    $businessKey=trim($businessKey);
    $featureParams=[];
    foreach($modules as $module){
        foreach($module->menus as $menu){
            foreach($menu->children as $child){
                if(empty($module->slug) || empty($menu->slug) || empty($child->slug))continue;
                $featureParams[$child->id]=[
                    'business'=>$businessKey,
                    'module'=>$module->slug,
                    'menu'=>$menu->slug,
                    'child'=>$child->slug
                ];
            }
        }
    }
    $adminLink=function($child) use($featureParams,$businessKey){
        if($child->route==='/dashboard')return route('store.admin.dashboard',['business'=>$businessKey]);
        if(!isset($featureParams[$child->id]))return '#';
        return route('store.admin.feature',$featureParams[$child->id]);
    };
?>





<svg xmlns="http://www.w3.org/2000/svg" style="position:absolute;width:0;height:0;overflow:hidden" aria-hidden="true">

    <symbol id="ws-i-grid" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></symbol>

    <symbol id="ws-i-layer" viewBox="0 0 24 24"><path d="m12 2 9 5-9 5-9-5 9-5ZM3 12l9 5 9-5M3 17l9 5 9-5"/></symbol>

    <symbol id="ws-i-list" viewBox="0 0 24 24"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></symbol>

    <symbol id="ws-i-home" viewBox="0 0 24 24"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V10ZM9 21v-8h6v8"/></symbol>

    <symbol id="ws-i-store" viewBox="0 0 24 24"><path d="M3 10h18l-2-6H5l-2 6ZM5 10v11h14V10M9 21v-7h6v7M3 10c0 2 3 3 4.5 1 1.5 2 4.5 2 6 0 1.5 2 4.5 1 4.5-1"/></symbol>

    <symbol id="ws-i-arrow" viewBox="0 0 24 24"><path d="M7 17 17 7M8 7h9v9"/></symbol>

    <symbol id="ws-i-right" viewBox="0 0 24 24"><path d="M5 12h14m-6-6 6 6-6 6"/></symbol>

    <symbol id="ws-i-menu" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/></symbol>

    <symbol id="ws-i-x" viewBox="0 0 24 24"><path d="M5 5l14 14M19 5 5 19"/></symbol>

    <symbol id="ws-i-search" viewBox="0 0 24 24"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></symbol>

    <symbol id="ws-i-chevron" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></symbol>

    <symbol id="ws-i-logout" viewBox="0 0 24 24"><path d="M9 4H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h4m5-4 4-4-4-4m4 4H9"/></symbol>

    <symbol id="ws-i-folder" viewBox="0 0 24 24"><path d="M3 6a2 2 0 0 1 2-2h5l2 3h7a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6Z"/></symbol>

    <symbol id="ws-i-info" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 11v6m0-10v.01"/></symbol>

</svg>

<div class="workspace-layout">

    <?php echo $__env->make('public.business.admin.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div>

    <div class="workspace-content">

        <div class="ws-toolbar">

            <div class="ws-header-actions"><button type="button" class="ws-button ws-mobile-menu" data-ws-sidebar-toggle aria-label="<?php echo e(t('Open navigation','নেভিগেশন খুলুন')); ?>"><svg class="ws-svg"><use href="#ws-i-menu"/></svg></button><div class="ws-breadcrumb"><svg class="ws-svg"><use href="#ws-i-home"/></svg><span><?php echo e(t('Workspace','ওয়ার্কস্পেস')); ?></span><span>/</span><strong><?php echo e(t('Dashboard','ড্যাশবোর্ড')); ?></strong></div></div>

            <a href="<?php echo e($storefrontUrl); ?>" target="_blank" rel="noopener noreferrer" class="ws-button"><svg class="ws-svg"><use href="#ws-i-store"/></svg><span class="ws-store-label"><?php echo e(t('Visit Store','স্টোর দেখুন')); ?></span><svg class="ws-svg"><use href="#ws-i-arrow"/></svg></a>

        </div>

        <section class="ws-hero">

            <div class="ws-hero-copy"><span class="ws-eyebrow"><?php echo e(t('Your business workspace','আপনার ব্যবসার ওয়ার্কস্পেস')); ?></span><h1><?php echo e(t('Welcome back','আবার স্বাগতম')); ?>, <?php echo e($user->name); ?>!</h1><p><?php echo e(t('Everything you need to manage','পরিচালনার জন্য প্রয়োজনীয় সবকিছু')); ?> <?php echo e($business->name); ?> <?php echo e(t('is right here. Access your assigned tools and stay organized from one place.','এখানেই রয়েছে। আপনার অনুমোদিত টুল ও কাজগুলো এক জায়গা থেকে পরিচালনা করুন।')); ?></p><div class="ws-hero-meta"><span class="ws-hero-chip"><svg class="ws-svg"><use href="#ws-i-store"/></svg><?php echo e($business->category?->name ?? t('Business','ব্যবসা')); ?></span><span class="ws-hero-chip"><?php echo e(now()->format('d M Y')); ?></span></div></div>

            <div class="ws-hero-art" aria-hidden="true"><div class="ws-art-circle"><svg class="ws-svg"><use href="#ws-i-store"/></svg></div><div class="ws-art-float one"><svg class="ws-svg"><use href="#ws-i-grid"/></svg></div><div class="ws-art-float two"><svg class="ws-svg"><use href="#ws-i-layer"/></svg></div></div>

        </section>

        <section class="ws-stats" aria-label="<?php echo e(t('Workspace statistics','ওয়ার্কস্পেস পরিসংখ্যান')); ?>">

            <div class="ws-stat"><div><small><?php echo e(t('Available modules','উপলব্ধ মডিউল')); ?></small><strong><?php echo e($modules->count()); ?></strong><p><?php echo e(t('Business tools','ব্যবসা পরিচালনার টুল')); ?></p></div><span class="ws-stat-icon violet"><svg class="ws-svg"><use href="#ws-i-layer"/></svg></span></div>

            <div class="ws-stat"><div><small><?php echo e(t('Navigation menus','নেভিগেশন মেনু')); ?></small><strong><?php echo e($menuCount); ?></strong><p><?php echo e(t('Organized menu groups','সাজানো মেনু গ্রুপ')); ?></p></div><span class="ws-stat-icon teal"><svg class="ws-svg"><use href="#ws-i-list"/></svg></span></div>

            <div class="ws-stat"><div><small><?php echo e(t('Accessible pages','অ্যাক্সেসযোগ্য পেজ')); ?></small><strong><?php echo e($pageCount); ?></strong><p><?php echo e(t('Pages in your workspace','আপনার ওয়ার্কস্পেসের পেজ')); ?></p></div><span class="ws-stat-icon orange"><svg class="ws-svg"><use href="#ws-i-grid"/></svg></span></div>

        </section>

        <div class="ws-lower-grid">

            <section class="ws-panel">

                <div class="ws-section-head"><div class="ws-section-title"><span class="ws-title-icon"><svg class="ws-svg"><use href="#ws-i-grid"/></svg></span><div><h2><?php echo e(t('Quick access','দ্রুত প্রবেশ')); ?></h2><p><?php echo e(t('Open your available pages in one click','এক ক্লিকে আপনার পেজগুলো খুলুন')); ?></p></div></div><span class="ws-mini-count"><?php echo e($quickPages->count()); ?> <?php echo e(t('shortcuts','শর্টকাট')); ?></span></div>

                <?php if($quickPages->isNotEmpty()): ?><div class="ws-quick-grid"><?php $__currentLoopData = $quickPages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><a class="ws-quick-link" href="<?php echo e($adminLink($entry['child'])); ?>"><span class="ws-quick-symbol"><svg class="ws-svg"><use href="#ws-i-folder"/></svg></span><span class="ws-quick-info"><strong><?php echo e(navName($entry['child'])); ?></strong><small><?php echo e($entry['module']); ?> · <?php echo e($entry['menu']); ?></small></span><svg class="ws-svg"><use href="#ws-i-arrow"/></svg></a><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div>

                <?php else: ?><div class="ws-empty-state"><svg class="ws-svg"><use href="#ws-i-folder"/></svg><strong><?php echo e(t('No pages available yet','এখনও কোনো পেজ নেই')); ?></strong><?php echo e(t('Assigned pages will appear here automatically.','অ্যাক্সেস দেওয়া হলে এখানে পেজ দেখা যাবে।')); ?></div><?php endif; ?>

            </section>

            <section class="ws-panel">

                <div class="ws-section-head"><div class="ws-section-title"><span class="ws-title-icon"><svg class="ws-svg"><use href="#ws-i-layer"/></svg></span><div><h2><?php echo e(t('Your modules','আপনার মডিউল')); ?></h2><p><?php echo e(t('Explore available business sections','ব্যবসার বিভাগগুলো দেখুন')); ?></p></div></div><span class="ws-mini-count"><?php echo e($modules->count()); ?></span></div>

                <?php if($modules->isNotEmpty()): ?><div class="ws-module-list"><?php $__currentLoopData = $modules->take(6); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php ($firstChild=$module->menus->flatMap(fn($menu)=>$menu->children)->first()); ?><div class="ws-module-card"><span class="ws-module-avatar"><?php echo e(mb_strtoupper(mb_substr(navName($module),0,1))); ?></span><div class="ws-module-info"><strong><?php echo e(navName($module)); ?></strong><span><?php echo e($module->menus->count()); ?> <?php echo e(t('menus','মেনু')); ?> · <?php echo e($module->menus->sum(fn($menu)=>$menu->children->count())); ?> <?php echo e(t('pages','পেজ')); ?></span></div><?php if($firstChild): ?><a class="ws-module-arrow" href="<?php echo e($adminLink($firstChild)); ?>" aria-label="<?php echo e(t('Open','খুলুন')); ?> <?php echo e(navName($module)); ?>"><svg class="ws-svg"><use href="#ws-i-right"/></svg></a><?php else: ?><span class="ws-module-arrow disabled"><svg class="ws-svg"><use href="#ws-i-right"/></svg></span><?php endif; ?></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div>

                <?php if($modules->count()>6): ?><p style="color:var(--ws-muted);font-size:11px;margin-top:14px">+<?php echo e($modules->count()-6); ?> <?php echo e(t('more modules in sidebar','টি মডিউল সাইডবারে রয়েছে')); ?></p><?php endif; ?>

                <?php else: ?><div class="ws-empty-state"><svg class="ws-svg"><use href="#ws-i-layer"/></svg><strong><?php echo e(t('No modules assigned','কোনো মডিউল অ্যাসাইন করা হয়নি')); ?></strong><?php echo e(t('Your available modules will appear here.','উপলব্ধ মডিউল এখানে দেখা যাবে।')); ?></div><?php endif; ?>

                <div class="ws-note"><svg class="ws-svg"><use href="#ws-i-info"/></svg><span><?php echo e(t('Only the modules and pages available to your account are shown.','আপনার অ্যাকাউন্টে অনুমোদিত মডিউল ও পেজগুলোই এখানে দেখানো হচ্ছে।')); ?></span></div>

            </section>

        </div>

        <div class="ws-footer-note"><span>© <?php echo e(date('Y')); ?> <strong><?php echo e($business->name); ?></strong></span><span><?php echo e(t('Business administration workspace','ব্যবসা পরিচালনার ওয়ার্কস্পেস')); ?></span></div>

    </div>

</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded',function(){
    const body=document.body;
    const sidebar=document.querySelector('.workspace-sidebar');
    document.querySelectorAll('[data-ws-sidebar-toggle],[data-ws-sidebar-overlay]').forEach(el=>{
        el.addEventListener('click',()=>body.classList.toggle('ws-sidebar-open'));
    });
    document.addEventListener('keydown',e=>{
        if(e.key==='Escape')body.classList.remove('ws-sidebar-open');
    });
    sidebar?.querySelectorAll('a').forEach(a=>{
        a.addEventListener('click',()=>body.classList.remove('ws-sidebar-open'));
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('public.business.admin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\saas-backend\resources\views/public/business/admin/dashboard.blade.php ENDPATH**/ ?>
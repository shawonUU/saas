@php
    // Use existing tenant/navigation context; never load another business's menu.
    $sideBusiness = $business ?? request()->attributes->get('current_business');
    $sideUser = $user ?? \Illuminate\Support\Facades\Auth::guard('business_admin')->user();
    $sideBusinessKey = (string)(data_get($sideBusiness, 'domain') ?: request()->route('business'));
    $sideName = (string)(data_get($sideBusiness, 'name') ?: t('My Store','আমার দোকান'));
    $sideLogoPath = data_get($sideBusiness, 'logo') ?: data_get($sideBusiness, 'logo_path') ?: data_get($sideBusiness, 'business_logo');
    $sideLogo = $sideLogoPath ? (preg_match('~^https?://~i', $sideLogoPath) ? $sideLogoPath : (str_starts_with($sideLogoPath, '/') ? url($sideLogoPath) : asset(str_starts_with($sideLogoPath, 'storage/') || str_starts_with($sideLogoPath, 'images/') ? $sideLogoPath : 'storage/'.$sideLogoPath))) : null;
    $sideModule = (string)(request()->route('module') ?: request()->segment(1));
    $sideMenu = (string)(request()->route('menu') ?: request()->segment(2));
    $sideChild = (string)(request()->route('child') ?: request()->segment(3));
    $sideModuleIcons = [
        'configurations'=>'sliders', 'sales'=>'cart', 'pos'=>'monitor', 'inventory'=>'boxes',
        'purchasing'=>'truck', 'finance'=>'wallet', 'ecommerce'=>'store', 'restaurant'=>'store',
        'pharmacy'=>'boxes', 'education'=>'book', 'laundry'=>'boxes', 'hr'=>'users',
        'assets'=>'boxes', 'support'=>'help', 'authorization'=>'shield', 'settings'=>'sliders',
        'analytics'=>'chart', 'crm'=>'users', 'logistics'=>'truck', 'kitchen'=>'store'
    ];
@endphp
<aside class="workspace-sidebar" aria-label="{{ t('Business navigation','বিজনেস নেভিগেশন') }}" id="ws-sidebar-navigation">
    <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="ws-sidebar-sprite">
        <symbol id="rs-i-grid" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></symbol>
        <symbol id="rs-i-sliders" viewBox="0 0 24 24"><path d="M4 7h16M4 17h16M9 4v6m6 4v6"/><circle cx="9" cy="7" r="2"/><circle cx="15" cy="17" r="2"/></symbol>
        <symbol id="rs-i-cart" viewBox="0 0 24 24"><path d="M2.5 4h2l2.6 11.5h12.2L22 8H6"/><circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></symbol>
        <symbol id="rs-i-monitor" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></symbol>
        <symbol id="rs-i-boxes" viewBox="0 0 24 24"><path d="m12 2 9 5-9 5-9-5 9-5Zm-9 5v10l9 5 9-5V7M12 12v10M7.5 4.5l9 5"/></symbol>
        <symbol id="rs-i-truck" viewBox="0 0 24 24"><path d="M3 5h12v12H3zM15 9h4l3 4v4h-7M3 17h2m8 0h4"/><circle cx="8" cy="18" r="2"/><circle cx="19" cy="18" r="2"/></symbol>
        <symbol id="rs-i-wallet" viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="15" rx="2"/><path d="M3 10h18M5 6V4a1 1 0 0 1 1-1h12"/><path d="M16 15h3"/></symbol>
        <symbol id="rs-i-store" viewBox="0 0 24 24"><path d="m3 10 2-6h14l2 6v11H3V10ZM8 21v-7h8v7M3 10c0 2 3 3 4.5 1 1.5 2 4.5 2 6 0 1.5 2 4.5 1 4.5-1"/></symbol>
        <symbol id="rs-i-users" viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"/><path d="M3 21v-2a6 6 0 0 1 12 0v2M17 6a3 3 0 0 1 0 6m1 4a4 4 0 0 1 3 4v1"/></symbol>
        <symbol id="rs-i-book" viewBox="0 0 24 24"><path d="M12 6c-2-2-6-2-9-1v14c3-1 7-1 9 1 2-2 6-2 9-1V5c-3-1-7-1-9 1ZM12 6v14"/></symbol>
        <symbol id="rs-i-shield" viewBox="0 0 24 24"><path d="m12 2 9 4v6c0 5-3.5 8-9 10-5.5-2-9-5-9-10V6l9-4Z"/><path d="m8 12 3 3 5-6"/></symbol>
        <symbol id="rs-i-chart" viewBox="0 0 24 24"><path d="M4 20V4m0 16h17M8 16v-4m5 4V7m5 9v-6"/></symbol>
        <symbol id="rs-i-help" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M9.6 9a2.5 2.5 0 0 1 4.9.7c0 2-2.5 2.3-2.5 4.3M12 17.5v.2"/></symbol>
        <symbol id="rs-i-folder" viewBox="0 0 24 24"><path d="M3 6a2 2 0 0 1 2-2h5l2 3h7a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6Z"/></symbol>
        <symbol id="rs-i-chevron" viewBox="0 0 24 24"><path d="m9 6 6 6-6 6"/></symbol>
        <symbol id="rs-i-search" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m16.5 16.5 4.5 4.5"/></symbol>
        <symbol id="rs-i-x" viewBox="0 0 24 24"><path d="M5 5l14 14M19 5 5 19"/></symbol>
        <symbol id="rs-i-logout" viewBox="0 0 24 24"><path d="M9 4H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h4m5-4 4-4-4-4m4 4H9"/></symbol>
    </svg>
    <div class="ws-side-top">
        <label class="ws-side-search">
            <svg class="ws-svg" aria-hidden="true"><use href="#rs-i-search"/></svg>
            <input type="search" id="ws-sidebar-search" placeholder="{{ t('Search menu...','মেনু খুঁজুন...') }}" autocomplete="off" aria-label="{{ t('Search menu','মেনু খুঁজুন') }}">
        </label>
    </div>
    <nav class="ws-side-scroll" aria-label="{{ t('Business modules','ব্যবসার মডিউল') }}">
        <a class="ws-nav-dashboard {{ request()->routeIs('business.admin.dashboard')?'active':'' }}" href="{{ route('business.admin.dashboard',['business'=>$sideBusinessKey]) }}" @if(request()->routeIs('business.admin.dashboard')) aria-current="page" @endif>
            <svg class="ws-svg" aria-hidden="true"><use href="#rs-i-grid"/></svg><span>{{ t('Dashboard','ড্যাশবোর্ড') }}</span>
        </a>
       
        @forelse(($modules ?? collect()) as $module)
            @php
                $moduleActive = $sideModule === (string)$module->slug;
                $moduleIcon = $sideModuleIcons[(string)$module->slug] ?? 'grid';
            @endphp
            <details class="ws-nav-group ws-module" data-ws-module @if($moduleActive) open @endif>
                <summary class="ws-module-label {{ $moduleActive?'active':'' }}">
                    <span class="ws-module-icon-wrap"><svg class="ws-svg ws-module-icon" aria-hidden="true"><use href="#rs-i-{{ $moduleIcon }}"/></svg></span>
                    <span class="ws-module-name" title="{{ navName($module) }}">{{ navName($module) }}</span>
                    <span class="ws-module-count">{{ $module->menus->count() }}</span>
                    <svg class="ws-svg ws-module-chevron" aria-hidden="true"><use href="#rs-i-chevron"/></svg>
                </summary>
                <div class="ws-module-content">
                    @foreach($module->menus as $menu)
                        @php
                            $menuActive = $moduleActive && $sideMenu === (string)$menu->slug;
                            $params = ['business'=>$sideBusinessKey,'module'=>$module->slug,'menu'=>$menu->slug];
                        @endphp
                        @if($menu->children->isNotEmpty())
                            <details class="ws-menu" data-ws-menu @if($menuActive) open @endif>
                                <summary class="{{ $menuActive?'active':'' }}">
                                    <svg class="ws-svg" aria-hidden="true"><use href="#rs-i-folder"/></svg>
                                    <span class="ws-menu-text" title="{{ navName($menu) }}">{{ navName($menu) }}</span>
                                    <svg class="ws-svg ws-chevron" aria-hidden="true"><use href="#rs-i-chevron"/></svg>
                                </summary>
                                <div class="ws-children">
                                    @foreach($menu->children as $child)
                                        @php $childActive = $menuActive && $sideChild === (string)$child->slug; @endphp
                                        <a class="{{ $childActive?'active':'' }}" href="{{ route('business.admin.feature',$params+['child'=>$child->slug]) }}" title="{{ navName($child) }}" @if($childActive) aria-current="page" @endif><span>{{ navName($child) }}</span></a>
                                    @endforeach
                                </div>
                            </details>
                        @else
                            <a class="ws-menu-direct {{ $menuActive?'active':'' }}" href="{{ route('business.admin.feature',$params) }}" title="{{ navName($menu) }}" @if($menuActive) aria-current="page" @endif>
                                <svg class="ws-svg" aria-hidden="true"><use href="#rs-i-folder"/></svg><span class="ws-menu-text">{{ navName($menu) }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </details>
        @empty
            <p class="ws-search-empty">{{ t('No menu access assigned yet.','এখনও কোনো মেনু অ্যাক্সেস দেওয়া হয়নি।') }}</p>
        @endforelse
        <p class="ws-search-empty" id="ws-search-empty" hidden>{{ t('No matching menus found.','কোনো মেনু পাওয়া যায়নি।') }}</p>
    </nav>
    <div class="ws-side-bottom">
        <div class="ws-side-user">
            <span class="ws-user-avatar">{{ mb_strtoupper(mb_substr($sideUser?->name ?? 'A',0,1)) }}</span>
            <span class="ws-user-text"><strong title="{{ $sideUser?->name ?? 'Admin' }}">{{ $sideUser?->name ?? 'Admin' }}</strong><small>{{ t('Store administrator','স্টোর অ্যাডমিন') }}</small></span>
            <form method="POST" action="{{ route('business.admin.logout',['business'=>$sideBusinessKey]) }}">@csrf
                <button class="ws-signout" type="submit" title="{{ t('Log out','লগআউট') }}" aria-label="{{ t('Log out','লগআউট') }}"><svg class="ws-svg" aria-hidden="true"><use href="#rs-i-logout"/></svg></button>
            </form>
        </div>
    </div>
</aside>
<style>
/* Consistent navigation on Dashboard, Configurations, Retail, E-commerce and Accounts. */
.workspace-layout,.ret-page{--ws-sidebar-width:284px;grid-template-columns:var(--ws-sidebar-width) minmax(0,1fr)}
.workspace-sidebar{--ws-side-surface:#fff;--ws-side-ink:#26334b;--ws-side-muted:#718098;--ws-side-border:#e9edf5;--ws-side-hover:#f1f4fb;--ws-side-accent:#5b50d9;--ws-side-accent-bg:#efedff;--ws-side-field:#f7f8fc;position:sticky;top:76px;height:calc(100vh - 76px);background:var(--ws-side-surface);color:var(--ws-side-ink);border-right:1px solid var(--ws-side-border);min-width:0;display:flex;flex-direction:column;z-index:30;box-shadow:4px 0 18px rgba(25,40,70,.018)}
html[data-theme="dark"] .workspace-sidebar{--ws-side-surface:#182234;--ws-side-ink:#f0f3fb;--ws-side-muted:#a9b6ca;--ws-side-border:#344257;--ws-side-hover:#26334a;--ws-side-accent:#c0b8ff;--ws-side-accent-bg:#332e5a;--ws-side-field:#222e43}
.workspace-sidebar .ws-sidebar-sprite{position:absolute;width:0;height:0;overflow:hidden;pointer-events:none}
.workspace-sidebar .ws-svg{width:19px;height:19px;fill:none;stroke:currentColor;stroke-width:1.85;stroke-linecap:round;stroke-linejoin:round;flex-shrink:0;overflow:visible}
.workspace-sidebar .ws-side-top{flex:0 0 auto;padding:0px 16px 15px;border-bottom:1px solid var(--ws-side-border)}
.workspace-sidebar .ws-side-brand{display:flex;align-items:center;gap:10px;min-width:0}
.workspace-sidebar .ws-brand-avatar{display:grid;place-items:center;width:40px;height:40px;flex:0 0 40px;border-radius:12px;background:var(--ws-side-accent-bg);color:var(--ws-side-accent);font-size:20px;font-weight:800;overflow:hidden}
.workspace-sidebar .ws-brand-avatar img{width:100%;height:100%;object-fit:contain;background:var(--ws-side-surface)}
.workspace-sidebar .ws-side-brand-text{display:flex;flex-direction:column;min-width:0;flex:1;gap:3px}
.workspace-sidebar .ws-side-brand-text strong{display:block;color:var(--ws-side-ink);font-size:13px;line-height:1.3;font-weight:800;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.workspace-sidebar .ws-side-brand-text small{display:block;color:var(--ws-side-muted);font-size:10px;line-height:1.4;font-weight:600}
.workspace-sidebar .ws-close-sidebar{display:none;align-items:center;justify-content:center;width:32px;height:32px;flex:0 0 32px;border:1px solid var(--ws-side-border);border-radius:9px;background:var(--ws-side-field);color:var(--ws-side-muted)}
.workspace-sidebar .ws-close-sidebar:hover{background:var(--ws-side-hover);color:var(--ws-side-ink)}
.workspace-sidebar .ws-side-search{display:block;position:relative;margin:0px 0 0;min-width:0}
.workspace-sidebar .ws-side-search>.ws-svg{position:absolute;left:13px;top:12px;width:17px;height:17px;color:var(--ws-side-muted);pointer-events:none}
.workspace-sidebar .ws-side-search input{display:block;width:100%;height:41px;min-width:0;padding:0 12px 0 40px;border:1px solid var(--ws-side-border);border-radius:11px;background:var(--ws-side-field);color:var(--ws-side-ink);font-size:12px;font-weight:500;outline:0}
.workspace-sidebar .ws-side-search input::placeholder{color:var(--ws-side-muted);opacity:.9}
.workspace-sidebar .ws-side-search input:focus{border-color:var(--ws-side-accent);box-shadow:0 0 0 3px rgba(95,80,220,.11)}
.workspace-sidebar .ws-side-scroll{display:block;flex:1;min-height:0;padding:0px 11px 14px;overflow-x:hidden;overflow-y:auto;scrollbar-width:thin;scrollbar-color:var(--ws-side-border) transparent;overscroll-behavior:contain}
.workspace-sidebar .ws-nav-label{padding:0 12px;margin:0 0 11px;color:var(--ws-side-muted);font-size:10px;font-weight:800;letter-spacing:1.05px;line-height:1.5}
.workspace-sidebar .ws-nav-modules-label{margin:25px 0 11px;padding-top:0}
.workspace-sidebar .ws-nav-dashboard{display:flex;align-items:center;gap:12px;min-height:43px;padding:0 13px;border-radius:10px;color:var(--ws-side-ink);font-size:13px;font-weight:700;text-decoration:none;transition:background .15s,color .15s}
.workspace-sidebar .ws-nav-dashboard .ws-svg{width:18px;height:18px;color:var(--ws-side-muted)}
.workspace-sidebar .ws-nav-dashboard:hover{background:var(--ws-side-hover);color:var(--ws-side-ink)}
.workspace-sidebar .ws-nav-dashboard.active{background:var(--ws-side-accent-bg);color:var(--ws-side-accent)}
.workspace-sidebar .ws-nav-dashboard.active .ws-svg{color:var(--ws-side-accent)}
.workspace-sidebar .ws-module,.workspace-sidebar .ws-menu{margin:0 0 3px;min-width:0}
.workspace-sidebar .ws-module[hidden],.workspace-sidebar .ws-menu[hidden],.workspace-sidebar .ws-menu-direct[hidden],.workspace-sidebar .ws-children a[hidden]{display:none!important}
.workspace-sidebar .ws-module>summary,.workspace-sidebar .ws-menu>summary{list-style:none;user-select:none;outline-offset:1px;cursor:pointer}
.workspace-sidebar .ws-module>summary::-webkit-details-marker,.workspace-sidebar .ws-menu>summary::-webkit-details-marker{display:none}
.workspace-sidebar .ws-module>summary::marker,.workspace-sidebar .ws-menu>summary::marker{content:''}
.workspace-sidebar .ws-module>.ws-module-label{display:flex;align-items:center;gap:10px;width:100%;min-height:45px;padding:6px 11px;border-radius:11px;background:transparent;color:var(--ws-side-ink);font-size:13px;letter-spacing:0;text-transform:none;font-weight:750;transition:background .15s,color .15s}
.workspace-sidebar .ws-module>.ws-module-label:hover{background:var(--ws-side-hover);color:var(--ws-side-ink)}
.workspace-sidebar .ws-module>.ws-module-label.active{background:var(--ws-side-accent-bg);color:var(--ws-side-accent)}
.workspace-sidebar .ws-module-icon-wrap{width:29px;height:29px;display:grid;place-items:center;flex:0 0 29px;border-radius:9px;background:var(--ws-side-field);color:var(--ws-side-muted)}
.workspace-sidebar .ws-module>.ws-module-label.active .ws-module-icon-wrap{background:var(--ws-side-surface);color:var(--ws-side-accent)}
.workspace-sidebar .ws-module>.ws-module-label .ws-module-icon{width:17px;height:17px}
.workspace-sidebar .ws-module>.ws-module-label .ws-module-name{display:block;flex:1;min-width:0;color:inherit;font-size:12.5px;font-weight:750;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.workspace-sidebar .ws-module-count{display:grid;place-items:center;flex:0 0 auto;min-width:21px;height:21px;padding:0 5px;background:var(--ws-side-field);color:var(--ws-side-muted);border-radius:7px;font-size:10px;font-weight:800;letter-spacing:0}
.workspace-sidebar .ws-module>.ws-module-label.active .ws-module-count{background:var(--ws-side-surface);color:var(--ws-side-accent)}
.workspace-sidebar .ws-module>.ws-module-label .ws-module-chevron{width:14px;height:14px;transform:rotate(0deg);transition:transform .17s}
.workspace-sidebar .ws-module[open]>.ws-module-label .ws-module-chevron{transform:rotate(90deg)}
.workspace-sidebar .ws-module-content{padding:6px 0 8px 14px;border-left:1px solid var(--ws-side-border);margin:3px 0 9px 24px}
.workspace-sidebar .ws-menu>summary,.workspace-sidebar .ws-menu-direct{display:flex;align-items:center;gap:10px;width:100%;min-height:38px;padding:8px 10px;border-radius:9px;background:transparent;color:var(--ws-side-muted);font-size:12px;font-weight:600;text-decoration:none;transition:background .15s,color .15s}
.workspace-sidebar .ws-menu>summary:hover,.workspace-sidebar .ws-menu-direct:hover{background:var(--ws-side-hover);color:var(--ws-side-ink)}
.workspace-sidebar .ws-menu>summary.active,.workspace-sidebar .ws-menu-direct.active{background:var(--ws-side-accent-bg);color:var(--ws-side-accent);font-weight:750}
.workspace-sidebar .ws-menu>summary>.ws-svg:first-child,.workspace-sidebar .ws-menu-direct>.ws-svg:first-child{width:15px;height:15px;color:inherit;opacity:.8}
.workspace-sidebar .ws-menu-text{display:block;flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:12px;line-height:1.5}
.workspace-sidebar .ws-menu>summary .ws-chevron{width:13px;height:13px;color:inherit;transition:transform .17s}
.workspace-sidebar .ws-menu[open]>summary .ws-chevron{transform:rotate(90deg)}
.workspace-sidebar .ws-children{display:flex;flex-direction:column;gap:2px;padding:4px 0 5px 13px}
.workspace-sidebar .ws-children a{display:flex;align-items:center;gap:9px;min-height:34px;padding:7px 9px;border-radius:8px;background:transparent;color:var(--ws-side-muted);font-size:11.5px;font-weight:550;text-decoration:none;min-width:0;transition:background .15s,color .15s}
.workspace-sidebar .ws-children a::before{content:'';display:block;width:5px;height:5px;flex:0 0 5px;border-radius:50%;background:var(--ws-side-border)}
.workspace-sidebar .ws-children a>span{min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.workspace-sidebar .ws-children a:hover{background:var(--ws-side-hover);color:var(--ws-side-ink)}
.workspace-sidebar .ws-children a.active{background:var(--ws-side-accent-bg);color:var(--ws-side-accent);font-weight:750}
.workspace-sidebar .ws-children a.active::before{background:var(--ws-side-accent)}
.workspace-sidebar .ws-search-empty{margin:5px 3px;padding:14px 10px;color:var(--ws-side-muted);font-size:12px;text-align:center;line-height:1.6}
.workspace-sidebar .ws-side-bottom{flex:0 0 auto;border-top:1px solid var(--ws-side-border);padding:13px 14px;background:var(--ws-side-surface)}
.workspace-sidebar .ws-side-user{display:flex;align-items:center;gap:9px;min-width:0}
.workspace-sidebar .ws-user-avatar{width:36px;height:36px;display:grid;place-items:center;flex:0 0 36px;border-radius:11px;background:var(--ws-side-accent-bg);color:var(--ws-side-accent);font-size:14px;font-weight:800}
.workspace-sidebar .ws-user-text{flex:1;min-width:0;display:block}
.workspace-sidebar .ws-user-text strong{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--ws-side-ink);font-size:11.5px;font-weight:750}
.workspace-sidebar .ws-user-text small{display:block;color:var(--ws-side-muted);font-size:10px;margin-top:3px}
.workspace-sidebar .ws-signout{display:grid;place-items:center;width:35px;height:35px;border:1px solid var(--ws-side-border);border-radius:10px;color:var(--ws-side-muted);background:var(--ws-side-field);cursor:pointer}
.workspace-sidebar .ws-signout:hover{color:#db596c;border-color:#db596c;background:var(--ws-side-hover)}
.workspace-sidebar .ws-signout .ws-svg{width:17px;height:17px}
@media(max-width:1050px){
 .workspace-layout,.ret-page{display:block}
 .workspace-sidebar{position:fixed!important;top:0!important;left:0;width:min(320px,88vw)!important;height:100dvh!important;z-index:220!important;transform:translateX(-105%);transition:transform .23s ease}
 .ws-sidebar-open .workspace-sidebar{transform:translateX(0)}
 .workspace-sidebar .ws-close-sidebar{display:flex}
 .ws-sidebar-open{overflow:hidden}
 .ws-sidebar-open .ws-sidebar-overlay{display:block;position:fixed;inset:0;background:rgba(12,18,35,.6);z-index:210}
}
@media(min-width:1051px){.workspace-sidebar .ws-close-sidebar{display:none}}
@media(prefers-reduced-motion:reduce){.workspace-sidebar,.workspace-sidebar *{transition:none!important}}
</style>
<script>
(function(){
    const start=()=>{
        const sidebar=document.getElementById('ws-sidebar-navigation');
        if(!sidebar||sidebar.dataset.wsReady)return;
        sidebar.dataset.wsReady='1';
        const groups=[...sidebar.querySelectorAll('[data-ws-module]')];
        const search=sidebar.querySelector('#ws-sidebar-search');
        const empty=sidebar.querySelector('#ws-search-empty');
        let searching=false;
        const modulesState=new Map(), menusState=new Map();
        groups.forEach(group=>{
            group.addEventListener('toggle',()=>{
                if(searching || !group.open)return;
                groups.forEach(other=>{if(other!==group)other.open=false});
            });
            group.querySelectorAll('[data-ws-menu]').forEach(menu=>{
                menu.addEventListener('toggle',()=>{
                    if(searching||!menu.open)return;
                    group.querySelectorAll('[data-ws-menu]').forEach(other=>{if(other!==menu)other.open=false});
                });
            });
        });
        search?.addEventListener('input',()=>{
            const q=search.value.trim().toLocaleLowerCase();
            if(q&&!searching){
                searching=true;
                groups.forEach(g=>{modulesState.set(g,g.open);g.querySelectorAll('[data-ws-menu]').forEach(m=>menusState.set(m,m.open))});
            }
            if(!q){
                groups.forEach(g=>{
                    g.hidden=false;
                    g.open=searching?(modulesState.get(g)??false):g.open;
                    g.querySelectorAll('[data-ws-menu],.ws-menu-direct').forEach(m=>{m.hidden=false;if(m.matches('details'))m.open=searching?(menusState.get(m)??false):m.open});
                    g.querySelectorAll('.ws-children a').forEach(a=>a.hidden=false);
                });
                searching=false;modulesState.clear();menusState.clear();if(empty)empty.hidden=true;return;
            }
            let any=false;
            groups.forEach(g=>{
                const moduleMatch=(g.querySelector('.ws-module-name')?.textContent||'').toLocaleLowerCase().includes(q);
                let match=moduleMatch;
                g.querySelectorAll('[data-ws-menu],.ws-menu-direct').forEach(item=>{
                    const menuMatch=moduleMatch||(item.querySelector('.ws-menu-text')?.textContent||'').toLocaleLowerCase().includes(q);
                    let childMatch=false;
                    if(item.matches('details')){
                        item.querySelectorAll('.ws-children a').forEach(a=>{
                            const visible=menuMatch||a.textContent.toLocaleLowerCase().includes(q);
                            a.hidden=!visible;
                            if(visible)childMatch=true;
                        });
                        item.hidden=!(menuMatch||childMatch);
                        if(!item.hidden)item.open=true;
                    }else item.hidden=!menuMatch;
                    if(!item.hidden)match=true;
                });
                g.hidden=!match;if(match){g.open=true;any=true}
            });
            if(empty)empty.hidden=any;
        });
        const current=sidebar.querySelector('a[aria-current="page"]');
        if(current){requestAnimationFrame(()=>current.scrollIntoView({block:'nearest'}));}
    };
    if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',start);else start();
})();
</script>


@php
    $sidebarLogoPath=data_get($business,'logo') ?? data_get($business,'logo_path') ?? data_get($business,'business_logo');
    $sidebarLogo=$sidebarLogoPath ? (preg_match('~^https?://~i',$sidebarLogoPath) ? $sidebarLogoPath : (str_starts_with($sidebarLogoPath,'/') ? url($sidebarLogoPath) : (str_starts_with($sidebarLogoPath,'storage/') || str_starts_with($sidebarLogoPath,'images/') ? asset($sidebarLogoPath) : asset('storage/'.$sidebarLogoPath)))) : null;
    $currentModuleSlug=(string)request()->route('moduleSlug');
    $currentMenuSlug=(string)request()->route('menuSlug');
    $currentChildSlug=(string)request()->route('childSlug');
@endphp
<style>
.workspace-sidebar .ws-menu-direct{display:flex;align-items:center;gap:12px;width:100%;padding:11px 14px;border-radius:10px;color:inherit;text-decoration:none;transition:background .2s,color .2s;cursor:pointer}
.workspace-sidebar .ws-menu-direct:hover{background:rgba(128,128,128,.1);color:inherit;text-decoration:none}
.workspace-sidebar .ws-menu-direct.active{background:rgba(128,128,128,.15);font-weight:600}
.workspace-sidebar .ws-menu-direct .ws-svg{width:18px;height:18px;flex-shrink:0}
.workspace-sidebar .ws-menu-direct .ws-menu-text{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap; font-size:12px}
.workspace-sidebar .ws-nav-group[hidden],.workspace-sidebar .ws-menu[hidden],.workspace-sidebar .ws-menu-direct[hidden]{display:none!important}
</style>
<aside class="workspace-sidebar" aria-label="{{ t('Business navigation','বিজনেস নেভিগেশন') }}">
    <div class="ws-side-top">
        <div class="ws-side-brand">
            <button class="ws-close-sidebar" data-ws-sidebar-toggle type="button" aria-label="{{ t('Close menu','মেনু বন্ধ করুন') }}">
                <svg class="ws-svg"><use href="#ws-i-x"/></svg>
            </button>
        </div>
        <label class="ws-side-search">
            <svg class="ws-svg"><use href="#ws-i-search"/></svg>
            <input type="search" id="ws-sidebar-search" placeholder="{{ t('Search menu...','মেনু খুঁজুন...') }}" autocomplete="off" aria-label="{{ t('Search menu','মেনু খুঁজুন') }}">
        </label>
    </div>
    <nav class="ws-side-scroll">
        <div class="ws-nav-label">{{ t('MAIN MENU','প্রধান মেনু') }}</div>
        <a class="ws-nav-dashboard {{ request()->routeIs('store.admin.dashboard') ? 'active' : '' }}" href="{{ route('store.admin.dashboard') }}">
            <svg class="ws-svg"><use href="#ws-i-grid"/></svg>
            {{ t('Dashboard','ড্যাশবোর্ড') }}
        </a>
        <div class="ws-nav-separator"></div>
        <div class="ws-nav-label">{{ t('WORKSPACE MODULES','ওয়ার্কস্পেস মডিউল') }}</div>
        @forelse($modules as $module)
            <div class="ws-nav-group">
                <div class="ws-module-label">
                    <span>{{ navName($module) }}</span>
                    <em>{{ $module->menus->count() }}</em>
                </div>
                @foreach($module->menus as $menu)
                    @php
                        $hasChildren=$menu->children->isNotEmpty();
                        $menuActive=$currentModuleSlug===(string)$module->slug && $currentMenuSlug===(string)$menu->slug;
                        $menuParams=['moduleSlug'=>$module->slug,'menuSlug'=>$menu->slug];
                    @endphp
                    @if($hasChildren)
                        <details class="ws-menu" {{ $menuActive ? 'open' : '' }}>
                            <summary class="{{ $menuActive ? 'active' : '' }}">
                                <svg class="ws-svg"><use href="#ws-i-folder"/></svg>
                                <span class="ws-menu-text">{{ navName($menu) }}</span>
                                <svg class="ws-svg ws-chevron"><use href="#ws-i-chevron"/></svg>
                            </summary>
                            <div class="ws-children">
                                @foreach($menu->children as $child)
                                    @php
                                        $childActive=$menuActive && $currentChildSlug===(string)$child->slug;
                                        $childParams=[...$menuParams,'childSlug'=>$child->slug];
                                    @endphp
                                    <a class="{{ $childActive ? 'active' : '' }}" href="{{ route('store.admin.feature',$childParams) }}">
                                        <span>{{ navName($child) }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </details>
                    @else
                        <a class="ws-menu-direct {{ $menuActive && $currentChildSlug==='' ? 'active' : '' }}" href="{{ route('store.admin.feature',$menuParams) }}">
                            <svg class="ws-svg"><use href="#ws-i-folder"/></svg>
                            <span class="ws-menu-text">{{ navName($menu) }}</span>
                        </a>
                    @endif
                @endforeach
            </div>
        @empty
            <p class="ws-search-empty">{{ t('No menu access assigned yet.','এখনও কোনো মেনু অ্যাক্সেস দেওয়া হয়নি।') }}</p>
        @endforelse
        <p class="ws-search-empty" id="ws-search-empty" hidden>{{ t('No matching menus found.','কোনো মেনু পাওয়া যায়নি।') }}</p>
    </nav>
    <div class="ws-side-bottom">
        <div class="ws-side-user">
            <span class="ws-user-avatar">{{ mb_strtoupper(mb_substr($user->name ?? 'A',0,1)) }}</span>
            <div class="ws-user-text">
                <strong>{{ $user->name }}</strong>
                <small>{{ t('Store administrator','স্টোর অ্যাডমিন') }}</small>
            </div>
            <form method="POST" action="{{ route('store.admin.logout') }}">
                @csrf
                <button class="ws-signout" type="submit" title="{{ t('Log out','লগআউট') }}" aria-label="{{ t('Log out','লগআউট') }}">
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

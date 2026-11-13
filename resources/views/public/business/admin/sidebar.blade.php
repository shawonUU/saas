@php
    $sidebarLogoPath=data_get($business,'logo') ?? data_get($business,'logo_path') ?? data_get($business,'business_logo');
    $sidebarLogo=$sidebarLogoPath ? (preg_match('~^https?://~i',$sidebarLogoPath) ? $sidebarLogoPath : (str_starts_with($sidebarLogoPath,'/') ? url($sidebarLogoPath) : (str_starts_with($sidebarLogoPath,'storage/') || str_starts_with($sidebarLogoPath,'images/') ? asset($sidebarLogoPath) : asset('storage/'.$sidebarLogoPath)))) : null;
    $currentChildParam=request()->route('childMenu');
    $currentChildId=is_object($currentChildParam) ? $currentChildParam->id : $currentChildParam;
@endphp
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
    <nav class="ws-side-scroll"><div class="ws-nav-label">{{ t('MAIN MENU','প্রধান মেনু') }}</div><a class="ws-nav-dashboard {{ request()->routeIs('store.admin.dashboard') ? 'active' : '' }}" href="{{ route('store.admin.dashboard',['business'=>$business->code]) }}"><svg class="ws-svg"><use href="#ws-i-grid"/></svg>{{ t('Dashboard','ড্যাশবোর্ড') }}</a><div class="ws-nav-separator"></div><div class="ws-nav-label">{{ t('WORKSPACE MODULES','ওয়ার্কস্পেস মডিউল') }}</div>
        @forelse($modules as $module)<div class="ws-nav-group"><div class="ws-module-label"><span>{{ navName($module) }}</span><em>{{ $module->menus->count() }}</em></div>@foreach($module->menus as $menu)@php($expanded=$menu->children->contains(fn($child)=>(string)$child->id===(string)$currentChildId))<details class="ws-menu" {{ $expanded ? 'open' : '' }}><summary><svg class="ws-svg"><use href="#ws-i-folder"/></svg><span class="ws-menu-text">{{ navName($menu) }}</span><svg class="ws-svg ws-chevron"><use href="#ws-i-chevron"/></svg></summary><div class="ws-children">@foreach($menu->children as $child)<a class="{{ (string)$currentChildId===(string)$child->id ? 'active' : '' }}" href="{{ $child->route==='/dashboard' ? route('store.admin.dashboard',['business'=>$business->code]) : route('store.admin.feature',['business'=>$business->code,'childMenu'=>$child->id]) }}"><span>{{ navName($child) }}</span></a>@endforeach</div></details>@endforeach</div>@empty<p class="ws-search-empty">{{ t('No menu access assigned yet.','এখনও কোনো মেনু অ্যাক্সেস দেওয়া হয়নি।') }}</p>@endforelse
        <p class="ws-search-empty" id="ws-search-empty" hidden>{{ t('No matching menus found.','কোনো মেনু পাওয়া যায়নি।') }}</p>
    </nav>
    <div class="ws-side-bottom"><div class="ws-side-user"><span class="ws-user-avatar">{{ mb_strtoupper(mb_substr($user->name ?? 'A',0,1)) }}</span><div class="ws-user-text"><strong>{{ $user->name }}</strong><small>{{ t('Store administrator','স্টোর অ্যাডমিন') }}</small></div><form method="POST" action="{{ route('store.admin.logout',['business'=>$business->code]) }}">@csrf<button class="ws-signout" type="submit" title="{{ t('Log out','লগআউট') }}" aria-label="{{ t('Log out','লগআউট') }}"><svg class="ws-svg"><use href="#ws-i-logout"/></svg></button></form></div></div>
</aside>

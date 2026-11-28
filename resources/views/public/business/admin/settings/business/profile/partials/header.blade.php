@php
    $profileDesignation = data_get($adminUser, 'designation') ?: data_get($adminUser, 'role.name') ?: t('Administrator', 'অ্যাডমিন');
    $profileName = $adminUser?->name ?: t('Administrator', 'অ্যাডমিন');
    $profileInitial = mb_strtoupper(mb_substr($profileName, 0, 1));
@endphp
<header class="site-header saas-admin-header" id="saas-admin-header">
    <div class="nav-inner saas-header-inner">
        <a href="{{ $dashboardUrl }}" class="brand saas-header-brand" title="{{ $storeName }}">
            @if($storeLogo)
                <img src="{{ $storeLogo }}" alt="{{ $storeName }}" class="brand-logo" onerror="this.style.display='none'">
            @else
                <span class="brand-logo brand-fallback">{{ mb_strtoupper(mb_substr($storeName,0,1)) }}</span>
            @endif
            <span class="brand-details">
                <span class="brand-name">{{ $storeName }}</span>
                <span class="brand-subtitle">{{ t('Store Administration','স্টোর অ্যাডমিন') }}</span>
            </span>
        </a>
        <div class="saas-header-actions" id="saas-header-actions"
             data-notification-url="{{ route('business.admin.settings.business.notifications.latest', ['business'=>$storeCode]) }}"
             data-notification-read-all="{{ route('business.admin.settings.business.notifications.read-all', ['business'=>$storeCode]) }}">
            <div class="saas-topbar-preferences" aria-label="{{ t('Display preferences', 'ডিসপ্লে সেটিংস') }}">
                <button type="button" id="theme-toggle" class="saas-topbar-control saas-topbar-theme" aria-pressed="false"
                        aria-label="{{ t('Toggle dark and light mode', 'ডার্ক ও লাইট মোড পরিবর্তন করুন') }}">
                    <svg class="saas-theme-icon-light" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/></svg>
                    <svg class="saas-theme-icon-dark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.8 14.1A9 9 0 0 1 9.9 3.2a9 9 0 1 0 10.9 10.9Z"/></svg>
                    <span id="theme-label">{{ t('Light', 'লাইট') }}</span>
                </button>
                <form method="POST" action="{{ route('language.set') }}" class="saas-topbar-language">
                    @csrf
                    <input type="hidden" name="lang" value="{{ session('portal_lang','en') === 'en' ? 'bn' : 'en' }}">
                    <button type="submit" class="saas-topbar-control saas-topbar-lang-button"
                            title="{{ session('portal_lang','en') === 'en' ? t('Switch to Bangla','বাংলায় পরিবর্তন করুন') : t('Switch to English','ইংরেজিতে পরিবর্তন করুন') }}"
                            aria-label="{{ session('portal_lang','en') === 'en' ? t('Switch to Bangla','বাংলায় পরিবর্তন করুন') : t('Switch to English','ইংরেজিতে পরিবর্তন করুন') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 0 1 0 18M12 3a15 15 0 0 0 0 18"/></svg>
                        <span>{{ session('portal_lang','en') === 'en' ? 'বাংলা' : 'English' }}</span>
                    </button>
                </form>
            </div>
            @include('public.business.admin.settings.business.notifications.partials.bell')
            @if($adminUser)
                <div class="saas-popover-anchor" id="saas-profile-anchor">
                    <button type="button" class="saas-profile-trigger" id="saas-profile-trigger"
                            aria-haspopup="true" aria-expanded="false" aria-controls="saas-profile-menu"
                            aria-label="{{ t('Open account menu', 'অ্যাকাউন্ট মেনু খুলুন') }}">
                        <span class="saas-trigger-avatar">{{ $profileInitial }}</span>
                    </button>
                    <div class="saas-flyout saas-profile-flyout" id="saas-profile-menu" hidden>
                        <div class="saas-profile-summary">
                            <span class="saas-summary-avatar">{{ $profileInitial }}</span>
                            <span class="saas-summary-text"><strong title="{{ $profileName }}">{{ $profileName }}</strong><small>{{ $profileDesignation }}</small><small class="saas-summary-email" title="{{ $adminUser->email }}">{{ $adminUser->email }}</small></span>
                        </div>
                        <div class="saas-flyout-label">{{ t('Workspace', 'ওয়ার্কস্পেস') }}</div>
                        <a href="{{ $storefrontUrl }}" class="saas-menu-row" target="_blank" rel="noopener noreferrer">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9 5 4h14l2 5v11H3V9Z"/><path d="M3 9c0 3 3.4 4 5.4 1.5C10 12 13.8 12 15.5 10.5 17.6 13 21 12 21 9M9 20v-7h6v7"/></svg>
                            <span>{{ t('View Store', 'স্টোর দেখুন') }}</span>
                            <svg class="saas-row-end" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13 5h6v6M19 5l-9 9"/><path d="M19 14v5H5V5h5"/></svg>
                        </a>
                        <div class="saas-flyout-divider"></div>
                        <button type="button" class="saas-menu-row" id="saas-password-open">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                            <span>{{ t('Change Password', 'পাসওয়ার্ড পরিবর্তন') }}</span>
                        </button>
                        @if(Route::has('business.admin.logout'))
                            <form method="POST" action="{{ route('business.admin.logout',['business'=>$storeCode]) }}">@csrf
                                <button type="submit" class="saas-menu-row saas-signout-row">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 4H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h4m5-4 4-4-4-4m4 4H9"/></svg>
                                    <span>{{ t('Log out', 'লগআউট') }}</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</header>
@if($adminUser)
<dialog class="saas-password-dialog" id="saas-password-dialog" aria-labelledby="saas-password-title">
    <form method="POST" action="{{ route('business.admin.settings.business.profile.password', ['business'=>$storeCode]) }}" class="saas-password-form">@csrf
        <div class="saas-dialog-heading">
            <span class="saas-dialog-symbol"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg></span>
            <button type="button" id="saas-password-close" class="saas-dialog-close" aria-label="{{ t('Close', 'বন্ধ করুন') }}">×</button>
        </div>
        <h2 id="saas-password-title">{{ t('Change Password', 'পাসওয়ার্ড পরিবর্তন') }}</h2>
        <p class="saas-dialog-subtitle">{{ t('Keep your admin account secure with a strong password.', 'শক্তিশালী পাসওয়ার্ড দিয়ে অ্যাডমিন অ্যাকাউন্ট নিরাপদ রাখুন।') }}</p>
        @if($errors->getBag('profilePassword')->any())
            <div class="saas-password-error" role="alert">{{ $errors->getBag('profilePassword')->first() }}</div>
        @endif
        <label class="saas-dialog-field">{{ t('Current password', 'বর্তমান পাসওয়ার্ড') }}<input type="password" name="current_password" required autocomplete="current-password" autofocus></label>
        <label class="saas-dialog-field">{{ t('New password', 'নতুন পাসওয়ার্ড') }}<input type="password" name="password" required minlength="8" autocomplete="new-password"></label>
        <label class="saas-dialog-field">{{ t('Confirm new password', 'নতুন পাসওয়ার্ড নিশ্চিত করুন') }}<input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password"></label>
        <div class="saas-dialog-actions"><button type="button" class="saas-dialog-cancel" id="saas-password-cancel">{{ t('Cancel', 'বাতিল') }}</button><button type="submit" class="saas-dialog-submit">{{ t('Update Password', 'পাসওয়ার্ড পরিবর্তন করুন') }}</button></div>
    </form>
</dialog>
@endif

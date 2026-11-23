
<style>
/* Theme Colors */
:root,
html[data-theme="light"] {
    color-scheme: light;
    --sf-header-bg: #ffffff;
    --sf-header-text: #222831;
    --sf-header-border: #e7e9ed;
    --sf-header-input: #f5f6f8;
    --sf-header-hover: #f0f2f5;
    --sf-header-muted: #697176;
}

html[data-theme="dark"] {
    color-scheme: dark;
    --sf-header-bg: #191c23;
    --sf-header-text: #f1f2f5;
    --sf-header-border: #343945;
    --sf-header-input: #272c35;
    --sf-header-hover: #343a45;
    --sf-header-muted: #b6bdc8;
}

/* Header */
.sf-header {
    background: var(--sf-header-bg);
    color: var(--sf-header-text);
    border-bottom: 1px solid var(--sf-header-border);
    transition: background .25s ease, color .25s ease;
}

.sf-header .sf-headrow {
    display: flex;
    align-items: center;
    gap: 14px;
    min-width: 0;
}

.sf-header .sf-logo {
    flex-shrink: 0;
}

.sf-header .sf-search {
    flex: 1;
    min-width: 0;
    display: flex;
    background: var(--sf-header-input);
    border: 1px solid var(--sf-header-border);
    border-radius: 8px;
    overflow: hidden;
}

.sf-header .sf-search input {
    flex: 1;
    width: 100%;
    min-width: 0;
    border: none;
    outline: none;
    background: transparent;
    color: var(--sf-header-text);
}

.sf-header .sf-search input::placeholder {
    color: var(--sf-header-muted);
}

.sf-header .sf-search button {
    background: transparent;
    border: none;
    color: var(--sf-header-text);
    cursor: pointer;
}

.sf-header .sf-search button svg {
    width: 20px;
    height: 20px;
}

.sf-header .sf-actions {
    display: flex;
    align-items: center;
    flex-shrink: 0;
    gap: 8px;
}

.sf-header .sf-actions form {
    margin: 0;
}

/* Header Settings */
.sf-header .sf-header-setting,
.sf-header .sf-theme-switch {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    padding: 0;
    border: 0;
    border-radius: 8px;
    background: transparent;
    color: var(--sf-header-text);
    cursor: pointer;
    transition: background .2s ease, color .2s ease;
}

.sf-header .sf-header-setting:hover,
.sf-header .sf-theme-switch:hover {
    background: var(--sf-header-hover);
}

/* Theme SVG */
.sf-header .sf-theme-switch svg {
    width: 21px;
    height: 21px;
    stroke: currentColor;
    stroke-width: 1.8;
    stroke-linecap: round;
    stroke-linejoin: round;
    fill: none;
    transition: opacity .2s ease;
}

.sf-header .sf-theme-switch .sf-sun-icon {
    display: none;
}

.sf-header .sf-theme-switch .sf-moon-icon {
    display: block;
}

html[data-theme="dark"] .sf-theme-switch .sf-sun-icon {
    display: block;
}

html[data-theme="dark"] .sf-theme-switch .sf-moon-icon {
    display: none;
}

/* Header Icons */
.sf-header .sf-header-icon {
    color: var(--sf-header-text);
    transition: color .2s ease;
}

.sf-header .sf-header-icon:hover {
    color: var(--sf-header-muted);
}

.sf-header .sf-mobile-menu {
    color: var(--sf-header-text);
}

/* Keyboard Accessibility */
.sf-header .sf-theme-switch:focus-visible,
.sf-header .sf-header-setting:focus-visible {
    outline: 2px solid currentColor;
    outline-offset: 2px;
}

/* Mobile */
@media(max-width:767px) {
    .sf-header .sf-headrow {
        flex-wrap: wrap;
        gap: 8px;
    }

    .sf-header .sf-search {
        order: 3;
        flex: 0 0 100%;
    }

    .sf-header .sf-actions {
        margin-left: auto;
        gap: 4px;
    }

    .sf-header .sf-header-setting,
    .sf-header .sf-theme-switch {
        width: 32px;
        height: 32px;
    }
}
</style>

<header class="sf-header">
    <div class="sf-headrow">

        <!-- Mobile Menu -->
        <button
            type="button"
            class="sf-mobile-menu"
            data-sidebar-open
            aria-label="{{ t('Open categories','ক্যাটাগরি খুলুন') }}">
            ☰
        </button>

        <!-- Logo -->
        <a href="{{ route('business.customer.home') }}"
           class="sf-logo"
           aria-label="{{ $b->name }}">

            @if($storeLogo)
                <img src="{{ $storeLogo }}" alt="{{ $b->name }}">
            @else
                <span class="sf-logo-mark">
                    {{ mb_substr($b->name,0,1) }}
                </span>
                <span class="sf-logo-text">
                    {{ $b->name }}
                </span>
            @endif
        </a>

        <!-- Search -->
        <form class="sf-search"
              action="{{ route('business.customer.shop') }}"
              method="GET">

            <input
                type="search"
                name="q"
                value="{{ request('q') }}"
                placeholder="{{ t('Search Product','পণ্য খুঁজুন') }}"
                aria-label="{{ t('Search Product','পণ্য খুঁজুন') }}">

            <button type="submit"
                    title="{{ t('Search','খুঁজুন') }}"
                    aria-label="{{ t('Search','খুঁজুন') }}">
                <svg viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="2">
                    <circle cx="10.5" cy="10.5" r="7.5"/>
                    <path d="m16 16 5 5"/>
                </svg>
            </button>
        </form>

        <!-- Header Actions -->
        <nav class="sf-actions"
             aria-label="{{ t('Shop navigation','শপ নেভিগেশন') }}">

            <!-- Language -->
            <form method="POST" action="{{ route('language.set') }}">
                @csrf
                <input
                    type="hidden"
                    name="lang"
                    value="{{ session('portal_lang','en')==='bn'?'en':'bn' }}">

                <button
                    type="submit"
                    class="sf-header-setting"
                    title="{{ t('Language','ভাষা') }}">
                    {{ session('portal_lang','en')==='bn'?'EN':'বাং' }}
                </button>
            </form>

            <!-- Dark / Light -->
            <button
                type="button"
                class="sf-theme-switch"
                data-sf-theme-toggle
                aria-label="Toggle dark and light theme"
                aria-pressed="false"
                title="Switch Theme">

                <!-- Moon -->
                <svg class="sf-moon-icon"
                     viewBox="0 0 24 24"
                     aria-hidden="true">
                    <path d="M20.5 14.5A8.5 8.5 0 0 1 9.5 3.5
                             8.5 8.5 0 1 0 20.5 14.5Z"/>
                </svg>

                <!-- Sun -->
                <svg class="sf-sun-icon"
                     viewBox="0 0 24 24"
                     aria-hidden="true">
                    <circle cx="12" cy="12" r="4"/>
                    <path d="M12 2v2M12 20v2M4.93 4.93l1.42 1.42
                             M17.65 17.65l1.42 1.42
                             M2 12h2M20 12h2
                             M4.93 19.07l1.42-1.42
                             M17.65 6.35l1.42-1.42"/>
                </svg>
            </button>

            <!-- Cart -->
            <a class="sf-header-icon"
               href="{{ route('business.customer.cart') }}"
               title="{{ t('Cart','কার্ট') }}">

                <svg viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="1.8">
                    <path d="M2 3h2l2.7 12h12.7l2.1-9H5"/>
                    <circle cx="9" cy="20" r="1"/>
                    <circle cx="18" cy="20" r="1"/>
                </svg>

                <em data-cart-count>0</em>
            </a>

            <!-- Wishlist -->
            <a class="sf-header-icon"
               href="{{ route('business.customer.wishlist') }}"
               title="{{ t('Wishlist','উইশলিস্ট') }}">

                <svg viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="1.8">
                    <path d="M20.5 8.8c0 5-8.5 10.5-8.5 10.5
                             S3.5 13.8 3.5 8.8a4.8 4.8 0 0 1
                             8.5-3.1 4.8 4.8 0 0 1 8.5 3.1Z"/>
                </svg>

                <em data-wish-count>0</em>
            </a>

            <!-- Account -->
            <a class="sf-header-icon"
               href="{{ \Illuminate\Support\Facades\Auth::guard('business_customer')->check()
                    ? route('business.customer.dashboard')
                    : route('business.customer.login') }}"
               title="{{ t('My Account','আমার অ্যাকাউন্ট') }}">

                <svg viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="1.8">
                    <circle cx="12" cy="7" r="4"/>
                    <path d="M4 21v-2a8 8 0 0 1 16 0v2"/>
                </svg>
            </a>

        </nav>
    </div>
</header>

<script>
(function () {
    const root = document.documentElement;
    const key = 'sf-color-theme';

    function getSavedTheme() {
        try {
            return localStorage.getItem(key)
                || localStorage.getItem('theme')
                || localStorage.getItem('sf_theme');
        } catch (e) {
            return null;
        }
    }

    function saveTheme(theme) {
        try {
            localStorage.setItem(key, theme);
            localStorage.setItem('theme', theme);
            localStorage.setItem('sf_theme', theme);
        } catch (e) {}
    }

    function applyTheme(theme, save = false) {
        const isDark = theme === 'dark';
        const mode = isDark ? 'dark' : 'light';

        root.setAttribute('data-theme', mode);
        root.setAttribute('data-bs-theme', mode);
        root.classList.toggle('dark', isDark);

        if (document.body) {
            document.body.setAttribute('data-theme', mode);
            document.body.setAttribute('data-bs-theme', mode);
            document.body.classList.toggle('dark', isDark);
        }

        document.querySelectorAll('[data-sf-theme-toggle]')
            .forEach(function (button) {
                button.setAttribute('aria-pressed', String(isDark));
                button.setAttribute(
                    'aria-label',
                    isDark ? 'Switch to light mode' : 'Switch to dark mode'
                );
                button.title = isDark ? 'Light Mode' : 'Dark Mode';
            });

        if (save) saveTheme(mode);
    }

    const saved = getSavedTheme();
    const preferred = window.matchMedia &&
        window.matchMedia('(prefers-color-scheme: dark)').matches
            ? 'dark'
            : 'light';

    applyTheme(
        saved === 'dark' || saved === 'light'
            ? saved
            : preferred
    );

    function initThemeToggle() {
        document.querySelectorAll('[data-sf-theme-toggle]')
            .forEach(function (button) {
                if (button.dataset.themeReady) return;
                button.dataset.themeReady = '1';

                button.addEventListener('click', function () {
                    const current = root.getAttribute('data-theme');
                    applyTheme(
                        current === 'dark' ? 'light' : 'dark',
                        true
                    );
                });
            });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initThemeToggle);
    } else {
        initThemeToggle();
    }
})();
</script>

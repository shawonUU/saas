<script>
/* Shared controls: appearance, accessible responsive navigation and keyboard behavior. */
(function () {
  'use strict';
  const init = () => {
    const themeButton = document.getElementById('theme-toggle');
    const label = document.getElementById('theme-label');
    const words = { light: @json(t('Light', 'লাইট')), dark: @json(t('Dark', 'ডার্ক')) };
    function refreshTheme() {
      const isDark = document.documentElement.dataset.theme === 'dark';
      if (themeButton) {
        themeButton.setAttribute('aria-pressed', String(isDark));
        themeButton.setAttribute('title', isDark ? words.light : words.dark);
      }
      if (label) label.textContent = isDark ? words.light : words.dark;
    }
    themeButton?.addEventListener('click', () => window.SaasUITheme?.toggle());
    document.addEventListener('saas:theme-change', refreshTheme);
    refreshTheme();

    const navButton = document.querySelector('[data-site-nav-toggle]');
    const nav = document.getElementById('site-primary-nav');
    const closeNav = () => {
      if (!nav || !navButton) return;
      nav.dataset.open = 'false';
      navButton.setAttribute('aria-expanded', 'false');
    };
    navButton?.addEventListener('click', () => {
      if (!nav) return;
      const expanded = nav.dataset.open !== 'true';
      nav.dataset.open = String(expanded);
      navButton.setAttribute('aria-expanded', String(expanded));
    });
    nav?.querySelectorAll('a').forEach(a => a.addEventListener('click', closeNav));
    document.addEventListener('click', event => {
      if (nav?.dataset.open === 'true' && !nav.contains(event.target) && !navButton?.contains(event.target)) closeNav();
    });
    document.addEventListener('keydown', event => {
      if (event.key === 'Escape') closeNav();
    });
    window.addEventListener('resize', () => { if (window.innerWidth > 1050) closeNav(); });
  };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, { once: true });
  else init();
})();
</script>

<script defer src="{{ asset('assets/saas-inline-search-select-v4.js') }}?v=20261128"></script>

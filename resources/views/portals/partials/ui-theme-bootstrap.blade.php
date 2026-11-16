<script>
/* Shared appearance preference for SaaS, admin and storefront. Runs before CSS paints. */
(function () {
  'use strict';
  const key = 'saas-ui-theme';
  const cookieName = 'saas_ui_theme';
  const domain = @json((string) config('saas.domain', ''));
  const hostname = location.hostname;
  const cleanDomain = String(domain).replace(/^https?:\/\//i, '').split(':')[0].replace(/^\./, '');
  const shared = cleanDomain && (hostname === cleanDomain || hostname.endsWith('.' + cleanDomain));
  const readCookie = () => {
    try {
      const item = document.cookie.split('; ').find(part => part.startsWith(cookieName + '='));
      return item ? decodeURIComponent(item.slice(cookieName.length + 1)) : null;
    } catch (_) { return null; }
  };
  const readLocal = name => { try { return localStorage.getItem(name); } catch (_) { return null; } };
  const valid = value => value === 'light' || value === 'dark';
  const persist = mode => {
    try {
      localStorage.setItem(key, mode);
      // Existing pages previously used these two keys. Keep legacy readers in sync.
      localStorage.setItem('nexabiz-theme', mode);
      localStorage.setItem('store-admin-theme', mode);
    } catch (_) {}
    try {
      document.cookie = cookieName + '=' + encodeURIComponent(mode) + '; Path=/; Max-Age=31536000; SameSite=Lax' + (shared ? '; Domain=' + cleanDomain : '');
    } catch (_) {}
  };
  const apply = (mode, save) => {
    if (!valid(mode)) mode = 'light';
    document.documentElement.dataset.theme = mode;
    document.documentElement.style.colorScheme = mode;
    if (save) persist(mode);
    document.dispatchEvent(new CustomEvent('saas:theme-change', { detail: { theme: mode } }));
    return mode;
  };
  const savedCookie = readCookie();
  const saved = valid(savedCookie) ? savedCookie :
    (readLocal(key) || readLocal('store-admin-theme') || readLocal('nexabiz-theme'));
  const initial = valid(saved) ? saved : (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
  window.SaasUITheme = {
    get: () => document.documentElement.dataset.theme,
    set: mode => apply(mode, true),
    toggle: () => apply(document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark', true)
  };
  apply(initial, false);
  window.addEventListener('storage', event => { if (event.key === key && valid(event.newValue)) apply(event.newValue, false); });
})();
</script>

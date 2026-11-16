<style id="saas-ui-foundation">
/* Unified UI design language, loaded after view-level CSS, no build pipeline required. */
:root {
    --ui-bg: #f6f8fc;
    --ui-panel: #ffffff;
    --ui-panel-alt: #f1f4fa;
    --ui-input: #ffffff;
    --ui-text: #17223a;
    --ui-muted: #66758d;
    --ui-border: #dfe5ef;
    --ui-accent: #5548d6;
    --ui-accent-hover: #4438b9;
    --ui-focus: rgba(94, 80, 225, .22);
    --ui-shadow: 0 14px 36px rgba(28, 41, 70, .055);
    color-scheme: light;
}
html[data-theme="dark"] {
    --ui-bg: #0f1727;
    --ui-panel: #192438;
    --ui-panel-alt: #222f45;
    --ui-input: #1b2940;
    --ui-text: #f1f5fd;
    --ui-muted: #b0bdd1;
    --ui-border: #3a4962;
    --ui-accent: #aa9dff;
    --ui-accent-hover: #cbc4ff;
    --ui-focus: rgba(170, 157, 255, .28);
    --ui-shadow: 0 14px 36px rgba(0, 0, 0, .19);
    color-scheme: dark;
}
html { width: 100%; scroll-behavior: smooth; }
body { min-width: 0; overflow-x: clip; background: var(--bg, var(--ui-bg)); color: var(--dark, var(--ui-text)); }
img, svg, video { max-width: 100%; }
button, input, textarea, select { font: inherit; }
button, [type="button"], [type="submit"] { -webkit-tap-highlight-color: transparent; }
button:not(:disabled), summary { cursor: pointer; }
:where(a, button, input, textarea, select, summary):focus-visible {
    outline: 3px solid var(--ui-focus); outline-offset: 2px;
}
input, select, textarea { accent-color: var(--ui-accent); }
input::placeholder, textarea::placeholder { color: var(--ui-muted); opacity: .82; }
[hidden] { display: none !important; }
.site-header { background: var(--ui-panel); border-bottom: 1px solid var(--ui-border); }
.nav-inner { min-width: 0; }
.site-header .brand, .site-header .brand-name { color: var(--ui-text); }
.site-header .brand-details { min-width: 0; }
.site-header .nav-links a { border-radius: 9px; color: var(--ui-muted); }
.site-header .nav-links a:is(:hover, :focus-visible, .active) { background: var(--ui-panel-alt); color: var(--ui-accent); }
.site-header .nav-actions { min-width: 0; flex-shrink: 0; }
.site-header .btn-outline, .site-header .admin-logout { background: var(--ui-panel); border-color: var(--ui-border); color: var(--ui-text); }
.site-header .btn-outline:hover, .site-header .admin-logout:hover { background: var(--ui-panel-alt); color: var(--ui-accent); }
.site-header .admin-avatar { background: var(--ui-panel-alt); color: var(--ui-accent); }
.theme-btn, .language-btn { background: var(--ui-panel-alt); border: 1px solid var(--ui-border); color: var(--ui-text); min-height: 39px; }
.theme-btn:hover, .language-btn:hover { background: var(--ui-panel); border-color: var(--ui-accent); }
.nav-menu-toggle { display: none; align-items: center; justify-content: center; border: 1px solid var(--ui-border); background: var(--ui-panel-alt); color: var(--ui-text); border-radius: 10px; width: 41px; height: 41px; font-size: 21px; line-height: 1; flex-shrink: 0; }
.nav-menu-toggle[aria-expanded="true"] { background: var(--ui-accent); color: #fff; }
.container, .workspace-content, .card, .authority-wrap { min-width: 0; }
.card, .ws-card, .ws-panel, .feature-card, .process-card, .authority-wrap .card {
    border-color: var(--ui-border); box-shadow: var(--ui-shadow);
}
.card, .authority-wrap .card { background: var(--ui-panel); color: var(--ui-text); }
.card label, .card h1, .card h2, .card strong { color: var(--ui-text); }
.card input, .card select, .card textarea,
.authority-wrap input:not([type="checkbox"]), .authority-wrap select, .authority-wrap textarea {
    background: var(--ui-input); color: var(--ui-text); border: 1px solid var(--ui-border); min-width: 0;
}
.card input:focus, .card select:focus, .card textarea:focus { border-color: var(--ui-accent); box-shadow: 0 0 0 3px var(--ui-focus); }
html[data-theme="dark"] .btn-outline { background: var(--ui-panel); color: var(--ui-text); border-color: var(--ui-border); }
html[data-theme="dark"] .alert.success { background: #173b35; border-color: #2c7663; color: #b7f6d6; }
html[data-theme="dark"] .alert.error { background: #47242f; border-color: #8a4351; color: #ffd6da; }
html[data-theme="dark"] .site-footer { background: #0a1120; }
/* Business workspace: consistent dark surfaces even on pages with inlined light defaults. */
.workspace-layout { min-width: 0; max-width: 100%; }
.workspace-layout .workspace-content { min-width: 0; }
.workspace-layout .workspace-sidebar { color: var(--ws-text, var(--ui-text)); }
.workspace-layout .ws-nav-dashboard, .workspace-layout .ws-menu>summary, .workspace-layout .ws-menu-direct, .workspace-layout .ws-children a { color: var(--ws-muted, var(--ui-muted)); }
.workspace-layout .ws-nav-dashboard:hover, .workspace-layout .ws-menu>summary:hover,
.workspace-layout .ws-menu-direct:hover, .workspace-layout .ws-children a:hover { background: var(--ws-bg, var(--ui-panel-alt)); color: var(--ws-accent, var(--ui-accent)); }
.workspace-layout :is(.ws-nav-dashboard,.ws-menu-direct,.ws-children a).active { background: color-mix(in srgb, var(--ui-accent) 12%, transparent); color: var(--ws-accent, var(--ui-accent)); }
.workspace-layout :is(.ws-panel, .ws-stat, .ws-module-card, .ws-quick-link) { min-width: 0; }
.workspace-layout :is(input, select, textarea):not([type="checkbox"]):not([type="radio"]) { max-width: 100%; }
html[data-theme="dark"] .workspace-layout {
    --ws-panel: #192438; --ws-bg: #0f1727; --ws-text: #f1f5fd;
    --ws-muted: #b0bdd1; --ws-line: #3a4962; --ws-accent: #aa9dff;
}
html[data-theme="dark"] .workspace-layout :is(.ws-stat,.ws-panel,.ws-module-card,.ws-quick-link,.ws-sidebar,.um-panel,.ac-panel) { border-color: var(--ui-border); }
html[data-theme="dark"] .workspace-layout .ws-note { background: var(--ui-panel-alt); color: var(--ui-muted); }
html[data-theme="dark"] .workspace-layout .ws-mini-count { background: var(--ui-panel-alt); }
html[data-theme="dark"] .workspace-layout .ws-side-search input { color: var(--ui-text); }
/* Consistent admin data tables and long content; keep horizontal scrolling within the panel. */
.cfg-panel, .um-panel, .ac-panel { max-width: 100%; min-width: 0; }
.cfg-table-scroll, .um-table-scroll, .ac-table-scroll { max-width: 100%; overscroll-behavior-inline: contain; -webkit-overflow-scrolling: touch; }
.cfg-table :is(td,th) { vertical-align: middle; }
.cfg-table tbody tr:last-child td { border-bottom: 0; }
.cfg-table th:last-child, .cfg-table td:last-child { position: sticky; right: 0; z-index: 1; background: var(--ui-panel); box-shadow: -7px 0 12px -12px rgba(0,0,0,.2); }
.cfg-table thead tr:first-child th:last-child { z-index: 4; background: var(--ui-panel-alt); }
.cfg-table .cfg-filter-row th:last-child { z-index: 3; background: var(--ui-panel-alt); }
.cfg-table tbody tr:hover td:last-child { background: var(--ui-panel-alt); }
.cfg-table .cfg-actions { flex-wrap: nowrap; }
.cfg-table a, .cfg-table button, .cfg-filter-row input, .cfg-filter-row select { touch-action: manipulation; }
/* Other dialogs: keep actual overlay above the page and readable in both appearances. */
.um-overlay, .cfg-modal { isolation: isolate; }
.um-overlay[hidden], .cfg-modal[hidden] { display: none !important; }
.um-dialog { background: var(--ui-panel); color: var(--ui-text); border-color: var(--ui-border); }
.um-form :is(input,select,textarea) { background: var(--ui-input); color: var(--ui-text); border-color: var(--ui-border); }
/* Authorization and user management: consistent contrast and tap targets. */
html[data-theme="dark"] .ac-eyebrow,html[data-theme="dark"] .ac-extra-head strong{color:var(--ui-accent)}
html[data-theme="dark"] .ac-crud-check {color:var(--ui-muted)}
html[data-theme="dark"] .ac-crud-check.is-on {background:var(--ui-panel-alt);color:var(--ui-accent)}
html[data-theme="dark"] .um-control,html[data-theme="dark"] .um-search input {background:var(--ui-input);color:var(--ui-text);border-color:var(--ui-border)}
html[data-theme="dark"] .ac-tag-muted {color:var(--ui-muted)}
html[data-theme="dark"] .ac-notice:not(.error){background:#173b35;border-color:#2c7663;color:#b7f6d6}
html[data-theme="dark"] .ac-notice.error{background:#47242f;border-color:#8a4351;color:#ffd6da}
/* Public/authority pages */
html[data-theme="dark"] .authority-wrap :is(.editor-item,.access-module,.access-menu) { border-color: var(--ui-border); }
html[data-theme="dark"] .authority-wrap :is(.access-child,.editor-item summary) { color: var(--ui-text); }
html[data-theme="dark"] .hero-label, html[data-theme="dark"] .eyebrow { color: var(--ui-accent); }
@media (max-width: 1050px) {
    .site-header .nav-inner { gap: 10px; }
    .nav-menu-toggle { display: inline-flex; order: 4; }
    .site-header .nav-links { display: none !important; position: absolute; top: calc(100% + 6px); left: max(12px, env(safe-area-inset-left)); right: max(12px, env(safe-area-inset-right)); z-index: 500; margin: 0; padding: 10px; border-radius: 14px; border: 1px solid var(--ui-border); background: var(--ui-panel); box-shadow: 0 15px 50px rgba(0,0,0,.18); flex-direction: column; align-items: stretch; gap: 2px; max-height: min(70dvh, 600px); overflow: auto; }
    .site-header .nav-links[data-open="true"] { display: flex !important; }
    .site-header .nav-links a { padding: 12px 14px; display: block; }
    .site-header .nav-actions { margin-left: auto; }
    .workspace-layout .workspace-content { max-width: 100%; }
}
@media (max-width: 750px) {
    .site-header .nav-inner { width: calc(100% - 24px); }
    .site-header .brand { flex: 1; min-width: 0; }
    .site-header .nav-actions { gap: 6px; }
    .site-header .nav-actions :is(.admin-name,.admin-role) { display: none !important; }
    .site-header .admin-profile { padding-left: 0; border: 0; }
    .site-header .admin-profile .admin-avatar { width: 32px; height: 32px; }
    .site-header .theme-btn { min-width: 38px; padding: 8px; }
    .site-header .theme-btn #theme-label { display: none; }
    .site-header .nav-actions .btn { min-height: 37px; padding: 8px 10px; font-size: 11px; }
    .workspace-layout .workspace-content { padding-left: 14px; padding-right: 14px; }
    .workspace-layout :is(.ws-panel,.ws-card) { padding: 17px; }
    .workspace-layout :is(.ws-hero, .ws-intro, .ws-toolbar, .ws-section-head, .um-heading, .ac-head, .cfg-heading) { min-width: 0; }
    .workspace-layout :is(.ws-intro,.um-heading,.ac-head,.cfg-heading) { gap: 13px; }
    .cfg-bottom, .um-bottom { align-items: flex-start; }
    .card { margin: 20px 12px; padding: 20px; }
    .authority-wrap { width: calc(100% - 24px); margin: 22px auto 45px; }
    .authority-edit, .authority-form { min-width: 0; }
}
@media (max-width: 420px) {
    .site-header .brand-name { max-width: 112px; }
    .site-header .brand-mark { display: none; }
    .site-header .nav-actions a.btn:not(.btn-outline) { display: none; }
    .site-header .admin-profile { display: none; }
    .site-header .language-btn { min-width: 38px; padding: 7px 8px; }
    .workspace-layout .ws-stats, .workspace-layout .um-kpis { grid-template-columns: 1fr 1fr; }
}
@media (prefers-reduced-motion: reduce) {
    html { scroll-behavior: auto; }
    *, *::before, *::after { transition-duration: .01ms !important; animation-duration: .01ms !important; }
}
</style>

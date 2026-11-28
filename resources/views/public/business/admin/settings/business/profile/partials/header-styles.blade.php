<style>
/* Business admin header only — inherits the app's light/dark design tokens. */
.saas-admin-header{height:72px;z-index:500;background:var(--surface,var(--white));border-bottom:1px solid var(--border);box-shadow:0 1px 0 rgba(30,41,70,.025)}
.saas-admin-header .saas-header-inner{max-width:none;width:calc(100% - 56px);height:100%;gap:20px}
.saas-admin-header .saas-header-brand{margin:0;min-width:0;max-width:min(50%,420px)}
.saas-admin-header .brand-name{font-size:16px;color:var(--dark)}
.saas-admin-header .brand-subtitle{color:var(--muted);font-size:11px}
.saas-header-actions{display:flex;align-items:center;gap:10px;margin-left:auto;isolation:isolate}
.saas-popover-anchor{position:relative}
.saas-icon-trigger,.saas-profile-trigger{display:inline-flex;align-items:center;justify-content:center;background:var(--surface-alt,var(--bg));border:1px solid var(--border);color:var(--text);cursor:pointer;transition:background .15s,border-color .15s,box-shadow .15s,transform .15s}
.saas-icon-trigger{width:43px;height:43px;border-radius:13px;position:relative}
.saas-icon-trigger>svg{width:20px;height:20px}
.saas-profile-trigger{height:44px;gap:8px;padding:4px 9px 4px 4px;border-radius:14px}
.saas-profile-trigger>svg{width:14px;height:14px;color:var(--muted)}
.saas-trigger-avatar,.saas-summary-avatar{display:grid;place-items:center;border-radius:11px;background:linear-gradient(145deg,var(--primary),#8278ef);color:white;font-weight:800;box-shadow:inset 0 1px 0 rgba(255,255,255,.2)}
.saas-trigger-avatar{height:34px;width:34px;font-size:14px}
.saas-icon-trigger:hover,.saas-profile-trigger:hover,.saas-icon-trigger[aria-expanded="true"],.saas-profile-trigger[aria-expanded="true"]{border-color:var(--primary);background:var(--surface,var(--white));box-shadow:0 0 0 3px rgba(111,94,230,.10)}
.saas-admin-header button:focus-visible,.saas-admin-header a:focus-visible,.saas-password-dialog button:focus-visible,.saas-password-dialog input:focus-visible{outline:3px solid var(--primary);outline-offset:3px}
.saas-admin-header [hidden],.saas-password-dialog [hidden]{display:none!important}
.saas-notification-count{position:absolute;top:-5px;right:-5px;min-width:19px;height:19px;padding:0 4px;border-radius:9px;background:#e74666;color:white;display:grid;place-items:center;font-size:10px;font-weight:800;border:2px solid var(--surface,var(--white));line-height:1}
.saas-flyout{position:absolute;right:0;top:calc(100% + 13px);z-index:600;border:1px solid var(--border);border-radius:18px;background:var(--surface,var(--white));color:var(--text);box-shadow:0 22px 60px rgba(17,24,39,.19),0 7px 15px rgba(17,24,39,.05);overflow:hidden;animation:saas-flyout-in .13s ease-out}
@keyframes saas-flyout-in{from{opacity:0;transform:translateY(-5px)}to{opacity:1;transform:translateY(0)}}
.saas-profile-flyout{width:320px;padding:9px}
.saas-profile-summary{display:flex;gap:12px;align-items:center;padding:16px 12px 15px;background:var(--surface-alt,var(--bg));border-radius:12px;margin-bottom:8px;min-width:0}
.saas-summary-avatar{width:46px;height:46px;flex:0 0 46px;font-size:19px;border-radius:13px}
.saas-summary-text{display:block;min-width:0;line-height:1.4}
.saas-summary-text strong,.saas-summary-text small{display:block;max-width:208px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.saas-summary-text strong{font-size:14px;font-weight:800;color:var(--dark)}
.saas-summary-text small{font-size:12px;color:var(--muted)}
.saas-summary-text .saas-summary-email{font-size:11px;margin-top:3px}
.saas-flyout-label{padding:10px 12px 5px;font-size:10px;font-weight:850;letter-spacing:.09em;text-transform:uppercase;color:var(--muted)}
.saas-menu-row{width:100%;min-height:44px;display:flex;align-items:center;gap:11px;padding:10px 12px;border:none;border-radius:10px;background:transparent;color:var(--text);text-align:left;cursor:pointer;font:inherit;font-size:12.5px;font-weight:700;transition:background .13s,color .13s}
.saas-menu-row:hover{background:var(--surface-alt,var(--bg));color:var(--primary)}
.saas-menu-row>svg{width:18px;height:18px;flex:0 0 18px;color:var(--muted)}
.saas-menu-row:hover>svg{color:var(--primary)}
.saas-menu-row>span:nth-child(2){flex:1;min-width:0}
.saas-menu-row .saas-row-end{width:15px;height:15px;flex:0 0 15px;color:var(--muted)}
.saas-row-value{font-size:11px;font-weight:800;color:var(--primary);background:rgba(115,101,230,.10);padding:5px 9px;border-radius:999px;white-space:nowrap}
.saas-flyout-divider{height:1px;margin:8px 9px;background:var(--border)}
.saas-menu-language,.saas-profile-flyout form{width:100%}
.saas-signout-row{color:#d64456;margin-bottom:2px}
.saas-signout-row>svg{color:#d64456}
.saas-signout-row:hover{background:rgba(220,65,83,.08);color:#c02c40}
.saas-notif-flyout{width:min(385px,calc(100vw - 28px));}
.saas-notif-header{padding:19px 18px 15px;border-bottom:1px solid var(--border);display:flex;align-items:flex-start;justify-content:space-between;gap:10px}
.saas-notif-header h2{font-size:15px;font-weight:850;color:var(--dark);letter-spacing:-.2px;margin:0}
.saas-notif-header p{font-size:11px;color:var(--muted);margin-top:4px}
.saas-mark-read{border:0;background:transparent;color:var(--primary);font-size:11px;font-weight:800;cursor:pointer;padding:5px 0;white-space:nowrap}
.saas-mark-read:disabled{opacity:.55;cursor:wait}
.saas-notif-list{max-height:min(420px,60vh);overflow-y:auto;overscroll-behavior:contain}
.saas-notif-item{width:100%;display:flex;align-items:flex-start;gap:11px;background:transparent;border:0;border-bottom:1px solid var(--border);padding:14px 16px;text-align:left;cursor:pointer;color:var(--text);font:inherit;transition:background .12s}
.saas-notif-item:last-child{border-bottom:0}
.saas-notif-item:hover{background:var(--surface-alt,var(--bg))}
.saas-notif-item.is-unread{background:rgba(105,86,215,.055)}
.saas-notif-item.is-unread:hover{background:rgba(105,86,215,.10)}
.saas-notif-symbol{height:36px;width:36px;flex:0 0 36px;border-radius:11px;background:rgba(105,86,215,.11);display:grid;place-items:center;color:var(--primary)}
.saas-notif-symbol>svg{width:18px;height:18px}
.saas-notif-copy{min-width:0;flex:1}
.saas-notif-copy strong{font-size:12px;line-height:1.45;color:var(--dark);font-weight:800;display:block;overflow-wrap:anywhere}
.saas-notif-copy p{font-size:11px;line-height:1.6;color:var(--muted);margin-top:4px;overflow-wrap:anywhere}
.saas-notif-copy time{font-size:10px;color:var(--muted);display:block;margin-top:6px}
.saas-unread-dot{width:7px;height:7px;flex:0 0 7px;border-radius:50%;background:var(--primary);margin-top:8px}
.saas-notif-empty{min-height:172px;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:8px;text-align:center;padding:28px 22px;font-size:12px;color:var(--muted);line-height:1.65}
.saas-notif-empty svg{width:32px;height:32px;color:var(--muted);opacity:.65}
.saas-notif-foot{text-align:center;padding:11px 15px;border-top:1px solid var(--border);font-size:10px;color:var(--muted)}
.saas-password-dialog{border:1px solid var(--border);border-radius:21px;padding:0;background:var(--surface,var(--white));color:var(--dark);width:min(460px,calc(100vw - 30px));max-height:min(90dvh,720px);margin:auto;box-shadow:0 25px 100px rgba(0,0,0,.26);overflow-y:auto}
.saas-password-dialog::backdrop{background:rgba(12,18,36,.63);backdrop-filter:blur(4px)}
.saas-password-form{padding:28px;min-width:0}
.saas-dialog-heading{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px}
.saas-dialog-symbol{height:49px;width:49px;display:grid;place-items:center;border-radius:15px;color:var(--primary);background:rgba(115,101,230,.11)}
.saas-dialog-symbol svg{height:25px;width:25px}
.saas-dialog-close{width:32px;height:32px;border:0;border-radius:9px;background:var(--surface-alt,var(--bg));color:var(--muted);font-size:23px;cursor:pointer;display:grid;place-items:center}
.saas-password-dialog h2{font-size:22px;font-weight:850;letter-spacing:-.5px;margin:0 0 6px;color:var(--dark)}
.saas-dialog-subtitle{font-size:12px;line-height:1.65;color:var(--muted);margin-bottom:22px}
.saas-dialog-field{display:block;font-size:12px;color:var(--text);font-weight:750;margin:0 0 15px}
.saas-dialog-field input{display:block;width:100%;margin-top:7px;min-height:44px;border:1px solid var(--border);border-radius:11px;padding:10px 13px;background:var(--input-bg,var(--surface-alt,var(--bg)));color:var(--dark);font:inherit;font-size:14px;outline:0}
.saas-dialog-field input:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(111,94,230,.13)}
.saas-password-error{padding:11px 13px;border-radius:10px;background:rgba(220,65,83,.10);color:#c93a4b;font-size:12px;line-height:1.6;margin-bottom:16px}
.saas-dialog-actions{display:flex;gap:10px;justify-content:flex-end;margin-top:25px}
.saas-dialog-actions button{padding:11px 15px;border-radius:11px;border:1px solid var(--border);font:inherit;font-size:12px;font-weight:800;cursor:pointer}
.saas-dialog-cancel{background:var(--surface-alt,var(--bg));color:var(--text)}
.saas-dialog-submit{background:var(--primary);color:#fff;border-color:var(--primary)!important}
@media(max-width:600px){.saas-admin-header{height:65px}.saas-admin-header .saas-header-inner{width:calc(100% - 26px)}.saas-admin-header .brand-logo{width:37px;height:37px}.saas-admin-header .brand-name{font-size:13px;max-width:44vw}.saas-admin-header .brand-subtitle{font-size:10px}.saas-header-actions{gap:7px}.saas-icon-trigger{width:40px;height:40px}.saas-profile-trigger{height:40px}.saas-trigger-avatar{height:30px;width:30px}.saas-flyout{position:fixed;top:70px;left:13px;right:13px;width:auto;max-height:calc(100dvh - 85px);overflow-y:auto}.saas-password-form{padding:22px}.saas-notif-list{max-height:calc(100dvh - 250px)}}
@media(prefers-reduced-motion:reduce){.saas-flyout,.saas-icon-trigger,.saas-profile-trigger,.saas-menu-row{animation:none;transition:none}}

/* Header preferences: compact navbar controls; avatar alone opens account menu. */
.saas-topbar-preferences{display:flex;align-items:center;gap:8px}
.saas-topbar-language{display:inline-flex;align-items:center;margin:0}
.saas-topbar-control{min-height:42px;display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:0 13px;border:1px solid var(--border);border-radius:12px;color:var(--text);background:var(--surface-alt,var(--bg));font:inherit;font-size:12px;font-weight:750;white-space:nowrap;cursor:pointer;transition:background .15s,border-color .15s,color .15s,box-shadow .15s}
.saas-topbar-control svg{height:18px;width:18px;flex:0 0 18px}
.saas-topbar-control:hover,.saas-topbar-control:focus-visible{color:var(--primary);background:var(--surface,var(--white));border-color:var(--primary);box-shadow:0 0 0 3px rgba(111,94,230,.10)}
.saas-topbar-control:focus-visible{outline:2px solid var(--primary);outline-offset:2px}
.saas-topbar-theme{min-width:88px}
html[data-theme="dark"] .saas-topbar-theme .saas-theme-icon-light,html:not([data-theme="dark"]) .saas-topbar-theme .saas-theme-icon-dark{display:none}
.saas-profile-trigger{width:44px;min-width:44px;height:44px;padding:4px;border-radius:13px}
.saas-topbar-lang-button{min-width:76px}
@media(max-width:650px){.saas-header-actions{gap:6px}.saas-topbar-preferences{gap:6px}.saas-topbar-control{min-height:40px}.saas-topbar-theme{width:40px;min-width:40px;padding:0}.saas-topbar-theme #theme-label{display:none}.saas-topbar-lang-button{padding:0 9px;min-width:0}.saas-topbar-lang-button svg{display:none}.saas-profile-trigger{width:40px;min-width:40px;height:40px}}
@media(max-width:380px){.saas-admin-header .brand-details{max-width:105px}.saas-admin-header .brand-subtitle{display:none}.saas-topbar-lang-button{font-size:11px;padding:0 7px}.saas-topbar-preferences{gap:4px}.saas-header-actions{gap:5px}}
</style>

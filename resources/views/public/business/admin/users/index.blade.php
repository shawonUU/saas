@extends('public.business.admin.layout')

@push('styles')

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


<style>
.um-page .workspace-content{max-width:1600px}
.um-heading{display:flex;justify-content:space-between;align-items:center;gap:18px;margin-bottom:22px}
.um-heading h1{font-family:Manrope,'DM Sans',sans-serif;font-size:27px;font-weight:800;letter-spacing:-.9px;margin-bottom:6px}
.um-heading p{font-size:12px;color:var(--ws-muted);line-height:1.7}
.um-head-icon{display:inline-grid;place-items:center;width:38px;height:38px;border-radius:12px;background:#f0efff;color:#6258df;margin-right:10px}
.um-head-icon .ws-svg{height:20px;width:20px}
.um-title{display:flex;align-items:center}
.um-kpis{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:15px;margin-bottom:22px}
.um-kpi{background:var(--ws-panel);border:1px solid var(--ws-line);border-radius:16px;padding:21px;display:flex;justify-content:space-between;align-items:center;gap:10px}
.um-kpi label{display:block;font-size:11px;color:var(--ws-muted);font-weight:700;margin-bottom:8px}
.um-kpi strong{font-family:Manrope,sans-serif;font-size:29px;letter-spacing:-1px}
.um-kpi-icon{width:43px;height:43px;display:grid;place-items:center;border-radius:13px;background:#efedff;color:#6258df}
.um-kpi:nth-child(2) .um-kpi-icon{background:#e3f7f3;color:#159981}
.um-kpi:nth-child(3) .um-kpi-icon{background:#fff0e5;color:#d68a3b}
.um-kpi-icon .ws-svg{width:21px;height:21px}
.um-panel{background:var(--ws-panel);border:1px solid var(--ws-line);border-radius:17px;overflow:hidden}
.um-panel-head{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:21px 23px 13px}
.um-panel-head h2{font-size:16px;font-family:Manrope,sans-serif;font-weight:800}
.um-panel-head p{font-size:11px;color:var(--ws-muted);margin-top:5px}
.um-filters{display:flex;align-items:center;gap:10px;padding:7px 23px 21px;flex-wrap:wrap}
.um-search{flex:1;min-width:210px;position:relative}
.um-search .ws-svg{position:absolute;left:13px;top:11px;height:17px;width:17px;color:#9aa3b7}
.um-control{height:40px;width:100%;background:var(--ws-panel);border:1px solid var(--ws-line);color:var(--ws-text);border-radius:10px;padding:0 12px;outline:none;font-family:inherit;font-size:12px}
.um-search .um-control{padding-left:39px}
.um-control:focus{border-color:#9f98f0;box-shadow:0 0 0 3px rgba(99,91,223,.10)}
.um-filters select{width:auto;min-width:130px}
.um-clear{font-size:12px;font-weight:700;color:var(--ws-muted);padding:9px}
.um-table-wrap{overflow:auto}
.um-table{width:100%;border-collapse:collapse;min-width:800px;text-align:left}
.um-table th{background:var(--ws-bg);padding:14px 20px;color:var(--ws-muted);font-size:10px;letter-spacing:.65px;text-transform:uppercase;white-space:nowrap}
.um-table td{padding:15px 20px;border-top:1px solid var(--ws-line);font-size:12px;vertical-align:middle}
.um-table tbody tr:hover{background:rgba(99,91,223,.025)}
.um-person{display:flex;align-items:center;gap:10px;min-width:175px}
.um-avatar{display:grid;place-items:center;width:38px;height:38px;flex-shrink:0;border-radius:11px;background:#eae9ff;color:#6259d7;font-size:14px;font-weight:800}
.um-person b{display:block;font-size:12px;font-weight:800;color:var(--ws-text);white-space:nowrap}
.um-person small{display:block;margin-top:5px;color:var(--ws-muted);font-size:10px}
.um-code{color:var(--ws-muted);font-size:11px}
.um-tag{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border:1px solid var(--ws-line);border-radius:8px;background:var(--ws-bg);font-weight:700;font-size:11px;white-space:nowrap}
.um-status{display:inline-flex;align-items:center;gap:6px;border-radius:20px;padding:6px 10px;font-weight:800;font-size:10px;white-space:nowrap}
.um-status:before{content:'';width:6px;height:6px;border-radius:50%;background:currentColor}
.um-status.active{background:#e5f7ef;color:#168356}
.um-status.inactive{background:#fff0f0;color:#c75d63}
.um-actions{display:flex;gap:6px;align-items:center}
.um-icon-button{width:34px;height:34px;display:grid;place-items:center;border:1px solid var(--ws-line);border-radius:9px;background:var(--ws-panel);color:var(--ws-muted);cursor:pointer}
.um-icon-button:hover{border-color:#b9b3f3;color:var(--ws-accent)}
.um-icon-button .ws-svg{width:16px;height:16px}
.um-locked{font-size:10px;color:var(--ws-muted)}
.um-empty{text-align:center;padding:55px 20px;color:var(--ws-muted);font-size:12px}
.um-empty .ws-svg{height:34px;width:34px;margin-bottom:11px;color:#a5a5bb}
.um-empty strong{display:block;color:var(--ws-text);font-size:14px;margin-bottom:6px}
.um-bottom{display:flex;justify-content:space-between;gap:12px;align-items:center;padding:17px 22px;border-top:1px solid var(--ws-line);color:var(--ws-muted);font-size:11px}
.um-paging{display:flex;gap:7px}
.um-paging a,.um-paging span{display:inline-flex;align-items:center;justify-content:center;border:1px solid var(--ws-line);border-radius:9px;padding:8px 12px;color:var(--ws-text);font-weight:700}
.um-paging a:hover{border-color:#a69fee;color:var(--ws-accent)}
.um-paging span{opacity:.45}
.um-overlay{position:fixed;inset:0;z-index:450;background:rgba(15,22,43,.61);display:flex;align-items:center;justify-content:center;padding:20px;overflow:auto}
.um-overlay[hidden]{display:none}
.um-dialog{width:min(580px,100%);max-height:min(92dvh,800px);display:flex;flex-direction:column;overflow:hidden;background:var(--ws-panel);color:var(--ws-text);border:1px solid var(--ws-line);border-radius:18px;box-shadow:0 30px 85px rgba(0,0,0,.23)}
.um-modal-head{padding:22px 25px;border-bottom:1px solid var(--ws-line);display:flex;align-items:center;justify-content:space-between;gap:10px}
.um-modal-head h2{font-family:Manrope,sans-serif;font-size:19px;font-weight:800}
.um-modal-head p{font-size:11px;color:var(--ws-muted);margin-top:5px}
.um-close{border:0;background:var(--ws-bg);color:var(--ws-muted);width:32px;height:32px;border-radius:9px;font-size:20px;cursor:pointer}
.um-form{overflow:auto;min-height:0}
.um-form-inner{padding:23px 25px;display:grid;grid-template-columns:1fr 1fr;gap:17px}
.um-field{min-width:0}
.um-field.full{grid-column:1/-1}
.um-field label{display:block;font-size:12px;font-weight:800;margin-bottom:8px}
.um-field label em{font-style:normal;color:#e4706c}
.um-field small{display:block;margin-top:6px;font-size:10px;color:var(--ws-muted);line-height:1.5}
.um-field .um-control{height:44px}
.um-field input[readonly]{background:var(--ws-bg);opacity:.8}
.um-alert{grid-column:1/-1;padding:11px 13px;background:#fff1ee;border:1px solid #f4c5ba;color:#a23b2a;font-size:11px;border-radius:10px}
.um-form-footer{display:flex;gap:9px;align-items:center;justify-content:flex-end;padding:17px 25px;border-top:1px solid var(--ws-line)}
.um-form-footer .ws-button{min-width:103px}
html[data-theme="dark"] .um-head-icon,html[data-theme="dark"] .um-avatar,html[data-theme="dark"] .um-kpi-icon{background:#34305e;color:#d8d5ff}
.um-status.pending{background:#fff4df;color:#9b702b}
html[data-theme="dark"] .um-status.pending{background:#473824;color:#f6c379}
html[data-theme="dark"] .um-status.active{background:#153f35;color:#78d6b1}
html[data-theme="dark"] .um-status.inactive{background:#522b32;color:#ffb3ba}
html[data-theme="dark"] .um-alert{background:#4c2527;color:#ffc1ad;border-color:#834b47}
body.um-lock{overflow:hidden}
@media(max-width:900px){.um-kpis{gap:10px}.um-kpi{padding:15px}.um-kpi strong{font-size:25px}}
@media(max-width:650px){.um-heading{align-items:flex-start;flex-direction:column}.um-heading h1{font-size:23px}.um-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}.um-kpi:nth-child(3){grid-column:1/-1}.um-panel-head,.um-filters{padding-left:15px;padding-right:15px}.um-filters select{flex:1}.um-bottom{flex-direction:column;align-items:flex-start}.um-form-inner{grid-template-columns:1fr;padding:19px}.um-modal-head,.um-form-footer{padding:17px 19px}}
</style>
@endpush

@section('content')
@php
    $businessKey=(string)$business->domain;
    $indexUrl=route('business.admin.authorization.users.index',['business'=>$businessKey]);
    $storeUrl=route('business.admin.authorization.users.store',['business'=>$businessKey]);
@endphp
<svg xmlns="http://www.w3.org/2000/svg" style="position:absolute;width:0;height:0;overflow:hidden" aria-hidden="true">
    <symbol id="ws-i-grid" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></symbol>
    <symbol id="ws-i-home" viewBox="0 0 24 24"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V10ZM9 21v-8h6v8"/></symbol>
    <symbol id="ws-i-folder" viewBox="0 0 24 24"><path d="M3 6a2 2 0 0 1 2-2h5l2 3h7a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6Z"/></symbol>
    <symbol id="ws-i-chevron" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></symbol>
    <symbol id="ws-i-menu" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/></symbol>
    <symbol id="ws-i-x" viewBox="0 0 24 24"><path d="M5 5l14 14M19 5 5 19"/></symbol>
    <symbol id="ws-i-search" viewBox="0 0 24 24"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></symbol>
    <symbol id="ws-i-logout" viewBox="0 0 24 24"><path d="M9 4H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h4m5-4 4-4-4-4m4 4H9"/></symbol>
    <symbol id="ws-i-users" viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"/><path d="M3 20v-2a6 6 0 0 1 12 0v2M17 5a3 3 0 0 1 0 6m1 3a5 5 0 0 1 3 5v1"/></symbol>
    <symbol id="ws-i-edit" viewBox="0 0 24 24"><path d="m16 5 3 3M4 20l4.5-1 10.7-10.7a2 2 0 0 0-2.8-2.8L5.7 16.2 4 20Z"/></symbol>
    <symbol id="ws-i-plus" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></symbol>
    <symbol id="ws-i-check" viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></symbol>
    <symbol id="ws-i-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></symbol>
</svg>
<div class="workspace-layout listui-page um-page">
    @include('public.business.admin.sidebar')
    <div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div>
    <div class="workspace-content">
        <div class="ws-toolbar">
            <div class="ws-header-actions">
                <button class="ws-button ws-mobile-menu" type="button" data-ws-sidebar-toggle aria-label="{{ t('Open menu','মেনু খুলুন') }}"><svg class="ws-svg"><use href="#ws-i-menu"/></svg></button>
                <div class="ws-breadcrumb"><svg class="ws-svg"><use href="#ws-i-home"/></svg><span>{{ t('Workspace','ওয়ার্কস্পেস') }}</span><span>/</span><strong>{{ t('Users','ইউজার') }}</strong></div>
            </div>
        </div>
        <div class="um-heading">
            <div>
                <div class="um-title"><span class="um-head-icon"><svg class="ws-svg"><use href="#ws-i-users"/></svg></span><h1>{{ t('Team Users','টিম ইউজার') }}</h1></div>
                <p>{{ t('Manage your team members, roles and access from one place.','এক জায়গা থেকে টিমের সদস্য, রোল ও অ্যাক্সেস পরিচালনা করুন।') }}</p>
            </div>
            <button type="button" class="ws-button ws-button-primary" id="um-create"><svg class="ws-svg"><use href="#ws-i-plus"/></svg>{{ t('Add New User','নতুন ইউজার') }}</button>
        </div>
        <section class="um-kpis" aria-label="{{ t('User statistics','ইউজার পরিসংখ্যান') }}">
            <div class="um-kpi"><div><label>{{ t('Total users','মোট ইউজার') }}</label><strong>{{ $totalMembers }}</strong></div><span class="um-kpi-icon"><svg class="ws-svg"><use href="#ws-i-users"/></svg></span></div>
            <div class="um-kpi"><div><label>{{ t('Active users','সক্রিয় ইউজার') }}</label><strong>{{ $activeMembers }}</strong></div><span class="um-kpi-icon"><svg class="ws-svg"><use href="#ws-i-check"/></svg></span></div>
            <div class="um-kpi"><div><label>{{ t('Inactive users','নিষ্ক্রিয় ইউজার') }}</label><strong>{{ $inactiveMembers }}</strong></div><span class="um-kpi-icon"><svg class="ws-svg"><use href="#ws-i-clock"/></svg></span></div>
        </section>
        <section class="cfg-panel um-panel listui-list-card">
            <div class="um-panel-head listui-extra-heading"><div><h2>{{ t('All Users','সকল ইউজার') }}</h2><p>{{ t('Showing users from this business only','শুধু এই ব্যবসার ইউজার দেখানো হচ্ছে') }}</p></div><span class="ws-mini-count">{{ $members->total() }} {{ t('results','ফলাফল') }}</span></div>
            
            @include('public.business.admin.shared.list-ui.standalone-toolbar', ['hasFilters'=>true, 'excludeParams'=>['search','status','role_id']])
            <div class="um-table-wrap cfg-table-scroll listui-table-scroll">
                <table class="cfg-table um-table"><thead><tr>
                    <th>{{ t('User','ইউজার') }}</th><th>{{ t('User code','ইউজার কোড') }}</th><th>{{ t('Role','রোল') }}</th><th>{{ t('Status','স্ট্যাটাস') }}</th><th>{{ t('Joined','যোগদানের তারিখ') }}</th><th>{{ t('Actions','অ্যাকশন') }}</th>
                </tr><tr id="listui-colfilters" class="cfg-filter-row listui-filter-row"><th><input class="cfg-input um-control" form="listui-standard-form" name="f[identity]" value="{{ is_scalar(request('f.identity')) ? request('f.identity') : '' }}" placeholder="{{ t('Name / email','নাম / ইমেইল') }}" aria-label="{{ t('Filter user name or email','নাম বা ইমেইল ফিল্টার') }}"></th><th><input class="cfg-input um-control" form="listui-standard-form" name="f[code]" value="{{ is_scalar(request('f.code')) ? request('f.code') : '' }}" placeholder="{{ t('Filter','ফিল্টার') }}"></th><th><select class="cfg-input um-control" form="listui-standard-form" name="f[role_id]" aria-label="{{ t('Filter by role','রোল অনুযায়ী ফিল্টার') }}"><option value="">{{ t('All roles','সব রোল') }}</option>@foreach($filterRoles as $filterRole)<option value="{{ $filterRole->id }}" @selected((string)request('f.role_id')===(string)$filterRole->id)>{{ $filterRole->name }}</option>@endforeach</select></th><th><select class="cfg-input um-control" form="listui-standard-form" name="f[account_status]" aria-label="{{ t('Filter by account status','অ্যাকাউন্টের অবস্থা অনুযায়ী ফিল্টার') }}"><option value="">{{ t('All statuses','সব স্ট্যাটাস') }}</option><option value="active" @selected(request('f.account_status')==='active')>{{ t('Active','সক্রিয়') }}</option><option value="inactive" @selected(request('f.account_status')==='inactive')>{{ t('Inactive','নিষ্ক্রিয়') }}</option></select></th><th><input type="date" class="cfg-input um-control" form="listui-standard-form" name="f[created_at]" value="{{ is_scalar(request('f.created_at')) ? request('f.created_at') : '' }}" aria-label="{{ t('Joined date','যোগদানের তারিখ') }}"></th><th><button type="submit" class="ws-button" form="listui-standard-form">{{ t('Go','যান') }}</button></th></tr></thead><tbody>
                @forelse($members as $member)
                    @php
                        $memberRole=strtolower(trim((string)$member->role?->name));
                        $locked=(bool)$member->role?->is_owner || $member->isOwnerOf($business) || (!$isOwner && $memberRole==='admin') || (int)$member->id===(int)$user->id;
                        $memberActive=$member->is_active && $member->approval_status==='Approved';
                    @endphp
                    <tr>
                        <td><div class="um-person"><span class="um-avatar">{{ mb_strtoupper(mb_substr($member->name,0,1)) }}</span><div><b>{{ $member->name }}</b><small>{{ $member->email }}</small></div></div></td>
                        <td><span class="um-code">{{ $member->code ?? '#'.$member->id }}</span><small class="um-code" style="display:block;margin-top:4px">ID: {{ $member->id }} · Global: {{ $member->user_id }}</small></td>
                        <td><span class="um-tag">{{ $member->role?->name ?? t('No role','রোল নেই') }}</span></td>
                        <td><span class="um-status {{ $member->approval_status!=='Approved'?'pending':($memberActive?'active':'inactive') }}">{{ $member->approval_status!=='Approved'?$member->approval_status:($memberActive?t('Active','সক্রিয়'):t('Inactive','নিষ্ক্রিয়')) }}</span></td>
                        <td><span class="um-code">{{ $member->created_at?->format('d M Y') ?? '—' }}</span></td>
                        <td><div class="um-actions">
                            @if(!$locked)
                                <button type="button" class="um-icon-button um-edit" title="{{ t('Edit user','ইউজার এডিট') }}" aria-label="{{ t('Edit','এডিট') }} {{ $member->name }}"
                                    data-id="{{ $member->id }}" data-name="{{ $member->name }}" data-email="{{ $member->email }}" data-role="{{ $member->role_id }}" data-active="{{ $member->is_active?1:0 }}"
                                    data-url="{{ route('business.admin.authorization.users.update',['business'=>$businessKey,'memberId'=>$member->id]) }}"><svg class="ws-svg"><use href="#ws-i-edit"/></svg></button>
                            @else<span class="um-locked">{{ t('Protected','সুরক্ষিত') }}</span>@endif
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="um-empty"><svg class="ws-svg"><use href="#ws-i-users"/></svg><strong>{{ t('No users found','কোনো ইউজার পাওয়া যায়নি') }}</strong>{{ t('Try another search or add your first team member.','অন্যভাবে খুঁজুন অথবা নতুন ইউজার যোগ করুন।') }}</div></td></tr>
                @endforelse
                </tbody></table>
            </div>
            @include('public.business.admin.shared.list-ui.pagination',['paginator'=>$members])
        </section>
    </div>
</div>

<div class="um-overlay" id="um-modal" hidden>
    <div class="um-dialog" role="dialog" aria-modal="true" aria-labelledby="um-modal-title">
        <div class="um-modal-head"><div><h2 id="um-modal-title">{{ t('Create User','ইউজার তৈরি') }}</h2><p id="um-modal-subtitle">{{ t('Enter the details to add a team member.','নতুন টিম সদস্যের তথ্য দিন।') }}</p></div><button type="button" class="um-close" id="um-close" aria-label="{{ t('Close','বন্ধ করুন') }}">×</button></div>
        <form class="um-form" id="um-form" action="{{ $storeUrl }}" method="POST">
            @csrf
            <input type="hidden" name="_method" id="um-method" value="POST" disabled>
            <input type="hidden" name="form_mode" id="um-mode" value="create">
            <input type="hidden" name="member_id" id="um-id" value="">
            <div class="um-form-inner">
                @if($errors->any())<div class="um-alert" id="um-errors">{{ $errors->first() }}</div>@endif
                <div class="um-field full"><label for="um-name">{{ t('Full name','পূর্ণ নাম') }} <em>*</em></label><input class="um-control" type="text" id="um-name" name="name" maxlength="255" autocomplete="name" required></div>
                <div class="um-field full"><label for="um-email">{{ t('Email address','ইমেইল') }} <em>*</em></label><input class="um-control" type="email" id="um-email" name="email" maxlength="255" autocomplete="off" required><small id="um-email-help" hidden>{{ t('Email cannot be changed from this screen.','এই পেজ থেকে ইমেইল পরিবর্তন করা যাবে না।') }}</small></div>
                <div class="um-field"><label for="um-role">{{ t('User role','ইউজার রোল') }} <em>*</em></label><select class="um-control" id="um-role" name="role_id" required><option value="">{{ t('Select role','রোল নির্বাচন') }}</option>@foreach($roles as $role)<option value="{{ $role->id }}">{{ $role->name }}</option>@endforeach @if(!$roles->contains('name','Staff'))<option value="staff">{{ t('Staff (default)','স্টাফ (ডিফল্ট)') }}</option>@endif</select></div>
                <div class="um-field"><label for="um-status">{{ t('Status','স্ট্যাটাস') }}</label><select class="um-control" name="is_active" id="um-status"><option value="1">{{ t('Active','সক্রিয়') }}</option><option value="0">{{ t('Inactive','নিষ্ক্রিয়') }}</option></select></div>
                <div class="um-field"><label for="um-password">{{ t('Password','পাসওয়ার্ড') }} <em id="um-required">*</em></label><input class="um-control" type="password" id="um-password" name="password" autocomplete="new-password" minlength="8"><small id="um-password-help">{{ t('Minimum 8 characters.','কমপক্ষে ৮ অক্ষর।') }}</small></div>
                <div class="um-field"><label for="um-confirm">{{ t('Confirm password','পাসওয়ার্ড নিশ্চিত করুন') }}</label><input class="um-control" type="password" id="um-confirm" name="password_confirmation" autocomplete="new-password" minlength="8"></div>
                <div class="um-field full"><small>{{ t('Access permissions can be configured separately from Authorization → Access Control.','Authorization → Access Control থেকে আলাদাভাবে পারমিশন সেট করা যাবে।') }}</small></div>
            </div>
            <div class="um-form-footer"><button type="button" class="ws-button" id="um-cancel">{{ t('Cancel','বাতিল') }}</button><button type="submit" class="ws-button ws-button-primary" id="um-save">{{ t('Create User','ইউজার তৈরি') }}</button></div>
        </form>
    </div>
</div>
@include('public.business.admin.shared.list-ui.styles')
@include('public.business.admin.shared.list-ui.scripts')
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded',function(){
    const modal=document.getElementById('um-modal'),form=document.getElementById('um-form');
    const name=document.getElementById('um-name'),email=document.getElementById('um-email'),role=document.getElementById('um-role'),status=document.getElementById('um-status');
    const method=document.getElementById('um-method'),mode=document.getElementById('um-mode'),memberId=document.getElementById('um-id');
    const password=document.getElementById('um-password'),confirm=document.getElementById('um-confirm');
    const title=document.getElementById('um-modal-title'),sub=document.getElementById('um-modal-subtitle'),save=document.getElementById('um-save');
    const help=document.getElementById('um-email-help'),required=document.getElementById('um-required'),phelp=document.getElementById('um-password-help');
    const createTitle=@json(t('Create User','ইউজার তৈরি')),editTitle=@json(t('Edit User','ইউজার এডিট'));
    const createSub=@json(t('Enter the details to add a team member.','নতুন টিম সদস্যের তথ্য দিন।'));
    const editSub=@json(t('Update member information without leaving this page.','এই পেজ থেকেই সদস্যের তথ্য আপডেট করুন।'));
    const optional=@json(t('Leave blank to keep the current password.','আগের পাসওয়ার্ড রাখতে ফাঁকা রাখুন।'));
    let lastFocus=null;
    function open(){lastFocus=document.activeElement;modal.hidden=false;document.body.classList.add('um-lock');window.NexaSearchSelect?.refresh(modal);name.focus();}
    function close(){modal.hidden=true;document.body.classList.remove('um-lock');lastFocus?.focus();}
    function reset(){form.reset();form.action=@json($storeUrl);method.disabled=true;method.value='POST';mode.value='create';memberId.value='';email.readOnly=false;email.required=true;password.required=true;required.hidden=false;help.hidden=true;phelp.textContent=@json(t('Minimum 8 characters.','কমপক্ষে ৮ অক্ষর।'));title.textContent=createTitle;sub.textContent=createSub;save.textContent=createTitle;if(document.getElementById('um-errors'))document.getElementById('um-errors').hidden=true;}
    function edit(btn){reset();form.action=btn.dataset.url;method.disabled=false;method.value='PUT';mode.value='edit';memberId.value=btn.dataset.id;name.value=btn.dataset.name;email.value=btn.dataset.email;email.readOnly=true;email.required=false;role.value=btn.dataset.role;status.value=btn.dataset.active;password.required=false;required.hidden=true;help.hidden=false;phelp.textContent=optional;title.textContent=editTitle;sub.textContent=editSub;save.textContent=@json(t('Save Changes','পরিবর্তন সংরক্ষণ'));open();}
    document.getElementById('um-create').addEventListener('click',()=>{reset();open();});
    document.querySelectorAll('.um-edit').forEach(btn=>btn.addEventListener('click',()=>edit(btn)));
    document.getElementById('um-close').addEventListener('click',close);document.getElementById('um-cancel').addEventListener('click',close);
    modal.addEventListener('click',e=>{if(e.target===modal)close();});
    document.addEventListener('keydown',e=>{if(e.key==='Escape'&&!modal.hidden)close();});
    document.querySelectorAll('[data-ws-sidebar-toggle],[data-ws-sidebar-overlay]').forEach(el=>el.addEventListener('click',()=>document.body.classList.toggle('ws-sidebar-open')));
    const oldMode=@json(old('form_mode','')),oldId=@json(old('member_id',''));
    if(oldMode==='edit'){
        const btn=[...document.querySelectorAll('.um-edit')].find(el=>el.dataset.id===String(oldId));
        if(btn){edit(btn);if(document.getElementById('um-errors'))document.getElementById('um-errors').hidden=false;name.value=@json(old('name',''));role.value=@json(old('role_id',''));status.value=@json(old('is_active','1'));window.NexaSearchSelect?.refresh(modal);}
    }else if(oldMode==='create'){reset();if(document.getElementById('um-errors'))document.getElementById('um-errors').hidden=false;name.value=@json(old('name',''));email.value=@json(old('email',''));role.value=@json(old('role_id',''));status.value=@json(old('is_active','1'));open();}
});
</script>
@endpush

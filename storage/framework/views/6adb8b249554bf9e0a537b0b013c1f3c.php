<?php echo $__env->make('public.business.admin.configurations._ui_styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<style>
/* All administration list cards share the Customers component tokens. No POS/detail selectors here. */
.ret-page.listui-page{--ws-panel:var(--rp-panel);--ws-bg:var(--rp-bg);--ws-text:var(--rp-ink);--ws-muted:var(--rp-muted);--ws-line:var(--rp-border);--ui-text:var(--rp-ink);--ui-muted:var(--rp-muted);--ui-panel:var(--rp-panel);--ui-panel-alt:var(--rp-panel);--ui-border:var(--rp-border);--ui-input:var(--rp-panel)}
.listui-page :is(.cfg-heading,.ret-title){display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;margin:0 0 22px}
.listui-page .ret-title h1,.listui-page .cfg-heading h1{font:800 25px Manrope,'DM Sans',sans-serif;letter-spacing:-.5px;line-height:1.35;color:var(--ws-text,var(--rp-ink));margin:0}
.listui-page .ret-title p{font-size:13px;color:var(--ws-muted,var(--rp-muted));margin-top:7px}
.listui-page .cfg-eyebrow{font-size:12px;color:var(--ws-muted,var(--rp-muted));font-weight:700;margin-bottom:5px}
html[data-theme="dark"] .ret-page.listui-page{--ui-panel-alt:#29354a;--ui-input:#1a2639}
.listui-page .cfg-panel{background:var(--ws-panel,var(--rp-panel,var(--ui-panel)));color:var(--ws-text,var(--rp-ink,var(--ui-text)));border:1px solid var(--ws-line,var(--rp-border,var(--ui-border)));border-radius:16px;box-shadow:0 9px 28px rgba(24,32,54,.06);padding:0!important;margin-bottom:16px;min-width:0;overflow:hidden}
.listui-page .cfg-panel :is(.ret-table-wrap,.auth-scroll,.um-table-wrap,.eo-table){margin:0!important}
.listui-page .cfg-panel .cfg-table-scroll{max-height:72vh;overflow:auto;max-width:100%;width:100%;scrollbar-width:thin;scrollbar-color:var(--ui-border) transparent}
.listui-page .cfg-panel table.cfg-table{width:100%;border-collapse:separate;border-spacing:0;text-align:left;white-space:nowrap;font-size:12px;min-width:max-content}
.listui-page .cfg-panel table.cfg-table th{position:sticky;top:0;z-index:2;background:var(--ui-panel-alt)!important;color:var(--ui-text)!important;padding:12px 13px;border-bottom:1px solid var(--ui-border);font-size:12px;font-weight:750;white-space:nowrap}
.listui-page .cfg-panel table.cfg-table thead tr.cfg-filter-row th{top:41px;z-index:2;padding:7px;background:var(--ui-panel-alt)!important;border-bottom:2px solid var(--ui-border)}
.listui-page .cfg-panel table.cfg-table td{padding:13px;border-bottom:1px solid var(--ui-border);color:var(--ui-text);vertical-align:middle;max-width:340px;overflow:hidden;text-overflow:ellipsis}
.listui-page .cfg-panel table.cfg-table tbody tr:hover td{background:rgba(86,73,218,.055)}
.listui-page .cfg-panel table.cfg-table th a{color:inherit;text-decoration:none}.listui-page .cfg-panel table.cfg-table th a:hover{color:#6859d9}
.listui-page .cfg-panel table.cfg-table .cfg-input{box-sizing:border-box;min-width:105px;width:100%;height:33px;padding:6px 9px;font-size:11px}
.listui-page .cfg-panel table.cfg-table .cfg-btn{height:33px}
.listui-page .cfg-panel .cfg-bottom{padding:14px 16px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;font-size:12px;color:var(--ws-muted,var(--ui-muted))}
.listui-page .cfg-panel .cfg-pagination{display:flex;gap:7px;align-items:center;flex-wrap:wrap;margin:0}
.listui-page .cfg-panel .cfg-pagination a,.listui-page .cfg-panel .cfg-pagination span{border:1px solid var(--ui-border);border-radius:8px;padding:8px 11px;background:var(--ui-panel);color:var(--ui-text);text-decoration:none;font-size:12px}
.listui-page .cfg-panel .cfg-pagination .is-disabled{opacity:.5;cursor:default}
.listui-page .cfg-panel .cfg-pagination a:hover{border-color:#6659dc;color:#6659dc}
/* One compact right toolbar, matching Configurations/Customers. */
.cfg-customers-toolbar,.listui-toolbar{display:flex!important;align-items:center!important;justify-content:flex-end!important;gap:8px!important;min-height:61px;padding:10px 15px!important;flex-wrap:nowrap!important;box-sizing:border-box;border-bottom:1px solid var(--ui-border);margin:0!important}
.cfg-customers-toolbar>.cfg-toolbar-actions,.listui-toolbar>.cfg-toolbar-actions{display:flex!important;align-items:center!important;justify-content:flex-end!important;flex:0 1 auto!important;flex-wrap:nowrap!important;width:max-content!important;max-width:100%!important;min-width:0!important;margin:0 0 0 auto!important;gap:8px!important}
.cfg-customers-toolbar select.cfg-input.cfg-small,.listui-toolbar select.cfg-input.cfg-small{display:block!important;box-sizing:border-box!important;width:136px!important;min-width:136px!important;max-width:136px!important;flex:0 0 136px!important;height:38px!important;padding:0 29px 0 12px!important;font-size:12px!important}
.cfg-customers-toolbar .cfg-btn.cfg-action-icon,.listui-toolbar .cfg-btn.cfg-action-icon{display:inline-flex!important;align-items:center!important;justify-content:center!important;flex:0 0 auto!important;min-width:0!important;width:auto!important;height:38px!important;min-height:38px!important;padding:0 11px!important;gap:7px!important;white-space:nowrap!important;line-height:1!important;border-radius:9px;font-size:12px;font-weight:750}
.cfg-customers-toolbar .cfg-action-icon svg,.listui-toolbar .cfg-action-icon svg{width:16px;height:16px;flex:none;stroke:currentColor;stroke-width:1.8;fill:none;stroke-linecap:round;stroke-linejoin:round}
.cfg-customers-toolbar .cfg-btn.cfg-filter-toggle,.listui-toolbar .cfg-btn.cfg-filter-toggle{position:relative;padding-left:35px!important}
.cfg-customers-toolbar .cfg-filter-toggle:before,.listui-toolbar .cfg-filter-toggle:before{content:'';position:absolute;left:11px;top:50%;transform:translateY(-50%);width:16px;height:16px;background:currentColor;-webkit-mask:center/contain no-repeat url('data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2" stroke-linecap="round"%3E%3Cpath d="M4 7h16M7 12h10M10 17h4"/%3E%3C/svg%3E');mask:center/contain no-repeat url('data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2" stroke-linecap="round"%3E%3Cpath d="M4 7h16M7 12h10M10 17h4"/%3E%3C/svg%3E')}
.cfg-customers-toolbar .cfg-export-excel,.listui-toolbar .cfg-export-excel{color:#16764c!important;border-color:rgba(22,118,76,.3)!important;background:rgba(22,118,76,.07)!important}
.cfg-customers-toolbar .cfg-export-pdf,.listui-toolbar .cfg-export-pdf{color:#bc4355!important;border-color:rgba(188,67,85,.3)!important;background:rgba(188,67,85,.07)!important}
.cfg-toolbar-separator{flex:0 0 1px!important;height:22px!important;border-left:1px solid var(--ui-border);margin:0 2px!important}
.cfg-filter-row[hidden],.listui-filter-row[hidden]{display:none!important}
.listui-page .cfg-panel .cfg-table .cfg-filter-row th :is(input,select){max-width:none!important}
/* Existing separate search/date/status fields appear as optional collapsible advanced filters. */
.listui-page [data-listui-filter-row]:not(tr){background:var(--ui-panel);border:1px solid var(--ui-border);border-radius:12px;padding:12px 15px!important;margin:0 0 10px!important}
.listui-page [data-listui-filter-row][hidden]:not(tr){display:none!important}
.listui-page .listui-add{display:flex;align-items:center;justify-content:flex-end;gap:8px;margin:0 0 14px;position:relative;z-index:1;min-height:38px;pointer-events:none}
.listui-page .listui-add>*{pointer-events:auto}
.listui-page .cfg-heading>.listui-add{margin:0 0 0 auto;min-height:0;pointer-events:auto}
.listui-page .cfg-heading>.ret-toolbar{margin:0 0 0 auto;display:flex;align-items:center;justify-content:flex-end;gap:8px}
.listui-page .cfg-panel .listui-extra-heading{display:none}
.listui-page .listui-add .ret-btn,.listui-page .listui-add .auth-btn{height:38px;background:#5547d9;color:#fff!important;border-color:#5547d9;border-radius:9px}
/* Modal layout from Customers, with the original forms/actions untouched. */
.listui-overlay.cfg-modal[hidden]{display:none!important}
.listui-overlay.cfg-modal{position:fixed!important;inset:0!important;z-index:2147483000!important;background:rgba(7,14,30,.68)!important;backdrop-filter:blur(5px);display:flex;justify-content:center;align-items:center;padding:18px 12px;overflow-y:auto}
.listui-dialog.cfg-dialog{width:min(740px,100%);max-height:min(92dvh,900px);display:flex;flex-direction:column;overflow:hidden;background:var(--ui-panel)!important;color:var(--ui-text)!important}
.listui-dialog-head.cfg-dialog-header{padding:17px 22px;flex-shrink:0}.listui-dialog-body{padding:20px 23px;overflow:auto;min-height:0}
.listui-dialog-body .ret-card,.listui-dialog-body .auth-panel{padding:0!important;border:0!important;box-shadow:none!important;margin:0!important}
.listui-dialog-body .ret-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:13px}
.listui-dialog-body .ret-field label,.listui-dialog-body label{font-weight:650;font-size:12px}
.listui-dialog-body :is(.ret-input,.auth-form input,.auth-form select,.um-control){background:var(--ui-input);color:var(--ui-text);border:1px solid var(--ui-border);min-height:41px;border-radius:9px}
.listui-dialog-close.cfg-dialog-close{border:1px solid var(--ui-border);background:var(--ui-panel);color:var(--ui-text);border-radius:9px}
html[data-theme=dark] .listui-page .cfg-panel{box-shadow:none}
@media(max-width:780px){.cfg-customers-toolbar,.listui-toolbar{flex-wrap:wrap!important}.cfg-customers-toolbar>.cfg-toolbar-actions,.listui-toolbar>.cfg-toolbar-actions{flex-wrap:wrap!important;width:100%!important;justify-content:flex-end!important}.cfg-toolbar-separator{display:none}.listui-page .listui-add{margin:0 0 12px}.listui-page .cfg-panel .cfg-table-scroll{max-height:65vh}}
@media(max-width:480px){.cfg-customers-toolbar,.listui-toolbar{padding:10px!important}.cfg-customers-toolbar>.cfg-toolbar-actions,.listui-toolbar>.cfg-toolbar-actions{gap:7px!important}.cfg-customers-toolbar select.cfg-input.cfg-small,.listui-toolbar select.cfg-input.cfg-small{width:124px!important;min-width:124px!important;max-width:124px!important;flex-basis:124px!important}.cfg-customers-toolbar .cfg-btn.cfg-action-icon,.listui-toolbar .cfg-btn.cfg-action-icon{padding:0 9px!important}.cfg-customers-toolbar .cfg-btn.cfg-filter-toggle,.listui-toolbar .cfg-btn.cfg-filter-toggle{padding-left:30px!important}.cfg-customers-toolbar .cfg-filter-toggle:before,.listui-toolbar .cfg-filter-toggle:before{left:8px}.listui-dialog-body{padding:15px}.listui-dialog-body .ret-grid{grid-template-columns:1fr}}

/* Secondary date/status forms open only on demand; main list matches Customers. */
.listui-advanced-layer[hidden]{display:none!important}
.listui-advanced-layer{position:fixed;inset:0;z-index:2147482000;display:flex;align-items:center;justify-content:center;padding:16px;background:rgba(6,14,31,.6);backdrop-filter:blur(4px)}
.listui-advanced-panel{width:min(550px,100%);max-height:92vh;overflow-y:auto;border-radius:16px;padding:18px;background:var(--ui-panel,#fff);color:var(--ui-text,#222);border:1px solid var(--ui-border,#ddd);box-shadow:0 25px 90px rgba(0,0,0,.3)}
.listui-advanced-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px;font-size:15px}
.listui-advanced-close{background:transparent;color:inherit;border:1px solid var(--ui-border,#ddd);border-radius:8px;width:32px;height:32px;cursor:pointer;font-size:20px;line-height:1}
.listui-advanced-panel .listui-advanced-form{display:flex!important;align-items:stretch;flex-direction:column;gap:10px;padding:0!important;margin:0!important;background:none!important;border:0!important;box-shadow:none!important}
.listui-advanced-form :is(input,select){width:100%;max-width:100%;min-width:0;min-height:38px;box-sizing:border-box}
.listui-advanced-form label{width:100%}
.listui-advanced-form button[type=submit]{align-self:flex-end}
.listui-page .cfg-customers-toolbar .listui-advanced-trigger{font-size:12px!important}
.listui-page .cfg-table .listui-status-filter{display:flex;align-items:center;gap:7px;min-width:180px}
.listui-page .cfg-table .listui-status-filter select{flex:1 1 auto;min-width:118px;width:auto}
.listui-page .cfg-table .listui-status-filter button{flex:0 0 auto;margin:0}
html[data-theme=dark] .listui-advanced-panel{--ui-panel:#1c293d;--ui-text:#edf3fb;--ui-border:#37455b}
html[data-theme=light] .listui-advanced-panel{--ui-panel:#fff;--ui-text:#14243d;--ui-border:#dce4f0}
</style>
<?php /**PATH C:\laragon\www\saas-backend\resources\views/public/business/admin/shared/list-ui/styles.blade.php ENDPATH**/ ?>
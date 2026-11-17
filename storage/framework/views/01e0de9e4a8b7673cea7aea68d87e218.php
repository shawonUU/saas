<style>
/* Solid, high-contrast configuration UI; modal intentionally above sticky header/sidebar. */
.cfg-page .workspace-content{max-width:1700px}
.cfg-heading{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-bottom:22px}
.cfg-heading h1{font:800 25px Manrope,sans-serif;color:var(--ws-text);letter-spacing:-.5px}
.cfg-heading p{margin-top:7px;color:var(--ws-muted);font-size:13px}
.cfg-eyebrow{font-size:12px;color:var(--ws-muted);font-weight:700;margin-bottom:5px}
.cfg-panel{background:var(--ws-panel);border:1px solid var(--ws-line);border-radius:16px;overflow:hidden;color:var(--ws-text);box-shadow:0 9px 28px rgba(24,32,54,.06)}
.cfg-toolbar{display:flex;align-items:center;gap:10px;flex-wrap:wrap;padding:17px;border-bottom:1px solid var(--ws-line)}
.cfg-input{height:39px;width:100%;padding:0 11px;border:1px solid var(--ui-border);border-radius:9px;color:var(--ui-text)!important;background:var(--ui-input)!important;font:500 13px 'DM Sans',sans-serif;outline:0}
.cfg-input::placeholder{color:#8996ab}.cfg-input:focus{border-color:#6659dc;box-shadow:0 0 0 3px rgba(99,91,223,.14)}
.cfg-search{flex:1;min-width:210px}.cfg-small{width:auto;min-width:110px}
.cfg-btn{height:38px;padding:0 14px;display:inline-flex;align-items:center;justify-content:center;gap:7px;border:1px solid var(--ui-border);background:var(--ui-panel);color:var(--ui-text);border-radius:9px;font-size:12px;font-weight:800;cursor:pointer;text-decoration:none;white-space:nowrap}
.cfg-btn:hover{border-color:#6254db;color:#5147ca}.cfg-primary{background:#5547d9;border-color:#5547d9;color:#fff}.cfg-primary:hover{background:#4537c0;color:#fff}
.cfg-danger{border-color:#f1d2d5;color:#ac3341}.cfg-danger:hover{border-color:#dd9ba2;color:#a12636}
.cfg-btn:focus-visible,.cfg-dialog button:focus-visible{outline:3px solid #ab9fff;outline-offset:2px}
.cfg-btn:disabled{opacity:.5;cursor:not-allowed}.cfg-code{font:650 11px ui-monospace,monospace;color:var(--ws-muted)}
.cfg-table-scroll{width:100%;overflow:auto;max-height:72vh}.cfg-table{width:100%;border-collapse:separate;border-spacing:0;text-align:left;white-space:nowrap;font-size:12px}
.cfg-table th{position:sticky;top:0;z-index:2;background:var(--ui-panel-alt);color:var(--ui-muted);padding:12px 13px;border-bottom:1px solid #dfe4ed;font-size:12px}
.cfg-table th a{color:inherit;text-decoration:none}.cfg-table th a:hover{color:#4c40c3}
.cfg-table td{padding:13px;border-bottom:1px solid var(--ws-line);color:var(--ws-text);max-width:260px;text-overflow:ellipsis;overflow:hidden}
.cfg-table tbody tr:hover td{background:rgba(86,73,218,.05)}
.cfg-table .cfg-filter-row th{position:sticky;top:41px;z-index:2;padding:7px;background:var(--ui-panel-alt);border-bottom:2px solid #dbe2ec}
.cfg-filter-row[hidden]{display:none}.cfg-filter-row .cfg-input{min-width:105px;height:33px;font-size:11px}
.cfg-status{display:inline-flex;border-radius:50px;padding:5px 9px;background:#dff7e7;color:#17633e;font-weight:800;font-size:11px}
.cfg-status.off{background:#ffeaea;color:#a33440}.cfg-swatches{display:inline-block;width:15px;height:15px;border-radius:50%;border:1px solid #c6cbdb;vertical-align:middle;margin-right:6px}
.cfg-actions{display:flex;align-items:center;gap:6px}.cfg-actions .cfg-btn{height:31px;padding:0 10px}
.cfg-bottom{padding:16px;display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;color:var(--ws-muted);font-size:12px}
.cfg-pagination{display:flex;gap:7px;align-items:center}.cfg-pagination a,.cfg-pagination span{border:1px solid var(--ws-line);border-radius:8px;padding:8px 11px;color:var(--ws-text);text-decoration:none}.cfg-pagination span{opacity:.65}
.cfg-empty{padding:46px 15px!important;text-align:center;color:var(--ws-muted)!important}.cfg-count{font-size:12px;color:var(--ws-muted)}
/* Modal is moved to body before opening, escaping sidebar and header stacking contexts. */
.cfg-modal{position:fixed!important;inset:0!important;z-index:2147483000!important;background:rgba(7,14,30,.78)!important;backdrop-filter:blur(5px);-webkit-backdrop-filter:blur(5px);display:flex;justify-content:center;align-items:center;padding:22px 14px;overflow-y:auto}
.cfg-modal[hidden]{display:none!important}.cfg-dialog{width:min(720px,100%);max-height:min(90dvh,850px);display:flex;flex-direction:column;background:var(--ui-panel)!important;color:var(--ui-text)!important;border:1px solid var(--ui-border)!important;border-radius:17px;overflow:hidden;box-shadow:0 30px 110px rgba(0,0,0,.65)!important}
.cfg-dialog-header{padding:21px 24px;display:flex;justify-content:space-between;align-items:center;gap:18px;background:var(--ui-panel-alt)!important;color:#17243c;border-bottom:1px solid var(--ui-border)}
.cfg-dialog-header h2{font:800 20px Manrope,sans-serif;color:var(--ui-text)!important}.cfg-dialog-header p{font-size:12px;color:var(--ui-muted)!important;margin-top:6px}
.cfg-dialog-close{font-size:20px;width:36px;padding:0;flex-shrink:0}
.cfg-form{display:flex;flex-direction:column;min-height:0;overflow:auto}.cfg-fields{padding:22px 24px;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:17px;background:var(--ui-panel)!important}
.cfg-field{min-width:0}.cfg-field.wide{grid-column:1/-1}.cfg-field label{display:block;color:var(--ui-text)!important;font-size:13px;font-weight:750;margin-bottom:8px}
.cfg-field .cfg-input{background:var(--ui-input)!important;color:var(--ui-text)!important;border:1px solid var(--ui-border)!important;height:43px;font-size:13px}
.cfg-field .cfg-input:focus{border-color:#5145cf!important;box-shadow:0 0 0 3px rgba(81,69,207,.14)}
.cfg-field textarea.cfg-input{min-height:78px;padding:12px;resize:vertical;height:auto}.cfg-field select.cfg-input{cursor:pointer}
.cfg-required{color:#ce3b4c}.cfg-actions-footer{position:sticky;bottom:0;display:flex;justify-content:flex-end;gap:10px;padding:17px 24px;border-top:1px solid var(--ui-border);background:var(--ui-panel-alt)!important}
.cfg-notice{margin:15px 24px 0;padding:12px 14px;background:#fff0f1;color:#962b36;border:1px solid #efbac0;border-radius:9px;font-size:12px;line-height:1.6}
html[data-theme="dark"] .cfg-page .cfg-panel{background:#1d283d}html[data-theme="dark"] .cfg-table th{background:#2a354a;color:#e1e8f5}
html[data-theme="dark"] .cfg-table .cfg-filter-row th{background:#223048}

@media(max-width:720px){.cfg-fields{grid-template-columns:1fr;padding:17px}.cfg-field.wide{grid-column:auto}.cfg-dialog-header{padding:17px}.cfg-actions-footer{padding:13px 17px}.cfg-toolbar{padding:12px}.cfg-table-scroll{max-height:65vh}.cfg-heading h1{font-size:22px}}
/* Polished action visibility and true dark-mode modal. */
.cfg-nav-toggle{display:none}
.cfg-input, .cfg-btn, .cfg-dialog {transition:border-color .16s ease,background-color .16s ease,box-shadow .16s ease}
.cfg-toolbar .cfg-btn {min-height:40px}
.cfg-toolbar>.cfg-search {flex:1 1 240px}
.cfg-dialog {box-shadow:0 34px 100px rgba(6,13,30,.36)!important}
.cfg-dialog-header,.cfg-actions-footer {flex-shrink:0}
.cfg-form {overscroll-behavior:contain}
.cfg-form :is(input,select,textarea) {color-scheme:inherit}
.cfg-table-scroll{scrollbar-width:thin;scrollbar-color:var(--ui-border) transparent}
.cfg-table th {background:var(--ui-panel-alt);color:var(--ui-text);border-color:var(--ui-border)}
.cfg-table .cfg-filter-row th {background:var(--ui-panel-alt);border-color:var(--ui-border)}
.cfg-table td {color:var(--ui-text)}
.cfg-table tbody tr:hover td {background:var(--ui-panel-alt)}
.cfg-dialog-header h2 {color:var(--ui-text)!important}
.cfg-dialog-header p {color:var(--ui-muted)!important}
.cfg-modal {background:rgba(7,14,30,.67)!important}
.cfg-btn:not(.cfg-primary):not(.cfg-danger) {background:var(--ui-panel);color:var(--ui-text);border-color:var(--ui-border)}
.cfg-btn:not(.cfg-primary):hover {background:var(--ui-panel-alt)}
.cfg-primary,.cfg-primary:hover{color:#fff!important}
.cfg-status {background:#e2f6e9;color:#12633c}
.cfg-status.off {background:#fde8ea;color:#982b3b}
html[data-theme="dark"] .cfg-status {background:#153f33;color:#a7f1cf}
html[data-theme="dark"] .cfg-status.off {background:#4c2832;color:#ffb5c1}
html[data-theme="dark"] .cfg-dialog,html[data-theme="dark"] .cfg-fields {background:var(--ui-panel)!important;color:var(--ui-text)!important}
html[data-theme="dark"] .cfg-dialog-header,html[data-theme="dark"] .cfg-actions-footer{background:var(--ui-panel-alt)!important;color:var(--ui-text)}
html[data-theme="dark"] .cfg-page .cfg-input,html[data-theme="dark"] .cfg-field .cfg-input{background:var(--ui-input)!important;color:var(--ui-text)!important;border-color:var(--ui-border)!important}
html[data-theme="dark"] .cfg-page .cfg-table th,html[data-theme="dark"] .cfg-page .cfg-table .cfg-filter-row th{background:var(--ui-panel-alt);color:var(--ui-text)}
html[data-theme="dark"] .cfg-pagination a,html[data-theme="dark"] .cfg-pagination span{background:var(--ui-panel);color:var(--ui-text)}
@media(max-width:1050px){.cfg-nav-toggle{display:inline-flex;order:0}.cfg-heading{align-items:flex-start}.cfg-heading>div:first-of-type{flex:1 1 100%;order:-1}.cfg-heading>.cfg-btn.cfg-primary{margin-left:auto;order:1}}
@media(max-width:750px){.cfg-heading{gap:10px}.cfg-toolbar{gap:8px}.cfg-toolbar>.cfg-search{min-width:100%;flex-basis:100%}.cfg-toolbar .cfg-btn{flex:1 1 auto}.cfg-toolbar .cfg-small{flex:0 0 100px}.cfg-dialog{max-height:calc(100dvh - 26px);border-radius:14px}.cfg-modal{padding:13px 9px}.cfg-actions-footer .cfg-btn{flex:1}.cfg-bottom{flex-direction:column;align-items:stretch}.cfg-pagination{justify-content:space-between}}
@media(max-width:390px){.cfg-toolbar .cfg-btn{padding:0 10px;font-size:11px}.cfg-dialog-header h2{font-size:18px}}
</style>
<?php /**PATH C:\laragon\www\saas-backend\resources\views/public/business/admin/configurations/_ui_styles.blade.php ENDPATH**/ ?>
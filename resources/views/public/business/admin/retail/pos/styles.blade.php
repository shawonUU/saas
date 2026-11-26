<style>
.pos-page{--p-accent:#635bdf;--p-soft:#efedff;--p-text:#192337;--p-muted:#6b7689;--p-border:#e2e6ee;--p-panel:#fff;--p-bg:#f3f5f9;color:var(--p-text);background:var(--p-bg)}html[data-theme=dark] .pos-page{--p-accent:#8b83ff;--p-soft:#34305c;--p-text:#edf1fa;--p-muted:#a6b2c6;--p-border:#354258;--p-panel:#1d2738;--p-bg:#111827}.pos-page [hidden]{display:none!important}.pos-page .pos-workspace{padding:24px;min-width:0}.pos-head,.pos-heading,.pos-tools,.pos-section-head,.pos-actions,.pos-filter,.pos-options{display:flex;align-items:center;gap:10px}.pos-head,.pos-section-head,.pos-options{justify-content:space-between}.pos-head{margin-bottom:22px;flex-wrap:wrap}.pos-head h1{font-size:30px;letter-spacing:-1px;line-height:1.2;margin:5px 0}.pos-head p,.pos-muted{font-size:12px;color:var(--p-muted);line-height:1.5}.pos-eyebrow{font-size:10px;letter-spacing:1.5px;font-weight:800;color:var(--p-accent)}.pos-tools,.pos-actions,.pos-options{flex-wrap:wrap}.pos-shell{display:grid;grid-template-columns:minmax(0,1fr) minmax(300px,370px);align-items:start;gap:18px}.pos-panel{background:var(--p-panel);border:1px solid var(--p-border);border-radius:18px;padding:18px;min-width:0;box-shadow:0 5px 20px #10182808}.pos-section-head{margin-bottom:16px}.pos-section-head h2{font-size:17px;letter-spacing:-.3px}.pos-tag,.pos-count{background:var(--p-soft);color:var(--p-accent);font-size:11px;font-weight:800;border-radius:20px;padding:5px 9px;white-space:nowrap}.pos-page input:not([type=checkbox]),.pos-page select{width:100%;min-width:0;border:1px solid var(--p-border);background:var(--p-panel);color:var(--p-text);border-radius:9px;padding:10px 11px;font:inherit;font-size:13px;min-height:40px}.pos-page input:focus,.pos-page select:focus,.pos-page button:focus-visible{outline:2px solid var(--p-accent);outline-offset:2px}.pos-search{display:flex;align-items:center;gap:8px;background:var(--p-bg);border:1px solid var(--p-border);border-radius:11px;padding:3px 10px;margin-bottom:12px}.pos-search span{font-size:24px;color:var(--p-muted)}.pos-page .pos-search input{border:0;background:transparent}.pos-search kbd{font-size:11px;border:1px solid var(--p-border);border-radius:5px;padding:3px 5px;color:var(--p-muted)}.pos-filter{overflow:auto;padding:3px 1px 9px}.pos-chip,.pos-secondary,.pos-text{cursor:pointer;color:var(--p-text);background:var(--p-panel);border:1px solid var(--p-border);border-radius:9px;padding:9px 12px;font-size:12px;font-weight:700;white-space:nowrap}.pos-chip{border-radius:30px}.pos-chip.active{color:var(--p-accent);background:var(--p-soft);border-color:var(--p-accent)}.pos-secondary:hover,.pos-chip:hover{border-color:var(--p-accent)}.pos-text{border:0;color:var(--p-accent);background:transparent}.pos-options{margin:4px 0 10px;font-size:12px;color:var(--p-muted)}.pos-options label{display:flex;align-items:center;gap:5px}.pos-page .pos-options select{width:auto;max-width:100%;min-height:32px;padding:6px}.pos-products{display:grid;grid-template-columns:repeat(auto-fill,minmax(145px,1fr));gap:12px}.pos-product{border:1px solid var(--p-border);border-radius:13px;background:var(--p-panel);color:var(--p-text);text-align:left;overflow:hidden;cursor:pointer;min-width:0;padding:0;transition:border-color .15s,transform .15s}.pos-product:hover:not(:disabled){border-color:var(--p-accent);transform:translateY(-2px)}.pos-product:disabled{cursor:not-allowed;opacity:.58}.pos-photo{height:126px;background:var(--p-bg);position:relative;display:grid;place-items:center;color:var(--p-muted);font-size:28px}.pos-photo img{width:100%;height:100%;position:absolute;inset:0;object-fit:contain;padding:8px;background:#fff}.pos-stock{position:absolute;top:8px;left:8px;font-size:10px;background:var(--p-panel);color:var(--p-text);padding:4px 6px;border-radius:5px;font-weight:700;box-shadow:0 1px 4px #0001}.pos-product-info{padding:11px;display:grid;gap:5px}.pos-product-name{font-size:13px;font-weight:800;line-height:1.35;min-height:35px;overflow-wrap:anywhere}.pos-product-meta{color:var(--p-muted);font-size:10px;overflow-wrap:anywhere}.pos-product-bottom{display:flex;justify-content:space-between;align-items:center;gap:3px}.pos-product-bottom strong{font-size:14px}.pos-add{background:var(--p-soft);color:var(--p-accent);border-radius:7px;padding:3px 8px;font-size:17px;font-weight:800}.pos-cart{position:sticky;top:90px}.pos-customer,.pos-adjust,.pos-payment-fields{display:grid;gap:9px}.pos-customer{padding-bottom:14px;border-bottom:1px solid var(--p-border)}.pos-page label{font-size:11px;font-weight:700;color:var(--p-muted)}.pos-page label>input:not([type=checkbox]),.pos-page label>select{margin-top:5px}.pos-cart-lines{max-height:330px;overflow:auto;min-height:94px}.pos-empty{color:var(--p-muted);text-align:center;padding:28px 12px;font-size:13px;grid-column:1/-1}.pos-line{display:grid;grid-template-columns:44px minmax(0,1fr) auto;gap:10px;align-items:center;border-bottom:1px solid var(--p-border);padding:12px 0}.pos-line-photo img{width:100%;height:100%;object-fit:contain}.pos-line-photo{overflow:hidden;display:grid;place-items:center;height:44px;width:44px;border-radius:8px;background:var(--p-bg);object-fit:contain}.pos-line-name{font-size:12px;font-weight:800;overflow-wrap:anywhere}.pos-line-meta{font-size:10px;color:var(--p-muted);margin:3px 0 7px}.pos-qty{display:flex;align-items:center;gap:4px}.pos-qty button{border:1px solid var(--p-border);background:var(--p-bg);color:var(--p-text);width:27px;height:27px;border-radius:6px;cursor:pointer}.pos-page .pos-qty input{width:65px;min-height:27px;height:27px;padding:3px;text-align:center;font-size:11px}.pos-line-end{text-align:right;font-size:12px}.pos-line-end button{display:block;margin:9px 0 0 auto;background:transparent;color:var(--p-muted);border:0;cursor:pointer;font-size:18px}.pos-actions{margin:12px 0}.pos-actions button{flex:1}.pos-adjust,.pos-payment-fields{grid-template-columns:1fr 1fr}.pos-tax{grid-column:1/-1}.pos-totals{border-top:1px solid var(--p-border);margin-top:14px;padding-top:12px;display:grid;gap:8px;font-size:12px}.pos-totals>div{display:flex;justify-content:space-between;gap:8px}.pos-totals span,.pos-totals small{color:var(--p-muted)}.pos-grand{border-top:1px dashed var(--p-border);padding-top:12px;font-size:18px;font-weight:800;align-items:center}.pos-grand strong{font-size:25px;color:var(--p-accent);letter-spacing:-.8px}.pos-primary{background:#635bdf;color:#fff;border:0;border-radius:11px;padding:14px 15px;font-weight:800;font-size:14px;cursor:pointer;width:100%;margin-top:15px;display:flex;justify-content:space-between;align-items:center;gap:8px}.pos-primary:disabled{opacity:.45;cursor:not-allowed}.pos-alert{background:var(--p-soft);color:var(--p-text);padding:12px;border-radius:10px;margin-bottom:12px;font-size:13px}.pos-more{margin:15px auto 0;display:block}.pos-dialog{position:fixed;inset:0;margin:auto;width:min(500px,calc(100% - 28px));max-height:90dvh;overflow:auto;padding:22px;border:1px solid var(--p-border);border-radius:18px;background:var(--p-panel);color:var(--p-text);box-shadow:0 30px 90px #0004;z-index:400}.pos-dialog::backdrop{background:#11182799;backdrop-filter:blur(3px)}.pos-payment-total{background:var(--p-soft);color:var(--p-accent);padding:16px;border-radius:12px;display:grid;gap:5px;margin-bottom:12px}.pos-payment-total strong{font-size:32px;letter-spacing:-1px}.pos-payment-fields{margin-top:15px}.pos-page #pos-submit{justify-content:center}.pos-page #pos-pay-message{min-height:20px;margin-top:10px}@media(min-width:1500px){.pos-shell{grid-template-columns:minmax(0,1fr) 410px}.pos-products{grid-template-columns:repeat(auto-fill,minmax(165px,1fr))}}@media(max-width:1250px) and (min-width:1051px){.pos-page .pos-workspace{padding:14px}.pos-panel{padding:13px}.pos-shell{gap:10px;grid-template-columns:minmax(0,1fr) 300px}.pos-products{grid-template-columns:repeat(auto-fill,minmax(128px,1fr))}}@media(max-width:800px){.pos-shell{grid-template-columns:1fr}.pos-cart{position:static}.pos-page .pos-workspace{padding:12px}.pos-products{grid-template-columns:repeat(auto-fill,minmax(130px,1fr))}.pos-head h1{font-size:25px}.pos-photo{height:105px}.pos-cart-lines{max-height:none}}

/* Compact POS only: no global layout file changes. */
body:has(#pos-app) > .site-header{display:none!important}
body:has(#pos-app) > .admin-main{min-height:0!important}
#pos-app.pos-fullscreen{display:block!important;width:100%;min-height:100dvh;margin:0;padding:0}
#pos-app .pos-workspace{width:100%;max-width:none;min-width:0;margin:0;padding:8px!important;box-sizing:border-box}
#pos-app #pos-sale{margin:0;width:100%}
#pos-app .pos-shell{display:grid;grid-template-columns:minmax(0,1fr) minmax(290px,330px);gap:8px;align-items:stretch}
#pos-app .pos-panel{padding:9px;border-radius:10px;box-shadow:none;min-width:0;min-height:0;display:flex;flex-direction:column;gap:5px}
#pos-app .pos-section-head{margin:0;gap:5px;min-height:26px;flex-shrink:0}
#pos-app .pos-section-head h2{font-size:14px;line-height:1.2}
#pos-app .pos-muted{font-size:10px;line-height:1.3}
#pos-app .pos-tag,#pos-app .pos-count{padding:2px 5px;font-size:10px}
#pos-app input:not([type=checkbox]),#pos-app select{min-height:29px;padding:4px 7px;border-radius:6px;font-size:12px}
#pos-app .pos-search{padding:0 6px;margin:0;gap:5px;flex-shrink:0;border-radius:7px}
#pos-app .pos-search span{font-size:19px}
#pos-app .pos-filter{padding:2px 0;gap:4px;flex-shrink:0;scrollbar-width:thin}
#pos-app #pos-categories{flex-wrap:nowrap;overflow-x:auto}
#pos-app .pos-chip,#pos-app .pos-secondary,#pos-app .pos-text{padding:5px 8px;font-size:11px;border-radius:6px}
#pos-app .pos-icon{min-width:30px}
#pos-app .pos-options{margin:0;font-size:10px}
#pos-app .pos-options select{min-height:25px;padding:3px 5px}
#pos-app .pos-product-scroll{min-height:0;overflow:auto;scrollbar-width:thin;overscroll-behavior:contain;flex:1}
#pos-app .pos-products{display:grid;grid-template-columns:repeat(auto-fill,minmax(105px,1fr));gap:5px;align-content:start}
#pos-app .pos-product{border-radius:7px;min-width:0;position:relative}
#pos-app .pos-product:hover:not(:disabled){transform:none}
#pos-app .pos-photo{height:64px;font-size:19px}
#pos-app .pos-photo img{padding:3px}
#pos-app .pos-stock{top:3px;left:3px;padding:2px 3px;font-size:8px;line-height:1.1;border-radius:3px}
#pos-app .pos-product-info{padding:4px 5px;gap:2px}
#pos-app .pos-product-name{font-size:11px;line-height:1.25;min-height:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#pos-app .pos-product-meta{font-size:9px;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;min-height:11px}
#pos-app .pos-product-bottom strong{font-size:12px;line-height:1.2}
#pos-app .pos-add{font-size:13px;padding:0 5px;border-radius:4px}
#pos-app .pos-more{margin:5px auto}
#pos-app .pos-cart{position:static;overflow:hidden;min-height:0;max-height:none}
#pos-app .pos-customer{padding-bottom:5px;gap:3px;flex-shrink:0}
#pos-app .pos-cart-lines{flex:1;max-height:none;min-height:42px;overflow:auto;scrollbar-width:thin;overscroll-behavior:contain}
#pos-app .pos-empty{padding:12px 5px;font-size:11px}
#pos-app .pos-line{grid-template-columns:30px minmax(0,1fr) auto;gap:5px;padding:5px 0}
#pos-app .pos-line-photo{height:30px;width:30px;border-radius:4px}
#pos-app .pos-line-name{font-size:11px;line-height:1.2}
#pos-app .pos-line-meta{font-size:9px;margin:2px 0}
#pos-app .pos-qty button{width:23px;height:23px;border-radius:4px}
#pos-app .pos-qty input{width:48px;min-height:23px;height:23px;padding:2px;font-size:10px}
#pos-app .pos-line-end{font-size:11px}
#pos-app .pos-line-end button{margin:2px 0 0 auto;font-size:15px}
#pos-app .pos-actions{margin:0;gap:4px;flex-shrink:0}
#pos-app .pos-adjust{grid-template-columns:repeat(3,minmax(0,1fr));gap:4px;flex-shrink:0}
#pos-app .pos-tax{grid-column:auto}
#pos-app label{font-size:10px}
#pos-app label>input:not([type=checkbox]),#pos-app label>select{margin-top:2px}
#pos-app .pos-totals{margin-top:3px;padding-top:5px;gap:3px;font-size:11px;flex-shrink:0}
#pos-app .pos-grand{padding-top:5px;font-size:14px}
#pos-app .pos-grand strong{font-size:21px}
#pos-app .pos-primary{padding:9px 10px;margin-top:3px;border-radius:7px;font-size:13px;flex-shrink:0}
#pos-app .pos-alert,#pos-app .ret-error{padding:5px 8px;margin-bottom:5px;font-size:11px}
#pos-app .pos-dialog{padding:13px;border-radius:12px}
#pos-app .pos-payment-total{padding:9px;margin-bottom:5px}
#pos-app .pos-payment-total strong{font-size:26px}
#pos-app .pos-payment-fields{gap:6px;margin-top:7px}
#pos-app #pos-quick{margin-top:7px}
#pos-app #pos-pay-message{min-height:14px;margin-top:5px}
#pos-app .pos-receipt-preview{font-size:12px;margin:8px 0;max-height:55dvh;overflow:auto}
#pos-app .pos-receipt-preview table{width:100%;border-collapse:collapse;margin:8px 0}
#pos-app .pos-receipt-preview td,#pos-app .pos-receipt-preview th{padding:5px 2px;border-bottom:1px solid var(--p-border);text-align:left}
#pos-app .pos-receipt-preview td:last-child,#pos-app .pos-receipt-preview th:last-child{text-align:right}
#pos-app .pos-success-dialog .pos-primary{width:auto;margin:0;justify-content:center}
@media(min-width:900px) and (min-height:560px){
 body:has(#pos-app){overflow:hidden}
 #pos-app .pos-workspace{height:100dvh;display:flex;flex-direction:column;min-height:0}
 #pos-app #pos-sale{flex:1;min-height:0;display:flex;flex-direction:column}
 #pos-app .pos-shell{flex:1;min-height:0;overflow:hidden}
}
@media(min-width:1450px){#pos-app .pos-shell{grid-template-columns:minmax(0,1fr) 350px}#pos-app .pos-products{grid-template-columns:repeat(auto-fill,minmax(112px,1fr))}}
@media(max-width:899px){#pos-app .pos-shell{grid-template-columns:minmax(0,1fr)}#pos-app .pos-cart-lines{max-height:300px}#pos-app .pos-product-scroll{max-height:55dvh}}
@media(max-height:559px) and (min-width:900px){#pos-app .pos-product-scroll{max-height:65dvh}#pos-app .pos-cart-lines{max-height:200px}}
</style>

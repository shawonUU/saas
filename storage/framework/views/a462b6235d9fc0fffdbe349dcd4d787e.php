<script>
(function(){
 if (window.__saasListUiLoaded) return;
 window.__saasListUiLoaded = true;
 document.addEventListener('DOMContentLoaded',()=>{
  // Reuse Customers header layout; the create control belongs beside the page title.
  document.querySelectorAll('.listui-page').forEach(page=>{
   const heading=page.querySelector('.cfg-heading');
   if(!heading)return;
   page.querySelectorAll('.listui-add').forEach(add=>{
    if(!heading.contains(add) && !add.closest('[data-listui-modal]')) heading.appendChild(add);
   });
   const toolbar=page.querySelector('.workspace-content > .ret-toolbar');
   if(toolbar && !toolbar.querySelector('input,select,form') && !toolbar.closest('[data-listui-modal]')) heading.appendChild(toolbar);
  });
  // Date-range and other special filters remain available, without a second filter bar.
  document.querySelectorAll('.listui-page form#listui-extra-filter[data-listui-filter-row]').forEach(extra=>{
   if(extra.dataset.listuiKeepVisible==='1')return;
   const form=document.getElementById('listui-standard-form');
   if(!form)return;
   const oldParent=extra.parentElement;
   const wrapper=document.createElement('div');wrapper.className='listui-advanced-layer';wrapper.hidden=true;
   const panel=document.createElement('div');panel.className='listui-advanced-panel';panel.setAttribute('role','dialog');panel.setAttribute('aria-modal','true');
   const head=document.createElement('div');head.className='listui-advanced-head';
   const heading=document.createElement('strong');heading.textContent=<?php echo json_encode(t('Additional filters', 'অতিরিক্ত ফিল্টার'), 512) ?>;
   const close=document.createElement('button');close.type='button';close.className='listui-advanced-close';close.setAttribute('aria-label',<?php echo json_encode(t('Close', 'বন্ধ করুন'), 512) ?>);close.textContent='×';
   head.append(heading,close);panel.append(head,extra);wrapper.append(panel);document.body.append(wrapper);
   extra.classList.add('listui-advanced-form');extra.removeAttribute('data-listui-filter-row');
   if(oldParent?.classList?.contains('eo-card') && oldParent.childElementCount===0 && !oldParent.textContent.trim())oldParent.remove();
   const openBtn=document.createElement('button');openBtn.type='button';openBtn.className='cfg-btn cfg-action-icon listui-advanced-trigger';
   openBtn.textContent=<?php echo json_encode(t('More filters', 'আরও ফিল্টার'), 512) ?>;openBtn.setAttribute('aria-expanded','false');
   const controls=form.querySelector('.cfg-toolbar-actions');const toggle=controls?.querySelector('[data-listui-toggle]');
   if(toggle)toggle.after(openBtn);else controls?.prepend(openBtn);
   let previous=null, scroll='';
   const hide=()=>{wrapper.hidden=true;document.body.style.overflow=scroll;openBtn.setAttribute('aria-expanded','false');previous?.focus?.();};
   openBtn.addEventListener('click',()=>{previous=document.activeElement;scroll=document.body.style.overflow;wrapper.hidden=false;document.body.style.overflow='hidden';openBtn.setAttribute('aria-expanded','true');extra.querySelector('input:not([type="hidden"]),select,button')?.focus?.();});
   close.addEventListener('click',hide);wrapper.addEventListener('click',e=>{if(e.target===wrapper)hide();});
   document.addEventListener('keydown',e=>{if(e.key==='Escape'&&!wrapper.hidden){e.preventDefault();hide();}});
   // Submitting special filters must preserve current column filters.
   extra.addEventListener('submit',()=>{
    extra.querySelectorAll('[data-listui-added-hidden]').forEach(el=>el.remove());
    new FormData(form).forEach((value,name)=>{if(!name.startsWith('f[') && name!=='sort' && name!=='dir')return;
     const hidden=document.createElement('input');hidden.type='hidden';hidden.name=name;hidden.value=value;hidden.dataset.listuiAddedHidden='1';extra.append(hidden);
    });
   });
  });
  // Some standalone reports have no Actions column; keep Go in their final filter cell.
  document.querySelectorAll('.listui-page #listui-colfilters').forEach(row=>{
   const last=row.querySelector('th:last-child');
   if(!last || row.querySelector('button[form="listui-standard-form"]'))return;
   const form=document.getElementById('listui-standard-form');if(!form)return;
   const button=document.createElement('button');
   button.type='submit';button.className='cfg-btn cfg-primary';button.setAttribute('form',form.id);
   button.textContent=<?php echo json_encode(t('Go', 'যান'), 512) ?>;
   button.style.marginTop=last.querySelector('input,select')?'6px':'0';
   last.appendChild(button);
  });
  document.querySelectorAll('[data-listui-form]').forEach(form=>{
   const root=form.closest('[data-listui-root]') || form.parentElement;
   const row=(root && root.querySelector('tr[data-listui-filter-row]')) || null;
   const rows=[...new Set([row,document.getElementById('listui-colfilters')].filter(Boolean))];
   const toggle=form.querySelector('[data-listui-toggle]');
   const key='list-ui-filter:'+location.pathname;
   let visible=true;
   try{visible=localStorage.getItem(key)!=='hidden';}catch(e){}
   const update=()=>{
    rows.forEach(item=>item.hidden=!visible);
    if(toggle){toggle.setAttribute('aria-expanded',String(visible));const txt=toggle.querySelector('[data-listui-toggle-label]');if(txt)txt.textContent=visible?toggle.dataset.hideLabel:toggle.dataset.showLabel;}
   };
   update();
   toggle?.addEventListener('click',()=>{visible=!visible;try{localStorage.setItem(key,visible?'shown':'hidden');}catch(e){}update();});
   form.querySelectorAll('[data-listui-export]').forEach(link=>link.addEventListener('click',()=>{
    const params=new URLSearchParams(location.search);
    const current=new FormData(form);const names=[...new Set([...current.keys()])];
    names.forEach(k=>{params.delete(k);current.getAll(k).forEach(v=>params.append(k,v));});
    // Column filter inputs live in the table but belong to this form by HTML form ID.
    const existingFields=new URLSearchParams(location.search);for(const key of [...existingFields.keys()])if(key.startsWith('f['))params.delete(key);
    params.delete('page');params.set('export',link.dataset.listuiExport);
    link.href=location.pathname+'?'+params.toString();
   }));
  });
  let active=null,prior=null,overflow='';
  const close=()=>{if(!active)return;active.hidden=true;document.body.style.overflow=overflow;active=null;prior?.focus?.();};
  document.querySelectorAll('[data-listui-open]').forEach(trigger=>trigger.addEventListener('click',()=>{
   const target=document.getElementById(trigger.dataset.listuiOpen);if(!target)return;
   close();prior=document.activeElement;overflow=document.body.style.overflow;
   if(target.parentElement!==document.body)document.body.appendChild(target);
   target.hidden=false;document.body.style.overflow='hidden';active=target;
   target.querySelector('input:not([type="hidden"]),button,select')?.focus?.();
   window.NexaSearchSelect?.refresh?.(target);
  }));
  document.querySelectorAll('[data-listui-modal]').forEach(overlay=>{
   overlay.querySelectorAll('[data-listui-close]').forEach(btn=>btn.addEventListener('click',close));
   overlay.addEventListener('click',e=>{if(e.target===overlay)close();});
  });
  document.addEventListener('keydown',e=>{
   if(e.key==='Escape'&&active){e.preventDefault();close();}
   if(e.key!=='Tab'||!active)return;
   const items=Array.from(active.querySelectorAll('a[href],button:not([disabled]),input:not([disabled]):not([type="hidden"]),select:not([disabled]),textarea:not([disabled])')).filter(x=>x.getClientRects().length);
   if(!items.length)return;
   if(e.shiftKey&&document.activeElement===items[0]){e.preventDefault();items[items.length-1].focus();}
   else if(!e.shiftKey&&document.activeElement===items[items.length-1]){e.preventDefault();items[0].focus();}
  });
  const recovery=document.querySelector('[data-listui-reopen="1"]');
  if(recovery){ const trigger=document.querySelector('[data-listui-open="'+recovery.id+'"]');trigger?.click(); }
 });
})();
</script>
<?php /**PATH C:\laragon\www\saas-backend\resources\views/public/business/admin/shared/list-ui/scripts.blade.php ENDPATH**/ ?>
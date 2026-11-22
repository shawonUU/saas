/* Dependency-free searchable <select> adapter. Native name/value, validation and change handlers stay intact. */
(() => {
  'use strict';
  if (window.NexaSearchSelect?.initialized) return;
  const states = new WeakMap();
  const live = new Set();
  const bn = (document.documentElement.lang || '').toLowerCase().startsWith('bn');
  const words = bn
    ? {search:'অপশন খুঁজুন...',none:'কোনো অপশন পাওয়া যায়নি',more:'আরও অপশন দেখতে সার্চ করুন'}
    : {search:'Search options...',none:'No matching option',more:'Type to find more options'};
  let active = null;
  let uid = 0;

  const label = option => option?.textContent?.trim().replace(/\s+/g,' ') || '';
  function selectedLabel(select) {
    const option = select.selectedOptions?.[0];
    return option && option.value !== '' ? label(option) : (option ? label(option) : '');
  }
  function position(state) {
    if (!state.open || !state.input.isConnected) return;
    const box = state.input.getBoundingClientRect();
    const bottom = innerHeight - box.bottom;
    const height = Math.min(300, Math.max(120, Math.floor(innerHeight * 0.45)));
    const above = bottom < Math.min(height,220) && box.top > bottom;
    state.menu.style.left = Math.max(8,Math.min(box.left,innerWidth-128)) + 'px';
    state.menu.style.width = Math.max(120,Math.min(box.width,innerWidth-16)) + 'px';
    state.menu.style.maxHeight = Math.max(80, Math.min(height,above?box.top-12:bottom-12)) + 'px';
    state.menu.style.top = above ? 'auto' : Math.max(4,box.bottom+4)+'px';
    state.menu.style.bottom = above ? Math.max(4,innerHeight-box.top+4)+'px' : 'auto';
  }
  function sync(state) {
    const {select,input} = state;
    input.disabled = select.disabled;
    input.required = false;
    if (!state.open) input.value = selectedLabel(select);
    input.placeholder = select.options.length && select.options[0].value === '' ? label(select.options[0]) : words.search;
    input.setAttribute('aria-invalid', String(Boolean(select.validity && !select.validity.valid && select.dataset.nxsTouched === '1')));
  }
  function render(state) {
    const {select,menu,input} = state;
    menu.replaceChildren();
    state.items=[];
    state.itemOptions=[];
    state.focusIndex=-1;
    const search = state.query.normalize('NFKC').toLocaleLowerCase();
    const options = Array.from(select.options);
    const filtered = options.filter(option => !search || (label(option)+' '+(option.parentElement?.label || '')).normalize('NFKC').toLocaleLowerCase().includes(search));
    const fragment=document.createDocumentFragment();
    const limit=100;
    filtered.slice(0,limit).forEach(option => {
      const button=document.createElement('button');
      button.type='button';
      button.setAttribute('role','option');
      button.id=state.id+'-opt-'+state.items.length;
      button.textContent=label(option);
      button.disabled=option.disabled || option.parentElement?.disabled === true;
      button.setAttribute('aria-selected',String(option.selected));
      button.addEventListener('pointerdown',event=>event.preventDefault());
      button.addEventListener('click',()=>choose(state,option));
      fragment.appendChild(button);
      state.items.push(button);
      state.itemOptions.push(option);
    });
    if(!filtered.length){const div=document.createElement('div');div.className='nxs-empty';div.textContent=words.none;fragment.appendChild(div);}
    if(filtered.length>limit){const note=document.createElement('div');note.className='nxs-note';note.textContent=words.more;fragment.appendChild(note);}
    menu.appendChild(fragment);
    const chosen=state.items.findIndex(el=>el.getAttribute('aria-selected')==='true' && !el.disabled);
    state.focusIndex=chosen>=0?chosen:state.items.findIndex(el=>!el.disabled);
    if(state.focusIndex>=0)state.items[state.focusIndex].classList.add('nxs-active');
    if(state.open)position(state);
  }
  function close(state,restore=true){
    if(!state?.open)return;
    state.open=false;
    state.menu.hidden=true;
    state.input.setAttribute('aria-expanded','false');
    state.input.removeAttribute('aria-activedescendant');
    if(active===state)active=null;
    if(restore)sync(state);
  }
  function open(state){
    if(state.select.disabled)return;
    if(active && active!==state)close(active);
    if(state.open)return;
    active=state;
    state.open=true;
    state.query='';
    state.input.value='';
    state.input.placeholder=words.search;
    state.menu.hidden=false;
    state.input.setAttribute('aria-expanded','true');
    render(state);
  }
  function choose(state,option){
    if(option.disabled || option.parentElement?.disabled)return;
    state.select.dataset.nxsTouched='1';
    state.select.value=option.value;
    state.select.dispatchEvent(new Event('input',{bubbles:true}));
    state.select.dispatchEvent(new Event('change',{bubbles:true}));
    close(state);
    state.input.focus({preventScroll:true});
  }
  function highlight(state,index){
    if(!state.items.length)return;
    let next=index;
    for(let tries=0;tries<state.items.length;tries++){
      next=(next+state.items.length)%state.items.length;
      if(!state.items[next].disabled)break;
      next+=index>=state.focusIndex?1:-1;
    }
    state.items.forEach((el,i)=>el.classList.toggle('nxs-active',i===next));
    state.focusIndex=next;
    const el=state.items[next];
    if(el && !el.disabled){state.input.setAttribute('aria-activedescendant',el.id);el.scrollIntoView({block:'nearest'});}
  }
  function enhance(select){
    if(states.has(select)||select.multiple||select.size>1||select.dataset.searchable==='false'||select.closest('[data-no-search]')||select.closest('template'))return;
    const wrap=document.createElement('span');
    wrap.className='nxs-select';
    const input=document.createElement('input');
    input.type='text';input.className='nxs-input';input.autocomplete='off';input.spellcheck=false;
    input.setAttribute('role','combobox');input.setAttribute('aria-autocomplete','list');input.setAttribute('aria-haspopup','listbox');input.setAttribute('aria-expanded','false');
    const originalLabel=select.getAttribute('aria-label') || (select.id && document.querySelector('label[for="'+CSS.escape(select.id)+'"]')?.textContent?.trim()) || select.name || words.search;
    input.setAttribute('aria-label',originalLabel);
    const arrow=document.createElement('span');arrow.className='nxs-arrow';arrow.setAttribute('aria-hidden','true');
    const menu=document.createElement('div');menu.className='nxs-menu';menu.hidden=true;menu.setAttribute('role','listbox');
    const state={select,wrap,input,arrow,menu,items:[],query:'',open:false,focusIndex:-1,id:'nxs-'+(++uid)};
    menu.id=state.id+'-list';input.setAttribute('aria-controls',menu.id);
    select.parentNode.insertBefore(wrap,select);
    wrap.append(select,input,arrow);
    document.body.appendChild(menu);
    states.set(select,state);live.add(state);
    select.tabIndex=-1;
    input.addEventListener('focus',()=>{sync(state);open(state);});
    input.addEventListener('click',()=>{if(!state.open)open(state);});
    input.addEventListener('input',()=>{const value=input.value;if(!state.open)open(state);state.query=value;input.value=value;render(state);});
    input.addEventListener('keydown',event=>{
      if(event.key==='Escape'){event.preventDefault();close(state);input.blur();return;}
      if(event.key==='Tab'){close(state);return;}
      if(event.key==='ArrowDown'||event.key==='ArrowUp'){
        event.preventDefault();if(!state.open)open(state);
        highlight(state,state.focusIndex+(event.key==='ArrowDown'?1:-1));
        return;
      }
      if(event.key==='Enter'&&state.open){event.preventDefault();const target=state.itemOptions[state.focusIndex];
        if(target&&!target.disabled)choose(state,target);return;
      }
    });
    select.addEventListener('change',()=>sync(state));
    select.addEventListener('invalid',()=>{select.dataset.nxsTouched='1';input.focus();open(state);});
    select.addEventListener('focus',()=>input.focus());
    select.form?.addEventListener('reset',()=>queueMicrotask(()=>sync(state)));
    const optsObserver=new MutationObserver(()=>{if(state.open)render(state);else sync(state);});
    optsObserver.observe(select,{childList:true,subtree:true,characterData:true,attributes:true,attributeFilter:['disabled','selected','label']});
    state.optsObserver=optsObserver;
    sync(state);
  }
  function scan(root=document){
    if(root.nodeType!==1 && root.nodeType!==9 && root.nodeType!==11)return;
    // cloneNode(true) of a form duplicates enhanced markup but not event listeners; unwrap those clones.
    const wrappers=[];
    if(root.nodeType===1 && root.matches('.nxs-select'))wrappers.push(root);
    root.querySelectorAll?.('.nxs-select').forEach(n=>wrappers.push(n));
    wrappers.forEach(w=>{const sel=w.querySelector(':scope > select');if(sel&&!states.has(sel)){
      w.parentNode?.insertBefore(sel,w);w.remove();
    }});
    if(root.nodeType===1 && root.matches('select'))enhance(root);
    root.querySelectorAll?.('select').forEach(enhance);
  }
  function refresh(root=document){
    scan(root);
    for(const state of live){if(!state.select.isConnected){close(state);state.optsObserver.disconnect();state.menu.remove();live.delete(state);}else if(root===document||root.contains?.(state.select))sync(state);}
  }
  function init(){
    scan(document);
    const observer=new MutationObserver(changes=>{
      const added=new Set();
      changes.forEach(change=>{change.addedNodes.forEach(node=>{if(node.nodeType===1 && !node.closest?.('.nxs-menu'))added.add(node);});});
      added.forEach(node=>{if(node.isConnected)scan(node);});
      // Dispose overlays for removed dynamic rows.
      for(const state of live)if(!state.select.isConnected){close(state);state.optsObserver.disconnect();state.menu.remove();live.delete(state);}
    });
    observer.observe(document.body,{childList:true,subtree:true});
    document.addEventListener('pointerdown',event=>{if(active&&!active.wrap.contains(event.target)&&!active.menu.contains(event.target))close(active);},true);
    document.addEventListener('focusin',event=>{if(active&&!active.wrap.contains(event.target)&&!active.menu.contains(event.target))close(active);});
    document.addEventListener('keydown',event=>{if(event.key==='Escape'&&active)close(active);});
    document.addEventListener('scroll',()=>{if(active)position(active);},true);
    window.addEventListener('resize',()=>{if(active)position(active);});
    window.NexaSearchSelect={initialized:true,refresh,close:()=>{if(active)close(active);}};
  }
  window.NexaSearchSelect={initialized:true,refresh,close:()=>{if(active)close(active);}};
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init,{once:true});else init();
})();

<script>
(() => {
    'use strict';
    const $=id=>document.getElementById(id),root=$('restaurant-pos'),preview=$('rpos-print-preview'),frame=$('rpos-print-frame'),help=$('rpos-shortcut-help');
    if(!root || !preview || !frame || !help)return;
    const templates={{ \Illuminate\Support\Js::from(['receipt'=>$routes['receipt'],'kot'=>$routes['kot']]) }};
    const words={{ \Illuminate\Support\Js::from(['bill'=>t('Bill preview','বিলের প্রিভিউ'),'kot'=>t('Kitchen ticket preview','কিচেন টিকিটের প্রিভিউ'),'loading'=>t('Loading preview…','প্রিভিউ লোড হচ্ছে…'),'failed'=>t('The preview could not be loaded. Check your connection and try again.','প্রিভিউ লোড করা যায়নি। সংযোগ পরীক্ষা করে আবার চেষ্টা করুন।'),'printFailed'=>t('Printing could not start. Please try again.','প্রিন্ট শুরু করা যায়নি। আবার চেষ্টা করুন।'),'noBill'=>t('Add items or select a saved order to print a bill.','বিল প্রিন্ট করতে খাবার যোগ করুন বা সংরক্ষিত অর্ডার নির্বাচন করুন।')]) }};
    const escaped=value=>value.replace(/[.*+?^$(){}|[\]\\]/g,'\\$&');
    const patterns=Object.entries(templates).map(([kind,value])=>({kind,path:new RegExp('^'+new URL(value,location.href).pathname.split('__ORDER__').map(escaped).join('[1-9]\\d*')+'$')}));
    let ticket=null,sequence=0,loadTimer=null,ready=false,printing=false,returnFocus=null,helpFocus=null;
    const available=node=>!!node && !node.disabled && node.getAttribute('aria-disabled')!=='true' && !node.closest('[hidden]') && node.getClientRects().length>0 && getComputedStyle(node).visibility!=='hidden';
    function commitInput(){
        const active=document.activeElement;
        if(active instanceof HTMLElement && active.matches('input,textarea,select,[contenteditable]'))active.blur();
    }
    const activate=id=>{if(!available($(id)))return false;commitInput();const node=$(id);if(!available(node))return false;node.click();return true;};
    const focusInput=id=>{const node=$(id);if(!available(node))return false;node.focus({preventScroll:false});node.select?.();return true;};
    function validTicket(value){
        if(typeof value!=='string')return null;
        try{
            const url=new URL(value,location.href),match=patterns.find(pattern=>pattern.path.test(url.pathname));
            if(url.origin!==location.origin || url.username || url.password || !match)return null;
            const batch=url.searchParams.get('batch');url.search='';url.hash='';
            if(match.kind==='kot' && batch && /^[1-9]\d*$/.test(batch))url.searchParams.set('batch',batch);
            url.searchParams.set('embed','1');
            return {url:url.href,kind:match.kind};
        }catch(_){return null;}
    }
    function clearLoadTimer(){if(loadTimer!==null){clearTimeout(loadTimer);loadTimer=null;}}
    function showError(message){
        ready=false;printing=false;clearLoadTimer();$('rpos-print-confirm').disabled=true;$('rpos-print-close').disabled=false;
        frame.hidden=true;$('rpos-print-stage').setAttribute('aria-busy','false');$('rpos-print-status').hidden=false;
        $('rpos-print-message').textContent=message;$('rpos-print-retry').hidden=!ticket;
    }
    function loadPreview(){
        if(!ticket)return;
        sequence++;const current=sequence;ready=false;printing=false;clearLoadTimer();
        frame.hidden=true;$('rpos-print-stage').setAttribute('aria-busy','true');$('rpos-print-status').hidden=false;
        $('rpos-print-message').textContent=words.loading;$('rpos-print-retry').hidden=true;$('rpos-print-confirm').disabled=true;
        $('rpos-print-close').disabled=false;
        loadTimer=setTimeout(()=>{if(current===sequence && preview.open)showError(words.failed);},15000);
        frame.src=ticket.url;
    }
    function open(value,title=''){
        if(printing)return false;
        const next=validTicket(value);if(!next)return false;
        const other=document.querySelector('dialog[open]');if(other && other!==preview)return false;
        if(!preview.open)returnFocus=document.activeElement;
        ticket=next;$('rpos-print-title').textContent=title || words[next.kind==='receipt'?'bill':'kot'];
        if(!preview.open)preview.showModal();
        loadPreview();return true;
    }
    function endPrint(){
        printing=false;$('rpos-print-close').disabled=false;$('rpos-print-confirm').disabled=!ready;
        if(preview.open)$('rpos-print-confirm').focus({preventScroll:true});
    }
    function printPreview(){
        if(!preview.open || !ready || printing)return;
        try{
            const target=frame.contentWindow;if(!target)throw Error();
            printing=true;$('rpos-print-confirm').disabled=true;$('rpos-print-close').disabled=true;
            target.focus();target.print();
        }catch(_){showError(words.printFailed);}
    }
    function closePreview(){if(!printing && preview.open)preview.close();}
    frame.addEventListener('load',async()=>{
        if(!ticket || !preview.open)return;
        const current=sequence,expected=ticket;
        try{
            const doc=frame.contentDocument,view=frame.contentWindow;
            if(!doc || !view || view.location.href!==expected.url || doc.querySelector('meta[name="rpos-print-document"]')?.content!==expected.kind)throw Error();
            await doc.fonts?.ready;
            await Promise.all([...doc.images].map(img=>img.complete?Promise.resolve():new Promise(resolve=>{img.addEventListener('load',resolve,{once:true});img.addEventListener('error',resolve,{once:true});})));
            if(current!==sequence || !preview.open)return;
            clearLoadTimer();ready=true;frame.hidden=false;$('rpos-print-status').hidden=true;$('rpos-print-stage').setAttribute('aria-busy','false');$('rpos-print-confirm').disabled=false;
            doc.addEventListener('keydown',keyboard);view.addEventListener('afterprint',endPrint);
            view.matchMedia('print').addEventListener('change',event=>{if(!event.matches && printing)endPrint();});
        }catch(_){if(current===sequence && preview.open)showError(words.failed);}
    });
    frame.addEventListener('error',()=>{if(preview.open)showError(words.failed);});
    preview.addEventListener('cancel',event=>{if(printing)event.preventDefault();});
    preview.addEventListener('close',()=>{
        if(preview.open)return;
        sequence++;clearLoadTimer();ticket=null;ready=false;printing=false;frame.removeAttribute('src');frame.hidden=true;
        $('rpos-print-stage').setAttribute('aria-busy','false');
        if(available(returnFocus))returnFocus.focus({preventScroll:true});
    });
    function openHelp(){if(document.querySelector('dialog[open]'))return;helpFocus=document.activeElement;help.showModal();}
    help.addEventListener('close',()=>{if(!help.open && available(helpFocus))helpFocus.focus({preventScroll:true});});
    function bill(){
        if(root.dataset.locked==='true' || root.dataset.step==='tables')return;
        if(activate('rpos-print-bill') || activate('rpos-receipt'))return;
        if(root.dataset.emptyOrder!=='true' || activate('rpos-last-receipt'))return;
        const alert=$('rpos-alert');if(alert){alert.textContent=words.noBill;alert.hidden=false;alert.classList.remove('error');}
    }
    function keyboard(event){
        if(event.defaultPrevented || event.isComposing)return;
        const printKey=(event.ctrlKey || event.metaKey) && !event.altKey && event.key.toLowerCase()==='p';
        if(printKey){
            event.preventDefault();if(event.repeat)return;
            if(preview.open){printPreview();return;}if(!document.querySelector('dialog[open]'))bill();return;
        }
        if(preview.open){
            if(event.key==='Escape'){event.preventDefault();closePreview();}
            return;
        }
        if(event.key==='F8' && !event.ctrlKey && !event.metaKey && !event.altKey && !event.repeat && $('rpos-payment')?.open){
            event.preventDefault();activate('rpos-payment-submit');return;
        }
        if(document.querySelector('dialog[open]') || event.ctrlKey || event.metaKey || event.altKey || event.repeat)return;
        const typing=event.target instanceof Element && !!event.target.closest('input,textarea,select,[contenteditable="true"],[contenteditable=""]');
        if(event.key==='?' && !typing){event.preventDefault();openHelp();return;}
        if(!['F2','F3','F4','F6','F8','F9','F10'].includes(event.key))return;
        event.preventDefault();
        if(event.key==='F2'){if(!focusInput('rpos-search'))focusInput('rpos-table-search');}
        if(event.key==='F3'){if(!activate('rpos-change-table') && !activate('rpos-continue-order'))focusInput('rpos-table-search');}
        if(event.key==='F4')activate('rpos-new');
        if(event.key==='F6')activate('rpos-save');
        if(event.key==='F8')activate('rpos-checkout');
        if(event.key==='F9')bill();
        if(event.key==='F10')activate('rpos-print-kot');
    }
    document.addEventListener('keydown',keyboard);
    document.addEventListener('click',event=>{
        const link=event.target instanceof Element?event.target.closest('#rpos-last-receipt[data-rpos-print],#rpos-last-kot[data-rpos-print]'):null;
        if(!link || event.defaultPrevented)return;
        event.preventDefault();if(available(link))open(link.href,link.textContent.trim());
    });
    document.addEventListener('auxclick',event=>{
        if(event.button!==1)return;
        const link=event.target instanceof Element?event.target.closest('a[data-rpos-print]'):null;
        if(!link || !root.contains(link))return;
        event.preventDefault();if(available(link))link.click();
    });
    document.addEventListener('restaurant-pos:print',event=>open(event.detail?.url,event.detail?.title));
    $('rpos-print-confirm').addEventListener('click',printPreview);
    $('rpos-print-retry').addEventListener('click',loadPreview);
    $('rpos-print-close').addEventListener('click',closePreview);
    $('rpos-shortcuts')?.addEventListener('click',openHelp);
    $('rpos-shortcut-close').addEventListener('click',()=>help.close());
    window.RestaurantPosPrint=Object.freeze({open});
})();
</script>

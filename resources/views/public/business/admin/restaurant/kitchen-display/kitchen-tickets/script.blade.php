<script>
(() => {
    'use strict';
    const root=document.getElementById('restaurant-kds');if(!root)return;
    const $=id=>document.getElementById(id),dataUrl={{ \Illuminate\Support\Js::from(route('business.admin.restaurant.kitchen-display.kitchen-tickets.data',['business'=>request()->route('business')])) }},updateUrl={{ \Illuminate\Support\Js::from(route('business.admin.restaurant.kitchen-display.kitchen-tickets.update',['business'=>request()->route('business'),'order'=>'__ORDER__','item'=>'__ITEM__'])) }},posUrl={{ \Illuminate\Support\Js::from(route('business.admin.sales.restaurant-pos.index',['business'=>request()->route('business')])) }},kotUrl={{ \Illuminate\Support\Js::from(route('business.admin.sales.restaurant-pos.kot',['business'=>request()->route('business'),'order'=>'__ORDER__'])) }},canUpdate={{ \Illuminate\Support\Js::from((bool)($permissions['update'] ?? false)) }},language={{ \Illuminate\Support\Js::from(session('portal_lang','en')) }},csrf=document.querySelector('meta[name=csrf-token]').content;
    const words={{ \Illuminate\Support\Js::from(['queued'=>t('Queued','অপেক্ষমাণ'),'preparing'=>t('Preparing','প্রস্তুত হচ্ছে'),'ready'=>t('Ready','প্রস্তুত'),'served'=>t('Served','পরিবেশিত'),'start'=>t('Start preparing','প্রস্তুত শুরু করুন'),'finish'=>t('Mark ready','প্রস্তুত চিহ্নিত করুন'),'serve'=>t('Mark served','পরিবেশিত চিহ্নিত করুন'),'empty'=>t('No active kitchen tickets.','কিচেনে কোনো চলমান টিকিট নেই।'),'failed'=>t('Unable to refresh kitchen tickets.','কিচেন টিকিট রিফ্রেশ করা যায়নি।'),'changed'=>t('Ticket changed. The latest state has been loaded; review before trying again.','টিকিট পরিবর্তন হয়েছে। সর্বশেষ অবস্থা দেখানো হয়েছে; আবার চেষ্টা করার আগে দেখুন।'),'retry'=>t('Connection interrupted. The latest ticket state will be refreshed before another action.','সংযোগ বিচ্ছিন্ন। পরের কাজের আগে সর্বশেষ টিকিট দেখানো হবে।'),'updated'=>t('Updated','হালনাগাদ'),'order'=>t('Open order','অর্ডার খুলুন'),'print'=>t('Print KOT','কিচেন টিকিট প্রিন্ট'),'batch'=>t('Batch','ব্যাচ'),'general'=>t('General kitchen','সাধারণ কিচেন'),'dine_in'=>t('Dine-in','ডাইন-ইন'),'takeaway'=>t('Takeaway','টেকঅ্যাওয়ে'),'delivery'=>t('Delivery','ডেলিভারি')]) }};
    let orders={{ \Illuminate\Support\Js::from($orders ?? []) }},loading=false,updating=false,blocked=false;
    const element=(tag,cls,text)=>{const node=document.createElement(tag);if(cls)node.className=cls;if(text!==undefined)node.textContent=text;return node;};
    const message=text=>{$('kds-alert').textContent=text || '';$('kds-alert').hidden=!text;};
    function render(){
        const holder=$('kds-tickets');holder.replaceChildren();let count=0;
        for(const order of Object.values(orders)){
            const visible=(order.items || []).filter(item=>['queued','preparing','ready'].includes(item.kitchen_status) && (!$('kds-station').value || String(item.kitchen_station_id)===$('kds-station').value));if(!visible.length)continue;count++;
            const card=element('article','kds-ticket'),head=element('div','kds-ticket-head');head.append(element('h2','',order.order_no));const table=order.table?(language==='bn'?(order.table.name_bn || order.table.name):order.table.name):'';head.append(element('p','rst-muted',[words[order.service_type],table,order.customer_name].filter(Boolean).join(' · ')));
            const links=element('div','kds-actions'),open=element('a','',words.order);open.href=posUrl+'?order='+order.id;const print=element('a','',words.print);print.href=kotUrl.replace('__ORDER__',order.id);print.target='_blank';print.rel='noopener';links.append(open,print);head.append(links);card.append(head);
            for(const item of visible){
                const line=element('div','kds-line'),title=element('div','rst-inline between');title.append(element('span','kds-food',language==='bn'?(item.name_bn || item.name):item.name),element('span','kds-qty','× '+item.quantity));line.append(title);
                const status=element('span','rst-badge',words[item.kitchen_status]);status.dataset.status=item.kitchen_status;const meta=element('div','rst-inline');meta.style.marginTop='9px';meta.append(status,element('span','rst-muted',words.batch+' '+item.kitchen_batch));line.append(meta);
                const station=language==='bn'?(item.kitchen_station_name_bn || item.kitchen_station_name):item.kitchen_station_name;line.append(element('p','kds-note',station || words.general));
                if(item.modifiers?.length)line.append(element('p','kds-note',item.modifiers.map(modifier=>language==='bn'?(modifier.name_bn || modifier.name):modifier.name).join(' · ')));
                if(item.kitchen_note)line.append(element('p','kds-note important',item.kitchen_note));
                if(item.sent_at)line.append(element('p','kds-note',new Date(item.sent_at).toLocaleTimeString(language==='bn'?'bn-BD':'en-BD',{hour:'2-digit',minute:'2-digit'})));
                if(canUpdate){const next={queued:'preparing',preparing:'ready',ready:'served'}[item.kitchen_status],label={queued:words.start,preparing:words.finish,ready:words.serve}[item.kitchen_status],button=element('button','ret-btn',label);button.type='button';button.disabled=updating || blocked;button.addEventListener('click',()=>update(order,item,next));line.append(button);}card.append(line);
            }
            if(order.restaurant_notes)card.append(element('div','kds-footer',order.restaurant_notes));holder.append(card);
        }
        if(!count)holder.append(element('div','ret-card rst-empty kds-empty',words.empty));
    }
    async function refresh(quiet=false){
        if(loading || updating)return false;loading=true;$('kds-refresh').disabled=true;
        try{const url=new URL(dataUrl,location.href);if($('kds-station').value)url.searchParams.set('station',$('kds-station').value);const response=await fetch(url,{credentials:'same-origin',headers:{Accept:'application/json'}}),body=await response.json();if(!response.ok || !Array.isArray(body.orders))throw Error(body.message || words.failed);orders=body.orders;blocked=false;render();$('kds-updated').textContent=words.updated+' '+new Date().toLocaleTimeString(language==='bn'?'bn-BD':'en-BD',{hour:'2-digit',minute:'2-digit'});if(!quiet)message('');return true;}catch(error){message(error.message || words.failed);return false;}finally{loading=false;$('kds-refresh').disabled=false;}
    }
    async function update(order,item,status){
        if(updating || blocked)return;updating=true;render();let error='';
        try{const response=await fetch(updateUrl.replace('__ORDER__',order.id).replace('__ITEM__',item.id),{method:'POST',credentials:'same-origin',headers:{Accept:'application/json','Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify({version:order.version,status})}),body=await response.json();if(!response.ok){error=Object.values(body.errors || {}).flat()[0] || (response.status===409?words.changed:body.message || words.retry);blocked=true;}else if(body.success!==true){error=words.retry;blocked=true;}}
        catch(_){error=words.retry;blocked=true;}
        finally{updating=false;await refresh(true);message(error);render();}
    }
    $('kds-refresh').addEventListener('click',()=>refresh());$('kds-station').addEventListener('change',()=>refresh());
    const timer=window.setInterval(()=>{if(!document.hidden && $('kds-auto').checked)refresh(true);},12000);window.addEventListener('pagehide',()=>window.clearInterval(timer),{once:true});document.addEventListener('visibilitychange',()=>{if(!document.hidden && $('kds-auto').checked)refresh(true);});render();refresh(true);
})();
</script>

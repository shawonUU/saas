<?php
    $auditBusiness = request()->attributes->get('current_business');
    $auditUser = \Illuminate\Support\Facades\Auth::guard('business_admin')->user();
    $auditAllowed = false;
    if ($auditBusiness && $auditUser && (int)$auditUser->business_id === (int)$auditBusiness->id) {
        $auditAllowed = $auditUser->isOwnerOf($auditBusiness);
        if (!$auditAllowed) {
            $auditModules = app(\App\Services\Public\Business\Admin\Settings\NavigationManagement\BusinessNavigation::class)->forBusiness($auditBusiness, $auditUser);
            $auditMenu = $auditModules->firstWhere('slug','settings')?->menus->firstWhere('slug','audit');
            $auditAllowed = (bool) $auditMenu?->children->contains('slug','activity-logs');
        }
    }
?>
<?php if($auditAllowed): ?>
<style>
.audit-row-btn{font-size:12px;font-weight:650;padding:7px 10px;border-radius:8px;background:transparent;color:var(--text,#e2e8f0);border:1px solid var(--border,#475569);white-space:nowrap;cursor:pointer;margin:2px}.audit-row-btn:hover{border-color:var(--primary,#7661ec);color:var(--primary,#7661ec)}
.audit-modal{position:fixed;inset:0;z-index:200000;display:none;align-items:center;justify-content:center;padding:12px;background:rgba(3,10,25,.72)}.audit-modal.is-open{display:flex}.audit-modal-box{width:min(850px,100%);max-height:min(85vh,800px);overflow:auto;background:var(--white,#1b263a);color:var(--text,#e2e8f0);border:1px solid var(--border,#475569);border-radius:14px;padding:18px}.audit-modal-head{display:flex;align-items:center;justify-content:space-between;gap:12px}.audit-modal-head button{background:transparent;border:1px solid var(--border,#475569);border-radius:8px;color:inherit;padding:5px 10px;cursor:pointer}.audit-event{padding:13px 0;border-top:1px solid var(--border,#475569)}.audit-event-title{font-size:13px;margin-bottom:7px}.audit-diff{width:100%;border-collapse:collapse;font-size:12px;table-layout:fixed}.audit-diff th,.audit-diff td{padding:7px;border-bottom:1px solid var(--border,#475569);overflow-wrap:anywhere;text-align:left}.audit-muted{color:var(--muted,#94a3b8)}
</style>
<div class="audit-modal" id="audit-history-modal" role="dialog" aria-modal="true" aria-labelledby="audit-history-title" aria-hidden="true">
 <section class="audit-modal-box"><div class="audit-modal-head"><h2 id="audit-history-title"><?php echo e(t('Record history','রেকর্ড ইতিহাস')); ?></h2><button type="button" id="audit-history-close"><?php echo e(t('Close','বন্ধ')); ?> ×</button></div><div id="audit-history-content" aria-live="polite"></div></section>
</div>
<?php
    $auditHistoryRecordUrl = route('business.admin.settings.audit.activity-logs.record', [
        'business' => request()->route('business'),
    ]);
    $auditHistoryLabels = [
        'log' => t('View Log', 'লগ দেখুন'),
        'loading' => t('Loading history…', 'ইতিহাস লোড হচ্ছে…'),
        'empty' => t('No history found for this record.', 'এই রেকর্ডের ইতিহাস পাওয়া যায়নি।'),
        'error' => t('Unable to load history.', 'ইতিহাস লোড করা যায়নি।'),
    ];
?>
<script>
(function(){
 const modal=document.getElementById('audit-history-modal');if(!modal)return;
 const content=document.getElementById('audit-history-content');const title=document.getElementById('audit-history-title');
 const base=<?php echo \Illuminate\Support\Js::from($auditHistoryRecordUrl)->toHtml() ?>;
 const word=<?php echo \Illuminate\Support\Js::from($auditHistoryLabels)->toHtml() ?>;
 const escape=s=>String(s??'—').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
 const human=v=>v===null||v===undefined?'—':(typeof v==='object'?JSON.stringify(v):String(v));
 let previous=null;
 function close(){modal.classList.remove('is-open');modal.setAttribute('aria-hidden','true');previous?.focus?.()}
 document.getElementById('audit-history-close').addEventListener('click',close);
 modal.addEventListener('click',e=>{if(e.target===modal)close()});
 window.addEventListener('keydown',e=>{if(e.key==='Escape'&&modal.classList.contains('is-open'))close()});
 async function open(btn,row){
   previous=btn;modal.classList.add('is-open');modal.setAttribute('aria-hidden','false');document.getElementById('audit-history-close').focus();
   title.textContent=word.log+' · '+row.dataset.auditTable+' #'+row.dataset.auditId;content.textContent=word.loading;
   try{
    const url=new URL(base,window.location.origin);url.searchParams.set('table',row.dataset.auditTable);url.searchParams.set('id',row.dataset.auditId);
    const response=await fetch(url,{credentials:'same-origin',headers:{Accept:'application/json'}});if(!response.ok)throw Error(String(response.status));const result=await response.json();
    if(!result.events?.length){content.textContent=word.empty;return;}
    content.innerHTML=result.events.map(event=>'<div class="audit-event"><div class="audit-event-title"><strong>'+escape(event.action)+'</strong> · '+escape(event.when)+' · '+escape(event.business_date)+' · '+escape(event.actor)+'</div><table class="audit-diff"><thead><tr><th>Field</th><th>Before</th><th>After</th></tr></thead><tbody>'+((event.fields||[]).map(x=>'<tr><td>'+escape(x.field)+'</td><td>'+escape(human(x.before))+'</td><td>'+escape(human(x.after))+'</td></tr>').join('')||'<tr><td colspan="3">—</td></tr>')+'</tbody></table></div>').join('');
   }catch(e){content.textContent=word.error}
 }
 function mount(root=document){
  root.querySelectorAll('tr[data-audit-table][data-audit-id]').forEach(row=>{
   if(row.dataset.auditMounted||!row.dataset.auditId)return;
   row.dataset.auditMounted='1';let cell=row.lastElementChild;if(!cell||cell.tagName!=='TD')return;
   let button=cell.querySelector('[data-audit-open]');
   if(!button){button=document.createElement('button');button.type='button';button.className='audit-row-btn';button.textContent=word.log;button.dataset.auditOpen='1';cell.appendChild(button)}
   button.addEventListener('click',()=>open(button,row));
  });
 }
 if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',()=>mount());else mount();
 // Re-scan AJAX-loaded rows without binding duplicate handlers.
 let timeout;const observer=new MutationObserver(()=>{clearTimeout(timeout);timeout=setTimeout(()=>mount(),50)});observer.observe(document.body,{childList:true,subtree:true});
})();
</script>
<?php endif; ?>
<?php /**PATH C:\laragon\www\saas-backend\resources\views/public/business/admin/settings/audit/activity-logs/partials/widget.blade.php ENDPATH**/ ?>
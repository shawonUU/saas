@extends('public.business.admin.layout')
@section('content')
<div class="ret-page restaurant-workspace" id="restaurant-kds">
@include('public.business.admin.retail._styles')
@include('public.business.admin.restaurant.dining.table-map.styles')
@include('public.business.admin.sidebar')<div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div>
<main class="workspace-content">
<div class="ret-title"><div class="rst-inline"><button type="button" class="ret-btn alt ret-mobile-nav" data-ws-sidebar-toggle aria-label="{{ t('Open navigation','মেনু খুলুন') }}">☰</button><div><h1>{{ t('Kitchen display','কিচেন ডিসপ্লে') }}</h1><p>{{ t('Prepare each ticket and mark food ready for service.','প্রতিটি টিকিট প্রস্তুত করে খাবার পরিবেশনের জন্য প্রস্তুত চিহ্নিত করুন।') }}</p></div></div><a class="ret-btn alt" href="{{ route('business.admin.sales.restaurant-pos.index',['business'=>request()->route('business')]) }}">{{ t('Open POS','পস খুলুন') }}</a></div>
<div id="kds-alert" class="ret-error" role="status" aria-live="polite" hidden></div>
<div class="ret-card"><div class="ret-toolbar" style="margin-bottom:0"><label for="kds-station" class="rst-muted">{{ t('Kitchen station','কিচেন স্টেশন') }}</label><select id="kds-station" class="ret-input"><option value="">{{ t('All stations','সব স্টেশন') }}</option>@foreach($stations as $station)<option value="{{ $station->id }}" @selected((int)request('station')===(int)$station->id)>{{ t($station->name,$station->name_bn) }}</option>@endforeach</select><button type="button" class="ret-btn alt" id="kds-refresh">{{ t('Refresh','রিফ্রেশ') }}</button><label class="rst-inline rst-muted"><input type="checkbox" id="kds-auto" checked> {{ t('Auto refresh','স্বয়ংক্রিয় রিফ্রেশ') }}</label><span id="kds-updated" class="rst-muted" aria-live="polite"></span></div></div>
<div class="rst-grid" id="kds-tickets"></div>
</main>
<style>#restaurant-kds .rst-grid{grid-template-columns:repeat(auto-fill,minmax(280px,1fr))}.kds-ticket{background:var(--rp-panel);border:1px solid var(--rp-border);border-radius:14px;overflow:hidden}.kds-ticket-head{padding:15px;background:var(--rp-bg);border-bottom:1px solid var(--rp-border)}.kds-ticket-head h2{font-size:17px;margin-bottom:6px}.kds-line{padding:14px;border-bottom:1px solid var(--rp-border)}.kds-food{font-size:14px;font-weight:700;line-height:1.5}.kds-qty{font-weight:800;font-size:17px;color:var(--rp-primary)}.kds-line .ret-btn{margin-top:10px;min-height:36px;width:100%}.kds-note{font-size:12px;color:var(--rp-muted);line-height:1.6;margin-top:8px}.kds-note.important{background:var(--rp-bg);padding:9px;border-radius:7px;color:var(--rp-ink)}.kds-footer{padding:11px 14px;font-size:11px}.kds-empty{grid-column:1/-1}.kds-actions{display:flex;gap:8px;margin-top:9px}.kds-actions a{font-size:11px;color:var(--rp-primary)}@media(max-width:500px){#restaurant-kds .rst-grid{grid-template-columns:1fr}}</style>
</div>
@include('public.business.admin.restaurant.kitchen-display.kitchen-tickets.script')
@endsection

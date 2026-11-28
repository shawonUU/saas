@extends('public.business.admin.layout')
@section('content')
<div class="ret-page listui-page">@include('public.business.admin.retail._styles')
@include('public.business.admin.shared.list-ui.styles')
@include('public.business.admin.sidebar')<div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div><main class="workspace-content">
<div class="ret-title cfg-heading"><div><button type="button" class="ret-btn alt ret-mobile-nav" data-ws-sidebar-toggle>☰</button><div class="cfg-eyebrow">{{ t('Accounts & Finance','হিসাব ও ফাইন্যান্স') }} / {{ t('Payments','পেমেন্ট') }}</div><h1>{{ $kind==='supplier' ? t('Supplier Payments','সরবরাহকারী পেমেন্ট') : t('Customer Collections','কাস্টমার কালেকশন') }}</h1><p>{{ t('Business workspace','বিজনেস ওয়ার্কস্পেস') }}</p></div></div>
@if(session('success'))<div class="ret-toast">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ret-error">{{ $errors->first() }}</div>@endif
<div class="listui-add"><button type="button" class="ret-btn" data-listui-open="listui-create">+ {{ t('Receive Payment','পেমেন্ট গ্রহণ') }}</button></div>
<div class="listui-overlay" id="listui-create" data-listui-modal @if($errors->any()) data-listui-reopen="1" @endif hidden>
 <div class="listui-dialog" role="dialog" aria-modal="true" aria-labelledby="listui-create-title" tabindex="-1">
  <div class="listui-dialog-head"><h2 id="listui-create-title">{{ t('Receive Payment','পেমেন্ট গ্রহণ') }}</h2><button type="button" class="listui-dialog-close" data-listui-close aria-label="{{ t('Close','বন্ধ') }}">×</button></div>
  <div class="listui-dialog-body"><form class="ret-card" method="POST" action="{{ route($kind==='supplier'?'business.admin.finance.payables.vendor-payments.store':'business.admin.finance.receivables.collections.store') }}">@csrf<div class="ret-grid"><div class="ret-field"><label>{{ t('Document','ডকুমেন্ট') }}</label><select class="ret-input" name="document_id" required><option value="">—</option>@foreach($docs as $doc)<option value="{{ $doc->id }}">{{ $doc->purchase_no??$doc->order_no }} ({{ t('Due','বকেয়া') }}: {{ number_format(max(0,$doc->total-$doc->paid-($doc->credited??0)),2) }})</option>@endforeach</select></div><div class="ret-field"><label>{{ t('Amount','পরিমাণ') }}</label><input class="ret-input" type="number" name="amount" min=".01" step=".01" required></div><div class="ret-field"><label>{{ t('Method','পদ্ধতি') }}</label><select class="ret-input" name="method"><option value="cash">Cash</option><option value="bank">Bank</option></select></div><div class="ret-field"><label>{{ t('Reference','রেফারেন্স') }}</label><input class="ret-input" name="reference_no"></div></div><button class="ret-btn" style="margin-top:14px">{{ t('Post Payment','পেমেন্ট পোস্ট') }}</button></form></div>
 </div>
</div>
@php $columns=['reference_id'=>'Document','method'=>'Method','direction'=>'Direction','amount'=>'Amount','reference_no'=>'Reference','created_at'=>'Date']; @endphp @include('public.business.admin.retail._table')
</main></div>
@include('public.business.admin.shared.list-ui.scripts')
@endsection

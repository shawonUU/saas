@extends('public.business.admin.layout')
@section('content')
<div class="ret-page listui-page">@include('public.business.admin.retail._styles')
@include('public.business.admin.shared.list-ui.styles')
@include('public.business.admin.sidebar')<div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div>
<main class="workspace-content"><div class="ret-title cfg-heading"><div><button type="button" class="ret-btn alt ret-mobile-nav" data-ws-sidebar-toggle>☰</button><div class="cfg-eyebrow">{{ t('Accounts & Finance','হিসাব ও ফাইন্যান্স') }} / {{ t('Bank Transfers','ব্যাংক ট্রান্সফার') }}</div><h1>{{ t("Cash & Bank Transfers","নগদ ও ব্যাংক স্থানান্তর") }}</h1><p>{{ t('Business workspace','বিজনেস ওয়ার্কস্পেস') }}</p></div></div>
@if(session('success'))<div class="ret-toast">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ret-error">{{ $errors->first() }}</div>@endif
<div class="listui-add"><button type="button" class="ret-btn" data-listui-open="listui-create">+ {{ t('New Bank Transfer','নতুন ব্যাংক ট্রান্সফার') }}</button></div>
<div class="listui-overlay" id="listui-create" data-listui-modal @if($errors->any()) data-listui-reopen="1" @endif hidden>
 <div class="listui-dialog" role="dialog" aria-modal="true" aria-labelledby="listui-create-title" tabindex="-1">
  <div class="listui-dialog-head"><h2 id="listui-create-title">{{ t('New Bank Transfer','নতুন ব্যাংক ট্রান্সফার') }}</h2><button type="button" class="listui-dialog-close" data-listui-close aria-label="{{ t('Close','বন্ধ') }}">×</button></div>
  <div class="listui-dialog-body"><form method="POST" class="ret-card" action="{{ route('business.admin.finance.banking.bank-transfers.store') }}">@csrf<div class="ret-grid"><div class="ret-field"><label>{{ t('Direction','দিক') }}</label><select class="ret-input" name="direction"><option value="cash_to_bank">Cash → Bank</option><option value="bank_to_cash">Bank → Cash</option></select></div><div class="ret-field"><label>{{ t('Amount','পরিমাণ') }}</label><input class="ret-input" type="number" step=".01" min=".01" name="amount" required></div><div class="ret-field"><label>{{ t('Reference','রেফারেন্স') }}</label><input class="ret-input" name="reference_no" required></div></div><button class="ret-btn" style="margin-top:12px">{{ t('Post Transfer','স্থানান্তর পোস্ট') }}</button></form></div>
 </div>
</div>@php $columns=['direction'=>'Direction','amount'=>'Amount','reference_no'=>'Reference','created_at'=>'Created']; @endphp @include('public.business.admin.retail._table')
</main></div>
@include('public.business.admin.shared.list-ui.scripts')
@endsection

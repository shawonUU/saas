@extends('public.business.admin.layout')
@section('content')
<div class="ret-page">@include('public.business.admin.retail._styles')
@include('public.business.admin.sidebar')<div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div>
<main class="workspace-content"><div class="ret-title"><div><button type="button" class="ret-btn alt ret-mobile-nav" data-ws-sidebar-toggle>☰</button><h1>{{ t("Cash & Bank Transfers","নগদ ও ব্যাংক স্থানান্তর") }}</h1><p>{{ t('Business workspace','বিজনেস ওয়ার্কস্পেস') }}</p></div><div class="ret-actions"><a class="ret-btn alt" href="{{ route('business.admin.dashboard') }}">{{ t('Dashboard','ড্যাশবোর্ড') }}</a></div></div>
@if(session('success'))<div class="ret-toast">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ret-error">{{ $errors->first() }}</div>@endif
<form method="POST" class="ret-card" action="{{ route('business.admin.finance.banking.bank-transfers.store') }}">@csrf<div class="ret-grid"><div class="ret-field"><label>{{ t('Direction','দিক') }}</label><select class="ret-input" name="direction"><option value="cash_to_bank">Cash → Bank</option><option value="bank_to_cash">Bank → Cash</option></select></div><div class="ret-field"><label>{{ t('Amount','পরিমাণ') }}</label><input class="ret-input" type="number" step=".01" min=".01" name="amount" required></div><div class="ret-field"><label>{{ t('Reference','রেফারেন্স') }}</label><input class="ret-input" name="reference_no" required></div></div><button class="ret-btn" style="margin-top:12px">{{ t('Post Transfer','স্থানান্তর পোস্ট') }}</button></form>@php $columns=['direction'=>'Direction','amount'=>'Amount','reference_no'=>'Reference','created_at'=>'Created']; @endphp @include('public.business.admin.retail._table')
</main></div>
@endsection

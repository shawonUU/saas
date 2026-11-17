@extends('public.business.admin.layout')
@section('content')
<div class="ret-page">@include('public.business.admin.retail._styles')
@include('public.business.admin.sidebar')<div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div>
<main class="workspace-content"><div class="ret-title"><div><button type="button" class="ret-btn alt ret-mobile-nav" data-ws-sidebar-toggle>☰</button><h1>{{ t('Gateway Bank Reconciliation','গেটওয়ে ব্যাংক মিল') }}</h1></div></div>
@if(session('success'))<div class="ret-toast">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ret-error">{{ $errors->first() }}</div>@endif
<div class="ret-card"><p class="ret-help">{{ t('Verified payments remain in Gateway Clearing until settled against the bank. Enter the actual gateway fee and bank deposit reference.','যাচাইকৃত পেমেন্ট ব্যাংকে জমা না হওয়া পর্যন্ত Gateway Clearing-এ থাকে। প্রকৃত ফি ও ব্যাংক রেফারেন্স দিয়ে মিল করুন।') }}</p></div>
@php $columns=['tran_id'=>'Reference','order_id'=>'Order','amount'=>'Amount','fee'=>'Fee','settled_at'=>'Date'];$rowActions='public.business.admin.retail._rows.gateway_settlements'; @endphp
@include('public.business.admin.retail._table')
</main></div>
@endsection

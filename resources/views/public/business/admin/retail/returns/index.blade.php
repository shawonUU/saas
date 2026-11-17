@extends('public.business.admin.layout')
@section('content')
<div class="ret-page">
@include('public.business.admin.retail._styles')
@include('public.business.admin.sidebar')
<div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div><main class="workspace-content">
<div class="ret-title"><div><button type="button" class="ret-btn alt ret-mobile-nav" data-ws-sidebar-toggle>☰</button><h1>{{ t('Sales Returns','বিক্রয় রিটার্ন') }}</h1><p>{{ t('Business workspace','বিজনেস ওয়ার্কস্পেস') }}</p></div><div class="ret-actions"><a class="ret-btn alt" href="{{ route('store.admin.dashboard') }}">{{ t('Dashboard','ড্যাশবোর্ড') }}</a></div></div>
@if(session('success'))<div class="ret-toast">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ret-error">{{ $errors->first() }}</div>@endif
@php $columns=['order_id'=>'Order ID','amount'=>'Credit Amount','refunded'=>'Refunded','reason'=>'Reason','created_at'=>'Date']; @endphp
<div class="ret-toolbar"><a class="ret-btn" href="{{ route('store.admin.retail.returns.create') }}">+ {{ t('Open / New','খুলুন / নতুন') }}</a></div>
<div class="ret-card"><h3>{{ t('Refund Against a Posted Return','পোস্ট করা রিটার্নের বিপরীতে রিফান্ড') }}</h3><form method="POST" action="{{ route('store.admin.retail.returns.refund') }}">@csrf<div class="ret-grid"><div class="ret-field"><label>{{ t('Return ID','রিটার্ন আইডি') }}</label><input class="ret-input" name="return_id" type="number" required min="1"></div><div class="ret-field"><label>{{ t('Amount','পরিমাণ') }}</label><input class="ret-input" name="amount" type="number" required min=".01" step=".01"></div><div class="ret-field"><label>{{ t('Payment Method','পেমেন্ট পদ্ধতি') }}</label><select class="ret-input" name="method"><option value="cash">Cash</option><option value="bank">Bank</option></select></div><div class="ret-field"><label>{{ t('Reference','রেফারেন্স') }}</label><input class="ret-input" name="reference_no"></div></div><button class="ret-btn" style="margin-top:14px">{{ t('Post Refund','রিফান্ড পোস্ট') }}</button></form></div>@include('public.business.admin.retail._table')
</main></div>
@endsection

@extends('public.business.admin.layout')
@section('content')
<div class="ret-page">
@include('public.business.admin.retail._styles')
@include('public.business.admin.sidebar')
<div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div><main class="workspace-content">
<div class="ret-title"><div><button type="button" class="ret-btn alt ret-mobile-nav" data-ws-sidebar-toggle>☰</button><h1>{{ t('Receive Inventory','পণ্য গ্রহণ') }}</h1><p>{{ t('Business workspace','বিজনেস ওয়ার্কস্পেস') }}</p></div><div class="ret-actions"><a class="ret-btn alt" href="{{ route('business.admin.dashboard') }}">{{ t('Dashboard','ড্যাশবোর্ড') }}</a></div></div>
@if(session('success'))<div class="ret-toast">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ret-error">{{ $errors->first() }}</div>@endif
<form class="ret-card" method="POST" action="{{ route('business.admin.retail.purchases.store') }}">@csrf<div class="ret-grid"><div class="ret-field"><label>{{ t('Supplier','সরবরাহকারী') }}</label><select class="ret-input" name="supplier_id"><option value="">—</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}">{{ $supplier->name }}</option>@endforeach</select></div><div class="ret-field"><label>{{ t('Notes','নোট') }}</label><input class="ret-input" name="notes"></div></div><hr class="ret-divider">@php $purchaseMode=true; @endphp @include('public.business.admin.retail._cart_editor')<hr class="ret-divider"><div class="ret-grid"><div class="ret-field"><label>{{ t('Pay Supplier Now','সরবরাহকারীকে এখন প্রদান') }}</label><input class="ret-input" name="pay_amount" type="number" step=".01" min="0" value="0"></div><div class="ret-field"><label>{{ t('Method','পদ্ধতি') }}</label><select class="ret-input" name="payment_method"><option value="cash">Cash</option><option value="bank">Bank</option></select></div></div><button class="ret-btn" style="margin-top:15px">{{ t('Receive & Post','গ্রহণ ও পোস্ট') }}</button></form>
</main></div>
@endsection

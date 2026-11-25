@extends('public.business.admin.layout')
@section('content')
<div class="ret-page">@include('public.business.admin.retail._styles')
@include('public.business.admin.sidebar')<div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div>
<main class="workspace-content"><div class="ret-title"><div><button type="button" class="ret-btn alt ret-mobile-nav" data-ws-sidebar-toggle>☰</button><h1>{{ t("New Purchase Order","নতুন ক্রয় আদেশ") }}</h1><p>{{ t('Business workspace','বিজনেস ওয়ার্কস্পেস') }}</p></div><div class="ret-actions"><a class="ret-btn alt" href="{{ route('business.admin.dashboard') }}">{{ t('Dashboard','ড্যাশবোর্ড') }}</a></div></div>
@if(session('success'))<div class="ret-toast">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ret-error">{{ $errors->first() }}</div>@endif
<form class="ret-card" method="POST" action="{{ route('business.admin.purchase.purchase-orders.new-po.store') }}">@csrf
<div class="ret-grid"><div class="ret-field"><label>{{ t('Supplier','সরবরাহকারী') }}</label><select required class="ret-input" name="supplier_id"><option value="">—</option>@foreach($suppliers as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select></div><div class="ret-field"><label>{{ t('Notes','নোট') }}</label><input name="notes" class="ret-input"></div></div>
<hr class="ret-divider">@php $purchaseMode=true; @endphp @include('public.business.admin.retail._cart_editor')<button class="ret-btn">{{ t('Save Draft','ড্রাফট সংরক্ষণ') }}</button></form>
</main></div>
@endsection

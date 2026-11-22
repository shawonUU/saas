@extends('public.business.admin.layout')
@section('content')
<div class="ret-page">@include('public.business.admin.retail._styles')
@include('public.business.admin.sidebar')<div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div>
<main class="workspace-content"><div class="ret-title"><div><button type="button" class="ret-btn alt ret-mobile-nav" data-ws-sidebar-toggle>☰</button><h1>{{ t("Abandoned Carts (Opt-in)","অসম্পূর্ণ কার্ট (সম্মতিসহ)") }}</h1><p>{{ t('Business workspace','বিজনেস ওয়ার্কস্পেস') }}</p></div><div class="ret-actions"><a class="ret-btn alt" href="{{ route('business.admin.dashboard') }}">{{ t('Dashboard','ড্যাশবোর্ড') }}</a></div></div>
@if(session('success'))<div class="ret-toast">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ret-error">{{ $errors->first() }}</div>@endif
<div class="ret-card"><p class="ret-help">{{ t('Only contacts who consented to marketing are stored. A completed checkout is removed from this list.','শুধু সম্মতিপ্রাপ্ত কাস্টমারের তথ্য রাখা হয়। অর্ডার সম্পন্ন হলে এই তালিকা থেকে সরে যায়।') }}</p></div>@php $columns=['customer_name'=>'Customer','customer_phone'=>'Phone','subtotal'=>'Subtotal','created_at'=>'Created']; @endphp @include('public.business.admin.retail._table')
</main></div>
@endsection

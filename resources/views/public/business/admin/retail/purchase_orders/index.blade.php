@extends('public.business.admin.layout')
@section('content')
<div class="ret-page">@include('public.business.admin.retail._styles')
@include('public.business.admin.sidebar')<div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div>
<main class="workspace-content"><div class="ret-title"><div><button type="button" class="ret-btn alt ret-mobile-nav" data-ws-sidebar-toggle>☰</button><h1>{{ t("Purchase Orders","ক্রয় আদেশ") }}</h1><p>{{ t('Business workspace','বিজনেস ওয়ার্কস্পেস') }}</p></div><div class="ret-actions"><a class="ret-btn alt" href="{{ route('business.admin.dashboard') }}">{{ t('Dashboard','ড্যাশবোর্ড') }}</a></div></div>
@if(session('success'))<div class="ret-toast">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ret-error">{{ $errors->first() }}</div>@endif
<div class="ret-card"><a class="ret-btn" href="{{ route('business.admin.purchase.purchase-orders.new-po.create') }}">+ {{ t('New Purchase Order','নতুন ক্রয় আদেশ') }}</a></div>@php $columns=['po_no'=>'Order','status'=>'Status','supplier_id'=>'Supplier ID','total'=>'Total','created_at'=>'Created'];$rowActions='public.business.admin.retail._rows.purchase_orders'; @endphp @include('public.business.admin.retail._table')
</main></div>
@endsection

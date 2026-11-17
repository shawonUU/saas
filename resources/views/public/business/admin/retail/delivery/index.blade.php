@extends('public.business.admin.layout')
@section('content')
<div class="ret-page">@include('public.business.admin.retail._styles')
@include('public.business.admin.sidebar')<div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div><main class="workspace-content">
<div class="ret-title"><div><button type="button" class="ret-btn alt ret-mobile-nav" data-ws-sidebar-toggle>☰</button><h1>{{ t('Order Fulfillment','অর্ডার ডেলিভারি') }}</h1><p>{{ t('Business workspace','বিজনেস ওয়ার্কস্পেস') }}</p></div><div class="ret-actions"><a class="ret-btn alt" href="{{ route('store.admin.dashboard') }}">{{ t('Dashboard','ড্যাশবোর্ড') }}</a></div></div>
@if(session('success'))<div class="ret-toast">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ret-error">{{ $errors->first() }}</div>@endif
<div class="ret-card"><a class="ret-btn" href="{{ route('store.admin.retail.courier.index') }}">{{ t('Pathao Courier Bookings','পাঠাও কুরিয়ার বুকিং') }}</a></div>
@php $columns=['order_no'=>'Order','customer_name'=>'Customer','customer_phone'=>'Phone','total'=>'Total','delivery_status'=>'Status','tracking_number'=>'Tracking','created_at'=>'Created'];$rowActions='public.business.admin.retail._rows.delivery'; @endphp @include('public.business.admin.retail._table')
</main></div>
@endsection

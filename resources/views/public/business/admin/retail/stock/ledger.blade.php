@extends('public.business.admin.layout')
@section('content')
<div class="ret-page">
@include('public.business.admin.retail._styles')
@include('public.business.admin.sidebar')
<div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div><main class="workspace-content">
<div class="ret-title"><div><button type="button" class="ret-btn alt ret-mobile-nav" data-ws-sidebar-toggle>☰</button><h1>{{ t('Stock Ledger','স্টক লেজার') }}</h1><p>{{ t('Business workspace','বিজনেস ওয়ার্কস্পেস') }}</p></div><div class="ret-actions"><a class="ret-btn alt" href="{{ route('business.admin.dashboard') }}">{{ t('Dashboard','ড্যাশবোর্ড') }}</a></div></div>
@if(session('success'))<div class="ret-toast">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ret-error">{{ $errors->first() }}</div>@endif
@php $columns=['variant_id'=>'Variant ID','kind'=>'Type','quantity'=>'Quantity','unit_cost'=>'Unit Cost','source_type'=>'Source','created_at'=>'Date']; @endphp
@include('public.business.admin.retail._table')
</main></div>
@endsection

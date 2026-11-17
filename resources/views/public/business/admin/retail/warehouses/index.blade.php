@extends('public.business.admin.layout')
@section('content')
<div class="ret-page">@include('public.business.admin.retail._styles')
@include('public.business.admin.sidebar')<div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div>
<main class="workspace-content"><div class="ret-title"><div><button type="button" class="ret-btn alt ret-mobile-nav" data-ws-sidebar-toggle>☰</button><h1>{{ t("Warehouses","গুদামসমূহ") }}</h1><p>{{ t('Business workspace','বিজনেস ওয়ার্কস্পেস') }}</p></div><div class="ret-actions"><a class="ret-btn alt" href="{{ route('store.admin.dashboard') }}">{{ t('Dashboard','ড্যাশবোর্ড') }}</a></div></div>
@if(session('success'))<div class="ret-toast">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ret-error">{{ $errors->first() }}</div>@endif
<form class="ret-card" method="POST" action="{{ route('store.admin.retail.warehouses.store') }}">@csrf<div class="ret-grid"><div class="ret-field"><label>Code</label><input class="ret-input" name="code" required></div><div class="ret-field"><label>{{ t('Warehouse Name','গুদামের নাম') }}</label><input class="ret-input" name="name" required></div></div><button class="ret-btn" style="margin-top:12px">{{ t('Create Warehouse','গুদাম তৈরি') }}</button></form>@php $columns=['code'=>'Code','name'=>'Name','is_default'=>'Default','is_active'=>'Active']; @endphp @include('public.business.admin.retail._table')
</main></div>
@endsection

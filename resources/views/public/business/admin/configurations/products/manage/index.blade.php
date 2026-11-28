@extends('public.business.admin.layout')
@section('content')
<div class="ret-page listui-page">
@include('public.business.admin.retail._styles')
@include('public.business.admin.sidebar')
<div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div><main class="workspace-content">
<div class="ret-title cfg-heading"><div><button type="button" class="ret-btn alt ret-mobile-nav" data-ws-sidebar-toggle>☰</button><h1>{{ t('Product Management','পণ্য ব্যবস্থাপনা') }}</h1><p>{{ t('Business workspace','বিজনেস ওয়ার্কস্পেস') }}</p></div><div class="ret-actions"><a class="ret-btn alt" href="{{ route('business.admin.dashboard') }}">{{ t('Dashboard','ড্যাশবোর্ড') }}</a></div></div>
@if(session('success'))<div class="ret-toast">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ret-error">{{ $errors->first() }}</div>@endif
@php $columns=['sku'=>'SKU','barcode'=>t('Barcode','বারকোড'),'name'=>'Name','price'=>'Base Price','cost'=>'Cost','is_active'=>'Active','created_at'=>'Created']; @endphp
<div class="ret-toolbar"><a class="ret-btn" href="{{ route('business.admin.configurations.products.create') }}">+ {{ t('Open / New','খুলুন / নতুন') }}</a></div>
@php $rowActions='public.business.admin.retail._rows.products_edit'; @endphp
@include('public.business.admin.retail._table')
</main></div>
@endsection

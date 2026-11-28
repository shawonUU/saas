@extends('public.business.admin.layout')
@section('content')
<div class="ret-page listui-page">
@include('public.business.admin.retail._styles')
@include('public.business.admin.sidebar')
<div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div><main class="workspace-content">
<div class="ret-title cfg-heading"><div><button type="button" class="ret-btn alt ret-mobile-nav" data-ws-sidebar-toggle>☰</button><div class="cfg-eyebrow">{{ t('Inventory','ইনভেন্টরি') }} / {{ t('Stock','স্টক') }}</div><h1>{{ t('Stock Report','স্টক রিপোর্ট') }}</h1><p>{{ t('Business workspace','বিজনেস ওয়ার্কস্পেস') }}</p></div></div>
@if(session('success'))<div class="ret-toast">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ret-error">{{ $errors->first() }}</div>@endif
<div class="ret-card"><a class="ret-btn" href="{{ route('business.admin.inventory.movements.stock-transfer.index') }}">{{ t('Warehouse Transfer','গুদাম স্টক স্থানান্তর') }}</a></div>
@php $columns=['sku'=>'SKU','price'=>'Price','cost'=>'Cost','stock'=>'Stock','created_at'=>'Created']; @endphp
<div class="ret-toolbar"><a class="ret-btn" href="{{ route('business.admin.inventory.movements.stock-adjustment.index') }}">+ {{ t('Open / New','খুলুন / নতুন') }}</a></div>
@include('public.business.admin.retail._table')
</main></div>
@endsection

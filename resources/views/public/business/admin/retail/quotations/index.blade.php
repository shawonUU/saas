@extends('public.business.admin.layout')
@section('content')
<div class="ret-page listui-page">@include('public.business.admin.retail._styles')
@include('public.business.admin.sidebar')<div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div>
<main class="workspace-content"><div class="ret-title cfg-heading"><div><button type="button" class="ret-btn alt ret-mobile-nav" data-ws-sidebar-toggle>☰</button><div class="cfg-eyebrow">{{ t('Sales','বিক্রয়') }} / {{ t('Quotations','কোটেশন') }}</div><h1>{{ t("Quotations","কোটেশন") }}</h1><p>{{ t('Business workspace','বিজনেস ওয়ার্কস্পেস') }}</p></div></div>
@if(session('success'))<div class="ret-toast">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ret-error">{{ $errors->first() }}</div>@endif
<div class="ret-card"><a class="ret-btn" href="{{ route('business.admin.sales.quotations.new-quotation.create') }}">+ {{ t('New Quotation','নতুন কোটেশন') }}</a></div>@php $columns=['quote_no'=>'Reference','customer_name'=>'Customer','total'=>'Total','status'=>'Status','valid_until'=>'Date'];$rowActions='public.business.admin.retail._rows.quotations'; @endphp @include('public.business.admin.retail._table')
</main></div>
@endsection

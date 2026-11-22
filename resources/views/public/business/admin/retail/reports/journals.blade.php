@extends('public.business.admin.layout')
@section('content')
<div class="ret-page">
@include('public.business.admin.retail._styles')
@include('public.business.admin.sidebar')
<div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div><main class="workspace-content">
<div class="ret-title"><div><button type="button" class="ret-btn alt ret-mobile-nav" data-ws-sidebar-toggle>☰</button><h1>{{ t('Journal Entries','জার্নাল এন্ট্রি') }}</h1><p>{{ t('Business workspace','বিজনেস ওয়ার্কস্পেস') }}</p></div><div class="ret-actions"><a class="ret-btn alt" href="{{ route('business.admin.dashboard') }}">{{ t('Dashboard','ড্যাশবোর্ড') }}</a></div></div>
@if(session('success'))<div class="ret-toast">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ret-error">{{ $errors->first() }}</div>@endif
<div class="ret-card"><a class="ret-btn" href="{{ route('business.admin.retail.manual_journals.index') }}">+ {{ t('Manual Journal','ম্যানুয়াল জার্নাল') }}</a></div>
@php $columns=['source_type'=>'Source','source_id'=>'Reference','memo'=>'Description','posted_at'=>'Posted']; @endphp
<div class="ret-toolbar"><a class="ret-btn" href="{{ route('business.admin.retail.reports.trial') }}">+ {{ t('Open / New','খুলুন / নতুন') }}</a></div>
@include('public.business.admin.retail._table')
</main></div>
@endsection

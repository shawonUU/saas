@extends('public.business.admin.layout')
@section('content')
<div class="ret-page">@include('public.business.admin.retail._styles')
@include('public.business.admin.sidebar')<div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div><main class="workspace-content">
<div class="ret-title"><div><button type="button" class="ret-btn alt ret-mobile-nav" data-ws-sidebar-toggle>☰</button><h1>{{ t('Chart of Accounts','হিসাবের খাতসমূহ') }}</h1><p>{{ t('Business workspace','বিজনেস ওয়ার্কস্পেস') }}</p></div><div class="ret-actions"><a class="ret-btn alt" href="{{ route('store.admin.dashboard') }}">{{ t('Dashboard','ড্যাশবোর্ড') }}</a></div></div>
@if(session('success'))<div class="ret-toast">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ret-error">{{ $errors->first() }}</div>@endif
<form class="ret-card" method="POST" action="{{ route('store.admin.retail.accounts.store') }}">@csrf<div class="ret-grid"><div class="ret-field"><label>{{ t('Account Code','হিসাব কোড') }}</label><input class="ret-input" name="code" placeholder="8100" required pattern="[0-9]{4,8}"></div><div class="ret-field"><label>{{ t('Account Name','হিসাবের নাম') }}</label><input class="ret-input" name="name" required></div><div class="ret-field"><label>{{ t('Type','ধরন') }}</label><select class="ret-input" name="type">@foreach(['asset','liability','equity','income','expense'] as $t)<option value="{{ $t }}">{{ ucfirst($t) }}</option>@endforeach</select></div></div><button class="ret-btn" style="margin-top:14px">{{ t('Create Account','হিসাব খাত তৈরি') }}</button></form>
@php $columns=['code'=>'Code','name'=>'Name','type'=>'Type','is_system'=>'System','is_active'=>'Active'];$rowActions='public.business.admin.retail._rows.accounts'; @endphp @include('public.business.admin.retail._table')
</main></div>
@endsection

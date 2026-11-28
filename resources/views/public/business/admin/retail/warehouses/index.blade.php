@extends('public.business.admin.layout')
@section('content')
<div class="ret-page listui-page">@include('public.business.admin.retail._styles')
@include('public.business.admin.shared.list-ui.styles')
@include('public.business.admin.sidebar')<div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div>
<main class="workspace-content"><div class="ret-title cfg-heading"><div><button type="button" class="ret-btn alt ret-mobile-nav" data-ws-sidebar-toggle>☰</button><div class="cfg-eyebrow">{{ t('Inventory','ইনভেন্টরি') }} / {{ t('Warehouses','গুদাম') }}</div><h1>{{ t("Warehouses","গুদামসমূহ") }}</h1><p>{{ t('Business workspace','বিজনেস ওয়ার্কস্পেস') }}</p></div></div>
@if(session('success'))<div class="ret-toast">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ret-error">{{ $errors->first() }}</div>@endif
<div class="listui-add"><button type="button" class="ret-btn" data-listui-open="listui-create">+ {{ t('Add Warehouse','গুদাম যোগ') }}</button></div>
<div class="listui-overlay" id="listui-create" data-listui-modal @if($errors->any()) data-listui-reopen="1" @endif hidden>
 <div class="listui-dialog" role="dialog" aria-modal="true" aria-labelledby="listui-create-title" tabindex="-1">
  <div class="listui-dialog-head"><h2 id="listui-create-title">{{ t('Add Warehouse','গুদাম যোগ') }}</h2><button type="button" class="listui-dialog-close" data-listui-close aria-label="{{ t('Close','বন্ধ') }}">×</button></div>
  <div class="listui-dialog-body"><form class="ret-card" method="POST" action="{{ route('business.admin.settings.locations.warehouses.store') }}">@csrf<div class="ret-grid"><div class="ret-field"><label>Code</label><input class="ret-input" name="code" required></div><div class="ret-field"><label>{{ t('Warehouse Name','গুদামের নাম') }}</label><input class="ret-input" name="name" required></div></div><button class="ret-btn" style="margin-top:12px">{{ t('Create Warehouse','গুদাম তৈরি') }}</button></form></div>
 </div>
</div>@php $columns=['code'=>'Code','name'=>'Name','is_default'=>'Default','is_active'=>'Active']; @endphp @include('public.business.admin.retail._table')
</main></div>
@include('public.business.admin.shared.list-ui.scripts')
@endsection

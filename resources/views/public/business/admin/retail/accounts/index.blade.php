@extends('public.business.admin.layout')
@section('content')
<div class="ret-page listui-page">@include('public.business.admin.retail._styles')
@include('public.business.admin.shared.list-ui.styles')
@include('public.business.admin.sidebar')<div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div><main class="workspace-content">
<div class="ret-title cfg-heading"><div><button type="button" class="ret-btn alt ret-mobile-nav" data-ws-sidebar-toggle>☰</button><div class="cfg-eyebrow">{{ t('Accounts & Finance','হিসাব ও ফাইন্যান্স') }} / {{ t('Accounts','হিসাব') }}</div><h1>{{ t('Chart of Accounts','হিসাবের খাতসমূহ') }}</h1><p>{{ t('Business workspace','বিজনেস ওয়ার্কস্পেস') }}</p></div></div>
@if(session('success'))<div class="ret-toast">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ret-error">{{ $errors->first() }}</div>@endif
<div class="listui-add"><button type="button" class="ret-btn" data-listui-open="listui-create">+ {{ t('Add Account','নতুন হিসাব যোগ') }}</button></div>
<div class="listui-overlay" id="listui-create" data-listui-modal @if($errors->any()) data-listui-reopen="1" @endif hidden>
 <div class="listui-dialog" role="dialog" aria-modal="true" aria-labelledby="listui-create-title" tabindex="-1">
  <div class="listui-dialog-head"><h2 id="listui-create-title">{{ t('Add Account','নতুন হিসাব যোগ') }}</h2><button type="button" class="listui-dialog-close" data-listui-close aria-label="{{ t('Close','বন্ধ') }}">×</button></div>
  <div class="listui-dialog-body"><form class="ret-card" method="POST" action="{{ route('business.admin.finance.chart-of-accounts.account-list.store') }}">@csrf<div class="ret-grid"><div class="ret-field"><label>{{ t('Account Code','হিসাব কোড') }}</label><input class="ret-input" name="code" placeholder="8100" required pattern="[0-9]{4,8}"></div><div class="ret-field"><label>{{ t('Account Name','হিসাবের নাম') }}</label><input class="ret-input" name="name" required></div><div class="ret-field"><label>{{ t('Bangla Name','বাংলা নাম') }}</label><input class="ret-input" name="name_bn"></div><div class="ret-field"><label>{{ t('Type','ধরন') }}</label><select class="ret-input" name="type">@foreach(['asset','liability','equity','income','expense'] as $t)<option value="{{ $t }}">{{ ucfirst($t) }}</option>@endforeach</select></div></div><button class="ret-btn" style="margin-top:14px">{{ t('Create Account','হিসাব খাত তৈরি') }}</button></form></div>
 </div>
</div>
@php $columns=['code'=>'Code','name'=>'Name','type'=>'Type','is_system'=>'System','is_active'=>'Active'];$rowActions='public.business.admin.retail._rows.accounts'; @endphp @include('public.business.admin.retail._table')
</main></div>
@include('public.business.admin.shared.list-ui.scripts')
@endsection

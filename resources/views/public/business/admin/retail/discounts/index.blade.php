@extends('public.business.admin.layout')
@section('content')
<div class="ret-page listui-page">@include('public.business.admin.retail._styles')
@include('public.business.admin.shared.list-ui.styles')
@include('public.business.admin.sidebar')<div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div><main class="workspace-content">
<div class="ret-title cfg-heading"><div><button type="button" class="ret-btn alt ret-mobile-nav" data-ws-sidebar-toggle>☰</button><div class="cfg-eyebrow">{{ t('Sales','বিক্রয়') }} / {{ t('Discounts','ডিসকাউন্ট') }}</div><h1>{{ t('Discount Coupons','ডিসকাউন্ট কুপন') }}</h1><p>{{ t('Business workspace','বিজনেস ওয়ার্কস্পেস') }}</p></div></div>
@if(session('success'))<div class="ret-toast">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ret-error">{{ $errors->first() }}</div>@endif
<div class="listui-add"><button type="button" class="ret-btn" data-listui-open="listui-create">+ {{ t('Add Coupon','কুপন যোগ') }}</button></div>
<div class="listui-overlay" id="listui-create" data-listui-modal @if($errors->any()) data-listui-reopen="1" @endif hidden>
 <div class="listui-dialog" role="dialog" aria-modal="true" aria-labelledby="listui-create-title" tabindex="-1">
  <div class="listui-dialog-head"><h2 id="listui-create-title">{{ t('Add Coupon','কুপন যোগ') }}</h2><button type="button" class="listui-dialog-close" data-listui-close aria-label="{{ t('Close','বন্ধ') }}">×</button></div>
  <div class="listui-dialog-body"><form class="ret-card" method="POST" action="{{ route('business.admin.sales.discounts.coupons.store') }}">@csrf<div class="ret-grid"><div class="ret-field"><label>{{ t('Coupon Code','কুপন কোড') }}</label><input class="ret-input" name="code" required></div><div class="ret-field"><label>{{ t('Discount Type','ছাড়ের ধরন') }}</label><select name="type" class="ret-input"><option value="fixed">Fixed Amount</option><option value="percent">Percentage</option></select></div><div class="ret-field"><label>{{ t('Value','পরিমাণ') }}</label><input class="ret-input" type="number" name="value" min=".01" step=".01" required></div><div class="ret-field"><label>{{ t('Starts At','শুরুর সময়') }}</label><input class="ret-input" type="datetime-local" name="starts_at"></div><div class="ret-field"><label>{{ t('Ends At','শেষের সময়') }}</label><input class="ret-input" type="datetime-local" name="ends_at"></div><div class="ret-field"><label>{{ t('Status','অবস্থা') }}</label><select class="ret-input" name="is_active"><option value="1">Active</option><option value="0">Inactive</option></select></div></div><button class="ret-btn" style="margin-top:14px">{{ t('Save Coupon','কুপন সংরক্ষণ') }}</button></form></div>
 </div>
</div>
@php $columns=['code'=>'Code','type'=>'Type','value'=>'Value','is_active'=>'Status','created_at'=>'Created'];$rowActions='public.business.admin.retail._rows.discounts'; @endphp @include('public.business.admin.retail._table')
</main></div>
@include('public.business.admin.shared.list-ui.scripts')
@endsection

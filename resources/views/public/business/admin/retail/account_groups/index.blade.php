@extends('public.business.admin.layout')
@section('content')
<div class="ret-page listui-page">@include('public.business.admin.retail._styles')
@include('public.business.admin.sidebar')<div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div>
<main class="workspace-content"><div class="ret-title cfg-heading"><div><button type="button" class="ret-btn alt ret-mobile-nav" data-ws-sidebar-toggle>☰</button><div class="cfg-eyebrow">{{ t('Accounts & Finance','হিসাব ও ফাইন্যান্স') }} / {{ t('Account Groups','হিসাব গ্রুপ') }}</div><h1>{{ t("Chart of Accounts — Groups","হিসাবের গ্রুপ") }}</h1><p>{{ t('Business workspace','বিজনেস ওয়ার্কস্পেস') }}</p></div></div>
@if(session('success'))<div class="ret-toast">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ret-error">{{ $errors->first() }}</div>@endif
<div class="cfg-panel ret-card listui-list-card"><a class="ret-btn alt" href="{{ route('business.admin.finance.chart-of-accounts.account-list.index') }}">{{ t('Chart of Accounts','হিসাবের খাতসমূহ') }}</a>@include('public.business.admin.shared.list-ui.standalone-toolbar', ['hasFilters'=>true])
<div class="ret-table-wrap cfg-table-scroll listui-table-scroll"><table class="cfg-table ret-table"><thead><tr><th>{{ t('Group','গ্রুপ') }}</th><th>{{ t('Accounts','হিসাব') }}</th><th>{{ t('Debit','ডেবিট') }}</th><th>{{ t('Credit','ক্রেডিট') }}</th></tr><tr class="cfg-filter-row listui-filter-row" id="listui-colfilters"><th><input class="ret-input" name="f[type]" form="listui-standard-form" value="{{ request('f.type') }}" placeholder="{{ t('Filter','ফিল্টার') }}"></th><th></th><th></th><th><button class="cfg-btn cfg-primary" type="submit" form="listui-standard-form">{{ t('Go','যান') }}</button></th></tr></thead><tbody>@foreach($groups as $g)<tr><td>{{ ucfirst($g->type) }}</td><td>{{ $g->accounts }}</td><td>{{ number_format($g->debit,2) }}</td><td>{{ number_format($g->credit,2) }}</td></tr>@endforeach</tbody></table></div>@include('public.business.admin.shared.list-ui.pagination',['paginator'=>$groups])</div>
</main></div>
@include('public.business.admin.shared.list-ui.styles')
@include('public.business.admin.shared.list-ui.scripts')
@endsection

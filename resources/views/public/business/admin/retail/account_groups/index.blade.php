@extends('public.business.admin.layout')
@section('content')
<div class="ret-page">@include('public.business.admin.retail._styles')
@include('public.business.admin.sidebar')<div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div>
<main class="workspace-content"><div class="ret-title"><div><button type="button" class="ret-btn alt ret-mobile-nav" data-ws-sidebar-toggle>☰</button><h1>{{ t("Chart of Accounts — Groups","হিসাবের গ্রুপ") }}</h1><p>{{ t('Business workspace','বিজনেস ওয়ার্কস্পেস') }}</p></div><div class="ret-actions"><a class="ret-btn alt" href="{{ route('business.admin.dashboard') }}">{{ t('Dashboard','ড্যাশবোর্ড') }}</a></div></div>
@if(session('success'))<div class="ret-toast">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ret-error">{{ $errors->first() }}</div>@endif
<div class="ret-card"><a class="ret-btn alt" href="{{ route('business.admin.retail.accounts.index') }}">{{ t('Chart of Accounts','হিসাবের খাতসমূহ') }}</a><div class="ret-table-wrap"><table class="ret-table"><thead><tr><th>{{ t('Group','গ্রুপ') }}</th><th>{{ t('Accounts','হিসাব') }}</th><th>{{ t('Debit','ডেবিট') }}</th><th>{{ t('Credit','ক্রেডিট') }}</th></tr></thead><tbody>@foreach($groups as $g)<tr><td>{{ ucfirst($g->type) }}</td><td>{{ $g->accounts }}</td><td>{{ number_format($g->debit,2) }}</td><td>{{ number_format($g->credit,2) }}</td></tr>@endforeach</tbody></table></div></div>
</main></div>
@endsection

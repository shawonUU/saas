@extends('public.business.admin.layout')
@section('content')
<div class="ret-page">@include('public.business.admin.retail._styles')
@include('public.business.admin.sidebar')<div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div><main class="workspace-content">
<div class="ret-title"><div><button type="button" class="ret-btn alt ret-mobile-nav" data-ws-sidebar-toggle>☰</button><h1>{{ $title }}</h1><p>{{ t('Business workspace','বিজনেস ওয়ার্কস্পেস') }}</p></div><div class="ret-actions"><a class="ret-btn alt" href="{{ route('store.admin.dashboard') }}">{{ t('Dashboard','ড্যাশবোর্ড') }}</a></div></div>
@if(session('success'))<div class="ret-toast">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ret-error">{{ $errors->first() }}</div>@endif
@php $grossDebit=$accounts->sum('debit');$grossCredit=$accounts->sum('credit');$income=$accounts->where('type','income')->sum(fn($a)=>$a->credit-$a->debit);$expense=$accounts->where('type','expense')->sum(fn($a)=>$a->debit-$a->credit);$profit=$income-$expense;@endphp
<div class="ret-kpis"><div class="ret-kpi">{{ t('Total Debit','মোট ডেবিট') }}<strong>{{ number_format($grossDebit,2) }}</strong></div><div class="ret-kpi">{{ t('Total Credit','মোট ক্রেডিট') }}<strong>{{ number_format($grossCredit,2) }}</strong></div><div class="ret-kpi">{{ t('Revenue','আয়') }}<strong>{{ number_format($income,2) }}</strong></div><div class="ret-kpi">{{ t('Net Profit','নিট লাভ') }}<strong>{{ number_format($profit,2) }}</strong></div></div>
<div class="ret-card" style="margin-top:15px"><div class="ret-table-wrap"><table class="ret-table"><thead><tr><th>{{ t('Code','কোড') }}</th><th>{{ t('Account','হিসাব') }}</th><th>{{ t('Type','ধরন') }}</th><th>{{ t('Debit','ডেবিট') }}</th><th>{{ t('Credit','ক্রেডিট') }}</th><th>{{ t('Balance','ব্যালেন্স') }}</th></tr></thead><tbody>
@foreach($accounts as $acc)
@if($mode==='trial'||($mode==='profit'&&in_array($acc->type,['income','expense']))||($mode==='sheet'&&in_array($acc->type,['asset','liability','equity'])))
<tr><td>{{ $acc->code }}</td><td>{{ $acc->name }}</td><td>{{ ucfirst($acc->type) }}</td><td>{{ number_format($acc->debit,2) }}</td><td>{{ number_format($acc->credit,2) }}</td><td>{{ number_format(in_array($acc->type,['asset','expense'])?$acc->debit-$acc->credit:$acc->credit-$acc->debit,2) }}</td></tr>@endif
@endforeach</tbody></table></div>@if($mode==='sheet')<p class="ret-help">{{ t('Net profit is retained earnings until closing entries are posted.','ক্লোজিং এন্ট্রির আগে নিট মুনাফা রিটেইন্ড আর্নিংস হিসেবে গণ্য হয়।') }}: {{ number_format($profit,2) }}</p>@endif</div>
</main></div>
@endsection

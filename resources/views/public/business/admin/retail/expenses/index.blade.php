@extends('public.business.admin.layout')
@section('content')
<div class="ret-page listui-page">@include('public.business.admin.retail._styles')
@include('public.business.admin.shared.list-ui.styles')
@include('public.business.admin.sidebar')<div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div>
<main class="workspace-content"><div class="ret-title cfg-heading"><div><button type="button" class="ret-btn alt ret-mobile-nav" data-ws-sidebar-toggle>☰</button><div class="cfg-eyebrow">{{ t('Accounts & Finance','হিসাব ও ফাইন্যান্স') }} / {{ t('Expenses','খরচ') }}</div><h1>{{ t("Business Expenses","ব্যবসার খরচ") }}</h1><p>{{ t('Business workspace','বিজনেস ওয়ার্কস্পেস') }}</p></div></div>
@if(session('success'))<div class="ret-toast">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ret-error">{{ $errors->first() }}</div>@endif
<div class="listui-add"><button type="button" class="ret-btn" data-listui-open="listui-create">+ {{ t('New Expense','নতুন খরচ') }}</button></div>
<div class="listui-overlay" id="listui-create" data-listui-modal @if($errors->any()) data-listui-reopen="1" @endif hidden>
 <div class="listui-dialog" role="dialog" aria-modal="true" aria-labelledby="listui-create-title" tabindex="-1">
  <div class="listui-dialog-head"><h2 id="listui-create-title">{{ t('New Expense','নতুন খরচ') }}</h2><button type="button" class="listui-dialog-close" data-listui-close aria-label="{{ t('Close','বন্ধ') }}">×</button></div>
  <div class="listui-dialog-body"><form class="ret-card" method="POST" action="{{ route('business.admin.finance.expenses.new-expense.store') }}">@csrf<div class="ret-grid"><div class="ret-field"><label>{{ t('Description','বিবরণ') }}</label><input class="ret-input" name="description" required></div><div class="ret-field"><label>{{ t('Amount','পরিমাণ') }}</label><input class="ret-input" name="amount" type="number" min=".01" step=".01" required></div><div class="ret-field"><label>{{ t('Date','তারিখ') }}</label><input class="ret-input" type="date" name="expense_date" value="{{ date('Y-m-d') }}" required></div><div class="ret-field"><label>{{ t('Paid Through','পেমেন্ট মাধ্যম') }}</label><select class="ret-input" name="method"><option value="cash">Cash</option><option value="bank">Bank</option></select></div></div><button class="ret-btn" style="margin-top:12px">{{ t('Post Expense','খরচ পোস্ট') }}</button></form></div>
 </div>
</div>@php $columns=['description'=>'Description','amount'=>'Amount','method'=>'Method','expense_date'=>'Date']; @endphp @include('public.business.admin.retail._table')
</main></div>
@include('public.business.admin.shared.list-ui.scripts')
@endsection

@extends('public.business.admin.layout')
@section('content')
<div class="ret-page">@include('public.business.admin.retail._styles')
@include('public.business.admin.sidebar')<div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div><main class="workspace-content">
<div class="ret-title"><div><button type="button" class="ret-btn alt ret-mobile-nav" data-ws-sidebar-toggle>☰</button><h1>{{ t('Cash Flow','নগদ প্রবাহ') }}</h1></div><div class="ret-actions"><a class="ret-btn alt" href="{{ route('business.admin.dashboard') }}">{{ t('Dashboard','ড্যাশবোর্ড') }}</a></div></div>
<p class="ret-help">{{ t('Cash and bank receipts/payments from posted journal entries.','পোস্ট করা জার্নাল থেকে নগদ ও ব্যাংক লেনদেন।') }}</p>@php $columns=['journal_id'=>'Journal','account_id'=>'Account ID','debit'=>'Cash In','credit'=>'Cash Out','created_at'=>'Date']; @endphp @include('public.business.admin.retail._table')
</main></div>@endsection
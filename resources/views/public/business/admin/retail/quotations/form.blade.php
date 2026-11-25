@extends('public.business.admin.layout')
@section('content')
<div class="ret-page">@include('public.business.admin.retail._styles')
@include('public.business.admin.sidebar')<div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div>
<main class="workspace-content"><div class="ret-title"><div><button type="button" class="ret-btn alt ret-mobile-nav" data-ws-sidebar-toggle>☰</button><h1>{{ t("New Quotation","নতুন কোটেশন") }}</h1><p>{{ t('Business workspace','বিজনেস ওয়ার্কস্পেস') }}</p></div><div class="ret-actions"><a class="ret-btn alt" href="{{ route('business.admin.dashboard') }}">{{ t('Dashboard','ড্যাশবোর্ড') }}</a></div></div>
@if(session('success'))<div class="ret-toast">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ret-error">{{ $errors->first() }}</div>@endif
<form class="ret-card" method="POST" action="{{ route('business.admin.sales.quotations.new-quotation.store') }}">@csrf<div class="ret-grid"><div class="ret-field"><label>{{ t('Customer Name','কাস্টমারের নাম') }}</label><input class="ret-input" name="customer_name" required></div><div class="ret-field"><label>{{ t('Phone','ফোন') }}</label><input class="ret-input" name="customer_phone"></div><div class="ret-field"><label>{{ t('Valid Until','মেয়াদ') }}</label><input class="ret-input" type="date" name="valid_until"></div></div><hr class="ret-divider">@include('public.business.admin.retail._cart_editor')<button class="ret-btn">{{ t('Save Quotation','কোটেশন সংরক্ষণ') }}</button><p class="ret-help">{{ t('Quotation does not move stock or post accounts until converted.','বিক্রয়ে রূপান্তরের আগে স্টক বা হিসাব পরিবর্তন হবে না।') }}</p></form>
</main></div>
@endsection

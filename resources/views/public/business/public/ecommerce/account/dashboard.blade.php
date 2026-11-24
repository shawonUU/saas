@extends('public.business.public.ecommerce.account.shell')
@section('title',t('My account','আমার অ্যাকাউন্ট'))
@section('account_heading',t('Welcome back','স্বাগতম'))
@section('account_content')
<div class="ec-stats">@foreach(['orders'=>['Total orders','মোট অর্ডার'],'processing'=>['In progress','চলমান'],'delivered'=>['Delivered','ডেলিভারি হয়েছে']] as $key=>$label)<div class="ec-card ec-stat"><small>{{ t(...$label) }}</small><strong>{{ $stats[$key] }}</strong></div>@endforeach</div>
<div class="ec-card"><div class="ec-row"><h2>{{ t('Recent orders','সাম্প্রতিক অর্ডার') }}</h2><a href="{{ route('business.customer.orders') }}">{{ t('View all','সব দেখুন') }} →</a></div>@include('public.business.public.ecommerce.account.order-list')</div>
<div class="ec-card"><div class="ec-row"><div><h2>{{ t('Address book','ঠিকানা তালিকা') }}</h2><p>{{ t('Manage saved delivery addresses','সংরক্ষিত ডেলিভারি ঠিকানা পরিচালনা করুন') }}</p></div><a class="ec-btn" href="{{ route('business.customer.addresses') }}">{{ t('Manage addresses','ঠিকানা পরিচালনা') }}</a></div></div>
<div class="ec-card"><h2>{{ t('Your profile','আপনার প্রোফাইল') }}</h2><div class="ec-row"><div><strong>{{ $customer->name }}</strong><p>{{ $customer->email }}</p></div><a class="ec-btn" href="{{ route('business.customer.profile') }}">{{ t('Edit profile','প্রোফাইল সম্পাদনা') }}</a></div></div>
<div class="ec-card"><h2>{{ t('Purchase overview','কেনাকাটার সারসংক্ষেপ') }}</h2><div class="ec-row"><span>{{ t('Delivered order value','ডেলিভারি হওয়া অর্ডারের মূল্য') }}</span><strong>৳{{ number_format($purchaseStats['delivered_total'],2) }}</strong></div><div class="ec-row"><span>{{ t('Recorded return credit','রেকর্ডকৃত রিটার্ন ক্রেডিট') }}</span><strong>৳{{ number_format($purchaseStats['return_credit'],2) }}</strong></div></div>
@endsection

@extends('layouts.portal')
@section('content')
<div class="card">
    <h1 class="title">{{ t('My SaaS Dashboard', 'আমার SaaS ড্যাশবোর্ড') }}</h1>
    <p class="sub">{{ t('Hello', 'স্বাগতম') }}, {{ $user->name }} · {{ $user->email }}</p>
    <div class="actions"><a href="{{ route('saas.shops.create') }}" class="btn">{{ t('Create new shop', 'নতুন শপ তৈরি করুন') }}</a><form method="post" action="{{ route('saas.logout') }}">@csrf<button type="submit" class="btn btn-outline">{{ t('Log out', 'লগআউট') }}</button></form></div>
    @if(session('shop_created'))
        <div class="alert success">
            <strong>{{ t('Shop created successfully', 'শপ সফলভাবে তৈরি হয়েছে') }}</strong>
            <p class="small">{{ t('Your storefront and admin portal use the same wildcard DNS setup. No individual DNS entry is needed for each new shop.', 'স্টোরফ্রন্ট ও অ্যাডমিন পোর্টাল একই ওয়াইল্ডকার্ড DNS ব্যবহার করে। প্রতিটি নতুন শপের জন্য আলাদা DNS এন্ট্রি লাগবে না।') }}</p>
            <div class="actions">
                <a class="btn" href="{{ route('store.customer.home', ['shop' => session('shop_created')]) }}" target="_blank" rel="noopener">{{ t('Open storefront', 'স্টোরফ্রন্ট খুলুন') }}</a>
                <a class="btn btn-outline" href="{{ route('store.admin.login', ['shop' => session('shop_created')]) }}" target="_blank" rel="noopener">{{ t('Open admin login', 'অ্যাডমিন লগইন খুলুন') }}</a>
            </div>
        </div>
    @endif
    <h2 style="margin:35px 0 12px">{{ t('My shops', 'আমার শপগুলো') }}</h2>
    @forelse($shops as $shop)
        <div class="shop"><h3>{{ $shop->name }}</h3>
            <div class="small">{{ $shop->code }} · {{ $shop->approval_status }}</div>
            <div class="actions">
                <a class="btn btn-outline" target="_blank" rel="noopener" href="{{ route('store.admin.login', ['shop' => $shop->code]) }}">{{ t('Shop admin login', 'শপ অ্যাডমিন লগইন') }}</a>
                <a class="btn btn-outline" target="_blank" rel="noopener" href="{{ route('store.customer.home', ['shop' => $shop->code]) }}">{{ t('Customer storefront', 'কাস্টমার স্টোর') }}</a>
            </div>
        </div>
    @empty<p class="small">{{ t('No shops yet. Create your first shop.', 'এখনো কোনো শপ নেই। প্রথম শপ তৈরি করুন।') }}</p>
    @endforelse
</div>
@endsection

@extends('public.layout')
@section('content')
<div class="card">
    <h1 class="title">{{ t('My SaaS Dashboard', 'আমার SaaS ড্যাশবোর্ড') }}</h1>
    <p class="sub">{{ t('Hello', 'স্বাগতম') }}, {{ $user->name }} · {{ $user->email }}</p>
    <div class="actions"><a href="{{ route('saas.businesss.create') }}" class="btn">{{ t('Create new business', 'নতুন শপ তৈরি করুন') }}</a><form method="post" action="{{ route('saas.logout') }}">@csrf<button type="submit" class="btn btn-outline">{{ t('Log out', 'লগআউট') }}</button></form></div>
    @if(session('business_created'))
        <div class="alert success">
            <strong>{{ t('Business created successfully', 'শপ সফলভাবে তৈরি হয়েছে') }}</strong>
            <p class="small">{{ t('Your storefront and admin portal use the same wildcard DNS setup. No individual DNS entry is needed for each new business.', 'স্টোরফ্রন্ট ও অ্যাডমিন পোর্টাল একই ওয়াইল্ডকার্ড DNS ব্যবহার করে। প্রতিটি নতুন শপের জন্য আলাদা DNS এন্ট্রি লাগবে না।') }}</p>
            <div class="actions">
                <a class="btn" href="{{ route('store.customer.home', ['business' => session('business_created')]) }}" target="_blank" rel="noopener">{{ t('Open storefront', 'স্টোরফ্রন্ট খুলুন') }}</a>
                <a class="btn btn-outline" href="{{ route('store.admin.login', ['business' => session('business_created')]) }}" target="_blank" rel="noopener">{{ t('Open admin login', 'অ্যাডমিন লগইন খুলুন') }}</a>
            </div>
        </div>
    @endif
    <h2 style="margin:35px 0 12px">{{ t('My businesss', 'আমার শপগুলো') }}</h2>
    @forelse($businesss as $business)
        <div class="business"><h3>{{ $business->name }}</h3>
            <div class="small">{{ $business->domain }} · {{ $business->approval_status }}</div>
            <div class="actions">
                <a class="btn btn-outline" target="_blank" rel="noopener" href="{{ route('store.admin.login', ['business' => $business->domain]) }}">{{ t('Business admin login', 'শপ অ্যাডমিন লগইন') }}</a>
                <a class="btn btn-outline" target="_blank" rel="noopener" href="{{ route('store.customer.home', ['business' => $business->domain]) }}">{{ t('Customer storefront', 'কাস্টমার স্টোর') }}</a>
            </div>
        </div>
    @empty<p class="small">{{ t('No businesss yet. Create your first business.', 'এখনো কোনো শপ নেই। প্রথম শপ তৈরি করুন।') }}</p>
    @endforelse
</div>
@endsection

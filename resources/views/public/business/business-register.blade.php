@extends('public.layout')
@section('content')
<div class="card auth" style="max-width:560px">
    <h1 class="title">{{ t('Create business', 'শপ তৈরি করুন') }}</h1>
    <p class="sub">{{ t('This will also create your business owner login.', 'এর সঙ্গে আপনার শপ ওনারের লগইনও তৈরি হবে।') }}</p>
    <form method="post" action="{{ route('saas.businesss.store') }}">
        @csrf
        <label for="name">{{ t('Business name', 'শপের নাম') }}</label><input id="name" name="name" value="{{ old('name') }}" required>
        <label for="domain">{{ t('Subdomain (lowercase, no spaces)', 'সাবডোমেইন (ছোট হাতের অক্ষর, স্পেস ছাড়া)') }}</label><input id="domain" name="domain" value="{{ old('domain') }}" pattern="[a-z][a-z0-9-]{2,39}" placeholder="rohimstore" required>
        <p class="small">{{ t('Automatically available after setup', 'একবার DNS সেটআপ করলে স্বয়ংক্রিয়ভাবে চালু হবে') }}: rohimstore.{{ config('saas.domain') }} · admin.rohimstore.{{ config('saas.domain') }}</p>
        <p class="small">{{ t('Reserved: admin, authority, www, api, mail, support. admin.saas.test is reserved for SaaS Authority only.', 'সংরক্ষিত: admin, authority, www, api, mail, support। admin.saas.test শুধু SaaS Authority-এর জন্য সংরক্ষিত।') }}</p>
        <label for="category_id">{{ t('Business category', 'শপের ক্যাটাগরি') }}</label>
        <select id="category_id" name="category_id" required><option value="">{{ t('Select category', 'ক্যাটাগরি বাছুন') }}</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ navName($category) }}</option>@endforeach</select>
        <label for="admin_password">{{ t('Separate admin password (optional)', 'আলাদা অ্যাডমিন পাসওয়ার্ড (ঐচ্ছিক)') }}</label><input id="admin_password" name="admin_password" type="password" minlength="8" autocomplete="new-password">
        <label for="admin_password_confirmation">{{ t('Confirm admin password', 'অ্যাডমিন পাসওয়ার্ড নিশ্চিত করুন') }}</label><input id="admin_password_confirmation" name="admin_password_confirmation" type="password" minlength="8" autocomplete="new-password">
        <p class="small">{{ t('Leave blank to initially use your SaaS account password. Logins remain separate.', 'খালি রাখলে শুরুতে SaaS অ্যাকাউন্টের পাসওয়ার্ড ব্যবহার হবে। লগইন আলাদাই থাকবে।') }}</p>
        <button type="submit" class="btn wide">{{ t('Create business', 'শপ তৈরি করুন') }}</button>
    </form>
    <p class="hint"><a href="{{ route('saas.dashboard') }}">{{ t('Back to dashboard', 'ড্যাশবোর্ডে ফিরুন') }}</a></p>
</div>
@endsection

@extends('public.business.admin.layout')
@push('styles')
@include('public.business.admin.configurations._workspace_styles')
@include('public.business.admin.settings.partials.form-styles')
@endpush
@section('content')
<div class="workspace-layout gs-page">
    @include('public.business.admin.sidebar')
    <div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div>
    <main class="workspace-content">
        <div class="gs-header"><div>
            <h1>{{ t('Business General Settings','বিজনেস সাধারণ সেটিংস') }}</h1>
            <p>{{ t('Manage company information. Storefront appearance is now under E-commerce Settings.','কোম্পানির তথ্য পরিচালনা করুন। স্টোরফ্রন্টের ডিজাইন এখন ই-কমার্স সেটিংসে।') }}</p>
        </div></div>
        @if($errors->any())<div class="gs-error" role="alert">{{ $errors->first() }}</div>@endif
        @if(session('success'))<div class="gs-success" role="status">{{ session('success') }}</div>@endif
        <form method="POST" action="{{ route('business.admin.settings.business.general-settings.update') }}">
            @csrf @method('PUT')
            <div class="gs-form gs-form--single">
                <section class="gs-panel">
                    <h2>{{ t('Company Information','কোম্পানির তথ্য') }}</h2>
                    <div class="gs-grid">
                        <label class="gs-field">{{ t('Business Name','বিজনেসের নাম') }}<input name="name" required maxlength="255" value="{{ old('name',$business->name) }}"></label>
                        <label class="gs-field">{{ t('Phone','ফোন') }}<input name="phone" maxlength="50" value="{{ old('phone',$business->phone) }}"></label>
                        <label class="gs-field">{{ t('Email','ইমেইল') }}<input type="email" name="email" maxlength="255" value="{{ old('email',$business->email) }}"></label>
                        <label class="gs-field">{{ t('Address','ঠিকানা') }}<input name="address" maxlength="255" value="{{ old('address',$business->address) }}"></label>
                    </div>
                    <label class="gs-field">{{ t('About your business','বিজনেস সম্পর্কে') }}<textarea name="about" rows="5" maxlength="3000">{{ old('about',$branding?->about) }}</textarea></label>
                </section>
            </div>
            <div class="gs-actions"><button class="gs-save" type="submit">{{ t('Save Settings','সেটিংস সংরক্ষণ') }}</button></div>
        </form>
    </main>
</div>
@endsection

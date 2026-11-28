@extends('public.business.admin.layout')
@push('styles')
@include('public.business.admin.configurations._workspace_styles')
@include('public.business.admin.settings.partials.form-styles')
@endpush
@section('content')
@php($brandAssets = app(\App\Services\Public\Business\Admin\Settings\Business\GeneralSettings\BrandingService::class))
<div class="workspace-layout gs-page">
    @include('public.business.admin.sidebar')
    <div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div>
    <main class="workspace-content">
        <div class="gs-header"><div>
            <h1>{{ t('E-commerce General Settings','ই-কমার্স সাধারণ সেটিংস') }}</h1>
            <p>{{ t('Set the storefront appearance, tagline, logo and homepage banners.','স্টোরফ্রন্টের রঙ, স্লোগান, লোগো এবং হোমপেজ ব্যানার পরিবর্তন করুন।') }}</p>
        </div></div>
        @if($errors->any())<div class="gs-error" role="alert">{{ $errors->first() }}</div>@endif
        @if(session('success'))<div class="gs-success" role="status">{{ session('success') }}</div>@endif
        <form method="POST" enctype="multipart/form-data" action="{{ route('business.admin.settings.ecommerce.general-settings.update') }}">
            @csrf @method('PUT')
            <div class="gs-form">
                <section class="gs-panel">
                    <h2>{{ t('Storefront Appearance','স্টোরফ্রন্টের ডিজাইন') }}</h2>
                    <label class="gs-field">{{ t('Theme Color','থিমের রঙ') }}
                        <input type="color" name="primary_color" value="{{ old('primary_color',$branding?->primary_color ?? '#5145e5') }}" required>
                    </label>
                    <label class="gs-field">{{ t('Storefront Tagline','স্টোরের স্লোগান') }}
                        <input name="tagline" maxlength="255" value="{{ old('tagline',$branding?->tagline) }}" placeholder="{{ t('A short message for your storefront','আপনার স্টোরের সংক্ষিপ্ত স্লোগান') }}">
                    </label>
                    <p class="gs-note">{{ t('The existing store, logo and banner data will be retained unless you upload new files.','নতুন ছবি আপলোড না করলে বর্তমান লোগো ও ব্যানার অপরিবর্তিত থাকবে।') }}</p>
                </section>
                <section class="gs-panel">
                    <h2>{{ t('Brand Assets','ব্র্যান্ডের ছবি') }}</h2>
                    <label class="gs-field">{{ t('Company Logo','কোম্পানির লোগো') }}
                        @if($business->logo)<img class="gs-preview" alt="{{ t('Existing logo','বর্তমান লোগো') }}" src="{{ $brandAssets->imageUrl($business->logo) }}">@endif
                        <input type="file" name="logo" accept="image/jpeg,image/png,image/webp">
                        <small class="gs-note">PNG, JPG, WebP · 3 MB</small>
                    </label>
                    <label class="gs-field">{{ t('Favicon','ফেভিকন') }}
                        @if($branding?->favicon)<img class="gs-preview favicon" alt="{{ t('Existing favicon','বর্তমান ফেভিকন') }}" src="{{ $brandAssets->imageUrl($branding->favicon) }}">@endif
                        <input type="file" name="favicon" accept="image/jpeg,image/png,image/webp,image/x-icon">
                        <small class="gs-note">1 MB</small>
                    </label>
                    <label class="gs-field">{{ t('Homepage Banner','হোমপেজ ব্যানার') }}
                        @if($branding?->banner)<img class="gs-preview banner" alt="{{ t('Existing homepage banner','বর্তমান হোমপেজ ব্যানার') }}" src="{{ $brandAssets->imageUrl($branding->banner) }}">@endif
                        <input type="file" name="banner" accept="image/jpeg,image/png,image/webp">
                        <small class="gs-note">5 MB · {{ t('Recommended 1200 × 330 px','প্রস্তাবিত ১২০০ × ৩৩০ পিক্সেল') }}</small>
                    </label>
                    <label class="gs-field">{{ t('Secondary Homepage Banner','হোমপেজের দ্বিতীয় ব্যানার') }}
                        @if($branding?->secondary_banner)<img class="gs-preview banner" alt="{{ t('Existing secondary banner','বর্তমান দ্বিতীয় ব্যানার') }}" src="{{ $brandAssets->imageUrl($branding->secondary_banner) }}">@endif
                        <input type="file" name="secondary_banner" accept="image/jpeg,image/png,image/webp">
                        <small class="gs-note">5 MB · {{ t('Recommended 600 × 330 px','প্রস্তাবিত ৬০০ × ৩৩০ পিক্সেল') }}</small>
                    </label>
                </section>
            </div>
            <div class="gs-actions"><button class="gs-save" type="submit">{{ t('Save E-commerce Settings','ই-কমার্স সেটিংস সংরক্ষণ') }}</button></div>
        </form>
    </main>
</div>
@endsection

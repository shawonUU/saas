@extends('public.business.public.ecommerce.layout')

@section('title', t('Create Account', 'অ্যাকাউন্ট তৈরি করুন'))

@push('styles')
<style>
.sf-register-page{
    --reg-bg:#fff;
    --reg-text:#182230;
    --reg-muted:#667085;
    --reg-border:#e4e7ec;
    --reg-input:#f9fafb;
    --reg-primary:#171717;
    --reg-primary-text:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    width:100%;
    min-height:650px;
    padding:40px 16px;
}
.sf-register-page *{box-sizing:border-box}
.sf-register-wrapper{
    display:grid;
    grid-template-columns:minmax(0,1fr) minmax(0,1.1fr);
    width:100%;
    max-width:980px;
    background:var(--reg-bg);
    border:1px solid var(--reg-border);
    border-radius:24px;
    overflow:hidden;
    box-shadow:0 16px 60px rgba(16,24,40,.07);
}
.sf-register-banner{
    position:relative;
    isolation:isolate;
    overflow:hidden;
    display:flex;
    flex-direction:column;
    justify-content:space-between;
    padding:40px;
    background:linear-gradient(145deg,#101828,#24344a);
    color:#fff;
}
.sf-register-banner:before{
    content:"";
    position:absolute;
    width:380px;
    height:380px;
    border:1px solid rgba(255,255,255,.1);
    border-radius:50%;
    right:-170px;
    top:-100px;
    z-index:-1;
}
.sf-register-banner:after{
    content:"";
    position:absolute;
    width:360px;
    height:360px;
    border-radius:50%;
    background:rgba(255,255,255,.04);
    bottom:-180px;
    left:-100px;
    z-index:-1;
}
.sf-register-brand{
    display:flex;
    align-items:center;
    gap:12px;
    font-size:19px;
    font-weight:700;
    color:#fff;
}
.sf-register-logo{
    display:flex;
    align-items:center;
    justify-content:center;
    width:46px;
    height:46px;
    flex-shrink:0;
    padding:7px;
    border-radius:12px;
    background:#fff;
    color:#101828;
    font-size:21px;
    font-weight:800;
    overflow:hidden;
}
.sf-register-logo img{
    width:100%;
    height:100%;
    object-fit:contain;
}
.sf-register-badge{
    display:inline-flex;
    align-items:center;
    gap:8px;
    padding:7px 12px;
    margin-bottom:22px;
    border:1px solid rgba(255,255,255,.15);
    border-radius:30px;
    background:rgba(255,255,255,.07);
    font-size:10px;
    font-weight:650;
    letter-spacing:.6px;
}
.sf-register-badge span{
    width:7px;
    height:7px;
    background:#a3e635;
    border-radius:50%;
}
.sf-register-banner h2{
    margin:0 0 16px;
    color:#fff;
    font-size:clamp(27px,3vw,38px);
    font-weight:750;
    line-height:1.25;
    letter-spacing:-1.2px;
}
.sf-register-banner p{
    margin:0;
    color:rgba(255,255,255,.65);
    font-size:13px;
    line-height:1.9;
}
.sf-register-benefits{
    display:grid;
    gap:15px;
    margin-top:30px;
}
.sf-register-benefit{
    display:flex;
    align-items:center;
    gap:12px;
    color:rgba(255,255,255,.9);
    font-size:13px;
}
.sf-register-benefit-icon{
    display:flex;
    align-items:center;
    justify-content:center;
    width:32px;
    height:32px;
    flex-shrink:0;
    border-radius:9px;
    background:rgba(255,255,255,.09);
}
.sf-register-copyright{
    margin-top:35px;
    color:rgba(255,255,255,.45);
    font-size:11px;
}
.sf-register-content{
    display:flex;
    flex-direction:column;
    justify-content:center;
    min-width:0;
    padding:36px 38px;
    color:var(--reg-text);
}
.sf-register-mobile-brand{display:none}
.sf-register-heading{margin-bottom:25px}
.sf-register-eyebrow{
    display:block;
    margin-bottom:9px;
    color:var(--reg-muted);
    font-size:10px;
    font-weight:750;
    letter-spacing:1.5px;
    text-transform:uppercase;
}
.sf-register-heading h1{
    margin:0 0 8px;
    color:var(--reg-text);
    font-size:29px;
    font-weight:750;
    line-height:1.3;
    letter-spacing:-1px;
}
.sf-register-heading p{
    margin:0;
    color:var(--reg-muted);
    font-size:13px;
    line-height:1.7;
}
.sf-register-form{
    display:grid;
    gap:17px;
}
.sf-register-field{
    display:flex;
    flex-direction:column;
    gap:7px;
    min-width:0;
}
.sf-register-field label{
    margin:0;
    color:var(--reg-text);
    font-size:12px;
    font-weight:650;
}
.sf-register-input-wrap{
    position:relative;
    display:flex;
    align-items:center;
    min-width:0;
}
.sf-register-input-icon{
    position:absolute;
    left:14px;
    display:flex;
    align-items:center;
    color:var(--reg-muted);
    pointer-events:none;
}
.sf-register-input-wrap input{
    display:block;
    width:100%;
    height:48px;
    margin:0;
    padding:0 42px;
    border:1px solid var(--reg-border);
    border-radius:10px;
    outline:none;
    background:var(--reg-input);
    color:var(--reg-text);
    font:inherit;
    font-size:13px;
    transition:border-color .2s,box-shadow .2s,background .2s;
}
.sf-register-input-wrap input::placeholder{
    color:var(--reg-muted);
    opacity:.7;
}
.sf-register-input-wrap input:focus{
    border-color:#98a2b3;
    background:var(--reg-bg);
    box-shadow:0 0 0 4px rgba(152,162,179,.14);
}
.sf-register-input-wrap input.is-invalid{
    border-color:#ef4444;
}
.sf-register-toggle{
    position:absolute;
    right:8px;
    display:flex;
    align-items:center;
    justify-content:center;
    width:32px;
    height:32px;
    padding:0;
    border:0;
    border-radius:7px;
    background:transparent;
    color:var(--reg-muted);
    cursor:pointer;
}
.sf-register-toggle:hover{
    color:var(--reg-text);
    background:rgba(128,128,128,.08);
}
.sf-register-page svg{
    width:18px;
    height:18px;
    flex-shrink:0;
    fill:none;
    stroke:currentColor;
    stroke-width:1.7;
    stroke-linecap:round;
    stroke-linejoin:round;
}
.sf-register-page svg[hidden]{display:none!important}
.sf-register-strength{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:5px;
    margin-top:3px;
}
.sf-register-strength span{
    height:3px;
    border-radius:5px;
    background:var(--reg-border);
    transition:background .2s;
}
.sf-register-password-hint{
    color:var(--reg-muted);
    font-size:11px;
    line-height:1.5;
}
.sf-register-error{
    color:#dc2626;
    font-size:11px;
    line-height:1.5;
}
.sf-register-alert{
    margin-bottom:18px;
    padding:12px 14px;
    border:1px solid rgba(239,68,68,.2);
    border-radius:9px;
    background:rgba(239,68,68,.06);
    color:#dc2626;
    font-size:12px;
    line-height:1.6;
}
.sf-register-submit{
    display:flex;
    align-items:center;
    justify-content:center;
    gap:10px;
    width:100%;
    height:49px;
    margin-top:4px;
    border:0;
    border-radius:10px;
    background:var(--reg-primary);
    color:var(--reg-primary-text);
    font:inherit;
    font-size:13px;
    font-weight:700;
    cursor:pointer;
    transition:transform .2s,opacity .2s,box-shadow .2s;
}
.sf-register-submit:hover{
    opacity:.92;
    transform:translateY(-1px);
    box-shadow:0 6px 18px rgba(0,0,0,.12);
}
.sf-register-submit:active{transform:translateY(0)}
.sf-register-submit:disabled{opacity:.65;cursor:wait}
.sf-register-login{
    margin:23px 0 0;
    text-align:center;
    color:var(--reg-muted);
    font-size:12px;
    line-height:1.8;
}
.sf-register-login a{
    margin-left:4px;
    color:var(--reg-text);
    font-weight:700;
    text-decoration:underline;
    text-underline-offset:4px;
}
.sf-register-security{
    display:flex;
    align-items:center;
    justify-content:center;
    gap:7px;
    margin-top:20px;
    padding-top:18px;
    border-top:1px solid var(--reg-border);
    color:var(--reg-muted);
    font-size:10px;
}
.sf-register-security svg{width:14px;height:14px}
[data-theme="dark"] .sf-register-page{
    --reg-bg:#151b25;
    --reg-text:#f1f5f9;
    --reg-muted:#98a2b3;
    --reg-border:#2d3542;
    --reg-input:#1c2430;
    --reg-primary:#f1f5f9;
    --reg-primary-text:#101828;
}
@media(max-width:1100px){
    .sf-register-banner{padding:30px}
    .sf-register-content{padding:30px}
}
@media(max-width:900px){
    .sf-register-wrapper{
        grid-template-columns:1fr;
        max-width:480px;
    }
    .sf-register-banner{display:none}
    .sf-register-mobile-brand{
        display:flex;
        align-items:center;
        gap:10px;
        margin-bottom:25px;
        font-size:17px;
        font-weight:700;
    }
    .sf-register-mobile-brand .sf-register-logo{
        border:1px solid var(--reg-border);
    }
    .sf-register-page{min-height:auto;padding:30px 12px}
}
@media(max-width:600px){
    .sf-register-page{padding:20px 4px 30px}
    .sf-register-wrapper{border-radius:17px}
    .sf-register-content{padding:26px 20px}
    .sf-register-heading h1{font-size:26px}
    .sf-register-input-wrap input{font-size:16px}
}
@media(prefers-reduced-motion:reduce){
    .sf-register-page *{transition:none!important}
}
</style>
@endpush

@section('content')
@php
    $registerBusiness = $business;
    $registerBrandService = app(\App\Services\Public\Business\Admin\Settings\Business\GeneralSettings\BrandingService::class);
    $registerLogo = $registerBrandService->imageUrl($registerBusiness->logo);
@endphp

<div class="sf-register-page">
    <div class="sf-register-wrapper">

        <aside class="sf-register-banner">
            <div class="sf-register-brand">
                <div class="sf-register-logo">
                    @if($registerLogo)
                        <img src="{{ $registerLogo }}" alt="{{ $registerBusiness->name }}">
                    @else
                        {{ mb_strtoupper(mb_substr($registerBusiness->name,0,1)) }}
                    @endif
                </div>
                <span>{{ $registerBusiness->name }}</span>
            </div>

            <div>
                <div class="sf-register-badge">
                    <span></span>
                    {{ t('JOIN OUR COMMUNITY','আমাদের সঙ্গে যুক্ত হোন') }}
                </div>

                <h2>{{ t('Your shopping journey starts here.','আপনার কেনাকাটার যাত্রা এখান থেকেই শুরু।') }}</h2>

                <p>
                    {{ t(
                        'Create your personal account and discover a smarter, simpler way to shop.',
                        'নিজের অ্যাকাউন্ট তৈরি করুন এবং উপভোগ করুন আরও সহজ ও সুন্দর কেনাকাটার অভিজ্ঞতা।'
                    ) }}
                </p>

                <div class="sf-register-benefits">
                    <div class="sf-register-benefit">
                        <span class="sf-register-benefit-icon">
                            <svg viewBox="0 0 24 24">
                                <path d="M6 7h12l2 14H4L6 7Z"/>
                                <path d="M9 8V6a3 3 0 0 1 6 0v2"/>
                            </svg>
                        </span>
                        {{ t('Faster and easier checkout','দ্রুত ও সহজ চেকআউট') }}
                    </div>

                    <div class="sf-register-benefit">
                        <span class="sf-register-benefit-icon">
                            <svg viewBox="0 0 24 24">
                                <rect x="3" y="3" width="18" height="18" rx="3"/>
                                <path d="M8 3v5h8V3M8 13h8M8 17h5"/>
                            </svg>
                        </span>
                        {{ t('Manage your orders in one place','এক জায়গায় সব অর্ডার পরিচালনা করুন') }}
                    </div>

                    <div class="sf-register-benefit">
                        <span class="sf-register-benefit-icon">
                            <svg viewBox="0 0 24 24">
                                <path d="M20.8 8.6c0 4.4-8.8 10.4-8.8 10.4S3.2 13 3.2 8.6a4.6 4.6 0 0 1 8.8-1.9 4.6 4.6 0 0 1 8.8 1.9Z"/>
                            </svg>
                        </span>
                        {{ t('Save your favourite products','পছন্দের পণ্য সংরক্ষণ করুন') }}
                    </div>

                    <div class="sf-register-benefit">
                        <span class="sf-register-benefit-icon">
                            <svg viewBox="0 0 24 24">
                                <path d="m12 3 8 4v5c0 5-8 9-8 9s-8-4-8-9V7l8-4Z"/>
                                <path d="m9 12 2 2 4-4"/>
                            </svg>
                        </span>
                        {{ t('Your personal shopping account','আপনার ব্যক্তিগত শপিং অ্যাকাউন্ট') }}
                    </div>
                </div>
            </div>

            <div class="sf-register-copyright">
                &copy; {{ date('Y') }} {{ $registerBusiness->name }}.
                {{ t('All rights reserved.','সর্বস্বত্ব সংরক্ষিত।') }}
            </div>
        </aside>

        <section class="sf-register-content">

            <div class="sf-register-mobile-brand">
                <div class="sf-register-logo">
                    @if($registerLogo)
                        <img src="{{ $registerLogo }}" alt="{{ $registerBusiness->name }}">
                    @else
                        {{ mb_strtoupper(mb_substr($registerBusiness->name,0,1)) }}
                    @endif
                </div>
                <span>{{ $registerBusiness->name }}</span>
            </div>

            <div class="sf-register-heading">
                <span class="sf-register-eyebrow">
                    {{ t('GET STARTED','শুরু করুন') }}
                </span>
                <h1>{{ t('Create your account','অ্যাকাউন্ট তৈরি করুন') }}</h1>
                <p>{{ t('Enter your details to get started.','অ্যাকাউন্ট তৈরি করতে আপনার তথ্য প্রদান করুন।') }}</p>
            </div>

            @if($errors->any() && !$errors->has('name') && !$errors->has('email') && !$errors->has('password') && !$errors->has('password_confirmation'))
                <div class="sf-register-alert" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ $action }}" method="post" class="sf-register-form" id="sfRegisterForm">
                @csrf

                <div class="sf-register-field">
                    <label for="name">{{ t('Full name','পুরো নাম') }}</label>

                    <div class="sf-register-input-wrap">
                        <span class="sf-register-input-icon">
                            <svg viewBox="0 0 24 24">
                                <circle cx="12" cy="8" r="4"/>
                                <path d="M4 21v-2a8 8 0 0 1 16 0v2"/>
                            </svg>
                        </span>

                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name') }}"
                            placeholder="{{ t('Enter your full name','আপনার পুরো নাম লিখুন') }}"
                            class="@error('name') is-invalid @enderror"
                            maxlength="255"
                            autocomplete="name"
                            required
                        >
                    </div>

                    @error('name')
                        <span class="sf-register-error" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <div class="sf-register-field">
                    <label for="email">{{ t('Email address','ইমেইল ঠিকানা') }}</label>

                    <div class="sf-register-input-wrap">
                        <span class="sf-register-input-icon">
                            <svg viewBox="0 0 24 24">
                                <rect x="3" y="5" width="18" height="14" rx="3"/>
                                <path d="m3 7 9 6 9-6"/>
                            </svg>
                        </span>

                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            placeholder="{{ t('Enter your email','আপনার ইমেইল লিখুন') }}"
                            class="@error('email') is-invalid @enderror"
                            autocomplete="username"
                            required
                        >
                    </div>

                    @error('email')
                        <span class="sf-register-error" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <div class="sf-register-field">
                    <label for="password">{{ t('Password','পাসওয়ার্ড') }}</label>

                    <div class="sf-register-input-wrap">
                        <span class="sf-register-input-icon">
                            <svg viewBox="0 0 24 24">
                                <rect x="4" y="10" width="16" height="11" rx="3"/>
                                <path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/>
                            </svg>
                        </span>

                        <input
                            id="password"
                            name="password"
                            type="password"
                            placeholder="{{ t('Minimum 8 characters','কমপক্ষে ৮ অক্ষর') }}"
                            class="@error('password') is-invalid @enderror"
                            autocomplete="new-password"
                            minlength="8"
                            required
                        >

                        <button type="button"
                                class="sf-register-toggle"
                                data-password-toggle="password"
                                aria-controls="password"
                                aria-pressed="false"
                                aria-label="{{ t('Show password','পাসওয়ার্ড দেখুন') }}"
                                data-show="{{ t('Show password','পাসওয়ার্ড দেখুন') }}"
                                data-hide="{{ t('Hide password','পাসওয়ার্ড লুকান') }}">
                            <svg class="eye-icon" viewBox="0 0 24 24">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg class="eye-off-icon" viewBox="0 0 24 24" hidden>
                                <path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"/>
                                <path d="M9.8 5.3A11 11 0 0 1 12 5c6.5 0 10 7 10 7a15 15 0 0 1-2 3M6.3 6.3C3.5 8.5 2 12 2 12s3.5 7 10 7a10 10 0 0 0 4.2-.9"/>
                            </svg>
                        </button>
                    </div>

                    <div class="sf-register-strength" id="sfPasswordStrength" aria-hidden="true">
                        <span></span><span></span><span></span><span></span>
                    </div>

                    <span class="sf-register-password-hint" id="sfPasswordHint" aria-live="polite">
                        {{ t('Use at least 8 characters.','কমপক্ষে ৮টি অক্ষর ব্যবহার করুন।') }}
                    </span>

                    @error('password')
                        <span class="sf-register-error" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <div class="sf-register-field">
                    <label for="password_confirmation">
                        {{ t('Confirm password','পাসওয়ার্ড নিশ্চিত করুন') }}
                    </label>

                    <div class="sf-register-input-wrap">
                        <span class="sf-register-input-icon">
                            <svg viewBox="0 0 24 24">
                                <rect x="4" y="10" width="16" height="11" rx="3"/>
                                <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                                <path d="m9 15 2 2 4-4"/>
                            </svg>
                        </span>

                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            placeholder="{{ t('Re-enter your password','আবার পাসওয়ার্ড লিখুন') }}"
                            class="@error('password_confirmation') is-invalid @enderror"
                            autocomplete="new-password"
                            minlength="8"
                            required
                        >

                        <button type="button"
                                class="sf-register-toggle"
                                data-password-toggle="password_confirmation"
                                aria-controls="password_confirmation"
                                aria-pressed="false"
                                aria-label="{{ t('Show password','পাসওয়ার্ড দেখুন') }}"
                                data-show="{{ t('Show password','পাসওয়ার্ড দেখুন') }}"
                                data-hide="{{ t('Hide password','পাসওয়ার্ড লুকান') }}">
                            <svg class="eye-icon" viewBox="0 0 24 24">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg class="eye-off-icon" viewBox="0 0 24 24" hidden>
                                <path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"/>
                                <path d="M9.8 5.3A11 11 0 0 1 12 5c6.5 0 10 7 10 7a15 15 0 0 1-2 3M6.3 6.3C3.5 8.5 2 12 2 12s3.5 7 10 7a10 10 0 0 0 4.2-.9"/>
                            </svg>
                        </button>
                    </div>

                    <span class="sf-register-error" id="sfPasswordMatchError" aria-live="polite" hidden></span>

                    @error('password_confirmation')
                        <span class="sf-register-error" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit"
                        class="sf-register-submit"
                        id="sfRegisterSubmit"
                        data-loading="{{ t('Creating account...','অ্যাকাউন্ট তৈরি হচ্ছে...') }}">
                    <span id="sfRegisterButtonText">
                        {{ t('Create account','অ্যাকাউন্ট তৈরি করুন') }}
                    </span>
                    <svg viewBox="0 0 24 24">
                        <path d="M4 12h15m-6-6 6 6-6 6"/>
                    </svg>
                </button>
            </form>

            @if(!empty($loginUrl))
                <p class="sf-register-login">
                    {{ t('Already have an account?','আগে থেকেই অ্যাকাউন্ট আছে?') }}
                    <a href="{{ $loginUrl }}">{{ t('Sign in','লগইন করুন') }}</a>
                </p>
            @endif

            <div class="sf-register-security">
                <svg viewBox="0 0 24 24">
                    <path d="m12 3 8 4v5c0 5-8 9-8 9s-8-4-8-9V7l8-4Z"/>
                    <path d="m9 12 2 2 4-4"/>
                </svg>
                {{ t('Create your personal shopping account','আপনার ব্যক্তিগত শপিং অ্যাকাউন্ট তৈরি করুন') }}
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded',function(){
    const form = document.getElementById('sfRegisterForm');
    if(!form) return;

    const password = document.getElementById('password');
    const confirmation = document.getElementById('password_confirmation');
    const strengthBars = document.querySelectorAll('#sfPasswordStrength span');
    const passwordHint = document.getElementById('sfPasswordHint');
    const matchError = document.getElementById('sfPasswordMatchError');
    const submit = document.getElementById('sfRegisterSubmit');
    const buttonText = document.getElementById('sfRegisterButtonText');
    const originalText = buttonText.textContent.trim();

    const messages = {
        minimum: @json(t('Use at least 8 characters.','কমপক্ষে ৮টি অক্ষর ব্যবহার করুন।')),
        weak: @json(t('Weak password','দুর্বল পাসওয়ার্ড')),
        fair: @json(t('Fair password','মাঝারি পাসওয়ার্ড')),
        good: @json(t('Good password','ভালো পাসওয়ার্ড')),
        strong: @json(t('Strong password','শক্তিশালী পাসওয়ার্ড')),
        mismatch: @json(t('Passwords do not match.','পাসওয়ার্ড দুটি মিলছে না।'))
    };

    form.querySelectorAll('[data-password-toggle]').forEach(function(button){
        button.addEventListener('click',function(){
            const input = document.getElementById(button.dataset.passwordToggle);
            const visible = input.type === 'password';

            input.type = visible ? 'text' : 'password';

            button.querySelector('.eye-icon').hidden = visible;
            button.querySelector('.eye-off-icon').hidden = !visible;

            button.setAttribute('aria-pressed',String(visible));
            button.setAttribute('aria-label',visible ? button.dataset.hide : button.dataset.show);
        });
    });

    function updateStrength(){
        const value = password.value;
        let score = 0;

        if(value.length >= 8){
            score = 1;
            if(/[a-z]/.test(value) && /[A-Z]/.test(value)) score++;
            if(/[0-9]/.test(value)) score++;
            if(/[^A-Za-z0-9]/.test(value) || value.length >= 14) score++;
        }

        const colors = ['#ef4444','#f59e0b','#84cc16','#16a34a'];
        const labels = [messages.weak,messages.fair,messages.good,messages.strong];

        strengthBars.forEach(function(bar,index){
            bar.style.background = index < score
                ? colors[score-1]
                : 'var(--reg-border)';
        });

        passwordHint.textContent = score > 0
            ? labels[score-1]
            : messages.minimum;

        passwordHint.style.color = score > 0
            ? colors[score-1]
            : 'var(--reg-muted)';
    }

    function validateMatch(){
        const mismatch = confirmation.value.length > 0
            && password.value !== confirmation.value;

        confirmation.setCustomValidity(mismatch ? messages.mismatch : '');
        confirmation.classList.toggle('is-invalid',mismatch);

        matchError.hidden = !mismatch;
        matchError.textContent = mismatch ? messages.mismatch : '';

        return !mismatch;
    }

    password.addEventListener('input',function(){
        updateStrength();
        validateMatch();
    });

    confirmation.addEventListener('input',validateMatch);

    form.addEventListener('submit',function(e){
        if(!validateMatch() || !form.checkValidity()){
            e.preventDefault();
            form.reportValidity();
            return;
        }

        submit.disabled = true;
        buttonText.textContent = submit.dataset.loading;
    });

    window.addEventListener('pageshow',function(){
        submit.disabled = false;
        buttonText.textContent = originalText;
    });
});
</script>
@endpush
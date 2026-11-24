@extends('public.business.public.ecommerce.layout')

@section('title', $title)

@push('styles')
<style>
.sf-login-page{
    --login-bg:#fff;
    --login-text:#182230;
    --login-muted:#667085;
    --login-border:#e4e7ec;
    --login-input:#f9fafb;
    --login-primary:#171717;
    --login-primary-text:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:48px 16px;
    min-height:620px;
    width:100%;
}
.sf-login-page *{box-sizing:border-box}
.sf-login-wrapper{
    display:grid;
    grid-template-columns:1fr 1fr;
    width:100%;
    max-width:980px;
    background:var(--login-bg);
    border:1px solid var(--login-border);
    border-radius:24px;
    overflow:hidden;
    box-shadow:0 16px 60px rgba(16,24,40,.07);
}
.sf-login-banner{
    position:relative;
    isolation:isolate;
    overflow:hidden;
    display:flex;
    flex-direction:column;
    justify-content:space-between;
    padding:46px;
    min-height:540px;
    background:linear-gradient(145deg,#101828,#24344a);
    color:#fff;
}
.sf-login-banner:before{
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
.sf-login-banner:after{
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
.sf-login-brand{
    display:flex;
    align-items:center;
    gap:14px;
    font-size:20px;
    font-weight:700;
    color:#fff;
}
.sf-login-brand-logo{
    display:flex;
    align-items:center;
    justify-content:center;
    width:48px;
    height:48px;
    border-radius:13px;
    background:#fff;
    overflow:hidden;
    padding:7px;
    flex-shrink:0;
    color:#101828;
    font-size:22px;
    font-weight:800;
}
.sf-login-brand-logo img{
    width:100%;
    height:100%;
    object-fit:contain;
}
.sf-login-banner-badge{
    display:inline-flex;
    align-items:center;
    gap:7px;
    padding:7px 12px;
    margin-bottom:24px;
    border:1px solid rgba(255,255,255,.15);
    background:rgba(255,255,255,.07);
    border-radius:30px;
    font-size:11px;
    font-weight:600;
    letter-spacing:.5px;
}
.sf-login-banner-dot{
    width:7px;
    height:7px;
    background:#a3e635;
    border-radius:50%;
    box-shadow:0 0 12px rgba(163,230,53,.4);
}
.sf-login-banner h2{
    margin:0 0 16px;
    font-size:clamp(28px,3vw,40px);
    font-weight:700;
    line-height:1.2;
    letter-spacing:-1.2px;
    color:#fff;
}
.sf-login-banner p{
    margin:0;
    max-width:340px;
    font-size:14px;
    line-height:1.8;
    color:rgba(255,255,255,.65);
}
.sf-login-benefits{
    display:flex;
    flex-direction:column;
    gap:15px;
    margin-top:30px;
}
.sf-login-benefit{
    display:flex;
    align-items:center;
    gap:12px;
    color:rgba(255,255,255,.9);
    font-size:13px;
}
.sf-login-benefit-icon{
    display:flex;
    align-items:center;
    justify-content:center;
    width:32px;
    height:32px;
    border-radius:9px;
    background:rgba(255,255,255,.09);
}
.sf-login-copyright{
    margin-top:40px;
    font-size:11px;
    color:rgba(255,255,255,.45);
}
.sf-login-content{
    display:flex;
    flex-direction:column;
    justify-content:center;
    padding:48px 46px;
    color:var(--login-text);
}
.sf-login-mobile-brand{display:none}
.sf-login-heading{
    margin-bottom:30px;
}
.sf-login-welcome{
    display:inline-block;
    font-size:11px;
    font-weight:700;
    letter-spacing:1.6px;
    text-transform:uppercase;
    color:var(--login-muted);
    margin-bottom:10px;
}
.sf-login-heading h1{
    margin:0 0 10px;
    font-size:32px;
    line-height:1.3;
    font-weight:750;
    letter-spacing:-1px;
    color:var(--login-text);
}
.sf-login-heading p{
    margin:0;
    color:var(--login-muted);
    font-size:14px;
    line-height:1.7;
}
.sf-login-form{
    display:flex;
    flex-direction:column;
    gap:20px;
}
.sf-login-field{
    display:flex;
    flex-direction:column;
    gap:8px;
}
.sf-login-field label{
    margin:0;
    color:var(--login-text);
    font-size:13px;
    font-weight:600;
}
.sf-login-label-row{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
}
.sf-login-input-wrap{
    position:relative;
    display:flex;
    align-items:center;
}
.sf-login-input-icon{
    position:absolute;
    left:15px;
    color:var(--login-muted);
    pointer-events:none;
    display:flex;
    align-items:center;
}
.sf-login-input-wrap input{
    display:block;
    width:100%;
    height:52px;
    margin:0;
    padding:0 44px;
    outline:none;
    border:1px solid var(--login-border);
    border-radius:11px;
    background:var(--login-input);
    color:var(--login-text);
    font:inherit;
    font-size:14px;
    transition:border-color .2s,box-shadow .2s,background .2s;
}
.sf-login-input-wrap input:focus{
    background:var(--login-bg);
    border-color:#98a2b3;
    box-shadow:0 0 0 4px rgba(152,162,179,.14);
}
.sf-login-input-wrap input::placeholder{
    color:var(--login-muted);
    opacity:.7;
}
.sf-login-input-wrap input.is-invalid{
    border-color:#ef4444;
}
.sf-login-password-toggle{
    display:flex;
    align-items:center;
    justify-content:center;
    position:absolute;
    right:10px;
    width:32px;
    height:32px;
    padding:0;
    background:transparent;
    border:0;
    border-radius:6px;
    color:var(--login-muted);
    cursor:pointer;
}
.sf-login-password-toggle:hover{
    background:rgba(128,128,128,.08);
    color:var(--login-text);
}
.sf-login-page svg{
    width:19px;
    height:19px;
    flex-shrink:0;
    fill:none;
    stroke:currentColor;
    stroke-width:1.7;
    stroke-linecap:round;
    stroke-linejoin:round;
}
.sf-login-page svg[hidden]{display:none}
.sf-login-link{
    color:var(--login-text);
    font-size:12px;
    font-weight:650;
    text-decoration:none;
}
.sf-login-link:hover{
    text-decoration:underline;
    text-underline-offset:4px;
}
.sf-login-submit{
    display:flex;
    align-items:center;
    justify-content:center;
    gap:12px;
    width:100%;
    height:52px;
    margin-top:4px;
    border:0;
    border-radius:11px;
    background:var(--login-primary);
    color:var(--login-primary-text);
    font:inherit;
    font-size:14px;
    font-weight:650;
    cursor:pointer;
    transition:transform .2s,opacity .2s,box-shadow .2s;
}
.sf-login-submit:hover{
    opacity:.92;
    transform:translateY(-1px);
    box-shadow:0 6px 18px rgba(0,0,0,.12);
}
.sf-login-submit:active{transform:translateY(0)}
.sf-login-submit:disabled{
    cursor:wait;
    opacity:.65;
}
.sf-login-register{
    margin-top:28px;
    text-align:center;
    font-size:13px;
    line-height:1.8;
    color:var(--login-muted);
}
.sf-login-register a{
    margin-left:4px;
    color:var(--login-text);
    font-weight:700;
    text-decoration:underline;
    text-underline-offset:4px;
}
.sf-login-secure{
    display:flex;
    align-items:center;
    justify-content:center;
    gap:7px;
    margin-top:26px;
    padding-top:22px;
    border-top:1px solid var(--login-border);
    color:var(--login-muted);
    font-size:11px;
}
.sf-login-secure svg{
    width:14px;
    height:14px;
}
.sf-login-error{
    font-size:12px;
    color:#dc2626;
    line-height:1.5;
}
.sf-login-alert{
    padding:12px 14px;
    margin-bottom:20px;
    border:1px solid rgba(239,68,68,.2);
    border-radius:9px;
    background:rgba(239,68,68,.06);
    color:#dc2626;
    font-size:13px;
    line-height:1.6;
}
[data-theme="dark"] .sf-login-page{
    --login-bg:#151b25;
    --login-text:#f1f5f9;
    --login-muted:#98a2b3;
    --login-border:#2d3542;
    --login-input:#1c2430;
    --login-primary:#f1f5f9;
    --login-primary-text:#101828;
}
@media(max-width:900px){
    .sf-login-banner{padding:32px}
    .sf-login-content{padding:36px 30px}
}
@media(max-width:700px){
    .sf-login-page{
        min-height:auto;
        padding:24px 12px 36px;
    }
    .sf-login-wrapper{
        grid-template-columns:1fr;
        max-width:460px;
        border-radius:18px;
    }
    .sf-login-banner{display:none}
    .sf-login-content{padding:30px 24px}
    .sf-login-mobile-brand{
        display:flex;
        align-items:center;
        gap:10px;
        margin-bottom:28px;
        font-size:17px;
        font-weight:700;
    }
    .sf-login-mobile-brand .sf-login-brand-logo{
        border:1px solid var(--login-border);
        width:44px;
        height:44px;
    }
    .sf-login-heading h1{font-size:28px}
    .sf-login-heading{margin-bottom:24px}
    .sf-login-input-wrap input{font-size:16px}
}
@media(prefers-reduced-motion:reduce){
    .sf-login-page *{transition:none!important}
}
</style>
@endpush

@section('content')
@php
    $loginBusiness = $business;
    $loginBrandService = app(\App\Services\Public\Business\Admin\Settings\Business\GeneralSettings\BrandingService::class);
    $loginLogo = $loginBrandService->imageUrl($loginBusiness->logo);
@endphp

<div class="sf-login-page">
    <div class="sf-login-wrapper">

        {{-- Desktop Branding Panel --}}
        <aside class="sf-login-banner">
            <div class="sf-login-brand">
                <div class="sf-login-brand-logo">
                    @if($loginLogo)
                        <img src="{{ $loginLogo }}" alt="{{ $loginBusiness->name }}">
                    @else
                        {{ mb_strtoupper(mb_substr($loginBusiness->name, 0, 1)) }}
                    @endif
                </div>
                <span>{{ $loginBusiness->name }}</span>
            </div>

            <div>
                <div class="sf-login-banner-badge">
                    <span class="sf-login-banner-dot"></span>
                    {{ t('YOUR SHOPPING DESTINATION', 'আপনার কেনাকাটার ঠিকানা') }}
                </div>

                <h2>
                    {{ t('A better way to shop.', 'কেনাকাটার আরও সুন্দর অভিজ্ঞতা।') }}
                </h2>

                <p>
                    {{ t(
                        'Discover your favourites, manage your orders and enjoy a seamless shopping experience.',
                        'পছন্দের পণ্য খুঁজুন, অর্ডার পরিচালনা করুন এবং উপভোগ করুন সহজ কেনাকাটার অভিজ্ঞতা।'
                    ) }}
                </p>

                <div class="sf-login-benefits">
                    <div class="sf-login-benefit">
                        <span class="sf-login-benefit-icon">
                            <svg viewBox="0 0 24 24">
                                <path d="M6 7h12l2 14H4L6 7Z"/>
                                <path d="M9 8V6a3 3 0 0 1 6 0v2"/>
                            </svg>
                        </span>
                        {{ t('Seamless shopping experience', 'সহজ কেনাকাটার অভিজ্ঞতা') }}
                    </div>

                    <div class="sf-login-benefit">
                        <span class="sf-login-benefit-icon">
                            <svg viewBox="0 0 24 24">
                                <path d="M3 7h12v11H3zM15 11h4l3 3v4h-7"/>
                                <circle cx="7" cy="19" r="2"/>
                                <circle cx="18" cy="19" r="2"/>
                            </svg>
                        </span>
                        {{ t('Easy order management', 'সহজ অর্ডার পরিচালনা') }}
                    </div>

                    <div class="sf-login-benefit">
                        <span class="sf-login-benefit-icon">
                            <svg viewBox="0 0 24 24">
                                <path d="M20.8 8.6c0 4.4-8.8 10.4-8.8 10.4S3.2 13 3.2 8.6a4.6 4.6 0 0 1 8.8-1.9 4.6 4.6 0 0 1 8.8 1.9Z"/>
                            </svg>
                        </span>
                        {{ t('Save your favourite products', 'পছন্দের পণ্য সংরক্ষণ করুন') }}
                    </div>
                </div>
            </div>

            <div class="sf-login-copyright">
                &copy; {{ date('Y') }} {{ $loginBusiness->name }}.
                {{ t('All rights reserved.', 'সর্বস্বত্ব সংরক্ষিত।') }}
            </div>
        </aside>

        {{-- Login Form --}}
        <section class="sf-login-content">
            <div class="sf-login-mobile-brand">
                <div class="sf-login-brand-logo">
                    @if($loginLogo)
                        <img src="{{ $loginLogo }}" alt="{{ $loginBusiness->name }}">
                    @else
                        {{ mb_strtoupper(mb_substr($loginBusiness->name, 0, 1)) }}
                    @endif
                </div>
                <span>{{ $loginBusiness->name }}</span>
            </div>

            <div class="sf-login-heading">
                <span class="sf-login-welcome">
                    {{ t('YOUR ACCOUNT', 'আপনার অ্যাকাউন্ট') }}
                </span>
                <h1>{{ t('Welcome back!', 'স্বাগতম!') }}</h1>
                <p>
                    {{ t(
                        'Enter your details to sign in to your account.',
                        'আপনার অ্যাকাউন্টে প্রবেশ করতে তথ্য প্রদান করুন।'
                    ) }}
                </p>
            </div>

            @if($errors->any() && !$errors->has('email') && !$errors->has('password'))
                <div class="sf-login-alert" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ $action }}" method="post" class="sf-login-form" id="sfLoginForm">
                @csrf

                <div class="sf-login-field">
                    <label for="email">
                        {{ t('Email address', 'ইমেইল ঠিকানা') }}
                    </label>

                    <div class="sf-login-input-wrap">
                        <span class="sf-login-input-icon">
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
                            placeholder="{{ t('Enter your email', 'আপনার ইমেইল লিখুন') }}"
                            autocomplete="username"
                            class="@error('email') is-invalid @enderror"
                            required
                            autofocus
                        >
                    </div>

                    @error('email')
                        <span class="sf-login-error" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <div class="sf-login-field">
                    <div class="sf-login-label-row">
                        <label for="password">
                            {{ t('Password', 'পাসওয়ার্ড') }}
                        </label>

                        @if(!empty($forgotUrl))
                            <a href="{{ $forgotUrl }}" class="sf-login-link">
                                {{ t('Forgot password?', 'পাসওয়ার্ড ভুলে গেছেন?') }}
                            </a>
                        @endif
                    </div>

                    <div class="sf-login-input-wrap">
                        <span class="sf-login-input-icon">
                            <svg viewBox="0 0 24 24">
                                <rect x="4" y="10" width="16" height="11" rx="3"/>
                                <path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/>
                            </svg>
                        </span>

                        <input
                            id="password"
                            name="password"
                            type="password"
                            placeholder="{{ t('Enter your password', 'আপনার পাসওয়ার্ড লিখুন') }}"
                            autocomplete="current-password"
                            class="@error('password') is-invalid @enderror"
                            required
                        >

                        <button
                            type="button"
                            class="sf-login-password-toggle"
                            id="sfPasswordToggle"
                            aria-controls="password"
                            aria-label="{{ t('Show password', 'পাসওয়ার্ড দেখুন') }}"
                            aria-pressed="false"
                            data-show="{{ t('Show password', 'পাসওয়ার্ড দেখুন') }}"
                            data-hide="{{ t('Hide password', 'পাসওয়ার্ড লুকান') }}"
                        >
                            <svg id="sfEyeIcon" viewBox="0 0 24 24">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg id="sfEyeOffIcon" viewBox="0 0 24 24" hidden>
                                <path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"/>
                                <path d="M9.8 5.3A11 11 0 0 1 12 5c6.5 0 10 7 10 7a15 15 0 0 1-2 3M6.3 6.3C3.5 8.5 2 12 2 12s3.5 7 10 7a10 10 0 0 0 4.2-.9"/>
                            </svg>
                        </button>
                    </div>

                    @error('password')
                        <span class="sf-login-error" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="sf-login-submit"
                    id="sfLoginSubmit"
                    data-loading="{{ t('Signing in...', 'লগইন হচ্ছে...') }}"
                >
                    <span id="sfLoginButtonText">
                        {{ t('Sign in', 'লগইন করুন') }}
                    </span>
                    <svg viewBox="0 0 24 24">
                        <path d="M4 12h15m-6-6 6 6-6 6"/>
                    </svg>
                </button>
            </form>

            @if(!empty($registerUrl))
                <div class="sf-login-register">
                    {{ t("Don't have an account?", 'অ্যাকাউন্ট নেই?') }}
                    <a href="{{ $registerUrl }}">
                        {{ t('Create an account', 'অ্যাকাউন্ট তৈরি করুন') }}
                    </a>
                </div>
            @endif

            <div class="sf-login-secure">
                <svg viewBox="0 0 24 24">
                    <path d="m12 3 8 4v5c0 5-8 9-8 9s-8-4-8-9V7l8-4Z"/>
                    <path d="m9 12 2 2 4-4"/>
                </svg>
                {{ t('Sign in to your personal account', 'আপনার ব্যক্তিগত অ্যাকাউন্টে লগইন করুন') }}
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const password = document.getElementById('password');
    const toggle = document.getElementById('sfPasswordToggle');
    const eye = document.getElementById('sfEyeIcon');
    const eyeOff = document.getElementById('sfEyeOffIcon');
    const form = document.getElementById('sfLoginForm');
    const submit = document.getElementById('sfLoginSubmit');
    const buttonText = document.getElementById('sfLoginButtonText');
    const originalText = buttonText?.textContent.trim();

    toggle?.addEventListener('click', function(){
        const visible = password.type === 'password';
        password.type = visible ? 'text' : 'password';
        eye.hidden = visible;
        eyeOff.hidden = !visible;
        toggle.setAttribute('aria-pressed', String(visible));
        toggle.setAttribute('aria-label', visible ? toggle.dataset.hide : toggle.dataset.show);
    });

    form?.addEventListener('submit', function(){
        if(!form.checkValidity()) return;
        submit.disabled = true;
        buttonText.textContent = submit.dataset.loading;
    });

    window.addEventListener('pageshow', function(){
        if(submit) submit.disabled = false;
        if(buttonText) buttonText.textContent = originalText;
    });
});
</script>
@endpush
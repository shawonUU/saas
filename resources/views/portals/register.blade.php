@extends('layouts.portal')
@section('content')
<div class="card auth">
    <h1 class="title">{{ $title }}</h1>
    <p class="sub">{{ t('Create your account in this portal.', 'এই পোর্টালে নতুন অ্যাকাউন্ট তৈরি করুন।') }}</p>
    <form action="{{ $action }}" method="post">
        @csrf
        <label for="name">{{ t('Full name', 'পুরো নাম') }}</label><input id="name" name="name" value="{{ old('name') }}" maxlength="255" required>
        <label for="email">{{ t('Email address', 'ইমেইল ঠিকানা') }}</label><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required>
        <label for="password">{{ t('Password (minimum 8 characters)', 'পাসওয়ার্ড (কমপক্ষে ৮ অক্ষর)') }}</label><input id="password" name="password" type="password" autocomplete="new-password" minlength="8" required>
        <label for="password_confirmation">{{ t('Confirm password', 'পাসওয়ার্ড নিশ্চিত করুন') }}</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required>
        <button class="btn wide" type="submit">{{ t('Register', 'রেজিস্ট্রেশন') }}</button>
    </form>
    <p class="hint">{{ t('Have an account?', 'আগে থেকেই অ্যাকাউন্ট আছে?') }} <a href="{{ $loginUrl }}">{{ t('Log in', 'লগইন করুন') }}</a></p>
</div>
@endsection

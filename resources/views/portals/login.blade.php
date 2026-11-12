@extends('layouts.portal')
@section('content')
<div class="card auth">
    <h1 class="title">{{ $title }}</h1>
    <p class="sub">{{ t('Log in with your portal account.', 'আপনার পোর্টাল অ্যাকাউন্টে লগইন করুন।') }}</p>
    <form action="{{ $action }}" method="post">
        @csrf
        <label for="email">{{ t('Email address', 'ইমেইল ঠিকানা') }}</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
        <label for="password">{{ t('Password', 'পাসওয়ার্ড') }}</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>
        <button type="submit" class="btn wide">{{ t('Log in', 'লগইন করুন') }}</button>
    </form>
    @if($registerUrl)<p class="hint">{{ t('New here?', 'নতুন ব্যবহারকারী?') }} <a href="{{ $registerUrl }}">{{ t('Create account', 'অ্যাকাউন্ট তৈরি করুন') }}</a></p>@endif
</div>
@endsection

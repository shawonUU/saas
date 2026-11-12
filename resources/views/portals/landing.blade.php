@extends('layouts.portal')
@section('content')
<div class="card" style="padding:45px 32px">
    <h1 class="title">{{ $title }}</h1><p class="sub">{{ $description }}</p>
    <div class="actions">
        @if(Auth::guard($guard)->check())
            <a class="btn" href="{{ $dashboardUrl }}">{{ t('My dashboard', 'আমার ড্যাশবোর্ড') }}</a>
        @else
            <a class="btn" href="{{ $loginUrl }}">{{ t('Log in', 'লগইন') }}</a>
            <a class="btn btn-outline" href="{{ $registerUrl }}">{{ t('Register', 'রেজিস্ট্রেশন') }}</a>
        @endif
    </div>
</div>
@endsection

@extends($layout)
@section('content')
<div class="card auth">
    <h1 class="title">{{ t('Reset password', 'পাসওয়ার্ড পরিবর্তন') }}</h1>
    @if($errors->any())<p class="hint">{{ $errors->first() }}</p>@endif
    <form method="post" action="{{ $action }}">@csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <label for="email">{{ t('Email', 'ইমেইল') }}</label>
        <input id="email" name="email" type="email" value="{{ old('email', $email) }}" autocomplete="email" required>
        <label for="password">{{ t('New password', 'নতুন পাসওয়ার্ড') }}</label>
        <input id="password" name="password" type="password" minlength="8" autocomplete="new-password" required>
        <label for="password_confirmation">{{ t('Confirm password', 'পাসওয়ার্ড নিশ্চিত করুন') }}</label>
        <input id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" required>
        <button class="btn wide" type="submit">{{ t('Update password', 'পাসওয়ার্ড পরিবর্তন করুন') }}</button>
    </form>
</div>
@endsection

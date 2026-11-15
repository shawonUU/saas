@extends($layout)
@section('content')
<div class="card auth">
    <h1 class="title">{{ t('Forgot password?', 'পাসওয়ার্ড ভুলে গেছেন?') }}</h1>
    <p class="sub">{{ t('Enter your account email to receive a reset link.', 'রিসেট লিংক পেতে ইমেইল দিন।') }}</p>
    @if(session('status'))<p class="hint">{{ session('status') }}</p>@endif
    @if($errors->any())<p class="hint">{{ $errors->first() }}</p>@endif
    <form method="post" action="{{ $action }}">@csrf
        <label for="email">{{ t('Email', 'ইমেইল') }}</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
        <button class="btn wide" type="submit">{{ t('Send reset link', 'রিসেট লিংক পাঠান') }}</button>
    </form>
</div>
@endsection

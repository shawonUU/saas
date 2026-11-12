@extends('layouts.portal')
@section('content')
<div class="card">
    <h1 class="title">{{ $title }}</h1><p class="sub">{{ $subtitle }}</p>
    <h3>{{ t('Welcome', 'স্বাগতম') }}, {{ $user->name }}</h3>
    <p class="small">{{ $user->email }}</p>
    @if(isset($user->role))<p class="small">{{ t('Role', 'পদবি') }}: {{ ucfirst($user->role) }}</p>@endif
    <div class="actions">
        @foreach($links as $link)<a href="{{ $link[1] }}" class="btn btn-outline">{{ $link[0] }}</a>@endforeach
        <form method="post" action="{{ $logoutUrl }}">@csrf<button class="btn" type="submit">{{ t('Log out', 'লগআউট') }}</button></form>
    </div>
</div>
@endsection

@extends('layouts.portal')
@section('content')
<div class="workspace-layout">
    @include('portals.partials.admin-sidebar')
    <main class="workspace-content">
        <div class="ws-intro"><div><small>{{ $child->menu->module->name }} / {{ $child->menu->name }}</small><h1>{{ navName($child) }}</h1><p>{{ t('Business workspace','বিজনেস ওয়ার্কস্পেস') }}</p></div></div>
        <div class="ws-card ws-placeholder"><div class="ws-placeholder-icon">▤</div><h2>{{ navName($child) }}</h2><p>{{ t('This menu is assigned and access-checked. Its business functionality has not been built in this project yet.','এই মেনুর অ্যাক্সেস যাচাই করা হয়েছে। এই প্রজেক্টে পেজটির ব্যবসায়িক ফাংশন এখনো তৈরি হয়নি।') }}</p><a class="btn btn-outline" href="{{ route('store.admin.dashboard',['business'=>$business->code]) }}">{{ t('Back to dashboard','ড্যাশবোর্ডে ফিরুন') }}</a></div>
    </main>
</div>
@endsection

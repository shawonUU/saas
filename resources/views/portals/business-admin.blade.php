@extends('layouts.portal')
@section('content')
<div class="workspace-layout">
    @include('portals.partials.admin-sidebar')
    <main class="workspace-content">
        <div class="ws-intro"><div><small>{{ t('YOUR WORKSPACE','আপনার ওয়ার্কস্পেস') }}</small><h1>{{ t('Dashboard','ড্যাশবোর্ড') }}</h1><p>{{ t('Welcome back','স্বাগতম') }}, {{ $user->name }}</p></div><span class="ws-pill">{{ $business->category?->name ?? t('Business','বিজনেস') }}</span></div>
        <div class="ws-card"><h2>{{ t('Your business menu','আপনার ব্যবসার মেনু') }}</h2><p>{{ t('Modules and pages are controlled by your business category and the SaaS authority.','আপনার ক্যাটাগরি ও SaaS Authority অনুযায়ী মডিউল ও পেজ দেখা যাচ্ছে।') }}</p>
        <div class="ws-stats"><div><strong>{{ $modules->count() }}</strong><small>{{ t('Modules','মডিউল') }}</small></div><div><strong>{{ $modules->sum(fn($m) => $m->menus->count()) }}</strong><small>{{ t('Menus','মেনু') }}</small></div><div><strong>{{ $modules->sum(fn($m) => $m->menus->sum(fn($n) => $n->children->count())) }}</strong><small>{{ t('Pages','পেজ') }}</small></div></div></div>
        <div class="ws-card"><h2>{{ t('Quick access','দ্রুত প্রবেশ') }}</h2><div class="ws-quick">@foreach($modules as $module)@foreach($module->menus as $menu)@foreach($menu->children->take(2) as $child)<a href="{{ ($child->route === '/dashboard' ? route('store.admin.dashboard',['business'=>$business->code]) : route('store.admin.feature',['business'=>$business->code,'childMenu'=>$child->id])) }}">{{ navName($child) }} <span>↗</span></a>@endforeach @endforeach @endforeach</div></div>
    </main>
</div>
@endsection

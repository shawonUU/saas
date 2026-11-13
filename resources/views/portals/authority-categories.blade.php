@extends('layouts.portal')
@section('content')
<main class="authority-wrap"><div class="authority-head"><div><small>{{ t('SAAS AUTHORITY','SaaS অথরিটি') }}</small><h1>{{ t('Business access control','বিজনেস অ্যাক্সেস কন্ট্রোল') }}</h1><p>{{ t('Set category defaults and optional shop-specific changes.','ক্যাটাগরির ডিফল্ট ও নির্দিষ্ট শপের পরিবর্তন পরিচালনা করুন।') }}</p></div><a href="{{ route('authority.features.index') }}" class="btn">{{ t('Manage menu tree','মেনু তালিকা') }}</a></div>
<div class="ws-card"><h2>{{ t('Categories','ক্যাটাগরি') }}</h2><div class="authority-lines">@foreach($categories as $category)<a href="{{ route('authority.access.category',$category) }}"><span>{{ navName($category) }}</span><span>{{ t('Configure access','অ্যাক্সেস নির্ধারণ') }} →</span></a>@endforeach</div></div>
<div class="ws-card"><h2>{{ t('Individual shops','নির্দিষ্ট শপ') }}</h2><div class="authority-lines">@forelse($businesses as $business)<a href="{{ route('authority.access.shop',$business) }}"><span>{{ $business->name }} <small>({{ $business->category ? navName($business->category) : '' }})</small></span><span>{{ t('Overrides','ওভাররাইড') }} →</span></a>@empty<p>{{ t('No shops yet.','এখনো কোনো শপ নেই।') }}</p>@endforelse</div></div>
</main>
@endsection

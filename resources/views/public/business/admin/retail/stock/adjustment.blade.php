@extends('public.business.admin.layout')
@section('content')
<div class="ret-page">
@include('public.business.admin.retail._styles')
@include('public.business.admin.sidebar')
<div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div><main class="workspace-content">
<div class="ret-title"><div><button type="button" class="ret-btn alt ret-mobile-nav" data-ws-sidebar-toggle>☰</button><h1>{{ t('Stock Adjustment','স্টক সমন্বয়') }}</h1><p>{{ t('Business workspace','বিজনেস ওয়ার্কস্পেস') }}</p></div><div class="ret-actions"><a class="ret-btn alt" href="{{ route('store.admin.dashboard') }}">{{ t('Dashboard','ড্যাশবোর্ড') }}</a></div></div>
@if(session('success'))<div class="ret-toast">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ret-error">{{ $errors->first() }}</div>@endif
<form class="ret-card" method="POST" action="{{ route('store.admin.retail.stock.adjust') }}">@csrf<div class="ret-grid"><div class="ret-field"><label>{{ t('Variant','ভ্যারিয়েন্ট') }}</label><select class="ret-input" required name="variant_id"><option value="">—</option>@foreach($variants as $variant)<option value="{{ $variant->id }}">{{ $variant->product?->name }} / {{ $variant->sku }} ({{ $variant->stock }})</option>@endforeach</select></div><div class="ret-field"><label>{{ t('Quantity (+ adds / - removes)','পরিমাণ (+ বাড়বে / - কমবে)') }}</label><input required class="ret-input" type="number" name="quantity" step=".001" placeholder="e.g. -2 or 5"></div><div class="ret-field"><label>{{ t('Reason','কারণ') }}</label><input required class="ret-input" name="note" maxlength="255"></div></div><button class="ret-btn" style="margin-top:15px">{{ t('Post Adjustment','সমন্বয় পোস্ট') }}</button></form>
</main></div>
@endsection

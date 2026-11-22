@extends('public.business.public.ecommerce.layout')
@section('title',t('Shop','শপ'))
@section('content')
<div class="sf-page-heading"><a class="sf-back" href="{{ route('business.customer.home') }}" aria-label="Back">‹</a><h1>{{ ($categories->firstWhere('id',(int)request('category'))?->name)??t('All Products','সব পণ্য') }}</h1><span class="sf-result-count">{{ number_format($products->total()) }} {{ t('items','টি পণ্য') }}</span></div>
<form class="sf-list-toolbar" action="{{ route('business.customer.shop') }}" method="GET"><input type="hidden" name="q" value="{{ request('q') }}"><input type="hidden" name="category" value="{{ request('category') }}"><input type="hidden" name="sub_category" value="{{ request('sub_category') }}">
<label>{{ t('Sort By','সাজান') }} <select name="sort" onchange="this.form.submit()"><option value="latest" @selected(request('sort','latest')==='latest')>{{ t('Newest','নতুন') }}</option><option value="price_low" @selected(request('sort')==='price_low')>{{ t('Price: Low to High','দাম কম থেকে বেশি') }}</option><option value="price_high" @selected(request('sort')==='price_high')>{{ t('Price: High to Low','দাম বেশি থেকে কম') }}</option><option value="name" @selected(request('sort')==='name')>{{ t('Name','নাম') }}</option></select></label>
@if(request('q'))<span class="sf-search-tag">{{ request('q') }} <a href="{{ route('business.customer.shop') }}" aria-label="Clear search">×</a></span>@endif
</form>
<div class="sf-catalog-grid sf-product-grid">@forelse($products as $product)@include('public.business.public.ecommerce.partials.product-card')@empty<div class="sf-empty">{{ t('No products found. Please choose a different category.','পণ্য পাওয়া যায়নি। অন্য ক্যাটাগরি দেখুন।') }}</div>@endforelse</div>
<div class="sf-pagination">{{ $products->links() }}</div>
@endsection

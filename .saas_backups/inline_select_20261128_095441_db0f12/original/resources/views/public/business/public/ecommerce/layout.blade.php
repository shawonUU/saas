<!doctype html>
<html lang="{{ session('portal_lang','en') }}" data-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

@include('portals.partials.ui-theme-bootstrap')

@php
    $b = $business;

    $brandService = app(\App\Services\Public\Business\Admin\Settings\Business\GeneralSettings\BrandingService::class);
    $brandSettings = $brandService->find($b);
    $storeLogo = $brandService->imageUrl($b->logo);
    $storeFavicon = $brandService->imageUrl($brandSettings?->favicon);

    $categories = $categories ?? app(
        \App\Services\Public\Business\Public\Ecommerce\Catalog\Products\CatalogService::class
    )->categories($b);
@endphp

<title>@yield('title',$b->name) · {{ $b->name }}</title>
<meta name="description" content="{{ $brandSettings?->tagline ?? $b->name }}">

@if($storeFavicon)
    <link rel="icon" href="{{ $storeFavicon }}">
@endif

<link rel="stylesheet" href="{{ asset('assets/nexabiz-storefront.css') }}?v=20261126">
<link rel="stylesheet" href="{{ asset('assets/saas-searchable-select-v2.css') }}?v=20261128">

@stack('styles')
</head>

<body
    data-business-id="{{ $b->id }}"
    data-cart-url="{{ route('business.customer.cart') }}"
    data-checkout-url="{{ route('business.customer.checkout') }}"
    data-wishlist-url="{{ route('business.customer.wishlist') }}"
>

<div class="sf-app">

    @include('public.business.public.ecommerce.partials.desktop-categories')

    <div class="sf-app-main">

        @include('public.business.public.ecommerce.partials.header')

        <main class="sf-shell sf-body">
            @yield('content')
        </main>

        @include('public.business.public.ecommerce.partials.footer')

    </div>
</div>

@include('public.business.public.ecommerce.partials.sidebar')
@include('public.business.public.ecommerce.partials.mobile-bar')

<script src="{{ asset('assets/nexabiz-storefront.js') }}?v=20261126" defer></script>
<script src="{{ asset('assets/saas-searchable-select-v2.js') }}?v=20261128" defer></script>

@stack('scripts')
</body>
</html>
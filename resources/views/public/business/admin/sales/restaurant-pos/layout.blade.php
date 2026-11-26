@php
    $posLayoutBusiness = $currentBusiness ?? $business ?? request()->attributes->get('business');
    $posLayoutBusiness = $posLayoutBusiness instanceof \App\Models\Business ? $posLayoutBusiness : null;
    $posLayoutBrandingService = app(\App\Services\Public\Business\Admin\Settings\Business\GeneralSettings\BrandingService::class);
    $posLayoutBranding = $posLayoutBrandingService->find($posLayoutBusiness);
    $posLayoutFavicon = $posLayoutBrandingService->imageUrl($posLayoutBranding?->favicon);
    $posLayoutPrimary = $posLayoutBranding?->primary_color ?? '#5145e5';
    $posLayoutStoreName = t($posLayoutBusiness?->name, $posLayoutBusiness?->name_bn) ?: t('Restaurant', 'রেস্টুরেন্ট');
@endphp
<!doctype html>
<html lang="{{ session('portal_lang', 'en') }}" data-theme="light">
<head>
    <meta charset="utf-8">
    @include('portals.partials.ui-theme-bootstrap')
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ t('Restaurant POS', 'রেস্টুরেন্ট পস') }} | {{ $posLayoutStoreName }}</title>
    @if($posLayoutFavicon)<link rel="icon" href="{{ $posLayoutFavicon }}">@endif
    <style>
        :root{--primary:{{ preg_match('/^#[0-9a-fA-F]{6}$/', $posLayoutPrimary) ? $posLayoutPrimary : '#5145e5' }};--primary-dark:#4035c7;--dark:#17223a;--text:#414861;--muted:#66758d;--border:#dfe5ef;--bg:#f6f8fc;--white:#fff}
        *,*::before,*::after{box-sizing:border-box}
        html,body{margin:0;min-height:100%;width:100%}
        body{font-family:'DM Sans','Noto Sans Bengali','Nirmala UI',system-ui,sans-serif;-webkit-font-smoothing:antialiased;line-height:1.5}
        h1,h2,h3,p{margin:0}
        a{color:inherit;text-decoration:none}
        button,input,select,textarea{font:inherit}
        button{touch-action:manipulation}
    </style>
    @include('portals.partials.theme-styles')
    @include('portals.partials.ui-foundation')
    @include('public.business.admin.sales.restaurant-pos.styles')
    @stack('styles')
</head>
<body class="restaurant-pos-page">
    @yield('content')
    @include('portals.partials.ui-controls')
    @stack('scripts')
</body>
</html>

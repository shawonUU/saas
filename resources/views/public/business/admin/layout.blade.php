@php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Route;
@endphp
<!doctype html>
<html lang="{{ session('portal_lang', 'en') }}" data-theme="light">
<head>
    <meta charset="utf-8">
    @include('portals.partials.ui-theme-bootstrap')
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $routeBusiness = request()->route('business');
        $business = $currentBusiness ?? $business ?? $shop ?? request()->attributes->get('business');
        $business = is_object($business) ? $business : null;
        $storeCode = is_object($routeBusiness) ? (data_get($routeBusiness,'slug') ?? data_get($routeBusiness,'code')) : $routeBusiness;
        $storeCode = $storeCode ?: (data_get($business,'slug') ?? data_get($business,'code'));
        $storeName = data_get($business,'name') ?? data_get($business,'business_name') ?? ($storeCode ? ucwords(str_replace(['-','_'],' ',$storeCode)) : 'My Store');
        $logoPath = data_get($business,'logo') ?? data_get($business,'logo_path') ?? data_get($business,'business_logo');
        $storeLogo = $logoPath ? (preg_match('~^https?://~i',$logoPath) ? $logoPath : (str_starts_with($logoPath,'/') ? url($logoPath) : (str_starts_with($logoPath,'storage/') || str_starts_with($logoPath,'images/') ? asset($logoPath) : asset('storage/'.$logoPath)))) : null;
        $brandingService = app(\App\Services\Public\Business\Admin\Settings\Business\GeneralSettings\BrandingService::class);
        $brandSettings = $brandingService->find($business);
        $faviconUrl = $brandingService->imageUrl($brandSettings?->favicon);
        $brandColor = $brandSettings?->primary_color ?? '#5145e5';
        $adminUser = Auth::guard('business_admin')->user();
        $dashboardUrl = $storeCode && Route::has('business.admin.dashboard') ? route('business.admin.dashboard',['business'=>$storeCode]) : url('/');
        $storefrontUrl = request()->getScheme().'://'.preg_replace('/^admin\./','',request()->getHttpHost());
    @endphp
    <title>{{ $title ?? t('Dashboard','ড্যাশবোর্ড') }} | {{ $storeName }}</title>
    <meta name="description" content="{{ $storeName }} admin portal">
    @if($faviconUrl)<link rel="icon" href="{{ $faviconUrl }}">@endif

    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Manrope:wght@400;500;600;700;800&display=swap');

        :root {
            --primary: {{ preg_match('/^#[0-9a-fA-F]{6}$/',$brandColor) ? $brandColor : '#5145e5' }};
            --primary-dark: #4035c7;
            --dark: #11152b;
            --text: #414861;
            --muted: #8990a5;
            --border: #e9eaf2;
            --bg: #f8f9fd;
            --white: #ffffff;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            color: var(--dark);
            background: var(--white);
            -webkit-font-smoothing: antialiased;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button {
            font: inherit;
        }

        .site-header {
            height: 78px;
            background: rgba(255,255,255,.94);
            border-bottom: 1px solid #eff0f5;
            display: flex;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(16px);
        }

        .nav-inner {
            width: min(1180px, calc(100% - 48px));
            margin: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 30px;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-family: 'Manrope', sans-serif;
            font-size: 23px;
            font-weight: 800;
            letter-spacing: -1px;
            color: var(--dark);
        }

        .brand-mark {
            width: 36px;
            height: 36px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, #685bf5, #4035c7);
            color: white;
            border-radius: 11px;
            font-size: 20px;
            font-weight: 800;
        }

        .brand span:last-child {
            color: var(--primary);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 32px;
        }

        .nav-links a {
            font-size: 14px;
            color: #626a80;
            font-weight: 600;
            transition: .2s;
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 13px;
        }

        .language-form {
            display: inline-flex;
        }

        .language-btn {
            background: #f4f3ff;
            border: 1px solid #e9e6ff;
            color: var(--primary);
            padding: 9px 13px;
            border-radius: 9px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 700;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 12px 21px;
            border-radius: 10px;
            border: 1px solid transparent;
            background: var(--primary);
            color: white;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: .25s ease;
        }

        .btn:hover {
            background: var(--primary-dark);
            color: white;
            transform: translateY(-2px);
        }

        .btn-outline {
            background: white;
            color: var(--dark);
            border-color: var(--border);
        }

        .btn-outline:hover {
            background: #f8f7ff;
            color: var(--primary);
            border-color: #d9d4ff;
        }

        .btn-light {
            background: white;
            color: var(--primary);
        }

        .btn-light:hover {
            background: #f1efff;
            color: var(--primary-dark);
        }

        .container {
            width: min(1180px, calc(100% - 48px));
            margin: auto;
        }

        .alert {
            padding: 13px 17px;
            border-radius: 10px;
            margin: 18px auto;
            font-size: 14px;
        }

        .alert.success {
            color: #14683b;
            background: #e8fff1;
            border: 1px solid #bbefd1;
        }

        .alert.error {
            color: #9e2432;
            background: #fff0f1;
            border: 1px solid #ffd6dc;
        }

        .section {
            padding: 100px 0;
        }

        .section-heading {
            text-align: center;
            max-width: 650px;
            margin: 0 auto 55px;
        }

        .eyebrow {
            display: inline-block;
            color: var(--primary);
            background: #f0efff;
            padding: 7px 13px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .7px;
            text-transform: uppercase;
            margin-bottom: 18px;
        }

        .section-heading h2 {
            font-family: 'Manrope', sans-serif;
            font-size: clamp(29px, 4vw, 43px);
            line-height: 1.2;
            letter-spacing: -1.7px;
            font-weight: 800;
            margin-bottom: 16px;
        }

        .section-heading p {
            font-size: 16px;
            color: var(--muted);
            line-height: 1.8;
        }

        .site-footer {
            background: #11152b;
            color: white;
            padding: 65px 0 25px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr 1fr;
            gap: 55px;
            padding-bottom: 45px;
        }

        .footer-brand {
            color: white;
        }

        .footer-description {
            color: #a4a9bd;
            font-size: 14px;
            line-height: 1.8;
            max-width: 340px;
            margin-top: 18px;
        }

        .footer-column h4 {
            font-size: 14px;
            margin-bottom: 19px;
        }

        .footer-column a {
            display: block;
            font-size: 13px;
            color: #a4a9bd;
            margin: 12px 0;
            transition: .2s;
        }

        .footer-column a:hover {
            color: white;
        }

        .footer-bottom {
            border-top: 1px solid #292d42;
            padding-top: 23px;
            display: flex;
            justify-content: space-between;
            gap: 20px;
            color: #9298ae;
            font-size: 12px;
        }

        @media(max-width: 850px) {
            .nav-links {
                display: none;
            }

            .footer-grid {
                grid-template-columns: 1fr 1fr;
            }

            .footer-grid > div:first-child {
                grid-column: 1 / -1;
            }
        }

        @media(max-width: 550px) {
            .site-header {
                height: 68px;
            }

            .nav-inner,
            .container {
                width: calc(100% - 32px);
            }

            .brand {
                font-size: 20px;
            }

            .brand-mark {
                width: 32px;
                height: 32px;
            }

            .nav-actions {
                gap: 7px;
            }

            .nav-actions .btn {
                padding: 10px 12px;
                font-size: 12px;
            }

            .language-btn {
                padding: 9px;
            }

            .section {
                padding: 70px 0;
            }

            .footer-grid {
                grid-template-columns: 1fr 1fr;
                gap: 30px 15px;
            }

            .footer-bottom {
                flex-direction: column;
            }
        }
        /* Store admin header */
        body{background:var(--bg)}
        .site-header{height:76px;background:var(--white)}
        .nav-inner{width:calc(100% - 48px);max-width:1600px;gap:20px}
        .brand{min-width:0;gap:12px;font-size:17px;letter-spacing:-.35px}
        .brand-logo{width:43px;height:43px;object-fit:contain;border:1px solid var(--border);border-radius:12px;background:#fff;padding:3px;flex-shrink:0}
        .brand-fallback{display:grid;place-items:center;background:var(--primary);color:#fff;font-size:21px;font-weight:800}
        .brand-details{display:flex;flex-direction:column;min-width:0;line-height:1.3}
        .brand-name{max-width:240px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .brand-subtitle{font-size:11px;color:var(--muted);font-weight:600;letter-spacing:0;margin-top:2px}
        .nav-links{gap:8px;margin-left:auto}
        .nav-links a{padding:11px 14px;border-radius:10px;white-space:nowrap}
        .nav-links a.active,.nav-links a:hover{color:var(--primary);background:#f4f3ff}
        .nav-actions{gap:10px}
        .admin-profile{display:flex;align-items:center;gap:9px;padding-left:12px;border-left:1px solid var(--border)}
        .admin-avatar{width:35px;height:35px;border-radius:50%;display:grid;place-items:center;background:#e9e7ff;color:var(--primary);font-weight:800}
        .admin-name{max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:13px;font-weight:700}
        .admin-role{font-size:11px;color:var(--muted)}
        .admin-logout{border:1px solid var(--border);border-radius:9px;background:#fff;padding:9px 11px;cursor:pointer;color:var(--text)}
        .admin-logout:hover{background:#fff0f1;color:#b42335}
        .admin-main{min-height:calc(100vh - 76px)}
        .nav-actions .mobile-dashboard{display:none}
        @media(max-width:1000px){.nav-links{display:none}.nav-actions .mobile-dashboard{display:inline-flex;padding:9px 12px}}
        @media(max-width:700px){.nav-inner{width:calc(100% - 24px);gap:10px}.site-header{height:68px}.brand-logo{height:36px;width:36px}.brand-name{max-width:130px;font-size:14px}.brand-subtitle,.admin-name,.admin-role,.nav-actions .mobile-dashboard{display:none}.admin-profile{padding-left:0;border-left:0}.nav-actions{gap:6px}.language-btn{padding:8px}.admin-logout{padding:8px}.admin-main{min-height:calc(100vh - 68px)}}
    </style>

    @include('portals.partials.theme-styles')
    @include('public.business.admin.settings.business.profile.partials.header-styles')
    @stack('styles')
    @include('portals.partials.ui-foundation')
</head>

<body>

@include('public.business.admin.settings.business.profile.partials.header')

@if(session('success'))
    <div class="container">
        <div class="alert success">{{ session('success') }}</div>
    </div>
@endif

@if($errors->any())
    <div class="container">
        <div class="alert error">{{ $errors->first() }}</div>
    </div>
@endif

<main class="admin-main">@yield('content')</main>

@include('portals.partials.ui-controls')
@include('public.business.admin.settings.business.profile.partials.header-script')
@stack('scripts')

</body>
</html>

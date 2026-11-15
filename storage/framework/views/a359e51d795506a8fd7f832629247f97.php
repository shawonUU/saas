
<!doctype html>
<html lang="<?php echo e(session('portal_lang', 'en')); ?>" data-theme="light">
<head>
    <meta charset="utf-8">
    <script>try{document.documentElement.dataset.theme=localStorage.getItem('store-admin-theme')==='dark'?'dark':'light'}catch(e){}</script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <?php
        $routeBusiness = request()->route('business');
        $store = $currentBusiness ?? $business ?? $shop ?? request()->attributes->get('business');
        $store = is_object($store) ? $store : null;
        $storeCode = is_object($routeBusiness) ? (data_get($routeBusiness,'slug') ?? data_get($routeBusiness,'code')) : $routeBusiness;
        $storeCode = $storeCode ?: (data_get($store,'slug') ?? data_get($store,'code'));
        $storeName = data_get($store,'name') ?? data_get($store,'business_name') ?? ($storeCode ? ucwords(str_replace(['-','_'],' ',$storeCode)) : 'My Store');
        $logoPath = data_get($store,'logo') ?? data_get($store,'logo_path') ?? data_get($store,'business_logo');
        $storeLogo = $logoPath ? (preg_match('~^https?://~i',$logoPath) ? $logoPath : (str_starts_with($logoPath,'/') ? url($logoPath) : (str_starts_with($logoPath,'storage/') || str_starts_with($logoPath,'images/') ? asset($logoPath) : asset('storage/'.$logoPath)))) : null;
        $adminUser = Auth::guard('business_admin')->user();
        $dashboardUrl = $storeCode && Route::has('store.admin.dashboard') ? route('store.admin.dashboard',['business'=>$storeCode]) : url('/');
        $storefrontUrl = request()->getScheme().'://'.preg_replace('/^admin\./','',request()->getHttpHost());
    ?>
    <title><?php echo e($title ?? t('Dashboard','ড্যাশবোর্ড')); ?> | <?php echo e($storeName); ?></title>
    <meta name="description" content="<?php echo e($storeName); ?> admin portal">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Manrope:wght@400;500;600;700;800&display=swap');

        :root {
            --primary: #5145e5;
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

    <?php echo $__env->make('portals.partials.theme-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>

<body>

<header class="site-header">
    <div class="nav-inner">
        <a href="<?php echo e($dashboardUrl); ?>" class="brand" title="<?php echo e($storeName); ?>">
            <?php if($storeLogo): ?>
                <img src="<?php echo e($storeLogo); ?>" alt="<?php echo e($storeName); ?>" class="brand-logo" onerror="this.style.display='none'">
            <?php else: ?>
                <span class="brand-logo brand-fallback"><?php echo e(mb_strtoupper(mb_substr($storeName,0,1))); ?></span>
            <?php endif; ?>
            <span class="brand-details">
                <span class="brand-name"><?php echo e($storeName); ?></span>
                <span class="brand-subtitle"><?php echo e(t('Store Administration','স্টোর অ্যাডমিন')); ?></span>
            </span>
        </a>
        <nav class="nav-links" aria-label="Store navigation">
            <a class="<?php echo e(request()->routeIs('store.admin.dashboard') ? 'active' : ''); ?>" href="<?php echo e($dashboardUrl); ?>"><?php echo e(t('Dashboard','ড্যাশবোর্ড')); ?></a>
            <?php if(Route::has('store.admin.products.index')): ?>
                <a href="<?php echo e(route('store.admin.products.index',['business'=>$storeCode])); ?>"><?php echo e(t('Products','পণ্য')); ?></a>
            <?php endif; ?>
            <?php if(Route::has('store.admin.orders.index')): ?>
                <a href="<?php echo e(route('store.admin.orders.index',['business'=>$storeCode])); ?>"><?php echo e(t('Orders','অর্ডার')); ?></a>
            <?php endif; ?>
            <?php if(Route::has('store.admin.settings.index')): ?>
                <a href="<?php echo e(route('store.admin.settings.index',['business'=>$storeCode])); ?>"><?php echo e(t('Settings','সেটিংস')); ?></a>
            <?php endif; ?>
            <a href="<?php echo e($storefrontUrl); ?>" target="_blank" rel="noopener noreferrer"><?php echo e(t('View Store','স্টোর দেখুন')); ?> ↗</a>
        </nav>
        <div class="nav-actions">
            <a href="<?php echo e($dashboardUrl); ?>" class="btn btn-outline mobile-dashboard"><?php echo e(t('Dashboard','ড্যাশবোর্ড')); ?></a>
            <button type="button" id="theme-toggle" class="theme-btn" aria-label="<?php echo e(t('Toggle light and dark mode','লাইট ও ডার্ক মোড বদলান')); ?>" aria-pressed="false">◐ <span id="theme-label"><?php echo e(t('Dark','ডার্ক')); ?></span></button>
            <form class="language-form" action="<?php echo e(url('/language')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="lang" value="<?php echo e(session('portal_lang','en') === 'en' ? 'bn' : 'en'); ?>">
                <button class="language-btn" type="submit"><?php echo e(session('portal_lang','en') === 'en' ? 'বাংলা' : 'EN'); ?></button>
            </form>
            <?php if($adminUser): ?>
                <div class="admin-profile">
                    <span class="admin-avatar"><?php echo e(mb_strtoupper(mb_substr($adminUser->name ?? 'A',0,1))); ?></span>
                    <span><span class="admin-name" style="display:block"><?php echo e($adminUser->name ?? 'Admin'); ?></span><span class="admin-role"><?php echo e(t('Administrator','অ্যাডমিন')); ?></span></span>
                </div>
                <?php if(Route::has('store.admin.logout')): ?>
                    <form method="POST" action="<?php echo e(route('store.admin.logout',['business'=>$storeCode])); ?>"><?php echo csrf_field(); ?><button type="submit" class="admin-logout"><?php echo e(t('Log out','লগআউট')); ?></button></form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</header>

<?php if(session('success')): ?>
    <div class="container">
        <div class="alert success"><?php echo e(session('success')); ?></div>
    </div>
<?php endif; ?>

<?php if($errors->any()): ?>
    <div class="container">
        <div class="alert error"><?php echo e($errors->first()); ?></div>
    </div>
<?php endif; ?>

<main class="admin-main"><?php echo $__env->yieldContent('content'); ?></main>

<script>
(function(){const btn=document.getElementById('theme-toggle');const label=document.getElementById('theme-label');const light=<?php echo json_encode(t('Light', 'লাইট'), 512) ?>;const dark=<?php echo json_encode(t('Dark', 'ডার্ক'), 512) ?>;function refresh(){const isDark=document.documentElement.dataset.theme==='dark';btn.setAttribute('aria-pressed',isDark?'true':'false');label.textContent=isDark?light:dark;}btn.addEventListener('click',function(){const next=document.documentElement.dataset.theme==='dark'?'light':'dark';document.documentElement.dataset.theme=next;try{localStorage.setItem('store-admin-theme',next)}catch(e){}refresh()});refresh()})();
</script>
<?php echo $__env->yieldPushContent('scripts'); ?>

</body>
</html>
<?php /**PATH C:\laragon\www\saas-backend\resources\views/public/business/admin/layout.blade.php ENDPATH**/ ?>
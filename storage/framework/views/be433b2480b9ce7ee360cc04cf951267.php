
<!doctype html>
<html lang="<?php echo e(session('portal_lang', 'en')); ?>" data-theme="light">
<head>
    <meta charset="utf-8">
    <script>try{document.documentElement.dataset.theme=localStorage.getItem('nexabiz-theme')==='dark'?'dark':'light'}catch(e){}</script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e($title ?? 'NexaBiz - Business Management Platform'); ?></title>

    <meta name="description" content="NexaBiz helps businesses manage operations, branches, sales, inventory and customers from one platform.">

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
    </style>

    <?php echo $__env->make('portals.partials.theme-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>

<body>

<header class="site-header">
    <div class="nav-inner">

        <a href="<?php echo e(url('/')); ?>" class="brand">
            <span class="brand-mark">N</span>
            Nexa<span>Biz</span>
        </a>

        <nav class="nav-links">
            <a href="<?php echo e(url('/')); ?>"><?php echo e(t('Home', 'হোম')); ?></a>
            <a href="<?php echo e(url('/#features')); ?>"><?php echo e(t('Features', 'ফিচার')); ?></a>
            <a href="<?php echo e(url('/#how-it-works')); ?>"><?php echo e(t('How it works', 'যেভাবে কাজ করে')); ?></a>
            <a href="<?php echo e(url('/#about')); ?>"><?php echo e(t('About', 'আমাদের সম্পর্কে')); ?></a>
        </nav>

        <div class="nav-actions">
            <button type="button" id="theme-toggle" class="theme-btn" aria-label="<?php echo e(t('Toggle light and dark mode','লাইট ও ডার্ক মোড বদলান')); ?>" aria-pressed="false">◐ <span id="theme-label"><?php echo e(t('Dark','ডার্ক')); ?></span></button>

            <form class="language-form" action="<?php echo e(url('/language')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="lang"
                       value="<?php echo e(session('portal_lang', 'en') === 'en' ? 'bn' : 'en'); ?>">

                <button class="language-btn" type="submit">
                    <?php echo e(session('portal_lang', 'en') === 'en' ? 'বাংলা' : 'EN'); ?>

                </button>
            </form>

            <?php
                $businessCode = request()->route('business');
                $portalDashboard = Auth::guard('authority')->check() && request()->getHost() === 'admin.'.config('saas.domain') ? route('authority.dashboard') :
                    (Auth::guard('business_admin')->check() && $businessCode && str_starts_with(request()->getHost(),'admin.') ? route('store.admin.dashboard',['business'=>$businessCode]) :
                    (Auth::guard('business_customer')->check() && $businessCode ? route('store.customer.dashboard',['business'=>$businessCode]) :
                    (Auth::guard('web')->check() ? route('saas.dashboard') : null)));
            ?>
            <?php if($portalDashboard): ?>
                <a href="<?php echo e($portalDashboard); ?>" class="btn"><?php echo e(t('Dashboard','ড্যাশবোর্ড')); ?></a>
            <?php else: ?>
                <a href="<?php echo e(route('saas.login')); ?>" class="btn btn-outline">
                    <?php echo e(t('Log in', 'লগইন')); ?>

                </a>

                <a href="<?php echo e(route('saas.register')); ?>" class="btn">
                    <?php echo e(t('Get Started', 'শুরু করুন')); ?>

                </a>
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

<?php echo $__env->yieldContent('content'); ?>

<footer class="site-footer">
    <div class="container">

        <div class="footer-grid">

            <div>
                <a href="<?php echo e(url('/')); ?>" class="brand footer-brand">
                    <span class="brand-mark">N</span>
                    Nexa<span>Biz</span>
                </a>

                <p class="footer-description">
                    <?php echo e(t(
                        'A smarter way to manage your business. Simplify operations, grow your business and manage everything from one platform.',
                        'আপনার ব্যবসা পরিচালনার আরও স্মার্ট উপায়। একটি প্ল্যাটফর্ম থেকেই ব্যবসার কার্যক্রম পরিচালনা করুন এবং ব্যবসাকে এগিয়ে নিন।'
                    )); ?>

                </p>
            </div>

            <div class="footer-column">
                <h4><?php echo e(t('Platform', 'প্ল্যাটফর্ম')); ?></h4>
                <a href="<?php echo e(url('/#features')); ?>"><?php echo e(t('Features', 'ফিচার')); ?></a>
                <a href="<?php echo e(url('/#how-it-works')); ?>"><?php echo e(t('How it works', 'যেভাবে কাজ করে')); ?></a>
                <a href="<?php echo e(route('saas.register')); ?>"><?php echo e(t('Create account', 'অ্যাকাউন্ট তৈরি করুন')); ?></a>
            </div>

            <div class="footer-column">
                <h4><?php echo e(t('Account', 'অ্যাকাউন্ট')); ?></h4>
                <a href="<?php echo e(route('saas.login')); ?>"><?php echo e(t('Log in', 'লগইন')); ?></a>
                <a href="<?php echo e(route('saas.register')); ?>"><?php echo e(t('Register', 'রেজিস্ট্রেশন')); ?></a>
            </div>

        </div>

        <div class="footer-bottom">
            <span>© <?php echo e(date('Y')); ?> <?php echo e(config('app.name')); ?> <?php echo e(t('All rights reserved.', 'সর্বস্বত্ব সংরক্ষিত।')); ?></span>
            <span><?php echo e(t('Built for growing businesses.', 'ব্যবসার উন্নতির জন্য তৈরি।')); ?></span>
        </div>

    </div>
</footer>

<script>
(function(){const btn=document.getElementById('theme-toggle');const label=document.getElementById('theme-label');const light=<?php echo json_encode(t('Light', 'লাইট'), 512) ?>;const dark=<?php echo json_encode(t('Dark', 'ডার্ক'), 512) ?>;function refresh(){const isDark=document.documentElement.dataset.theme==='dark';btn.setAttribute('aria-pressed',isDark?'true':'false');label.textContent=isDark?light:dark;}btn.addEventListener('click',function(){const next=document.documentElement.dataset.theme==='dark'?'light':'dark';document.documentElement.dataset.theme=next;try{localStorage.setItem('nexabiz-theme',next)}catch(e){}refresh()});refresh()})();
</script>
<?php echo $__env->yieldPushContent('scripts'); ?>

</body>
</html>
<?php /**PATH C:\laragon\www\saas-backend\resources\views/public/layout.blade.php ENDPATH**/ ?>
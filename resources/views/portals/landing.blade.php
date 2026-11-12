
@extends('layouts.portal')

@section('content')

@push('styles')
<style>
    .hero {
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(circle at 85% 20%, rgba(116,101,255,.12), transparent 30%),
            radial-gradient(circle at 5% 90%, rgba(70,111,255,.08), transparent 30%),
            #fafaff;
        padding: 95px 0 105px;
    }

    .hero-inner {
        display: grid;
        grid-template-columns: 1fr 1.05fr;
        align-items: center;
        gap: 65px;
    }

    .hero-content {
        position: relative;
        z-index: 2;
    }

    .hero-label {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        background: white;
        border: 1px solid #e9e6ff;
        border-radius: 30px;
        padding: 9px 15px;
        font-size: 12px;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 25px;
        box-shadow: 0 5px 20px rgba(48,42,100,.04);
    }

    .hero-label span {
        width: 7px;
        height: 7px;
        background: #36c98b;
        border-radius: 50%;
    }

    .hero h1 {
        font-family: 'Manrope', sans-serif;
        font-size: clamp(39px, 5vw, 61px);
        line-height: 1.13;
        letter-spacing: -2.8px;
        font-weight: 800;
        margin-bottom: 24px;
    }

    .hero h1 strong {
        color: var(--primary);
        display: block;
    }

    .hero-description {
        max-width: 500px;
        color: #777f96;
        font-size: 17px;
        line-height: 1.85;
        margin-bottom: 32px;
    }

    .hero-buttons {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 13px;
    }

    .hero-buttons .btn {
        padding: 15px 24px;
    }

    .hero-note {
        display: flex;
        align-items: center;
        gap: 9px;
        margin-top: 25px;
        font-size: 12px;
        color: #9299ac;
    }

    .hero-note svg {
        color: #20a879;
        flex-shrink: 0;
    }

    /* Dashboard Preview */

    .preview-wrapper {
        position: relative;
        padding: 15px 0 15px 15px;
    }

    .preview-glow {
        position: absolute;
        width: 80%;
        height: 80%;
        right: 0;
        top: 10%;
        background: #d9d5ff;
        filter: blur(75px);
        opacity: .65;
        z-index: 0;
    }

    .dashboard-preview {
        position: relative;
        z-index: 1;
        background: white;
        border: 1px solid #e8e8f1;
        border-radius: 17px;
        overflow: hidden;
        box-shadow: 0 30px 90px rgba(39,37,95,.13);
        transform: perspective(1200px) rotateY(-4deg);
    }

    .preview-topbar {
        height: 55px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 17px;
        border-bottom: 1px solid #eff0f5;
    }

    .preview-logo {
        font-size: 13px;
        font-weight: 800;
        color: #343b59;
    }

    .preview-dots {
        display: flex;
        gap: 5px;
    }

    .preview-dots span {
        width: 7px;
        height: 7px;
        background: #dddfea;
        border-radius: 50%;
    }

    .preview-user {
        width: 25px;
        height: 25px;
        border-radius: 50%;
        background: #e9e6ff;
    }

    .preview-body {
        display: grid;
        grid-template-columns: 125px 1fr;
        min-height: 330px;
    }

    .preview-sidebar {
        padding: 19px 12px;
        background: #fafaff;
        border-right: 1px solid #eff0f5;
    }

    .side-item {
        padding: 9px 10px;
        border-radius: 7px;
        font-size: 9px;
        color: #8a90a5;
        margin-bottom: 8px;
        font-weight: 600;
    }

    .side-item.active {
        background: #eeecff;
        color: #5145e5;
    }

    .preview-main {
        padding: 22px 17px;
        background: #fff;
    }

    .preview-heading {
        font-size: 15px;
        font-weight: 800;
        margin-bottom: 5px;
        color: #202741;
    }

    .preview-subheading {
        font-size: 9px;
        color: #a0a5b6;
        margin-bottom: 18px;
    }

    .stat-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 9px;
        margin-bottom: 15px;
    }

    .stat-card {
        border: 1px solid #eff0f5;
        border-radius: 9px;
        padding: 12px 9px;
    }

    .stat-card small {
        display: block;
        color: #969caf;
        font-size: 8px;
        margin-bottom: 8px;
    }

    .stat-card strong {
        display: block;
        font-size: 15px;
        color: #252b45;
    }

    .stat-card span {
        font-size: 8px;
        color: #1da778;
        display: block;
        margin-top: 5px;
    }

    .chart-card {
        border: 1px solid #eff0f5;
        border-radius: 9px;
        padding: 14px;
    }

    .chart-title {
        font-size: 10px;
        font-weight: 800;
        margin-bottom: 15px;
    }

    .chart {
        height: 95px;
        display: flex;
        align-items: end;
        gap: 10px;
        border-bottom: 1px solid #eeeef4;
        background: repeating-linear-gradient(
            to bottom,
            transparent 0,
            transparent 30px,
            #f4f4f8 31px
        );
    }

    .chart-bar {
        flex: 1;
        background: linear-gradient(180deg, #877cff, #5145e5);
        border-radius: 4px 4px 0 0;
        min-width: 8px;
    }

    .chart-labels {
        display: flex;
        justify-content: space-between;
        color: #a1a6b6;
        font-size: 8px;
        padding-top: 7px;
    }

    .floating-card {
        position: absolute;
        z-index: 2;
        bottom: -12px;
        left: -35px;
        background: white;
        border: 1px solid #eeedf5;
        border-radius: 12px;
        padding: 13px 17px;
        box-shadow: 0 12px 35px rgba(34,32,75,.12);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .floating-icon {
        width: 35px;
        height: 35px;
        display: grid;
        place-items: center;
        background: #e8fff3;
        color: #16a36d;
        border-radius: 9px;
        font-size: 17px;
        font-weight: 800;
    }

    .floating-card strong {
        display: block;
        font-size: 12px;
    }

    .floating-card small {
        display: block;
        font-size: 10px;
        color: #9299aa;
        margin-top: 3px;
    }

    /* Trust Section */

    .trust-section {
        padding: 37px 0;
        border-top: 1px solid #f0f0f5;
        border-bottom: 1px solid #f0f0f5;
        background: white;
    }

    .trust-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 25px;
        flex-wrap: wrap;
    }

    .trust-text {
        color: #9298aa;
        font-size: 13px;
        font-weight: 600;
    }

    .trust-items {
        display: flex;
        gap: 35px;
        align-items: center;
        flex-wrap: wrap;
    }

    .trust-items span {
        font-family: 'Manrope', sans-serif;
        color: #9ba0b0;
        font-size: 15px;
        font-weight: 800;
        letter-spacing: -.5px;
    }

    /* Features */

    .features-section {
        background: white;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
    }

    .feature-card {
        padding: 30px;
        border: 1px solid #ececf3;
        border-radius: 15px;
        background: white;
        transition: .3s ease;
    }

    .feature-card:hover {
        border-color: #d8d3ff;
        box-shadow: 0 18px 45px rgba(50,43,110,.07);
        transform: translateY(-5px);
    }

    .feature-icon {
        width: 49px;
        height: 49px;
        display: grid;
        place-items: center;
        background: #f0efff;
        color: var(--primary);
        border-radius: 13px;
        margin-bottom: 23px;
    }

    .feature-icon svg {
        width: 23px;
        height: 23px;
    }

    .feature-card h3 {
        font-size: 17px;
        margin-bottom: 11px;
        font-weight: 800;
    }

    .feature-card p {
        color: #858ca0;
        font-size: 13px;
        line-height: 1.8;
    }

    /* How it works */

    .process-section {
        background: #f8f9fd;
    }

    .process-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 25px;
    }

    .process-card {
        text-align: center;
        padding: 28px 25px;
    }

    .process-number {
        width: 55px;
        height: 55px;
        display: grid;
        place-items: center;
        border-radius: 17px;
        margin: 0 auto 23px;
        background: #eeecff;
        color: var(--primary);
        font-size: 19px;
        font-weight: 800;
    }

    .process-card h3 {
        font-size: 17px;
        margin-bottom: 12px;
    }

    .process-card p {
        font-size: 13px;
        line-height: 1.8;
        color: #858ca0;
    }

    /* About */

    .about-section {
        background: white;
    }

    .about-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 75px;
        align-items: center;
    }

    .about-visual {
        background: linear-gradient(135deg, #f0efff, #f8f7ff);
        padding: 38px;
        border-radius: 22px;
        position: relative;
        overflow: hidden;
    }

    .about-box {
        background: white;
        border: 1px solid #e9e7f7;
        padding: 24px;
        border-radius: 15px;
        box-shadow: 0 15px 40px rgba(47,39,100,.07);
        margin-bottom: 14px;
    }

    .about-box:last-child {
        margin-bottom: 0;
    }

    .about-box small {
        color: #9298aa;
        font-size: 11px;
    }

    .about-box strong {
        display: block;
        font-size: 20px;
        margin-top: 7px;
    }

    .about-content h2 {
        font-family: 'Manrope', sans-serif;
        font-size: clamp(29px, 4vw, 42px);
        line-height: 1.25;
        letter-spacing: -1.5px;
        margin: 17px 0;
    }

    .about-content > p {
        color: #858ca0;
        line-height: 1.9;
        font-size: 14px;
    }

    .about-points {
        margin-top: 27px;
        display: grid;
        gap: 16px;
    }

    .about-point {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 14px;
        font-weight: 600;
        color: #555e76;
    }

    .check-icon {
        width: 23px;
        height: 23px;
        display: grid;
        place-items: center;
        border-radius: 50%;
        background: #e7fff3;
        color: #13a36c;
        font-size: 12px;
        font-weight: 800;
        flex-shrink: 0;
    }

    /* CTA */

    .cta-section {
        padding: 35px 0 100px;
        background: white;
    }

    .cta-box {
        position: relative;
        overflow: hidden;
        background: linear-gradient(120deg, #5145e5, #392db9);
        padding: 65px 35px;
        text-align: center;
        border-radius: 23px;
        color: white;
    }

    .cta-box:before,
    .cta-box:after {
        content: "";
        position: absolute;
        border: 1px solid rgba(255,255,255,.12);
        border-radius: 50%;
        width: 280px;
        height: 280px;
    }

    .cta-box:before {
        left: -120px;
        top: -170px;
    }

    .cta-box:after {
        right: -110px;
        bottom: -190px;
    }

    .cta-box h2 {
        position: relative;
        z-index: 1;
        font-family: 'Manrope', sans-serif;
        font-size: clamp(28px, 4vw, 42px);
        letter-spacing: -1.5px;
        margin-bottom: 15px;
    }

    .cta-box p {
        position: relative;
        z-index: 1;
        color: #e0ddff;
        font-size: 14px;
        line-height: 1.8;
        margin-bottom: 27px;
    }

    .cta-box .btn {
        position: relative;
        z-index: 1;
    }

    @media(max-width: 950px) {
        .hero-inner {
            grid-template-columns: 1fr;
            gap: 55px;
        }

        .hero-content {
            max-width: 700px;
        }

        .preview-wrapper {
            max-width: 650px;
            margin: auto;
            width: 100%;
        }

        .features-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .about-grid {
            gap: 35px;
        }
    }

    @media(max-width: 650px) {
        .hero {
            padding: 65px 0 75px;
        }

        .hero h1 {
            letter-spacing: -1.8px;
        }

        .hero-description {
            font-size: 15px;
        }

        .preview-wrapper {
            padding-left: 0;
        }

        .dashboard-preview {
            transform: none;
        }

        .preview-body {
            grid-template-columns: 90px 1fr;
            min-height: 280px;
        }

        .preview-sidebar {
            padding: 14px 7px;
        }

        .side-item {
            font-size: 8px;
            padding: 8px 5px;
        }

        .preview-main {
            padding: 17px 9px;
        }

        .stat-grid {
            gap: 5px;
        }

        .stat-card {
            padding: 9px 6px;
        }

        .stat-card strong {
            font-size: 11px;
        }

        .floating-card {
            left: -5px;
            bottom: -28px;
        }

        .trust-content {
            justify-content: center;
            text-align: center;
        }

        .trust-items {
            justify-content: center;
            gap: 18px;
        }

        .trust-items span {
            font-size: 12px;
        }

        .features-grid,
        .process-grid,
        .about-grid {
            grid-template-columns: 1fr;
        }

        .feature-card {
            padding: 25px;
        }

        .about-visual {
            padding: 22px;
        }

        .cta-box {
            padding: 50px 20px;
        }
    }
</style>
@endpush

{{-- HERO SECTION --}}

<section class="hero">
    <div class="container hero-inner">

        <div class="hero-content">

            <div class="hero-label">
                <span></span>
                {{ t('ONE PLATFORM. COMPLETE CONTROL.', 'একটি প্ল্যাটফর্ম। সম্পূর্ণ নিয়ন্ত্রণ।') }}
            </div>

            <h1>
                {{ t('Run Your Business', 'আপনার ব্যবসা পরিচালনা করুন') }}
                <strong>{{ t('Smarter & Better.', 'আরও স্মার্টভাবে।') }}</strong>
            </h1>

            <p class="hero-description">
                {{ t(
                    'Everything your business needs in one place. Manage operations, branches, sales, inventory and customers with a powerful business management platform.',
                    'আপনার ব্যবসার প্রয়োজনীয় সবকিছু একটি জায়গায়। শক্তিশালী বিজনেস ম্যানেজমেন্ট প্ল্যাটফর্মের মাধ্যমে পরিচালনা করুন আপনার ব্যবসা, শাখা, বিক্রয়, ইনভেন্টরি এবং কাস্টমার।'
                ) }}
            </p>

            <div class="hero-buttons">

                @if(isset($guard) && Auth::guard($guard)->check())
                    <a href="{{ route('saas.dashboard') }}" class="btn">
                        {{ t('Go to Dashboard', 'ড্যাশবোর্ডে যান') }}
                        <span>→</span>
                    </a>
                @else
                    <a href="{{ route('saas.register') }}" class="btn">
                        {{ t('Get Started Free', 'বিনামূল্যে শুরু করুন') }}
                        <span>→</span>
                    </a>

                    <a href="{{ route('saas.login') }}" class="btn btn-outline">
                        {{ t('Sign In', 'লগইন করুন') }}
                    </a>
                @endif

            </div>

            <div class="hero-note">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 6 9 17l-5-5"/>
                </svg>
                {{ t('Simple setup. Powerful management. Built to grow.', 'সহজ সেটআপ। শক্তিশালী ব্যবস্থাপনা। ব্যবসার উন্নতির জন্য তৈরি।') }}
            </div>

        </div>

        {{-- Dashboard Preview --}}

        <div class="preview-wrapper">

            <div class="preview-glow"></div>

            <div class="dashboard-preview">

                <div class="preview-topbar">
                    <div class="preview-dots">
                        <span></span><span></span><span></span>
                    </div>

                    <div class="preview-logo">NexaBiz Dashboard</div>

                    <div class="preview-user"></div>
                </div>

                <div class="preview-body">

                    <aside class="preview-sidebar">
                        <div class="side-item active">Overview</div>
                        <div class="side-item">Businesses</div>
                        <div class="side-item">Branches</div>
                        <div class="side-item">Products</div>
                        <div class="side-item">Customers</div>
                        <div class="side-item">Reports</div>
                        <div class="side-item">Settings</div>
                    </aside>

                    <div class="preview-main">

                        <div class="preview-heading">Business Overview</div>
                        <div class="preview-subheading">Welcome back! Here's your business summary.</div>

                        <div class="stat-grid">

                            <div class="stat-card">
                                <small>Total Sales</small>
                                <strong>$24,850</strong>
                                <span>+12.5%</span>
                            </div>

                            <div class="stat-card">
                                <small>Orders</small>
                                <strong>1,284</strong>
                                <span>+8.2%</span>
                            </div>

                            <div class="stat-card">
                                <small>Customers</small>
                                <strong>856</strong>
                                <span>+16.4%</span>
                            </div>

                        </div>

                        <div class="chart-card">

                            <div class="chart-title">Sales Performance</div>

                            <div class="chart">
                                <div class="chart-bar" style="height:35%"></div>
                                <div class="chart-bar" style="height:52%"></div>
                                <div class="chart-bar" style="height:43%"></div>
                                <div class="chart-bar" style="height:68%"></div>
                                <div class="chart-bar" style="height:55%"></div>
                                <div class="chart-bar" style="height:82%"></div>
                                <div class="chart-bar" style="height:72%"></div>
                                <div class="chart-bar" style="height:95%"></div>
                            </div>

                            <div class="chart-labels">
                                <span>Jan</span>
                                <span>Feb</span>
                                <span>Mar</span>
                                <span>Apr</span>
                                <span>May</span>
                                <span>Jun</span>
                            </div>

                        </div>

                    </div>
                </div>

            </div>

            <div class="floating-card">
                <div class="floating-icon">✓</div>
                <div>
                    <strong>Business Organized</strong>
                    <small>Everything in one place</small>
                </div>
            </div>

        </div>

    </div>
</section>

{{-- TRUST SECTION --}}

<section class="trust-section">
    <div class="container trust-content">

        <div class="trust-text">
            {{ t('ONE PLATFORM FOR EVERY BUSINESS NEED', 'ব্যবসার প্রতিটি প্রয়োজনে একটি প্ল্যাটফর্ম') }}
        </div>

        <div class="trust-items">
            <span>RETAIL</span>
            <span>WHOLESALE</span>
            <span>RESTAURANT</span>
            <span>ECOMMERCE</span>
            <span>SERVICES</span>
        </div>

    </div>
</section>

{{-- FEATURES SECTION --}}

<section class="section features-section" id="features">

    <div class="container">

        <div class="section-heading">
            <span class="eyebrow">{{ t('POWERFUL FEATURES', 'শক্তিশালী ফিচার') }}</span>

            <h2>
                {{ t('Everything You Need to', 'আপনার ব্যবসা পরিচালনায়') }}
                {{ t('Manage Your Business', 'প্রয়োজনীয় সবকিছু') }}
            </h2>

            <p>
                {{ t(
                    'A complete set of tools designed to simplify your daily business operations and help you focus on growth.',
                    'আপনার প্রতিদিনের ব্যবসায়িক কার্যক্রম সহজ করতে এবং ব্যবসার উন্নতিতে মনোযোগ দিতে প্রয়োজনীয় সব টুলস।'
                ) }}
            </p>
        </div>

        <div class="features-grid">

            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="3" y="3" width="18" height="18" rx="3"/>
                        <path d="M3 9h18M9 21V9"/>
                    </svg>
                </div>
                <h3>{{ t('Business Management', 'ব্যবসা ব্যবস্থাপনা') }}</h3>
                <p>{{ t('Manage your business information, operations and activities from a centralized dashboard.', 'একটি ড্যাশবোর্ড থেকেই আপনার ব্যবসার তথ্য, কার্যক্রম এবং পরিচালনা নিয়ন্ত্রণ করুন।') }}</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6"/>
                    </svg>
                </div>
                <h3>{{ t('Multi-Branch Management', 'একাধিক শাখা ব্যবস্থাপনা') }}</h3>
                <p>{{ t('Organize multiple branches and manage business activities with better control.', 'একাধিক শাখা তৈরি করুন এবং সব শাখার কার্যক্রম সহজে পরিচালনা করুন।') }}</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M3 3h2l2.4 12.5a2 2 0 0 0 2 1.5h8.8a2 2 0 0 0 2-1.5L22 8H6"/>
                        <circle cx="10" cy="21" r="1"/>
                        <circle cx="18" cy="21" r="1"/>
                    </svg>
                </div>
                <h3>{{ t('Sales & Orders', 'বিক্রয় ও অর্ডার') }}</h3>
                <p>{{ t('Keep track of sales, orders and business transactions in an organized system.', 'বিক্রয়, অর্ডার এবং ব্যবসায়িক লেনদেন একটি সুশৃঙ্খল সিস্টেমে পরিচালনা করুন।') }}</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M4 7h16v13H4zM4 11h16M8 7V4h8v3"/>
                        <path d="M8 15h3M8 18h6"/>
                    </svg>
                </div>
                <h3>{{ t('Inventory Management', 'ইনভেন্টরি ব্যবস্থাপনা') }}</h3>
                <p>{{ t('Monitor products, stock levels and inventory movements with greater accuracy.', 'পণ্য, স্টক এবং ইনভেন্টরির পরিবর্তনগুলো নির্ভুলভাবে পর্যবেক্ষণ করুন।') }}</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <h3>{{ t('Customer Management', 'কাস্টমার ব্যবস্থাপনা') }}</h3>
                <p>{{ t('Keep customer information organized and build better business relationships.', 'কাস্টমারের তথ্য সংরক্ষণ করুন এবং ব্যবসায়িক সম্পর্ক আরও উন্নত করুন।') }}</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M3 3v18h18"/>
                        <path d="m7 14 4-4 4 3 6-7"/>
                    </svg>
                </div>
                <h3>{{ t('Reports & Analytics', 'রিপোর্ট ও অ্যানালিটিক্স') }}</h3>
                <p>{{ t('Understand business performance and make better decisions using organized reports.', 'সুশৃঙ্খল রিপোর্টের মাধ্যমে ব্যবসার পারফরম্যান্স বুঝুন এবং ভালো সিদ্ধান্ত নিন।') }}</p>
            </div>

        </div>

    </div>
</section>

{{-- HOW IT WORKS --}}

<section class="section process-section" id="how-it-works">

    <div class="container">

        <div class="section-heading">
            <span class="eyebrow">{{ t('GET STARTED', 'শুরু করুন') }}</span>

            <h2>{{ t('Get Your Business Online in Three Steps', 'মাত্র তিনটি ধাপে ব্যবসা শুরু করুন') }}</h2>

            <p>
                {{ t('Getting started with NexaBiz is simple. Create your account and start managing your business.', 'NexaBiz ব্যবহার শুরু করা খুব সহজ। অ্যাকাউন্ট তৈরি করুন এবং ব্যবসা পরিচালনা শুরু করুন।') }}
            </p>
        </div>

        <div class="process-grid">

            <div class="process-card">
                <div class="process-number">01</div>
                <h3>{{ t('Create Your Account', 'অ্যাকাউন্ট তৈরি করুন') }}</h3>
                <p>{{ t('Register your account with your basic information to get started.', 'আপনার প্রয়োজনীয় তথ্য দিয়ে একটি অ্যাকাউন্ট তৈরি করুন।') }}</p>
            </div>

            <div class="process-card">
                <div class="process-number">02</div>
                <h3>{{ t('Set Up Your Business', 'ব্যবসা সেটআপ করুন') }}</h3>
                <p>{{ t('Add your business details and organize your branches and operations.', 'আপনার ব্যবসার তথ্য যোগ করুন এবং শাখা ও কার্যক্রম সাজিয়ে নিন।') }}</p>
            </div>

            <div class="process-card">
                <div class="process-number">03</div>
                <h3>{{ t('Manage & Grow', 'পরিচালনা ও উন্নতি করুন') }}</h3>
                <p>{{ t('Access your dashboard and manage your business from anywhere.', 'ড্যাশবোর্ড ব্যবহার করে যেকোনো জায়গা থেকে ব্যবসা পরিচালনা করুন।') }}</p>
            </div>

        </div>

    </div>
</section>

{{-- ABOUT SECTION --}}

<section class="section about-section" id="about">

    <div class="container about-grid">

        <div class="about-visual">

            <div class="about-box">
                <small>{{ t('BUSINESS MANAGEMENT', 'ব্যবসা ব্যবস্থাপনা') }}</small>
                <strong>{{ t('One Centralized Platform', 'একটি কেন্দ্রীয় প্ল্যাটফর্ম') }}</strong>
            </div>

            <div class="about-box">
                <small>{{ t('BUSINESS OPERATIONS', 'ব্যবসায়িক কার্যক্রম') }}</small>
                <strong>{{ t('Organized & Simplified', 'সহজ ও সুশৃঙ্খল') }}</strong>
            </div>

            <div class="about-box">
                <small>{{ t('BUSINESS GROWTH', 'ব্যবসার উন্নতি') }}</small>
                <strong>{{ t('Built for Your Future', 'আপনার ভবিষ্যতের জন্য') }}</strong>
            </div>

        </div>

        <div class="about-content">

            <span class="eyebrow">{{ t('WHY NEXABIZ', 'কেন NexaBiz') }}</span>

            <h2>
                {{ t('Focus on Growing Your Business,', 'ব্যবসার উন্নতিতে মনোযোগ দিন,') }}
                {{ t('We Simplify the Rest.', 'বাকি কাজ সহজ করি আমরা।') }}
            </h2>

            <p>
                {{ t(
                    'Running a business involves many moving parts. NexaBiz brings essential business management tools together to help you stay organized, save time and make informed decisions.',
                    'একটি ব্যবসা পরিচালনায় অনেক ধরনের কাজ থাকে। NexaBiz প্রয়োজনীয় ব্যবসায়িক ম্যানেজমেন্ট টুলস এক জায়গায় নিয়ে এসেছে, যাতে আপনি সময় বাঁচাতে পারেন, সুশৃঙ্খল থাকতে পারেন এবং সঠিক সিদ্ধান্ত নিতে পারেন।'
                ) }}
            </p>

            <div class="about-points">

                <div class="about-point">
                    <span class="check-icon">✓</span>
                    {{ t('Centralized business management', 'কেন্দ্রীয়ভাবে ব্যবসা ব্যবস্থাপনা') }}
                </div>

                <div class="about-point">
                    <span class="check-icon">✓</span>
                    {{ t('Organized business operations', 'সুশৃঙ্খল ব্যবসায়িক কার্যক্রম') }}
                </div>

                <div class="about-point">
                    <span class="check-icon">✓</span>
                    {{ t('Manage your business from anywhere', 'যেকোনো জায়গা থেকে ব্যবসা পরিচালনা') }}
                </div>

                <div class="about-point">
                    <span class="check-icon">✓</span>
                    {{ t('Designed for growing businesses', 'উন্নয়নশীল ব্যবসার জন্য তৈরি') }}
                </div>

            </div>

        </div>

    </div>
</section>

{{-- FINAL CTA --}}

<section class="cta-section">
    <div class="container">

        <div class="cta-box">

            <h2>
                {{ t('Ready to Simplify Your Business?', 'আপনার ব্যবসা আরও সহজ করতে প্রস্তুত?') }}
            </h2>

            <p>
                {{ t(
                    'Take control of your business operations with NexaBiz. Get started today.',
                    'NexaBiz-এর মাধ্যমে আপনার ব্যবসার কার্যক্রমের নিয়ন্ত্রণ নিন। আজই শুরু করুন।'
                ) }}
            </p>

            @if(isset($guard) && Auth::guard($guard)->check())
                <a href="{{ route('saas.dashboard') }}" class="btn btn-light">
                    {{ t('Go to Dashboard', 'ড্যাশবোর্ডে যান') }}
                    →
                </a>
            @else
                <a href="{{ route('saas.register') }}" class="btn btn-light">
                    {{ t('Create Your Account', 'অ্যাকাউন্ট তৈরি করুন') }}
                    →
                </a>
            @endif

        </div>

    </div>
</section>

@endsection

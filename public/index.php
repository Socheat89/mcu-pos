<?php 
require_once __DIR__ . '/../core/classes/Database.php'; 
require_once __DIR__ . '/../core/helpers/url.php';

$canonicalUrl = rtrim(mc_url('', true), '/') . '/';
$ogImage = mc_url('public/images/my-logo.jpg', true);
$structuredData = [
    '@context' => 'https://schema.org',
    '@type' => 'SoftwareApplication',
    'name' => 'Mekong CyberUnit',
    'applicationCategory' => 'PointOfSaleApplication',
    'operatingSystem' => 'Web, Android, iOS',
    'url' => $canonicalUrl,
    'image' => $ogImage,
    'author' => [
        '@type' => 'Organization',
        'name' => 'Mekong CyberUnit'
    ],
    'publisher' => [
        '@type' => 'Organization',
        'name' => 'Mekong CyberUnit',
        'logo' => $ogImage
    ],
    'description' => 'Khmer-first POS system that unifies sales, inventory, and subscription management for Cambodian SMEs.',
    'offers' => [
        '@type' => 'Offer',
        'priceCurrency' => 'USD',
        'price' => '10.00',
        'availability' => 'https://schema.org/InStock'
    ],
    'inLanguage' => ['en', 'km', 'zh'],
    'areaServed' => ['Cambodia', 'Laos', 'Vietnam'],
    'sameAs' => [
        'https://t.me/SOCHEAT_DOEM'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mekong CyberUnit | Unified Business Platform</title>
    <meta name="description" content="Mekong CyberUnit is a Khmer-first POS system for Cambodia that unifies POS, inventory, HR, and subscription billing into one secure cloud dashboard.">
    <meta name="keywords" content="Mekong CyberUnit, POS Khmer, POS system Cambodia, Khmer POS software, cloud POS for SMEs">
    <meta name="robots" content="index, follow">
    <meta name="language" content="en, km">
    <link rel="canonical" href="<?php echo htmlspecialchars($canonicalUrl, ENT_QUOTES); ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Mekong CyberUnit | Khmer POS System">
    <meta property="og:description" content="Bilingual cloud POS software built in Cambodia for Khmer retailers and franchises.">
    <meta property="og:url" content="<?php echo htmlspecialchars($canonicalUrl, ENT_QUOTES); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($ogImage, ENT_QUOTES); ?>">
    <meta property="og:locale" content="en_US">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Mekong CyberUnit | Khmer POS">
    <meta name="twitter:description" content="POS Khmer system that manages orders, inventory, subscriptions, and HR in one dashboard.">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($ogImage, ENT_QUOTES); ?>">
    
    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@400;500;600;700&family=Sora:wght@300;400;500;600;700&family=Battambang:wght@300;400;700&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link rel="stylesheet" href="css/landing.css?v=4.1">

    
    <!-- Favicon -->
    <link rel="icon" href="images/my-logo.jpg" type="image/jpeg">
    <link rel="shortcut icon" href="images/my-logo.jpg" type="image/jpeg">
    
    <script type="application/ld+json">
<?php echo json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); ?>
    </script>
    
    
    
    <!-- Payment Success Modal (Bootstrap) -->
    <div class="modal fade" id="successModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content text-center p-4">
                <div class="mx-auto mb-3 d-grid place-items-center rounded-circle text-white" style="width:64px;height:64px;font-size:1.8rem;background:linear-gradient(135deg,#10B981,#059669)"><i class="ph-bold ph-check"></i></div>
                <h4>Payment Successful!</h4>
                <p class="text-muted">Your workspace setup is being initialized.</p>
                <div class="text-muted"><i class="ph-bold ph-spinner ph-spin"></i> Redirecting to setup...</div>
            </div>
        </div>
    </div>

    <!-- Waiting Modal (Bootstrap) -->
    <div class="modal fade" id="waitingModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header text-white" style="background:linear-gradient(135deg,#0088cc,#006699)">
                    <h5 class="modal-title"><i class="ph-bold ph-telegram-logo"></i> Awaiting Approval</h5>
                    <button type="button" class="btn-close btn-close-white" onclick="closeWaitingModal()"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="position-relative d-inline-block mb-3" style="width:120px;height:120px">
                        <svg width="120" height="120" style="transform:rotate(-90deg)">
                            <circle cx="60" cy="60" r="56" fill="none" stroke="#F1F5F9" stroke-width="6"/>
                            <circle id="countdown-progress" cx="60" cy="60" r="56" fill="none" stroke="var(--mc-primary)" stroke-width="6" stroke-linecap="round" stroke-dasharray="351.85" stroke-dashoffset="0"/>
                        </svg>
                        <div id="countdown-text" class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center fw-bold" style="font-size:1.6rem">120</div>
                    </div>
                    <h6>Admin Notification Sent</h6>
                    <p class="text-muted small">We've notified our team. This usually takes under 2 minutes.<br><strong>Please stay on this page.</strong></p>
                    <span class="badge rounded-pill px-3 py-2" style="background:rgba(0,136,204,0.1);color:#0088cc"><i class="ph-bold ph-spinner ph-spin"></i> <span id="waitingBadgeText">Waiting for manual approval...</span></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Sign In Modal (Bootstrap) -->
    <div class="modal fade" id="authModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ph-bold ph-user-circle"></i> Welcome Back</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="authError" class="alert alert-danger d-none py-2"></div>
                    <form id="authForm" action="login_process.php" method="POST">
                        <div class="mb-3">
                            <label for="modal-username" class="form-label">Username</label>
                            <input type="text" id="modal-username" name="username" class="form-control" placeholder="Enter your username" required>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <label for="modal-password" class="form-label">Password</label>
                                <a href="forgot_password.php" class="small fw-semibold" style="color:var(--mc-primary)">Forgot?</a>
                            </div>
                            <input type="password" id="modal-password" name="password" class="form-control" placeholder="Enter your password" required>
                        </div>
                        <button type="submit" id="signInBtn" class="btn btn-primary w-100">Sign In <i class="ph-bold ph-sign-in"></i></button>
                        <div class="text-center my-3 text-muted small">or</div>
                        <p class="text-center mb-0 small">New here? <a href="register.php" class="fw-semibold" style="color:var(--mc-primary)">Create an account</a></p>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="js/khqr-1.0.2.min.js"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body class="landing-page">

    <div class="page-loader" id="pageLoader">
        <div class="text-center">
            <div class="loader-spinner mx-auto mb-3"></div>
            <p class="fw-bold mb-1">Mekong CyberUnit</p>
            <p class="text-muted small text-uppercase">Preparing POS workspace</p>
        </div>
    </div>
    
    <!-- Bootstrap Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <span class="navbar-brand-icon"><img src="images/my-logo.jpg" alt="MCU" style="width:100%;height:100%;object-fit:contain;border-radius:inherit;"></span>
                Mekong CyberUnit
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <i class="ph-bold ph-list fs-5"></i>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-1">
                    <li class="nav-item"><a class="nav-link" href="#about" data-i18n="why_mcu">Why MCU</a></li>
                    <li class="nav-item"><a class="nav-link" href="#features" data-i18n="features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="#how-it-works" data-i18n="how_it_works">How It Works</a></li>
                    <li class="nav-item"><a class="nav-link" href="#pricing" data-i18n="pricing">Pricing</a></li>
                    <li class="nav-item"><a class="nav-link" href="#faq" data-i18n="faq">FAQ</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact" data-i18n="contact">Contact</a></li>
                </ul>
                <div class="d-flex align-items-center gap-2">
                    <!-- Language Switcher -->
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1 px-2 py-1" type="button" id="langDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="font-size:0.75rem;border-radius:20px;">
                            <i class="ph-bold ph-translate" style="font-size:0.9rem"></i>
                            <span id="currentLangLabel">EN</span>
                            <i class="ph-bold ph-caret-down" style="font-size:0.7rem"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="langDropdown" style="min-width:140px;border-radius:12px;">
                            <li><a class="dropdown-item d-flex align-items-center gap-2 lang-option" href="#" data-lang="en">
                                <span class="flag-icon">🇺🇸</span> <span>English</span>
                                <i class="ph-bold ph-check ms-auto text-success lang-check" id="check-en"></i>
                            </a></li>
                            <li><a class="dropdown-item d-flex align-items-center gap-2 lang-option" href="#" data-lang="km">
                                <span class="flag-icon">🇰🇭</span> <span>ខ្មែរ</span>
                                <i class="ph-bold ph-check ms-auto text-success lang-check d-none" id="check-km"></i>
                            </a></li>
                            <li><a class="dropdown-item d-flex align-items-center gap-2 lang-option" href="#" data-lang="zh">
                                <span class="flag-icon">🇨🇳</span> <span>中文</span>
                                <i class="ph-bold ph-check ms-auto text-success lang-check d-none" id="check-zh"></i>
                            </a></li>
                        </ul>
                    </div>
                    <a href="#" data-bs-toggle="modal" data-bs-target="#authModal" class="nav-link" data-i18n="sign_in">Sign In</a>
                    <a href="register.php" class="btn btn-primary btn-sm" data-i18n="get_started">Get Started</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="hero-section" id="top">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <div class="hero-pill"><i class="ph-fill ph-sparkle"></i> Trusted by 120+ Cambodian Businesses</div>
                    <h1 class="hero-title">The POS system that speaks Khmer, thinks local, and scales with you.</h1>
                    <p class="text-secondary fs-5 mb-4">Ditch the spreadsheets. One dashboard for sales, inventory, staff, and billing — with built-in Bakong KHQR and offline mode.</p>
                    <div class="d-flex flex-wrap gap-3 mb-5">
                        <a href="register.php" class="btn btn-primary btn-lg">Start Free Trial <i class="ph-bold ph-arrow-right"></i></a>
                        <a href="#features" class="btn btn-outline-primary btn-lg">Explore Features</a>
                    </div>
                    <div class="hero-metrics d-flex gap-3">
                        <div class="metric-item text-center"><strong class="d-block">120+</strong><small class="text-muted text-uppercase">Merchants</small></div>
                        <div class="metric-item text-center"><strong class="d-block">99.9%</strong><small class="text-muted text-uppercase">Uptime</small></div>
                        <div class="metric-item text-center"><strong class="d-block">3 min</strong><small class="text-muted text-uppercase">To First Sale</small></div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="dashboard-mockup">
                        <div class="dashboard-header">
                            <span class="dashboard-dot red"></span><span class="dashboard-dot yellow"></span><span class="dashboard-dot green"></span>
                            <span class="dashboard-title">Live Dashboard — Phnom Penh Branch</span>
                        </div>
                        <div class="dashboard-body">
                            <div class="row g-2 mb-3">
                                <div class="col-6"><div class="dashboard-stat"><span class="label">Today's Sales</span><span class="value up">៛ 4,250,000</span></div></div>
                                <div class="col-6"><div class="dashboard-stat"><span class="label">Orders</span><span class="value">47</span></div></div>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-6"><div class="dashboard-stat"><span class="label">KHQR Payments</span><span class="value accent">32 txns</span></div></div>
                                <div class="col-6"><div class="dashboard-stat"><span class="label">Low Stock Alerts</span><span class="value warn">3 items</span></div></div>
                            </div>
                            <div class="dashboard-bars">
                                <div class="dashboard-bar" style="height:60%"><span>Mon</span></div>
                                <div class="dashboard-bar" style="height:80%"><span>Tue</span></div>
                                <div class="dashboard-bar peak" style="height:100%"><span>Wed</span></div>
                                <div class="dashboard-bar" style="height:70%"><span>Thu</span></div>
                                <div class="dashboard-bar" style="height:90%"><span>Fri</span></div>
                                <div class="dashboard-bar" style="height:50%"><span>Sat</span></div>
                                <div class="dashboard-bar" style="height:40%"><span>Sun</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section id="features" class="py-5">
        <div class="container py-4">
            <div class="text-center mb-5">
                <div class="section-kicker"><i class="ph-bold ph-sparkle"></i> Why Teams Love MCU</div>
                <h2 class="fw-bold">Everything your business needs. Nothing you don't.</h2>
                <p class="text-muted mx-auto" style="max-width:640px">Purpose-built for Cambodian retailers, cafés, and restaurants — from single counters to multi-branch chains.</p>
            </div>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                <div class="col"><div class="feature-card"><div class="feature-icon"><i class="ph-bold ph-cash-register"></i></div><h5>Lightning-Fast POS</h5><p class="text-muted small mb-0">Process sales in under 2 seconds. Hold orders, split bills, apply discounts, and print Khmer/English receipts.</p></div></div>
                <div class="col"><div class="feature-card"><div class="feature-icon"><i class="ph-bold ph-qr-code"></i></div><h5>Bakong KHQR Built-In</h5><p class="text-muted small mb-0">Generate dynamic QR codes. Auto-verify payments in real-time. No more manual bank slip checks.</p></div></div>
                <div class="col"><div class="feature-card"><div class="feature-icon"><i class="ph-bold ph-package"></i></div><h5>Smart Inventory</h5><p class="text-muted small mb-0">Track stock across branches in real-time. Low-stock alerts and purchase order management.</p></div></div>
                <div class="col"><div class="feature-card"><div class="feature-icon"><i class="ph-bold ph-wifi-slash"></i></div><h5>Offline-First Mode</h5><p class="text-muted small mb-0">Internet down? Keep selling. Auto-sync when you reconnect. Zero data loss.</p></div></div>
                <div class="col"><div class="feature-card"><div class="feature-icon"><i class="ph-bold ph-chart-bar"></i></div><h5>Live Analytics</h5><p class="text-muted small mb-0">Sales dashboards, top-product rankings, profit margin reports — accessible from any device.</p></div></div>
                <div class="col"><div class="feature-card"><div class="feature-icon"><i class="ph-bold ph-buildings"></i></div><h5>Multi-Branch Control</h5><p class="text-muted small mb-0">Unlimited outlets, single login. Compare performance, transfer stock, centralize reporting.</p></div></div>
                <div class="col"><div class="feature-card"><div class="feature-icon"><i class="ph-bold ph-translate"></i></div><h5>Khmer & English UI</h5><p class="text-muted small mb-0">Toggle between ខ្មែរ and English instantly. Every label supports both languages.</p></div></div>
                <div class="col"><div class="feature-card"><div class="feature-icon"><i class="ph-bold ph-users-three"></i></div><h5>Staff & Cashier Mgmt</h5><p class="text-muted small mb-0">Track shifts, permissions, cash control sessions, and payroll reports — all integrated.</p></div></div>
            </div>
        </div>
    </section>

    <!-- Why MCU -->
    <section id="about" class="py-5 bg-light bg-opacity-50">
        <div class="container py-4">
            <div class="text-center mb-5">
                <div class="section-kicker"><i class="ph-bold ph-shield-check"></i> Why Mekong CyberUnit</div>
                <h2 class="fw-bold">Built in Cambodia, for Cambodian businesses.</h2>
                <p class="text-muted mx-auto" style="max-width:640px">We're not a generic POS translated into Khmer. We live and work here, building features that match how local shops operate.</p>
            </div>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-5">
                <div class="col"><div class="why-card"><div class="why-icon"><i class="ph-bold ph-bank"></i></div><h5>Bakong-First Payments</h5><p class="text-muted small mb-0">Deep KHQR integration. Customers pay with any banking app. No extra hardware, no manual reconciliation.</p></div></div>
                <div class="col"><div class="why-card"><div class="why-icon"><i class="ph-bold ph-clock-countdown"></i></div><h5>10-Minute Setup</h5><p class="text-muted small mb-0">Create account, add products via CSV or manually, and start selling — all before your first customer arrives.</p></div></div>
                <div class="col"><div class="why-card"><div class="why-icon"><i class="ph-bold ph-cloud-arrow-down"></i></div><h5>Works Offline, Syncs Online</h5><p class="text-muted small mb-0">Internet unpredictable? MCU stores transactions locally and syncs when you're back. Zero lost sales.</p></div></div>
                <div class="col"><div class="why-card"><div class="why-icon"><i class="ph-bold ph-headset"></i></div><h5>Khmer-Speaking Support</h5><p class="text-muted small mb-0">Stuck on a Saturday evening? Our Telegram support responds in Khmer within minutes. Real humans, no bots.</p></div></div>
                <div class="col"><div class="why-card"><div class="why-icon"><i class="ph-bold ph-shield"></i></div><h5>Enterprise Security</h5><p class="text-muted small mb-0">Encrypted data, role-based access, session timeouts, and audit logs keep your business safe.</p></div></div>
                <div class="col"><div class="why-card"><div class="why-icon"><i class="ph-bold ph-rocket-launch"></i></div><h5>Weekly Improvements</h5><p class="text-muted small mb-0">Updates every week based on merchant feedback. Your feature request could be live by next Monday.</p></div></div>
            </div>
            <div class="trust-strip d-flex align-items-center justify-content-center gap-3 gap-md-5 py-4 px-3 flex-wrap">
                <div class="text-center"><strong class="d-block fs-4" style="color:var(--mc-primary-dark)">120+</strong><small class="text-muted text-uppercase">Active Merchants</small></div>
                <div class="trust-divider d-none d-md-block"></div>
                <div class="text-center"><strong class="d-block fs-4" style="color:var(--mc-primary-dark)">3</strong><small class="text-muted text-uppercase">Languages (EN/KM/ZH)</small></div>
                <div class="trust-divider d-none d-md-block"></div>
                <div class="text-center"><strong class="d-block fs-4" style="color:var(--mc-primary-dark)">24/7</strong><small class="text-muted text-uppercase">Telegram Support</small></div>
                <div class="trust-divider d-none d-md-block"></div>
                <div class="text-center"><strong class="d-block fs-4" style="color:var(--mc-primary-dark)">2020</strong><small class="text-muted text-uppercase">Established Since</small></div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section id="how-it-works" class="py-5">
        <div class="container py-4">
            <div class="text-center mb-5">
                <div class="section-kicker"><i class="ph-bold ph-path"></i> Get Started in Minutes</div>
                <h2 class="fw-bold">From sign-up to first sale in 3 simple steps.</h2>
                <p class="text-muted mx-auto" style="max-width:640px">No technical skills needed. No hardware to install. Just a browser and a dream.</p>
            </div>
            <div class="row align-items-start justify-content-center g-3">
                <div class="col-md-3 text-center">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <div class="step-icon"><i class="ph-bold ph-user-plus"></i></div>
                        <h5>Create Your Account</h5>
                        <p class="text-muted small">Sign up in 60 seconds. Choose your plan and workspace name. No credit card needed for trial.</p>
                    </div>
                </div>
                <div class="col-auto d-none d-md-flex align-items-center pt-5 text-muted fs-4"><i class="ph-bold ph-arrow-right"></i></div>
                <div class="col-md-3 text-center">
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <div class="step-icon"><i class="ph-bold ph-file-csv"></i></div>
                        <h5>Add Your Products</h5>
                        <p class="text-muted small">Import via CSV or add one by one. Set prices in USD or KHR, upload images, organize by category.</p>
                    </div>
                </div>
                <div class="col-auto d-none d-md-flex align-items-center pt-5 text-muted fs-4"><i class="ph-bold ph-arrow-right"></i></div>
                <div class="col-md-3 text-center">
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <div class="step-icon"><i class="ph-bold ph-cash-register"></i></div>
                        <h5>Start Selling</h5>
                        <p class="text-muted small">Open your POS on any device. Accept cash or KHQR, print receipts, and watch your dashboard come alive.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing -->
    <section id="pricing" class="py-5">
        <div class="container py-4">
            <div class="text-center mb-5">
                <div class="section-kicker"><i class="ph-bold ph-credit-card"></i> <span data-i18n="transparent_pricing">Transparent Pricing</span></div>
                <h2 class="fw-bold" data-i18n="pricing_headline">One subscription. All features. No surprises.</h2>
                <p class="text-muted mx-auto" style="max-width:640px" data-i18n="pricing_subtext">Every plan includes unlimited transactions, free updates, and Telegram support. No hidden fees, no per-transaction charges.</p>
                <!-- Annual Toggle -->
                <div class="d-flex justify-content-center align-items-center gap-3 mt-4">
                    <span class="fw-semibold" data-i18n="billing_monthly">Monthly</span>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" role="switch" id="billingToggle" style="width:2.5rem;height:1.3rem;cursor:pointer">
                        <label class="form-check-label" for="billingToggle"></label>
                    </div>
                    <span class="fw-semibold" data-i18n="billing_annual">Annual</span>
                    <span class="badge rounded-pill px-3 py-1" style="background:linear-gradient(135deg,#10B981,#059669);color:#fff;font-size:0.75rem;" data-i18n="save_label">Save up to 25%</span>
                </div>
                <!-- Annual Promo Info -->
                <div id="annualPromoInfo" class="d-none mt-3">
                    <div class="d-inline-flex flex-wrap justify-content-center gap-2">
                        <span class="badge rounded-pill px-3 py-2" style="background:rgba(13,148,136,0.1);color:var(--mc-primary);border:1px solid rgba(13,148,136,0.3);font-size:0.8rem">
                            <i class="ph-bold ph-gift"></i> <span data-i18n="inventory_annual_promo">Inventory System: Buy 1 Year → Get 1 Month Free</span>
                        </span>
                        <span class="badge rounded-pill px-3 py-2" style="background:rgba(99,102,241,0.1);color:#6366f1;border:1px solid rgba(99,102,241,0.3);font-size:0.8rem">
                            <i class="ph-bold ph-gift"></i> <span data-i18n="premium_annual_promo">POS Premium: Buy 1 Year → Get 3 Months Free</span>
                        </span>
                    </div>
                </div>
            </div>
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-4 justify-content-center align-items-start">

                <?php
                try {
                $db = Database::getInstance();
                $plans = $db->fetchAll("SELECT * FROM systems WHERE status = 'active' ORDER BY price ASC");
                if (empty($plans)) {
                    echo '<div style="grid-column: 1/-1; text-align: center; padding: 2rem; background: rgba(99, 102, 241, 0.1); border-radius: 1rem; border: 1px dashed rgba(99, 102, 241, 0.3); color: #cbd5e1;">
                            <i class="ph-bold ph-warning-circle" style="font-size: 2rem; margin-bottom: 1rem; display: block; color: var(--accent);"></i>
                            No active pricing plans found. Please configure them in the <a href="' . (strpos($_SERVER['REQUEST_URI'], '/public/') !== false ? '../admin/plans.php' : 'admin/plans.php') . '" style="text-decoration: underline; font-weight: 700; color: var(--primary);">Admin Panel</a>.

                          </div>';
                } else {
                foreach ($plans as $index => $plan):
                    $planCode = strtolower(str_replace(' ', '_', $plan['name']));
                    $isPopular = (floatval($plan['price']) == 30.00); // Mark the 30.00 plan (POS Standard/Inventory) as popular for UI
                    $planName  = $plan['name'];
                    $isInventory = (stripos($planName, 'Inventory') !== false || floatval($plan['price']) == 30.00);
                    $isPremium   = (stripos($planName, 'Premium') !== false || floatval($plan['price']) == 99.99 || floatval($plan['price']) == 100.00);

                    // Free-month bonus: Inventory=1, Premium=3
                    $freeMonths   = $isInventory ? 1 : ($isPremium ? 3 : 0);
                    $annualTotal  = $plan['price'] * 12;
                    $annualCharge = $annualTotal - ($plan['price'] * $freeMonths);
                    $annualPerMo  = round($annualCharge / 12, 2);

                    // Fetch linked features for this plan
                    $features = $db->fetchAll("SELECT sm.module_name, sm.feature_key FROM system_modules sm WHERE sm.system_id = ?", [$plan['id']]);

                    // Feature labels
                    $featureLabels = [
                        'pos_core'           => 'POS Terminal & Dashboard',
                        'pos_orders'         => 'Order History',
                        'pos_inventory'      => 'Product & Inventory',
                        'pos_customers'      => 'Customer Management',
                        'pos_reports'        => 'Sales Reports & Analytics',
                        'pos_holds'          => 'Hold Orders',
                        'pos_digital_menu'   => 'Digital Menu (QR)',
                        'pos_settings'       => 'POS Settings',
                        'pos_sessions'       => 'Cash Control Sessions',
                        'pos_cashiers'       => 'Cashier Management',
                        'inventory_stock_in' => 'Stock-In Management',
                        'hr_staff'           => 'Staff Management',
                    ];
                    $storeLimit = (int)($plan['store_limit'] ?? 1);
                    $cashierLimit = (int)($plan['cashier_limit'] ?? 1);
                ?>
                <div class="col">
                <div class="pricing-card <?php echo $isPopular ? 'popular' : ''; ?>" data-plan="<?php echo htmlspecialchars($planCode); ?>" data-monthly-price="<?php echo $plan['price']; ?>" data-annual-per-mo="<?php echo $annualPerMo; ?>" data-free-months="<?php echo $freeMonths; ?>">
                    <?php if ($isPopular): ?>
                    <div class="pricing-badge" data-i18n="popular">Popular</div>
                    <?php endif; ?>
                    <?php if ($freeMonths > 0): ?>
                    <div class="annual-bonus-badge d-none" id="annual-badge-<?php echo $planCode; ?>">
                        <i class="ph-bold ph-gift"></i>
                        <?php if ($isInventory): ?>
                        <span data-i18n="free_1_month">1 Month FREE on Annual</span>
                        <?php else: ?>
                        <span data-i18n="free_3_months">3 Months FREE on Annual</span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <h4 class="fw-bold mb-2"><?php echo htmlspecialchars($plan['name']); ?></h4>
                    <p class="text-muted small mb-3"><?php echo htmlspecialchars($plan['description']); ?></p>
                    <div class="d-flex align-items-baseline gap-1 mb-1 pb-0">
                        <span class="price-amount" id="price-<?php echo $planCode; ?>">$<?php echo number_format($plan['price'], 2); ?></span>
                        <span class="text-muted price-period"><span data-i18n="per_month">/month</span></span>
                    </div>
                    <?php if ($freeMonths > 0): ?>
                    <div class="annual-saving-note d-none mb-3 pb-3 border-bottom" id="saving-<?php echo $planCode; ?>">
                        <small class="text-success fw-semibold"><i class="ph-bold ph-tag"></i>
                        <?php if ($isInventory): ?>
                        <span data-i18n="annual_note_inventory">$<?php echo number_format($annualCharge, 2); ?>/yr · 1 month free</span>
                        <?php else: ?>
                        <span data-i18n="annual_note_premium">$<?php echo number_format($annualCharge, 2); ?>/yr · 3 months free</span>
                        <?php endif; ?>
                        </small>
                    </div>
                    <div class="mb-3 pb-3 border-bottom d-block" id="monthly-border-<?php echo $planCode; ?>"></div>
                    <?php else: ?>
                    <div class="mb-3 pb-3 border-bottom"></div>
                    <?php endif; ?>
                    <ul class="feature-list mb-4">
                        <?php foreach ($features as $f): ?>
                            <?php $labelKey = $f['module_name'] . '_' . $f['feature_key']; ?>
                            <li><i class="ph-bold ph-check-circle"></i> <?php echo htmlspecialchars($featureLabels[$labelKey] ?? ucfirst($f['feature_key'])); ?></li>
                        <?php endforeach; ?>
                        <?php if ($isInventory): ?>
                            <li><i class="ph-bold ph-check-circle"></i> <span data-i18n="feat_stock_alerts">Low-Stock Alerts</span></li>
                            <li><i class="ph-bold ph-check-circle"></i> <span data-i18n="feat_purchase_orders">Purchase Order Management</span></li>
                            <li><i class="ph-bold ph-check-circle"></i> <span data-i18n="feat_supplier_mgmt">Supplier Management</span></li>
                            <li><i class="ph-bold ph-check-circle"></i> <span data-i18n="feat_inventory_reports">Inventory Reports</span></li>
                            <li><i class="ph-bold ph-check-circle"></i> <span data-i18n="feat_barcode_scan">Barcode Scanner Support</span></li>
                            <li><i class="ph-bold ph-check-circle"></i> <span data-i18n="feat_stock_transfer">Stock Transfer Between Stores</span></li>
                        <?php endif; ?>
                        <?php if ($storeLimit > 0): ?>
                            <li><i class="ph-bold ph-check-circle"></i> <span data-i18n="up_to_stores">Up to <?php echo $storeLimit; ?> Store<?php echo $storeLimit > 1 ? 's' : ''; ?></span></li>
                        <?php else: ?>
                            <li><i class="ph-bold ph-check-circle"></i> <span data-i18n="unlimited_stores">Unlimited Stores</span></li>
                        <?php endif; ?>
                        <?php if ($cashierLimit > 0): ?>
                            <li><i class="ph-bold ph-check-circle"></i> <span data-i18n="up_to_cashiers">Up to <?php echo $cashierLimit; ?> Cashier<?php echo $cashierLimit > 1 ? 's' : ''; ?></span></li>
                        <?php else: ?>
                            <li><i class="ph-bold ph-check-circle"></i> <span data-i18n="unlimited_cashiers">Unlimited Cashiers</span></li>
                        <?php endif; ?>
                        <?php if ($plan['price'] >= 30): ?>
                            <li><i class="ph-bold ph-check-circle"></i> <span data-i18n="cloud_storage">Cloud Storage</span></li>
                        <?php endif; ?>
                        <?php if ($plan['price'] >= 50): ?>
                            <li><i class="ph-bold ph-check-circle"></i> <span data-i18n="priority_support">24/7 Priority Support</span></li>
                        <?php endif; ?>
                    </ul>
                    <a href="register.php?plan=<?php echo $planCode; ?>" class="btn <?php echo $isPopular ? 'btn-primary' : 'btn-outline-primary'; ?> w-100"><span data-i18n="choose">Choose</span> <?php echo htmlspecialchars($plan['name']); ?></a>
                </div>
                </div>
                <?php endforeach; ?>
                <?php } 
                } catch (Exception $e) {
                    echo '<div style="grid-column: 1/-1; color: #f87171; padding: 1rem; border: 1px solid #ef4444; border-radius: 0.5rem; background: rgba(239, 68, 68, 0.1);">
                            <strong>DATABASE ERROR:</strong> ' . htmlspecialchars($e->getMessage()) . '
                           </div>';

                }
                ?>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section id="testimonials" class="py-5 bg-light bg-opacity-50">
        <div class="container py-4">
            <div class="text-center mb-5">
                <div class="section-kicker"><i class="ph-bold ph-chats-circle"></i> Merchant Stories</div>
                <h2 class="fw-bold">Trusted by businesses across Cambodia</h2>
                <p class="text-muted mx-auto" style="max-width:640px">From street-side coffee carts to multi-branch pharmacies — hear why they chose MCU.</p>
            </div>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <div class="col"><div class="testimonial-card"><div class="testimonial-stars mb-2">★★★★★</div><p class="fst-italic text-secondary">"We switched from paper ledgers to MCU. The KHQR integration saves us 30 minutes of reconciliation daily. Our baristas picked it up in one shift."</p><div class="d-flex align-items-center gap-2 mt-3 pt-3 border-top"><div class="rounded-3 d-grid place-items-center flex-shrink-0" style="width:40px;height:40px;background:rgba(13,148,136,0.1);font-size:1.2rem">☕</div><div><strong class="d-block small">Sokha Pich</strong><small class="text-muted">Brown & Bloom Cafe, Phnom Penh</small></div></div></div></div>
                <div class="col"><div class="testimonial-card"><div class="testimonial-stars mb-2">★★★★★</div><p class="fst-italic text-secondary">"Managing 3 outlets from my phone is a game-changer. Inventory alerts tell me what to restock before I run out. Offline mode saved us during rainy season outages."</p><div class="d-flex align-items-center gap-2 mt-3 pt-3 border-top"><div class="rounded-3 d-grid place-items-center flex-shrink-0" style="width:40px;height:40px;background:rgba(13,148,136,0.1);font-size:1.2rem">🛍️</div><div><strong class="d-block small">Piseth Vong</strong><small class="text-muted">Kravan Retail, Siem Reap</small></div></div></div></div>
                <div class="col"><div class="testimonial-card"><div class="testimonial-stars mb-2">★★★★★</div><p class="fst-italic text-secondary">"We manage 4,000+ drug SKUs across two locations. MCU's cloud sync is instant, and bilingual UI means our Khmer staff and I can both use it comfortably."</p><div class="d-flex align-items-center gap-2 mt-3 pt-3 border-top"><div class="rounded-3 d-grid place-items-center flex-shrink-0" style="width:40px;height:40px;background:rgba(13,148,136,0.1);font-size:1.2rem">💊</div><div><strong class="d-block small">Dr. Chantrea Keo</strong><small class="text-muted">Angkor Pharmacy, Battambang</small></div></div></div></div>
            </div>
        </div>
    </section>

    <!-- FAQ (Bootstrap Accordion) -->
    <section id="faq" class="py-5">
        <div class="container py-4">
            <div class="text-center mb-5">
                <div class="section-kicker"><i class="ph-bold ph-question"></i> <span data-i18n="faq_kicker">Quick Answers</span></div>
                <h2 class="fw-bold" data-i18n="faq_headline">Frequently asked questions</h2>
                <p class="text-muted mx-auto" style="max-width:640px" data-i18n="faq_subtext">Everything you need to know before getting started with MCU.</p>
            </div>
            <div class="accordion mx-auto" id="faqAccordion" style="max-width:860px">
                <div class="accordion-item border mb-2 rounded-3 overflow-hidden">
                    <h2 class="accordion-header"><button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1" data-i18n="faq1_q">How long does the initial setup take?</button></h2>
                    <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body text-muted" data-i18n="faq1_a">Setting up your workspace is instant! After registration, configure your menu items, import products via CSV, and start selling in under 10 minutes.</div></div>
                </div>
                <div class="accordion-item border mb-2 rounded-3 overflow-hidden">
                    <h2 class="accordion-header"><button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2" data-i18n="faq2_q">What hardware is compatible?</button></h2>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body text-muted" data-i18n="faq2_a">MCU is cloud-based and runs on any modern browser. Compatible with iPad, Android tablets, Windows PCs, and macOS. Connects to standard Bluetooth/USB receipt printers and cash drawers.</div></div>
                </div>
                <div class="accordion-item border mb-2 rounded-3 overflow-hidden">
                    <h2 class="accordion-header"><button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3" data-i18n="faq3_q">Does it support offline sales?</button></h2>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body text-muted" data-i18n="faq3_a">Yes! Offline mode allows you to continue taking orders and printing receipts. Once your internet connection is restored, all data automatically syncs back to the cloud.</div></div>
                </div>
                <div class="accordion-item border mb-2 rounded-3 overflow-hidden">
                    <h2 class="accordion-header"><button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq4" data-i18n="faq4_q">How does the Bakong KHQR integration work?</button></h2>
                    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body text-muted" data-i18n="faq4_a">Our platform generates dynamic KHQR codes including transaction amount and store metadata. Your customer scans it with any mobile banking app, and our system receives instant confirmation webhook to complete the order without manual verification.</div></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact -->
    <section id="contact" class="py-5 bg-light bg-opacity-50">
        <div class="container py-4">
            <div class="text-center mb-5">
                <div class="section-kicker"><i class="ph-bold ph-envelope"></i> Get In Touch</div>
                <h2 class="fw-bold">We're here to help</h2>
                <p class="text-muted mx-auto" style="max-width:640px">Questions about features, pricing, or custom integrations? Reach out anytime.</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="p-4 bg-white rounded-4 border h-100">
                        <h5 class="fw-bold mb-3">Contact Information</h5>
                        <div class="d-flex align-items-center gap-3 mb-3 p-2 rounded-3"><div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px;height:40px;background:rgba(13,148,136,0.1);color:var(--mc-primary)"><i class="ph-bold ph-envelope-simple"></i></div><div><strong class="d-block small">Email Support</strong><small class="text-muted">support@mekongcyberunit.app</small></div></div>
                        <div class="d-flex align-items-center gap-3 mb-3 p-2 rounded-3"><div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px;height:40px;background:rgba(13,148,136,0.1);color:var(--mc-primary)"><i class="ph-bold ph-telegram-logo"></i></div><div><strong class="d-block small">Telegram</strong><small class="text-muted"><a href="https://t.me/SOCHEAT_DOEM" target="_blank" rel="noopener" style="color:var(--mc-primary);font-weight:600">@SOCHEAT_DOEM</a></small></div></div>
                        <div class="d-flex align-items-center gap-3 p-2 rounded-3"><div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px;height:40px;background:rgba(13,148,136,0.1);color:var(--mc-primary)"><i class="ph-bold ph-map-pin"></i></div><div><strong class="d-block small">Headquarters</strong><small class="text-muted">Sensok District, Phnom Penh, Cambodia</small></div></div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="p-4 bg-white rounded-4 border">
                        <form action="#" method="POST" onsubmit="event.preventDefault();alert('Thank you! We will get back to you shortly.');this.reset();">
                            <div class="row g-3">
                                <div class="col-md-6"><label class="form-label small fw-semibold">First Name</label><input type="text" class="form-control" placeholder="John" required></div>
                                <div class="col-md-6"><label class="form-label small fw-semibold">Last Name</label><input type="text" class="form-control" placeholder="Doe" required></div>
                                <div class="col-12"><label class="form-label small fw-semibold">Email Address</label><input type="email" class="form-control" placeholder="john@example.com" required></div>
                                <div class="col-12"><label class="form-label small fw-semibold">Message</label><textarea class="form-control" rows="4" placeholder="How can we help your business?" required></textarea></div>
                                <div class="col-12"><button type="submit" class="btn btn-primary w-100">Send Message <i class="ph-bold ph-paper-plane-right"></i></button></div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-5">
        <div class="container">
            <div class="cta-box text-center text-white position-relative">
                <h2 class="fw-bold position-relative z-1">Ready to upgrade your shop?</h2>
                <p class="position-relative z-1 text-white-50 mb-4">Join 120+ Cambodian businesses already running on Mekong CyberUnit. Free trial, no credit card, cancel anytime.</p>
                <div class="d-flex flex-wrap justify-content-center gap-3 position-relative z-1">
                    <a href="register.php" class="btn btn-light btn-lg px-4 fw-semibold" style="color:var(--mc-primary-dark)">Start Your Free Trial <i class="ph-bold ph-arrow-right"></i></a>
                    <a href="https://t.me/SOCHEAT_DOEM" target="_blank" rel="noopener" class="btn btn-outline-light btn-lg px-4"><i class="ph-bold ph-telegram-logo"></i> Chat on Telegram</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-5">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="navbar-brand-icon"><img src="images/my-logo.jpg" alt="MCU" style="width:100%;height:100%;object-fit:contain;border-radius:inherit;"></span>
                        <span class="fw-bold text-white">Mekong CyberUnit</span>
                    </div>
                    <p class="small">Empowering Cambodian businesses with enterprise-grade POS tools at a fraction of the cost.</p>
                </div>
                <div class="col-md-2 col-6">
                    <h5>Product</h5>
                    <ul class="list-unstyled small"><li><a href="#features">Features</a></li><li><a href="#pricing">Pricing</a></li><li><a href="#how-it-works">How It Works</a></li><li><a href="register.php">Sign Up</a></li></ul>
                </div>
                <div class="col-md-2 col-6">
                    <h5>Support</h5>
                    <ul class="list-unstyled small"><li><a href="#faq">FAQ</a></li><li><a href="#contact">Contact</a></li><li><a href="https://t.me/SOCHEAT_DOEM" target="_blank" rel="noopener">Telegram</a></li><li><a href="mailto:support@mekongcyberunit.app">Email Us</a></li></ul>
                </div>
                <div class="col-md-3 col-6">
                    <h5>Company</h5>
                    <ul class="list-unstyled small"><li><a href="#about">About</a></li><li><a href="#">Privacy Policy</a></li><li><a href="#">Terms of Service</a></li></ul>
                </div>
            </div>
            <div class="footer-bottom text-center">
                &copy; 2026 Mekong CyberUnit. All rights reserved.
            </div>
        </div>
    </footer>
    <!-- Payment Modal (Bootstrap) -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header text-white" style="background:linear-gradient(135deg,var(--mc-primary),var(--mc-primary-dark))">
                    <h5 class="modal-title"><span class="badge bg-white bg-opacity-25 me-2 rounded-2"><i class="ph-bold ph-qr-code"></i></span> Scan to Pay (Bakong)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="fs-2 fw-bold mb-1" id="modalAmount">$0.00</div>
                    <p class="text-muted small mb-3">Scan with Bakong or any Banking App</p>
                    <div class="d-flex justify-content-center mb-3" style="min-height:200px">
                        <div id="qrPlaceholder" class="d-none"><i class="ph-bold ph-spinner ph-spin fs-1 text-muted"></i></div>
                        <img id="qrImage" src="" alt="KHQR Payment" class="d-none rounded-3 shadow-sm" style="max-width:220px">
                    </div>
                    <div id="staticNotice" class="alert alert-success d-none py-2 small"><i class="ph-bold ph-seal-check"></i> Please click 'I Have Paid' after scanning.</div>
                    <div id="pollingNotice" class="alert alert-warning py-2 small text-start">
                        <i class="ph-bold ph-spinner ph-spin"></i> កំពុងរង់ចាំការទូទាត់... (Waiting for payment)
                        <div id="apiStatus" class="small text-muted mt-1 font-monospace">Status: INITIALIZING...</div>
                        <button type="button" id="manualCheckBtn" onclick="if(window.currentMd5) checkStatusManual(window.currentMd5)" class="btn btn-sm btn-warning mt-2 d-none"><i class="ph-bold ph-arrows-clockwise"></i> ពិនិត្យឡើងវិញ (Check Now)</button>
                    </div>
                    <p class="text-muted small mt-2">Payment for <span id="planName" class="fw-semibold">Plan</span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" id="confirmBtn" class="btn btn-primary d-none" onclick="confirmStaticPayment()"><i class="ph-bold ph-check-circle"></i> I Have Paid</button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/loader.js"></script>
    <script>
        // Bootstrap modal helpers
        function openAuthModal() {
            const m = new bootstrap.Modal(document.getElementById('authModal'));
            m.show();
        }
        function closeAuthModal() {
            const m = bootstrap.Modal.getInstance(document.getElementById('authModal'));
            if (m) m.hide();
        }
        function closeWaitingModal() {
            if (confirm("Are you sure you want to cancel? Your payment notification has already been sent.")) {
                const m = bootstrap.Modal.getInstance(document.getElementById('waitingModal'));
                if (m) m.hide();
                if (pollingInterval) clearInterval(pollingInterval);
                if (countdownInterval) clearInterval(countdownInterval);
            }
        }

        let currentPlan = '';
        let currentAmount = 0;
        let pollingInterval = null;
        const paymentModalEl = document.getElementById('paymentModal');
        let paymentModal = null;

        function getPaymentModal() {
            if (!paymentModal) paymentModal = new bootstrap.Modal(paymentModalEl);
            return paymentModal;
        }

        function closeModal() {
            getPaymentModal().hide();
            if (pollingInterval) clearInterval(pollingInterval);
        }

        // Helper to handle relative paths
        // Use origin-relative paths for API calls to avoid subfolder issues on mekongcyberunit.app
        const isMekongDomain = window.location.hostname === 'mekongcyberunit.app';
        const projectPath = isMekongDomain ? '' : (window.location.pathname.includes('/public/') ? '' : 'public/');

        async function openPaymentModal(plan, price) {
            currentPlan = plan;
            currentAmount = price;
            document.getElementById('modalAmount').textContent = '$' + price.toFixed(2);
            document.getElementById('planName').textContent = plan.charAt(0).toUpperCase() + plan.slice(1) + ' Plan';
            
            // Reset modal state
            const confirmBtn = document.getElementById('confirmBtn');
            const staticNotice = document.getElementById('staticNotice');
            const pollingNotice = document.getElementById('pollingNotice');
            const qrImage = document.getElementById('qrImage');
            const qrPlaceholder = document.getElementById('qrPlaceholder');
            
            qrImage.style.display = 'none';
            qrPlaceholder.classList.remove('d-none');
            confirmBtn.classList.add('d-none');
            staticNotice.classList.add('d-none');
            pollingNotice.innerHTML = '<i class="ph-bold ph-spinner ph-spin"></i> កំពុងភ្ជាប់ទៅកាន់ប្រព័ន្ធទូទាត់... (Connecting...)';
            
            getPaymentModal().show();

            try {
                const response = await fetch(`${projectPath}api/bakong_qr.php?plan=${plan}&method=bakong&t=${Date.now()}`);
                if (!response.ok) throw new Error('HTTP ' + response.status);
                const result = await response.json();

                if (result.success) {
                    qrImage.src = result.image;
                    qrImage.style.display = 'block';
                    qrPlaceholder.style.display = 'none';
                    
                    if (result.is_static) {
                        confirmBtn.style.display = 'none'; // Auto-trigger notification
                        staticNotice.style.display = 'block';
                        staticNotice.innerHTML = '<p style="font-size: 0.85rem; color: #065f46; margin: 0; display: flex; align-items: center; gap: 0.5rem;"><i class="ph-bold ph-spinner ph-spin"></i> Waiting for Admin to verify payment...</p>';
                        pollingNotice.style.display = 'none';
                        
                        // Automatically notify admin and start waiting
                        confirmStaticPayment();
                    } else {
                        // Dynamic QR - Fully Automatic
                        confirmBtn.style.display = 'none'; 
                        staticNotice.style.display = 'none';
                        pollingNotice.style.display = 'block';
                        pollingNotice.innerHTML = '<p style="font-size: 0.85rem; color: #92400e; margin: 0; display: flex; align-items: center; gap: 0.5rem;"><i class="ph-bold ph-spinner ph-spin"></i> Detecting payment automatically...</p>';
                        

                        window.currentMd5 = result.md5;
                        startPolling(result.md5);
                        // Show manual check button after 5 seconds
                        setTimeout(() => {
                            const btn = document.getElementById('manualCheckBtn');
                            if(btn) btn.style.display = 'inline-block';
                        }, 5000);
                    }
                } else {
                    alert('Error generating QR: ' + result.error);
                }
            } catch (error) {
                console.error('Payment Error:', error);
                alert('Connection failed. Please try again.');
            }
        }

        window.confirmStaticPayment = async function() {
            try {
                const response = await fetch(`${projectPath}api/notify_payment.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ plan: currentPlan, amount: currentAmount })
                });
                const result = await response.json();

                if (result.success) {
                    // Switch to waiting modal
                    closeModal();
                    (new bootstrap.Modal(document.getElementById('waitingModal'))).show();
                    
                    startCountdown(120);
                    startApprovalPolling(result.ref);
                } else {
                    alert('Notification Error: ' + (result.error || 'Unknown error'));
                }
            } catch (error) {
                console.error('Notification Connection Error:', error);
                alert('Connection error. Please try again.');
            }
        }

        let countdownInterval = null;
        function startCountdown(duration) {
            let timeLeft = duration;
            const textDisplay = document.getElementById('countdown-text');
            const progressCircle = document.getElementById('countdown-progress');
            const totalDash = 351.85; // 2 * PI * 56
            
            // Initial state
            textDisplay.textContent = timeLeft;
            progressCircle.style.strokeDashoffset = 0;
            
            if (countdownInterval) clearInterval(countdownInterval);
            
            countdownInterval = setInterval(() => {
                timeLeft--;
                if (timeLeft < 0) {
                    clearInterval(countdownInterval);
                    document.getElementById('waitingBadgeText').textContent = "Taking longer than usual, please wait...";
                    return;
                }
                
                textDisplay.textContent = timeLeft;
                const offset = totalDash - (timeLeft / duration) * totalDash;
                progressCircle.style.strokeDashoffset = offset;
            }, 1000);
        }

        function startApprovalPolling(ref) {
            if (pollingInterval) clearInterval(pollingInterval);
            
            pollingInterval = setInterval(async () => {
                try {
                    const response = await fetch(`${projectPath}api/check_approval.php?ref=${ref}`);
                    const result = await response.json();

                    // Make status check case-insensitive
                    const statusUpper = (result.status || '').toUpperCase();
                    
                    if (result.success && (statusUpper === 'SUCCESS' || statusUpper === 'APPROVED')) {
                        clearInterval(pollingInterval);
                        clearInterval(countdownInterval);
                        
                        // Show success state in waiting modal first
                        const waitingContent = document.querySelector('#waitingModal .modal-body');
                        waitingContent.innerHTML = `
                            <div style="text-align:center; color: #16a34a; padding: 15px;">
                                <i class="ph-bold ph-check-circle" style="font-size: 5rem; margin-bottom: 20px; animation: scaleIn 0.5s ease;"></i>
                                <h2 style="margin-bottom: 10px;">Payment Approved!</h2>
                                <p style="color: #64748b; font-size: 1.1rem;">Redirecting to setup your workspace...</p>
                            </div>
                        `;
                        
                        setTimeout(() => {
                            window.location.href = `${projectPath}setup.php?plan=${currentPlan}&paid=true&ref=${ref}`;
                        }, 2000);
                    } else if (result.success && statusUpper === 'REJECTED') {
                        clearInterval(pollingInterval);
                        clearInterval(countdownInterval);
                        alert('Payment was rejected. Please contact support.');
                        closeWaitingModal();
                    }
                } catch (error) {
                    console.error('Approval Polling Error:', error);
                }
            }, 3000);
        }

        async function checkStatusManual(md5) {
            const btn = document.getElementById('manualCheckBtn');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="ph-bold ph-spinner ph-spin"></i> Checking...';

            try {
                const response = await fetch(`${projectPath}api/bakong_check.php?md5=${md5}&t=${Date.now()}`);
                if (!response.ok) throw new Error('HTTP error ' + response.status);
                const result = await response.json();

                if (result.success && ['SUCCESS', 'APPROVED', 'PAID', 'COMPLETED', 'SETTLED'].includes(result.status.toUpperCase())) {
                     btn.innerHTML = '<i class="ph-bold ph-check"></i> Paid!';
                     btn.style.background = '#10b981';
                     btn.style.borderColor = '#10b981';
                     clearInterval(pollingInterval);
                     setTimeout(() => {
                         closeModal();
                         (new bootstrap.Modal(document.getElementById('successModal'))).show();
                         setTimeout(() => {
                            window.location.href = `setup.php?plan=${currentPlan}&paid=true&md5=${md5}`;
                         }, 1000);
                     }, 500);
                } else {
                    alert('ស្ថានភាពទូទាត់: ' + (result.status || 'រង់ចាំ...') + '។ សូមព្យាយាមម្តងទៀតបន្តិចទៀតនេះ។');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            } catch (error) {
                console.error('Manual Check Error:', error);
                const pollingNotice = document.getElementById('pollingNotice');
                if(pollingNotice) {
                    pollingNotice.innerHTML += `<div style="color:#ef4444; font-size:11px; margin-top:5px;">ការឆែកមានបញ្ហា: ${error.message}</div>`;
                }
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }

        function startPolling(md5) {
            if (pollingInterval) clearInterval(pollingInterval);
            
            pollingInterval = setInterval(async () => {
                try {
                    const response = await fetch(`${projectPath}api/bakong_check.php?md5=${md5}&t=${Date.now()}`);
                    if (!response.ok) throw new Error('HTTP ' + response.status);
                    const result = await response.json();

                    const statusDisplay = document.getElementById('apiStatus');
                    if (statusDisplay) {
                        statusDisplay.textContent = `Status: ${result.status || 'UNKNOWN'} (${new Date().toLocaleTimeString()})`;
                    }

                    if (result.success && ['SUCCESS', 'APPROVED', 'PAID', 'COMPLETED', 'SETTLED'].includes(result.status.toUpperCase())) {
                        clearInterval(pollingInterval);
                        
                        // Show Success Message
                        const pollingNotice = document.getElementById('pollingNotice');
                        if (pollingNotice) {
                            pollingNotice.innerHTML = '<p style="font-size: 0.85rem; color: #10b981; margin: 0; display: flex; align-items: center; gap: 0.5rem;"><i class="ph-bold ph-check-circle"></i> ការទូទាត់ជោគជ័យ! កំពុងបញ្ជូនបន្ត...</p>';
                        }

                        setTimeout(() => {
                            closeModal();
                            (new bootstrap.Modal(document.getElementById('successModal'))).show();
                            setTimeout(() => {
                                const setupPath = window.location.origin + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1) + 'setup.php';
                                window.location.href = `${setupPath}?plan=${currentPlan}&paid=true&md5=${md5}`;
                            }, 1000);
                        }, 500);
                    } else if (result.success === false) {
                        const pollingNotice = document.getElementById('pollingNotice');
                        if(pollingNotice && !document.getElementById('api-err')) {
                           pollingNotice.innerHTML += `<div id="api-err" style="color:#ef4444; font-size:10px; margin-top:5px;">API Error: ${result.error}</div>`;
                        }
                    }
                } catch (error) {
                    console.error('Polling Error:', error);
                    const pollingNotice = document.getElementById('pollingNotice');
                    if(pollingNotice && !document.getElementById('api-err')) {
                        pollingNotice.innerHTML += `<div id="api-err" style="color:#ef4444; font-size:10px; margin-top:5px;">បញ្ហា API: ${error.message} (កំពុងព្យាយាមឡើងវិញ...)</div>`;
                    }
                }
            }, 2000); // Check every 2 seconds
        }

        // Auth form handler
        async function handleAuthSubmit(event) {
            event.preventDefault();
            const form = event.target;
            const btn = document.getElementById('signInBtn');
            const errorDiv = document.getElementById('authError');
            
            btn.disabled = true;
            btn.innerHTML = '<i class="ph-bold ph-spinner ph-spin"></i> Signing In...';
            errorDiv.classList.add('d-none');
            
            const formData = new FormData(form);
            formData.append('ajax', '1');
            
            try {
                const response = await fetch(`${projectPath}login_process.php`, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    btn.innerHTML = '<i class="ph-bold ph-check"></i> Success!';
                    btn.style.background = '#10b981';
                    btn.style.borderColor = '#10b981';
                    setTimeout(() => {
                        window.location.href = result.redirect;
                    }, 500);
                } else {
                    errorDiv.textContent = result.error || 'Login failed';
                    errorDiv.classList.remove('d-none');
                    btn.disabled = false;
                    btn.innerHTML = 'Sign In <i class="ph-bold ph-sign-in" style="margin-left: 8px;"></i>';
                }
            } catch (error) {
                console.error('Login error:', error);
                errorDiv.textContent = 'Connection error. Please try again.';
                errorDiv.style.display = 'block';
                btn.disabled = false;
                btn.innerHTML = 'Sign In <i class="ph-bold ph-sign-in" style="margin-left: 8px;"></i>';
            }
        }

        window.closeWaitingModal = function() {
            if(confirm("Are you sure you want to cancel the waiting process? Your payment notification has already been sent.")) {
                const m = bootstrap.Modal.getInstance(document.getElementById('waitingModal'));
                if (m) m.hide();
                if (pollingInterval) clearInterval(pollingInterval);
                if (countdownInterval) clearInterval(countdownInterval);
            }
        };

        // ═══════════════════════════════════════════════════════
        // SCROLL ANIMATIONS — Intersection Observer
        // ═══════════════════════════════════════════════════════
        (function() {
            const observerOptions = {
                threshold: 0.15,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry, idx) => {
                    if (entry.isIntersecting) {
                        // Staggered delay based on index within parent
                        const siblings = entry.target.parentElement ? 
                            Array.from(entry.target.parentElement.children).filter(c => c.classList.contains('animate-on-scroll')) : [];
                        const delay = siblings.indexOf(entry.target) * 80;
                        setTimeout(() => {
                            entry.target.classList.add('visible');
                        }, Math.min(delay, 400));
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            // Observe all elements with animate-on-scroll class
            document.querySelectorAll('.animate-on-scroll').forEach(el => {
                observer.observe(el);
            });

            // Also observe existing sections for staggered entrance
            const sectionSelectors = [
                '.feature-card', '.testimonial-card', '.system-card',
                '.faq-item', '.metric-card', '.stat-card'
            ];
            
            sectionSelectors.forEach(selector => {
                document.querySelectorAll(selector).forEach((el, idx) => {
                    el.classList.add('animate-on-scroll');
                    el.style.transitionDelay = (idx % 6) * 60 + 'ms';
                    observer.observe(el);
                });
            });
        })();

        // ═══════════════════════════════════════════════════════
        // HEADER SCROLL SHADOW
        // ═══════════════════════════════════════════════════════
        (function() {
            const header = document.querySelector('.main-header');
            if (!header) return;
            
            let lastScroll = 0;
            window.addEventListener('scroll', () => {
                const currentScroll = window.pageYOffset || document.documentElement.scrollTop;
                if (currentScroll > 10) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
                lastScroll = currentScroll;
            }, { passive: true });
        })();

        // ═══════════════════════════════════════════════════════
        // ACTIVE NAV LINK HIGHLIGHT ON SCROLL
        // ═══════════════════════════════════════════════════════
        (function() {
            const sections = document.querySelectorAll('section[id]');
            const navLinks = document.querySelectorAll('.nav-links .nav-item, .mobile-drawer .nav-item');
            if (!sections.length || !navLinks.length) return;

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const id = entry.target.getAttribute('id');
                        navLinks.forEach(link => {
                            link.classList.toggle('active', link.getAttribute('href') === '#' + id);
                        });
                    }
                });
            }, { threshold: 0.3, rootMargin: '-80px 0px -20% 0px' });

            sections.forEach(section => observer.observe(section));
        })();
        // ═══════════════════════════════════════════════════════
        // ANNUAL BILLING TOGGLE
        // ═══════════════════════════════════════════════════════
        (function() {
            const toggle = document.getElementById('billingToggle');
            const promoInfo = document.getElementById('annualPromoInfo');
            if (!toggle) return;

            toggle.addEventListener('change', function() {
                const isAnnual = this.checked;
                promoInfo && promoInfo.classList.toggle('d-none', !isAnnual);

                document.querySelectorAll('.pricing-card[data-plan]').forEach(card => {
                    const planCode     = card.dataset.plan;
                    const monthlyPrice = parseFloat(card.dataset.monthlyPrice);
                    const annualPerMo  = parseFloat(card.dataset.annualPerMo);
                    const freeMonths   = parseInt(card.dataset.freeMonths || '0');

                    const priceEl   = document.getElementById('price-' + planCode);
                    const savingEl  = document.getElementById('saving-' + planCode);
                    const badgeEl   = document.getElementById('annual-badge-' + planCode);
                    const borderEl  = document.getElementById('monthly-border-' + planCode);

                    if (priceEl) {
                        if (isAnnual && freeMonths > 0) {
                            priceEl.textContent = '$' + annualPerMo.toFixed(2);
                        } else {
                            priceEl.textContent = '$' + monthlyPrice.toFixed(2);
                        }
                    }
                    if (savingEl)  savingEl.classList.toggle('d-none', !isAnnual || freeMonths === 0);
                    if (badgeEl)   badgeEl.classList.toggle('d-none', !isAnnual || freeMonths === 0);
                    if (borderEl)  borderEl.classList.toggle('d-none', isAnnual && freeMonths > 0);
                });
            });
        })();

        // ═══════════════════════════════════════════════════════
        // LANGUAGE SWITCHER (i18n)
        // ═══════════════════════════════════════════════════════
        (function() {
            const translations = {
                en: {
                    why_mcu: 'Why MCU', features: 'Features', how_it_works: 'How It Works',
                    pricing: 'Pricing', faq: 'FAQ', contact: 'Contact',
                    sign_in: 'Sign In', get_started: 'Get Started',
                    transparent_pricing: 'Transparent Pricing',
                    pricing_headline: 'One subscription. All features. No surprises.',
                    pricing_subtext: 'Every plan includes unlimited transactions, free updates, and Telegram support. No hidden fees, no per-transaction charges.',
                    billing_monthly: 'Monthly', billing_annual: 'Annual',
                    save_label: 'Save up to 25%',
                    inventory_annual_promo: 'Inventory System: Buy 1 Year → Get 1 Month Free',
                    premium_annual_promo: 'POS Premium: Buy 1 Year → Get 3 Months Free',
                    free_1_month: '1 Month FREE on Annual',
                    free_3_months: '3 Months FREE on Annual',
                    per_month: '/month',
                    popular: 'Popular', choose: 'Choose',
                    feat_stock_alerts: 'Low-Stock Alerts',
                    feat_purchase_orders: 'Purchase Order Management',
                    feat_supplier_mgmt: 'Supplier Management',
                    feat_inventory_reports: 'Inventory Reports',
                    feat_barcode_scan: 'Barcode Scanner Support',
                    feat_stock_transfer: 'Stock Transfer Between Stores',
                    cloud_storage: 'Cloud Storage',
                    priority_support: '24/7 Priority Support',
                    unlimited_stores: 'Unlimited Stores',
                    unlimited_cashiers: 'Unlimited Cashiers',
                    // FAQ
                    faq_kicker: 'Quick Answers',
                    faq_headline: 'Frequently asked questions',
                    faq_subtext: 'Everything you need to know before getting started with MCU.',
                    faq1_q: 'How long does the initial setup take?',
                    faq1_a: 'Setting up your workspace is instant! After registration, configure your menu items, import products via CSV, and start selling in under 10 minutes.',
                    faq2_q: 'What hardware is compatible?',
                    faq2_a: 'MCU is cloud-based and runs on any modern browser. Compatible with iPad, Android tablets, Windows PCs, and macOS. Connects to standard Bluetooth/USB receipt printers and cash drawers.',
                    faq3_q: 'Does it support offline sales?',
                    faq3_a: 'Yes! Offline mode allows you to continue taking orders and printing receipts. Once your internet connection is restored, all data automatically syncs back to the cloud.',
                    faq4_q: 'How does the Bakong KHQR integration work?',
                    faq4_a: 'Our platform generates dynamic KHQR codes including transaction amount and store metadata. Your customer scans it with any mobile banking app, and our system receives instant confirmation webhook to complete the order without manual verification.',
                },
                km: {
                    why_mcu: 'ហេតុអ្វី MCU', features: 'មុខងារ', how_it_works: 'របៀបប្រើប្រាស់',
                    pricing: 'តម្លៃ', faq: 'សំណួរញឹកញាប់', contact: 'ទំនាក់ទំនង',
                    sign_in: 'ចូលគណនី', get_started: 'ចាប់ផ្តើម',
                    transparent_pricing: 'តម្លៃប្រកបដោយតម្លាភាព',
                    pricing_headline: 'ជាវមួយ។ មុខងារទាំងអស់។ គ្មានការភ្ញាក់ផ្អើល។',
                    pricing_subtext: 'គ្រប់ package រួមមានប្រតិបត្តិការគ្មានដំណើ ការធ្វើបច្ចុប្បន្នភាព និងការគាំទ្រតេឡេក្រាម។ គ្មានថ្លៃបន្ថែម។',
                    billing_monthly: 'ប្រចាំខែ', billing_annual: 'ប្រចាំឆ្នាំ',
                    save_label: 'សន្សំបាន 25%',
                    inventory_annual_promo: 'Inventory System: ទិញ ១ ឆ្នាំ → ទទួល ១ ខែ ឥតគិតថ្លៃ',
                    premium_annual_promo: 'POS Premium: ទិញ ១ ឆ្នាំ → ទទួល ៣ ខែ ឥតគិតថ្លៃ',
                    free_1_month: '១ ខែ ឥតគិតថ្លៃ (ការជាវប្រចាំឆ្នាំ)',
                    free_3_months: '៣ ខែ ឥតគិតថ្លៃ (ការជាវប្រចាំឆ្នាំ)',
                    per_month: '/ខែ',
                    popular: 'ពេញនិយម', choose: 'ជ្រើសរើស',
                    feat_stock_alerts: 'ការជូនដំណឹងស្តុកទាប',
                    feat_purchase_orders: 'ការគ្រប់គ្រងការបញ្ជាទិញ',
                    feat_supplier_mgmt: 'ការគ្រប់គ្រងអ្នកផ្គត់ផ្គង់',
                    feat_inventory_reports: 'របាយការណ៍ស្តុក',
                    feat_barcode_scan: 'ការស្គែនបាខូដ',
                    feat_stock_transfer: 'ការផ្ទេរស្តុករវាងហាង',
                    cloud_storage: 'ទំហំផ្ទុកពពក',
                    priority_support: 'ការគាំទ្រអាទិភាព ២៤/៧',
                    unlimited_stores: 'ហាងគ្មានដំណើ',
                    unlimited_cashiers: 'អ្នកលក់គ្មានដំណើ',
                    // FAQ
                    faq_kicker: 'ចម្លើយរហ័ស',
                    faq_headline: 'សំណួរដែលសួរញឹកញាប់',
                    faq_subtext: 'អ្វីៗទាំងអស់ដែលអ្នកត្រូវដឹងមុននឹងចាប់ផ្តើមប្រើ MCU។',
                    faq1_q: 'តើការតំឡើងដំបូងចំណាយពេលប៉ុន្មាន?',
                    faq1_a: 'ការតំឡើង workspace គឺភ្លាមៗ! បន្ទាប់ពីការចុះឈ្មោះ ចាត់រៀបចំទំនិញ នាំចូលផលិតផលតាម CSV ហើយចាប់ផ្តើមលក់ក្នុងរយៈពេលតិចជាង ១០ នាទី។',
                    faq2_q: 'តើឧបករណ៍ណាខ្លះដែលអាចប្រើប្រាស់បាន?',
                    faq2_a: 'MCU ដំណើរការលើ browser ទំនើបណាមួយ។ ស្របតាម iPad, Android tablet, Windows PC, និង macOS។ ភ្ជាប់ជាមួយម៉ាស៊ីនបោះពុម្ព receipt និង cash drawer ស្តង់ដារ។',
                    faq3_q: 'តើវាគាំទ្រការលក់ក្រៅប្រព័ន្ធ (Offline) ដែរឬទេ?',
                    faq3_a: 'បាទ/ចាស! របៀប Offline អនុញ្ញាតឱ្យអ្នកបន្តទទួលការបញ្ជាទិញ និងបោះពុម្ព receipt។ នៅពេលអ៊ីនធឺណិតភ្ជាប់ឡើងវិញ ទិន្នន័យទាំងអស់នឹងធ្វើសមកាលកម្មដោយស្វ័យប្រវត្តិ។',
                    faq4_q: 'តើការទូទាត់តាម KHQR បាគងដំណើរការដូចម្តេច?',
                    faq4_a: 'វេទិការបស់យើងបង្កើត KHQR code ថាមវន្ត រួមមានចំនួនទឹកប្រាក់ និងព័ត៌មាន store។ អតិថិជនស្កែនតាម app ធនាគារ ហើយប្រព័ន្ធរបស់យើងទទួលការបញ្ជាក់ភ្លាមៗដោយមិនចាំបាច់ផ្ទៀងផ្ទាត់ដោយដៃ។',
                },
                zh: {
                    why_mcu: '为什么选MCU', features: '功能', how_it_works: '如何使用',
                    pricing: '价格', faq: '常见问题', contact: '联系我们',
                    sign_in: '登录', get_started: '开始使用',
                    transparent_pricing: '透明定价',
                    pricing_headline: '一次订阅。所有功能。无惊喜。',
                    pricing_subtext: '每个计划包括无限交易、免费更新和Telegram支持。无隐藏费用，无每笔交易费用。',
                    billing_monthly: '按月', billing_annual: '按年',
                    save_label: '最多节省25%',
                    inventory_annual_promo: '库存系统：购买1年 → 免费获得1个月',
                    premium_annual_promo: 'POS Premium：购买1年 → 免费获得3个月',
                    free_1_month: '年付免费1个月',
                    free_3_months: '年付免费3个月',
                    per_month: '/月',
                    popular: '热门', choose: '选择',
                    feat_stock_alerts: '低库存警报',
                    feat_purchase_orders: '采购订单管理',
                    feat_supplier_mgmt: '供应商管理',
                    feat_inventory_reports: '库存报告',
                    feat_barcode_scan: '条码扫描支持',
                    feat_stock_transfer: '门店间库存调拨',
                    cloud_storage: '云存储',
                    priority_support: '24/7优先支持',
                    unlimited_stores: '不限门店',
                    unlimited_cashiers: '不限收银员',
                    // FAQ
                    faq_kicker: '快速解答',
                    faq_headline: '常见问题解答',
                    faq_subtext: '开始使用 MCU 之前，您需要了解的一切。',
                    faq1_q: '初始设置需要多长时间？',
                    faq1_a: '设置工作区即时完成！注册后，配置菜单项目、通过CSV导入产品，不到10分钟即可开始销售。',
                    faq2_q: '兼容哪些硬件？',
                    faq2_a: 'MCU 基于云端，可在任何现代浏览器上运行。兼容 iPad、安卓平板、Windows PC 和 macOS。支持标准蓝牙/USB收据打印机和收银抽屉。',
                    faq3_q: '支持离线销售吗？',
                    faq3_a: '支持！离线模式允许您继续接单和打印收据。网络恢复后，所有数据将自动同步至云端。',
                    faq4_q: 'Bakong KHQR 集成是如何工作的？',
                    faq4_a: '我们的平台生成动态 KHQR 码，包含交易金额和门店信息。客户用任意手机银行App扫码，系统即时收到确认通知，无需人工核实。',
                }
            };

            const langLabels = { en: 'EN', km: 'ខ្មែរ', zh: '中文' };

            function applyLang(lang) {
                const t = translations[lang] || translations.en;
                document.querySelectorAll('[data-i18n]').forEach(el => {
                    const key = el.getAttribute('data-i18n');
                    if (t[key]) el.textContent = t[key];
                });
                // Update current lang label
                const lbl = document.getElementById('currentLangLabel');
                if (lbl) lbl.textContent = langLabels[lang] || 'EN';
                // Update checkmarks
                ['en','km','zh'].forEach(l => {
                    const chk = document.getElementById('check-' + l);
                    if (chk) chk.classList.toggle('d-none', l !== lang);
                });
                // Persist
                localStorage.setItem('mcuLang', lang);
                document.documentElement.lang = lang;
            }

            // Apply saved language on load
            const saved = localStorage.getItem('mcuLang') || 'en';
            applyLang(saved);

            // Handle language option clicks
            document.querySelectorAll('.lang-option').forEach(el => {
                el.addEventListener('click', function(e) {
                    e.preventDefault();
                    applyLang(this.dataset.lang);
                });
            });
        })();
    </script>
</body>
</html>


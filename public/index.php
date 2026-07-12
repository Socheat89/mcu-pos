<?php 
require_once __DIR__ . '/../core/classes/Database.php'; 
require_once __DIR__ . '/../core/helpers/url.php';

$canonicalUrl = rtrim(mc_url('', true), '/') . '/';
$ogImage = mc_url('public/images/logo.png', true);
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
    <link rel="stylesheet" href="css/landing.css?v=4.0">

    
    <!-- Favicon -->
    <link rel="icon" href="images/logo.png" type="image/png">
    <link rel="shortcut icon" href="images/logo.png" type="image/png">
    
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
                    <button type="button" class="btn-close" onclick="closeAuthModal()"></button>
                </div>
                <div class="modal-body">
                    <div id="authError" class="alert alert-danger d-none py-2"></div>
                    <form id="authForm" onsubmit="handleAuthSubmit(event)">
                        <div class="mb-3">
                            <label for="modal-username" class="form-label">Username</label>
                            <input type="text" id="modal-username" class="form-control" placeholder="Enter your username" required>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <label for="modal-password" class="form-label">Password</label>
                                <a href="forgot_password.php" class="small fw-semibold" style="color:var(--mc-primary)">Forgot?</a>
                            </div>
                            <input type="password" id="modal-password" class="form-control" placeholder="Enter your password" required>
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
                <span class="navbar-brand-icon"><i class="ph-bold ph-cube"></i></span>
                Mekong CyberUnit
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <i class="ph-bold ph-list fs-5"></i>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-1">
                    <li class="nav-item"><a class="nav-link" href="#about">Why MCU</a></li>
                    <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="#how-it-works">How It Works</a></li>
                    <li class="nav-item"><a class="nav-link" href="#pricing">Pricing</a></li>
                    <li class="nav-item"><a class="nav-link" href="#faq">FAQ</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                </ul>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" onclick="openAuthModal();return false" class="nav-link">Sign In</a>
                    <a href="register.php" class="btn btn-primary btn-sm">Get Started</a>
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
                <div class="section-kicker"><i class="ph-bold ph-credit-card"></i> Transparent Pricing</div>
                <h2 class="fw-bold">One subscription. All features. No surprises.</h2>
                <p class="text-muted mx-auto" style="max-width:640px">Every plan includes unlimited transactions, free updates, and Telegram support. No hidden fees, no per-transaction charges.</p>
            </div>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 align-items-start">

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
                    $isPopular = ($index === 1); // Mark second plan as popular for UI
                    
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
                <div class="pricing-card <?php echo $isPopular ? 'popular' : ''; ?>">
                    <?php if ($isPopular): ?>
                    <div class="pricing-badge">Popular</div>
                    <?php endif; ?>
                    <h4 class="fw-bold mb-2"><?php echo htmlspecialchars($plan['name']); ?></h4>
                    <p class="text-muted small mb-3"><?php echo htmlspecialchars($plan['description']); ?></p>
                    <div class="d-flex align-items-baseline gap-1 mb-3 pb-3 border-bottom">
                        <span class="price-amount">$<?php echo number_format($plan['price'], 2); ?></span>
                        <span class="text-muted">/month</span>
                    </div>
                    <ul class="feature-list mb-4">
                        <?php foreach ($features as $f): ?>
                            <?php $labelKey = $f['module_name'] . '_' . $f['feature_key']; ?>
                            <li><i class="ph-bold ph-check-circle"></i> <?php echo htmlspecialchars($featureLabels[$labelKey] ?? ucfirst($f['feature_key'])); ?></li>
                        <?php endforeach; ?>
                        <?php if ($storeLimit > 0): ?>
                            <li><i class="ph-bold ph-check-circle"></i> Up to <?php echo $storeLimit; ?> Store<?php echo $storeLimit > 1 ? 's' : ''; ?></li>
                        <?php else: ?>
                            <li><i class="ph-bold ph-check-circle"></i> Unlimited Stores</li>
                        <?php endif; ?>
                        <?php if ($cashierLimit > 0): ?>
                            <li><i class="ph-bold ph-check-circle"></i> Up to <?php echo $cashierLimit; ?> Cashier<?php echo $cashierLimit > 1 ? 's' : ''; ?></li>
                        <?php else: ?>
                            <li><i class="ph-bold ph-check-circle"></i> Unlimited Cashiers</li>
                        <?php endif; ?>
                        <?php if ($plan['price'] >= 30): ?>
                            <li><i class="ph-bold ph-check-circle"></i> Cloud Storage</li>
                        <?php endif; ?>
                        <?php if ($plan['price'] >= 50): ?>
                            <li><i class="ph-bold ph-check-circle"></i> 24/7 Priority Support</li>
                        <?php endif; ?>
                    </ul>
                    <a href="register.php?plan=<?php echo $planCode; ?>" class="btn <?php echo $isPopular ? 'btn-primary' : 'btn-outline-primary'; ?> w-100">Choose <?php echo htmlspecialchars($plan['name']); ?></a>
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
                <div class="section-kicker"><i class="ph-bold ph-question"></i> Quick Answers</div>
                <h2 class="fw-bold">Frequently asked questions</h2>
                <p class="text-muted mx-auto" style="max-width:640px">Everything you need to know before getting started with MCU.</p>
            </div>
            <div class="accordion mx-auto" id="faqAccordion" style="max-width:860px">
                <div class="accordion-item border mb-2 rounded-3 overflow-hidden">
                    <h2 class="accordion-header"><button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">How long does the initial setup take? / តើការតំឡើងដំបូងចំណាយពេលប៉ុន្មាន?</button></h2>
                    <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body text-muted">Setting up your workspace is instant! After registration, configure your menu items, import products via CSV, and start selling in under 10 minutes.</div></div>
                </div>
                <div class="accordion-item border mb-2 rounded-3 overflow-hidden">
                    <h2 class="accordion-header"><button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">What hardware is compatible? / តើឧបករណ៍ណាខ្លះដែលអាចប្រើប្រាស់បាន?</button></h2>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body text-muted">MCU is cloud-based and runs on any modern browser. Compatible with iPad, Android tablets, Windows PCs, and macOS. Connects to standard Bluetooth/USB receipt printers and cash drawers.</div></div>
                </div>
                <div class="accordion-item border mb-2 rounded-3 overflow-hidden">
                    <h2 class="accordion-header"><button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">Does it support offline sales? / តើវាគាំទ្រការលក់ក្រៅប្រព័ន្ធ (Offline) ដែរឬទេ?</button></h2>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body text-muted">Yes! Offline mode allows you to continue taking orders and printing receipts. Once your internet connection is restored, all data automatically syncs back to the cloud.</div></div>
                </div>
                <div class="accordion-item border mb-2 rounded-3 overflow-hidden">
                    <h2 class="accordion-header"><button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">How does the Bakong KHQR integration work? / តើការទូទាត់តាម KHQR បាគងដំណើរការដូចម្តេច?</button></h2>
                    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body text-muted">Our platform generates dynamic KHQR codes including transaction amount and store metadata. Your customer scans it with any mobile banking app, and our system receives instant confirmation webhook to complete the order without manual verification.</div></div>
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
                        <div class="d-flex gap-3 mb-3 p-2 rounded-3"><div class="rounded-3 d-grid place-items-center flex-shrink-0" style="width:40px;height:40px;background:rgba(13,148,136,0.1);color:var(--mc-primary)"><i class="ph-bold ph-envelope-simple"></i></div><div><strong class="d-block small">Email Support</strong><small class="text-muted">support@mekongcyberunit.app</small></div></div>
                        <div class="d-flex gap-3 mb-3 p-2 rounded-3"><div class="rounded-3 d-grid place-items-center flex-shrink-0" style="width:40px;height:40px;background:rgba(13,148,136,0.1);color:var(--mc-primary)"><i class="ph-bold ph-telegram-logo"></i></div><div><strong class="d-block small">Telegram</strong><small class="text-muted"><a href="https://t.me/SOCHEAT_DOEM" target="_blank" rel="noopener" style="color:var(--mc-primary);font-weight:600">@SOCHEAT_DOEM</a></small></div></div>
                        <div class="d-flex gap-3 p-2 rounded-3"><div class="rounded-3 d-grid place-items-center flex-shrink-0" style="width:40px;height:40px;background:rgba(13,148,136,0.1);color:var(--mc-primary)"><i class="ph-bold ph-map-pin"></i></div><div><strong class="d-block small">Headquarters</strong><small class="text-muted">Sensok District, Phnom Penh, Cambodia</small></div></div>
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
                        <span class="navbar-brand-icon"><i class="ph-bold ph-cube"></i></span>
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
            currentPlan = plan;
            currentAmount = price;
            document.getElementById('modalAmount').textContent = '$' + price.toFixed(2);
            document.getElementById('planName').textContent = plan.charAt(0).toUpperCase() + plan.slice(1) + ' Plan';
            
            // Reset modal state
            const confirmBtn = document.getElementById('confirmBtn');
            const staticNotice = document.getElementById('staticNotice');
            const pollingNotice = document.getElementById('pollingNotice');
            
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
    </script>
</body>
</html>


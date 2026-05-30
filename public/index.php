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
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link rel="stylesheet" href="css/landing.css?v=2.2">
    
    <!-- Favicon -->
    <link rel="icon" href="images/logo.png" type="image/png">
    <link rel="shortcut icon" href="images/logo.png" type="image/png">
    
    <script type="application/ld+json">
<?php echo json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); ?>
    </script>
    
    <style>
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background-color: #fff;
            margin: auto;
            padding: 0;
            border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            width: 90%;
            max-width: 450px;
            animation: modalFadeIn 0.3s ease-out;
            position: relative;
            overflow: hidden;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8fafc;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #64748b;
            line-height: 1;
        }

        .modal-body {
            padding: 2rem;
            text-align: center;
        }

        .qr-code-container {
            margin-bottom: 1.5rem;
            border: 2px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 1rem;
            display: inline-block;
            background: white;
        }

        .qr-code-container img {
            max-width: 100%;
            height: auto;
            display: block;
        }

        .payment-amount {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        /* Countdown Style */
        .countdown-container {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .countdown-svg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            transform: rotate(-90deg);
        }

        .countdown-circle-bg {
            fill: none;
            stroke: #f1f5f9;
            stroke-width: 8;
        }

        .countdown-circle-progress {
            fill: none;
            stroke: #E31E26;
            stroke-width: 8;
            stroke-linecap: round;
            stroke-dasharray: 351.85; /* 2 * PI * r (r=56) */
            stroke-dashoffset: 0;
            transition: stroke-dashoffset 1s linear;
        }

        .countdown-text {
            font-size: 1.75rem;
            font-weight: 800;
            color: #1e293b;
            font-variant-numeric: tabular-nums;
        }

        .waiting-status {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 1rem;
        }

        .waiting-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
        }

        .waiting-desc {
            font-size: 0.95rem;
            color: #64748b;
            line-height: 1.5;
        }

        .telegram-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: #f0f9ff;
            color: #0369a1;
            border-radius: 2rem;
            font-weight: 600;
            font-size: 0.85rem;
            margin-top: 1rem;
        }

        /* Auth Modal Specific */
        .auth-form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }

        .auth-form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            font-size: 0.9rem;
            color: #1e293b;
        }

        .auth-form-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .auth-form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .auth-divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 1.5rem 0;
            color: #94a3b8;
            font-size: 0.85rem;
        }

        .auth-divider::before,
        .auth-divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e2e8f0;
        }

        .auth-divider:not(:empty)::before {
            margin-right: .5em;
        }

        .auth-divider:not(:empty)::after {
            margin-left: .5em;
        }

        .auth-error {
            background: #fef2f2;
            color: #b91c1c;
            padding: 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
            display: none;
            border: 1px solid #fecaca;
        }

        /* About Section */
        .about-section {
            background: radial-gradient(circle at 20% 20%, rgba(59, 130, 246, 0.35), transparent 55%),
                        radial-gradient(circle at 80% 0%, rgba(14, 165, 233, 0.25), transparent 45%),
                        #020617;
            color: #e2e8f0;
            padding: 5rem 0;
            position: relative;
            overflow: hidden;
        }

        .about-section::after {
            content: '';
            position: absolute;
            inset: 20% auto auto -60px;
            width: 220px;
            height: 220px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.15), transparent);
            filter: blur(0);
            transform: rotate(25deg);
            border-radius: 30px;
            border: 1px solid rgba(148, 163, 184, 0.2);
            opacity: 0.6;
        }

        .about-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2.5rem;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .about-intro h2 {
            font-family: 'Space Grotesk', 'Inter', sans-serif;
            font-size: clamp(2rem, 4vw, 3rem);
            margin: 1rem 0 1.5rem;
            color: #f8fafc;
        }

        .about-intro p {
            color: #cbd5f5;
            line-height: 1.7;
            margin-bottom: 1.5rem;
        }

        .about-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.4rem 0.9rem;
            border-radius: 999px;
            background: rgba(148, 163, 184, 0.15);
            color: #93c5fd;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .about-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            gap: 0.9rem;
        }

        .about-list li {
            display: flex;
            align-items: flex-start;
            gap: 0.7rem;
            color: #cbd5f5;
        }

        .about-profile {
            background: rgba(15, 23, 42, 0.85);
            border-radius: 1.5rem;
            padding: 2rem;
            border: 1px solid rgba(148, 163, 184, 0.2);
            box-shadow: 0 40px 60px -35px rgba(15, 23, 42, 0.9);
        }

        .profile-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 1.25rem;
        }

        .profile-meta span:last-child {
            color: #67e8f9;
        }

        .profile-name {
            font-family: 'Space Grotesk', 'Inter', sans-serif;
            font-size: 1.8rem;
            margin: 0 0 0.5rem;
            color: #e2e8f0;
        }

        .profile-role {
            color: #60a5fa;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .profile-bio {
            color: #cbd5f5;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .profile-stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(120px, 1fr));
            gap: 1.2rem;
        }

        .stat-card {
            background: rgba(59, 130, 246, 0.08);
            border-radius: 1rem;
            padding: 1rem;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        .stat-value {
            display: block;
            font-size: 1.75rem;
            font-weight: 700;
            color: #f4faff;
        }

        .stat-label {
            font-size: 0.85rem;
            color: #94a3b8;
        }

        .signature {
            margin-top: 2rem;
            font-family: 'Space Grotesk', 'Inter', sans-serif;
            font-size: 1rem;
            color: #cbd5f5;
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
        }
        
        /* Premium Mockup and UI additions */
        .mockup-container {
            transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
            transform: translateY(0);
        }
        .mockup-container:hover {
            transform: translateY(-6px);
            box-shadow: 0 35px 80px -15px rgba(15, 23, 42, 0.3) !important;
        }
        @keyframes glowPulse {
            0%, 100% { filter: drop-shadow(0 0 15px rgba(20, 184, 166, 0.1)); }
            50% { filter: drop-shadow(0 0 30px rgba(20, 184, 166, 0.25)); }
        }
        .animate-glow {
            animation: glowPulse 4s infinite ease-in-out;
        }
        @keyframes badgeRipple {
            0% { box-shadow: 0 0 0 0px rgba(124, 58, 237, 0.4); }
            100% { box-shadow: 0 0 0 8px rgba(124, 58, 237, 0); }
        }
        .badge-pulse {
            animation: badgeRipple 2s infinite ease-out;
        }
    </style>
    
    <!-- Payment Success Modal -->
    <div id="successModal" class="modal">
        <div class="modal-content" style="max-width: 400px; padding: 3rem 2rem; text-align: center;">
            <div style="width: 80px; height: 80px; background: #ecfdf5; color: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 1.5rem; animation: scaleIn 0.5s cubic-bezier(0.16, 1, 0.3, 1);">
                <i class="ph-bold ph-check"></i>
            </div>
            <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem; color: #0f172a;">Payment Successful!</h3>
            <p style="color: #64748b; margin-bottom: 2rem;">Thank you for your payment. Your workspace setup is being initialized.</p>
            <div style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; color: var(--primary); font-weight: 600;">
                <i class="ph-bold ph-spinner ph-spin"></i> Redirecting to setup...
            </div>
        </div>
    </div>

    <!-- Waiting for Approval Modal -->
    <div id="waitingModal" class="modal">
        <div class="modal-content" style="max-width: 450px;">
            <div class="modal-header" style="background: #0088cc; color: white; border-bottom: none;">
                <h3 style="font-weight: 600;">
                    <i class="ph-bold ph-telegram-logo"></i> Awaiting Approval
                </h3>
                <button type="button" class="modal-close" onclick="closeWaitingModal()" style="color: white;">&times;</button>
            </div>
            <div class="modal-body" style="padding: 3rem 2rem;">
                <div class="waiting-status">
                    <div class="countdown-container">
                        <svg class="countdown-svg">
                            <circle class="countdown-circle-bg" cx="60" cy="60" r="56"></circle>
                            <circle id="countdown-progress" class="countdown-circle-progress" cx="60" cy="60" r="56"></circle>
                        </svg>
                        <div id="countdown-text" class="countdown-text">120</div>
                    </div>
                    
                    <div class="waiting-title">Admin Notification Sent</div>
                    <div class="waiting-desc">
                        We've notified our team to verify your payment. 
                        This usually takes less than 2 minutes. 
                        <br><strong>Please stay on this page.</strong>
                    </div>
                    
                    <div class="telegram-badge">
                        <i class="ph-bold ph-spinner ph-spin"></i>
                        <span id="waitingBadgeText">Waiting for manual approval...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes scaleIn {
            0% { transform: scale(0); }
            100% { transform: scale(1); }
        }
    </style>

    <!-- Sign In Modal -->
    <div id="authModal" class="modal">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h3 style="font-weight: 600;">
                    <i class="ph-bold ph-user-circle"></i> Welcome Back
                </h3>
                <button type="button" class="modal-close" onclick="closeAuthModal()">&times;</button>
            </div>
            <div class="modal-body" style="padding: 2.5rem 2rem;">
                <div id="authError" class="auth-error"></div>
                <form id="authForm" onsubmit="handleAuthSubmit(event)">
                    <div class="auth-form-group">
                        <label for="modal-username">Username</label>
                        <input type="text" id="modal-username" name="username" placeholder="Enter your username" required>
                    </div>
                    
                    <div class="auth-form-group">
                        <div style="display: flex; justify-content: space-between;">
                            <label for="modal-password">Password</label>
                            <a href="#" style="font-size: 0.8rem; color: var(--primary);">Forgot?</a>
                        </div>
                        <input type="password" id="modal-password" name="password" placeholder="Enter your password" required>
                    </div>
                    
                    <button type="submit" id="signInBtn" class="btn btn-primary" style="width: 100%; margin-top: 0.5rem;">
                        Sign In <i class="ph-bold ph-sign-in" style="margin-left: 8px;"></i>
                    </button>
                    
                    <div class="auth-divider">or</div>
                    
                    <p style="font-size: 0.9rem; margin: 0;">
                        New here? <a href="register.php" style="color: var(--primary); font-weight: 600;">Create an account</a>
                    </p>
                </form>
            </div>
        </div>
    </div>

    <script src="js/khqr-1.0.2.min.js"></script>
    <!-- Icons (Phosphor Icons for a premium look) -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body class="landing-page">
    <div class="page-loader" id="pageLoader">
        <div class="loader-card">
            <div class="loader-logo">
                <i class="ph-bold ph-cube"></i>
            </div>
            <p class="loader-title">Mekong CyberUnit</p>
            <p class="loader-caption">Preparing POS workspace</p>
            <div class="loader-spinner"></div>
            <div class="loader-progress"><span></span></div>
        </div>
    </div>
    
    <!-- Header -->
    <header class="main-header">
        <div class="container nav-container">
            <a href="#" class="logo">
                <div class="logo-icon">
                    <i class="ph-bold ph-cube"></i>
                </div>
                <span>Mekong CyberUnit</span>
            </a>
            
            <nav class="nav-links">
                <a href="#about" class="nav-item">About Me</a>
                <a href="#features" class="nav-item">Features</a>
                <a href="#pricing" class="nav-item">Pricing</a>
                <a href="#faq" class="nav-item">FAQ</a>
                <a href="#contact" class="nav-item">Contact</a>
            </nav>
            
            <div class="flex items-center gap-4 nav-actions">
                <a href="login.php" onclick="openAuthModal()" class="nav-item">Sign In</a>
                <a href="register.php" class="btn btn-primary" style="padding: 0.5rem 1.25rem; font-size: 0.9rem;">Get Started</a>
            </div>

            <button id="mobileNavToggle" class="mobile-nav-toggle" aria-label="Toggle navigation" aria-expanded="false" aria-controls="mobileNavDrawer">
                <i class="ph-bold ph-list"></i>
            </button>
        </div>
    </header>

    <div class="mobile-drawer" id="mobileNavDrawer" role="navigation">
        <nav>
            <a href="#about" class="nav-item" onclick="closeMobileDrawer()">About Me</a>
            <a href="#features" class="nav-item" onclick="closeMobileDrawer()">Features</a>
            <a href="#pricing" class="nav-item" onclick="closeMobileDrawer()">Pricing</a>
            <a href="#faq" class="nav-item" onclick="closeMobileDrawer()">FAQ</a>
            <a href="#contact" class="nav-item" onclick="closeMobileDrawer()">Contact</a>
        </nav>
        <div class="mobile-cta">
            <a href="login.php" class="btn btn-outline" onclick="closeMobileDrawer(); openAuthModal();">Sign In</a>
            <a href="register.php" class="btn btn-primary" onclick="closeMobileDrawer()">Get Started</a>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-pill badge-pulse">
                <i class="ph-fill ph-sparkle" style="margin-right: 6px;"></i> v2.0 is now live
            </div>
            <h1>
                Run your entire business on <br>
                <span class="gradient-text">Cambodia’s Unified POS Platform</span>
            </h1>
            <p>
                Stop juggling multiple disjointed subscriptions. Mekong CyberUnit is a Khmer POS system built for Cambodia so anyone searching “POS Khmer” or “POS system” discovers a bilingual suite that merges POS, Inventory, HR, and Accounting in one secure cloud workspace.
            </p>
            <div class="btn-group">
                <a href="register.php" class="btn btn-primary">
                    Start Free Trial <i class="ph-bold ph-arrow-right" style="margin-left: 8px;"></i>
                </a>
            </div>
               <!-- Premium Dashboard Preview / Mockup -->
            <div class="mockup-container">
                <div class="mock-dashboard">
                    
                    <!-- Mockup Header -->
                    <div class="mock-header">
                        <div class="mock-header-left">
                            <span class="mock-dot red"></span>
                            <span class="mock-dot yellow"></span>
                            <span class="mock-dot green"></span>
                            <span style="margin-left: 8px; font-family: monospace; color: #64748b;">Mekong POS Terminal v2.0</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <span class="mock-badge-active">
                                <span class="mock-badge-dot"></span> Active Terminal 01
                            </span>
                            <span style="color: #94a3b8; font-family: monospace;">16:15:30</span>
                        </div>
                    </div>

                    <!-- Mockup Main Body -->
                    <div class="mock-body">
                        
                        <!-- Mockup Sidebar -->
                        <div class="mock-sidebar">
                            <div class="mock-sidebar-top">
                                <div class="mock-logo">MCU</div>
                                <div class="mock-sidebar-item"><i class="ph-bold ph-house"></i></div>
                                <div class="mock-sidebar-item active"><i class="ph-bold ph-shopping-cart"></i></div>
                                <div class="mock-sidebar-item"><i class="ph-bold ph-package"></i></div>
                                <div class="mock-sidebar-item"><i class="ph-bold ph-users"></i></div>
                                <div class="mock-sidebar-item"><i class="ph-bold ph-chart-bar"></i></div>
                            </div>
                            <div class="mock-sidebar-item"><i class="ph-bold ph-gear"></i></div>
                        </div>

                        <!-- Mockup Content Area -->
                        <div class="mock-main-content">
                            
                            <!-- Products Grid -->
                            <div class="mock-products-grid">
                                <!-- Search -->
                                <div class="mock-search-bar">
                                    <div class="mock-search-wrapper">
                                        <i class="ph-bold ph-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #64748b;"></i>
                                        <input type="text" class="mock-search-input" placeholder="Search coffee, drinks, snacks..." readonly>
                                    </div>
                                    <div class="mock-filter-btn">
                                        All Categories <i class="ph-bold ph-caret-down"></i>
                                    </div>
                                </div>

                                <!-- Cards -->
                                <div class="mock-grid">
                                    <div class="mock-card mock-product-card">
                                        <div class="mock-card-img">
                                            <span class="mock-card-badge">15 In Stock</span>
                                            ☕
                                        </div>
                                        <div class="mock-card-title">Espresso Single</div>
                                        <div class="mock-card-footer">
                                            <span class="mock-card-price">$1.50</span>
                                            <span class="mock-card-category">Coffee</span>
                                        </div>
                                    </div>

                                    <div class="mock-card selected mock-product-card">
                                        <div class="mock-card-img">
                                            <span class="mock-card-badge low">4 Low Stock</span>
                                            🥤
                                        </div>
                                        <div class="mock-card-title">Ice Latte Premium</div>
                                        <div class="mock-card-footer">
                                            <span class="mock-card-price">$2.20</span>
                                            <span class="mock-card-category">Coffee</span>
                                        </div>
                                        <span class="mock-selected-count">1</span>
                                    </div>

                                    <div class="mock-card mock-product-card">
                                        <div class="mock-card-img">
                                            <span class="mock-card-badge">12 In Stock</span>
                                            🥐
                                        </div>
                                        <div class="mock-card-title">Croissant Butter</div>
                                        <div class="mock-card-footer">
                                            <span class="mock-card-price">$1.80</span>
                                            <span class="mock-card-category">Bakery</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Cart Info -->
                            <div class="mock-cart">
                                <div class="mock-cart-header">
                                    <span class="mock-cart-title"><i class="ph-bold ph-shopping-bag" style="color: #10b981;"></i> Active Cart</span>
                                    <span class="mock-cart-count">1 Items</span>
                                </div>
                                
                                <div class="mock-cart-item">
                                    <div class="mock-cart-item-thumb">🥤</div>
                                    <div class="mock-cart-item-info">
                                        <h6 class="mock-cart-item-name">Ice Latte Premium</h6>
                                        <span class="mock-cart-item-price">$2.20</span>
                                    </div>
                                    <div class="mock-qty-selector">
                                        <span style="color: #64748b; cursor: pointer;">-</span>
                                        <span>1</span>
                                        <span style="color: #64748b; cursor: pointer;">+</span>
                                    </div>
                                </div>

                                <div class="mock-cart-summary">
                                    <div class="mock-summary-line">
                                        <span>Subtotal</span>
                                        <span>$2.20</span>
                                    </div>
                                    <div class="mock-summary-line">
                                        <span>Tax (0%)</span>
                                        <span>$0.00</span>
                                    </div>
                                    <div class="mock-summary-line total">
                                        <span>Total</span>
                                        <span style="color: #34d399;">$2.20</span>
                                    </div>

                                    <div class="mock-checkout-btn-group">
                                        <button class="mock-btn-trash" type="button"><i class="ph-bold ph-trash"></i></button>
                                        <button class="mock-btn-checkout" type="button">Checkout <i class="ph-bold ph-arrow-right"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Simulated KHQR Overlay Preview -->
                    <div class="mock-khqr-overlay">
                        <div class="mock-khqr-card animate-pulse">
                            <div class="mock-khqr-header">
                                <span class="mock-khqr-badge"><i class="ph-bold ph-qr-code"></i></span>
                                <span class="mock-khqr-title">Scan to Pay (Bakong)</span>
                            </div>
                            <div class="mock-khqr-amount">$2.20</div>
                            <div class="mock-khqr-qr">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=simulate_khqr_pay" alt="Mock QR" style="height: 100px; width: 100px; display: block;" />
                            </div>
                            <div class="mock-khqr-status">
                                <span style="height: 6px; width: 6px; border-radius: 50%; background: #f43f5e; display: inline-block;"></span> Awaiting Approval...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features-section" style="padding: 100px 0; border-top: 1px solid rgba(255, 255, 255, 0.05);">
        <div class="container">
            <div class="section-header">
                <div class="hero-pill" style="margin-bottom: 1rem;">
                    <i class="ph-bold ph-sparkle" style="margin-right: 6px;"></i> Platform Highlights
                </div>
                <h2>Packed with Enterprise-grade Features</h2>
                <p>Everything you need to scale your retail, cafe, or restaurant business in Cambodia.</p>
            </div>
            
            <div class="features-grid">
                <!-- Feature 1 -->
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="ph-bold ph-arrows-clockwise"></i>
                    </div>
                    <h3>Real-time Cloud Sync / ការសមកាលកម្មពេលវេលាជាក់ស្តែង</h3>
                    <p>Transactions and stock updates sync instantly across all devices. Never worry about losing data even if your connection drops.</p>
                </div>
                <!-- Feature 2 -->
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="ph-bold ph-translate"></i>
                    </div>
                    <h3>Bilingual Khmer & English / គាំទ្រពីរភាសា ខ្មែរ និង អង់គ្លេស</h3>
                    <p>Designed with Khmer-first usability. Seamlessly toggle languages to support local cashiers and international managers.</p>
                </div>
                <!-- Feature 3 -->
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="ph-bold ph-qr-code"></i>
                    </div>
                    <h3>Bakong KHQR Payments / ការទូទាត់តាម KHQR បាគង</h3>
                    <p>Generate dynamic KHQR codes for instant customer scanning. Automatically verifies payments with real-time notifications.</p>
                </div>
                <!-- Feature 4 -->
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="ph-bold ph-package"></i>
                    </div>
                    <h3>Smart Inventory Control / ការគ្រប់គ្រងសារពើភ័ណ្ឌឆ្លាតវៃ</h3>
                    <p>Track raw ingredients and products. Receive automated low-stock warnings and manage purchase orders easily.</p>
                </div>
                <!-- Feature 5 -->
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="ph-bold ph-chart-line-up"></i>
                    </div>
                    <h3>Sales & Analytics / របាយការណ៍លក់លម្អិត</h3>
                    <p>Access deep sales performance charts, top-selling items, and profit margin analysis anywhere, anytime.</p>
                </div>
                <!-- Feature 6 -->
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="ph-bold ph-storefront"></i>
                    </div>
                    <h3>Multi-outlet Management / គ្រប់គ្រងសាខាច្រើន</h3>
                    <p>Manage multiple branch locations, centralize warehouse stock, and compare store performances from a single dashboard.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Me Section -->
    <section id="about" class="about-section">
        <div class="container">
            <div class="about-grid">
                <div class="about-intro">
                    <span class="about-pill">
                        <i class="ph-bold ph-user-focus"></i>
                        Meet the Builder
                    </span>
                    <h2>Human-centered Khmer POS systems for Cambodian operators</h2>
                    <p>
                        I design Mekong CyberUnit the way I run my own businesses: fast iterations, transparent pricing,
                        and obsessive care for local workflows. Every release blends field interviews with rapid prototyping,
                        so features feel like a teammate instead of another SaaS chore—and keeps us ranking whenever teams search for POS Khmer or modern POS systems in the Mekong.
                    </p>
                    <ul class="about-list">
                        <li>
                            <i class="ph-bold ph-lightning"></i>
                            Built the first unified POS, HR, and inventory stack tailored for Khmer + English crews.
                        </li>
                        <li>
                            <i class="ph-bold ph-flask"></i>
                            Ship weekly experiments so the Khmer POS experience stays fast across menus, tax rules, and subscription rails.
                        </li>
                        <li>
                            <i class="ph-bold ph-handshake"></i>
                            Partner with cafés, clinics, and franchise brands to co-create playbooks that scale and boost search visibility for Cambodian POS system keywords.
                        </li>
                    </ul>
                    <div class="signature">
                        <strong>— The Mekong CyberUnit maker</strong>
                        <span>Product, engineering, and customer success in one desk.</span>
                    </div>
                </div>
                <div class="about-profile">
                    <div class="profile-meta">
                        <span>Independent Founder</span>
                        <span>Est. 2020</span>
                    </div>
                    <h3 class="profile-name">Your operator-in-residence</h3>
                    <div class="profile-role">Builder · Systems Thinker · Customer Partner</div>
                    <p class="profile-bio">
                        I grew up between restaurant back offices and dusty warehouses, so I obsess over practical UX:
                        offline-friendly flows, bilingual interfaces, and automations your team trusts after one shift.
                        If something feels clunky, ping me—I'm the one answering support chats too.
                    </p>
                    <div class="profile-stats">
                        <div class="stat-card">
                            <span class="stat-value">120+</span>
                            <span class="stat-label">SMEs onboarded</span>
                        </div>
                        <div class="stat-card">
                            <span class="stat-value">9.4/10</span>
                            <span class="stat-label">Support CSAT</span>
                        </div>
                        <div class="stat-card">
                            <span class="stat-value">3</span>
                            <span class="stat-label">Languages shipped</span>
                        </div>
                        <div class="stat-card">
                            <span class="stat-value">24 hr</span>
                            <span class="stat-label">Average rollout</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Cloud POS Pricing Section -->
    <section class="pricing-section" id="pricing">
        <div class="container">
            <div class="section-header">
                <div class="hero-pill" style="margin-bottom: 1rem;">Cloud POS Plans</div>
                <h2>Simple, Transparent Pricing</h2>
                <p>Choose the right plan for your business needs.</p>
            </div>
            
            <div class="systems-grid">
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
                    
                    // Fetch linked modules for this plan
                    $modules = $db->fetchAll("SELECT module_name FROM system_modules WHERE system_id = ?", [$plan['id']]);
                ?>
                <div class="system-card <?php echo $isPopular ? 'popular-card border-pulse' : ''; ?>" <?php echo $isPopular ? 'style="transform: scale(1.03); z-index: 1;"' : ''; ?>>
                    <?php if ($isPopular): ?>
                    <div style="position: absolute; top: 0; right: 0; background: linear-gradient(135deg, var(--primary), var(--accent)); color: white; padding: 0.35rem 1rem; font-size: 0.75rem; font-weight: 700; border-bottom-left-radius: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">POPULAR</div>
                    <?php endif; ?>
                    
                    <h3 class="system-title" style="margin-bottom: 0.5rem; font-size: 1.5rem; font-weight: 800;"><?php echo htmlspecialchars($plan['name']); ?></h3>
                    <p class="system-desc" style="margin-bottom: 1.5rem; min-height: 50px; color: var(--text-muted); font-size: 0.9rem; line-height: 1.5;"><?php echo htmlspecialchars($plan['description']); ?></p>
                    
                    <div class="price-tag" style="margin-bottom: 2rem; display: flex; align-items: baseline; gap: 4px;">
                        <span class="price-amount" style="font-size: 2.25rem; font-weight: 800; color: #f8fafc;">$<?php echo number_format($plan['price'], 2); ?></span>
                        <span class="price-period" style="color: var(--text-muted); font-size: 0.9rem;">/month</span>
                    </div>
                    
                    <ul style="list-style: none; padding: 0; margin: 0 0 2rem 0; color: var(--text-muted); text-align: left; display: flex; flex-direction: column; gap: 0.75rem;">
                        <?php if (empty($modules)): ?>
                            <li style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem;">
                                <i class="ph-bold ph-info" style="color: var(--secondary); font-size: 1.1rem;"></i> Basic Platform Access
                            </li>
                        <?php else: ?>
                            <?php foreach ($modules as $mod): ?>
                            <li style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; color: #e2e8f0;">
                                <i class="ph-bold ph-check-circle" style="color: var(--primary); font-size: 1.1rem;"></i> 
                                <span style="text-transform: uppercase; font-weight: 600; font-size: 0.8rem; letter-spacing: 0.03em;"><?php echo htmlspecialchars($mod['module_name']); ?></span> Module
                            </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <li style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; color: #e2e8f0;">
                            <i class="ph-bold ph-check-circle" style="color: var(--primary); font-size: 1.1rem;"></i> Cloud Storage
                        </li>
                        <li style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; color: #e2e8f0;">
                            <i class="ph-bold ph-check-circle" style="color: var(--primary); font-size: 1.1rem;"></i> 24/7 Priority Support
                        </li>
                    </ul>
                    
                    <a href="register.php?plan=<?php echo $planCode; ?>" class="btn <?php echo $isPopular ? 'btn-primary' : 'btn-outline'; ?>" style="width: 100%; text-align: center; text-decoration: none; display: block; padding: 0.75rem 1.5rem; font-weight: 700;">
                        Choose <?php echo htmlspecialchars($plan['name']); ?>
                    </a>
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

    <!-- Testimonials Section -->
    <section id="testimonials" class="testimonials-section">
        <div class="container">
            <div class="section-header">
                <div class="hero-pill" style="margin-bottom: 1rem;">
                    <i class="ph-bold ph-chats" style="margin-right: 6px;"></i> Customer Reviews
                </div>
                <h2>Loved by Operators Across Cambodia</h2>
                <p>See how local cafes, retail stores, and pharmacies scale their businesses with Mekong CyberUnit.</p>
            </div>
            
            <div class="testimonials-grid">
                <!-- Testimonial 1 -->
                <div class="testimonial-card">
                    <p class="testimonial-quote">
                        "Mekong CyberUnit changed the way we handle morning rushes. Toggling between Khmer and English works perfectly for our baristas, and the KHQR Bakong integration means we don't have to check manual bank slips anymore."
                    </p>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar">☕</div>
                        <div class="testimonial-info">
                            <h4>Sokha Pich / ពេជ្រ សុខា</h4>
                            <p>Owner, Brown & Bloom Cafe (Phnom Penh)</p>
                        </div>
                    </div>
                </div>
                
                <!-- Testimonial 2 -->
                <div class="testimonial-card">
                    <p class="testimonial-quote">
                        "Managing three outlets from my phone is a breeze. The smart inventory alerts help us restock before running out. The offline mode saved us multiple times during internet outages."
                    </p>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar">🛍️</div>
                        <div class="testimonial-info">
                            <h4>Piseth Vong / វង្ស ពិសិដ្ឋ</h4>
                            <p>Founder, Kravan Retail Shop (Siem Reap)</p>
                        </div>
                    </div>
                </div>
                
                <!-- Testimonial 3 -->
                <div class="testimonial-card">
                    <p class="testimonial-quote">
                        "We were searching for a POS Khmer solution that can manage thousands of item SKUs. The cloud sync is instantaneous, and the automated reports give us exact daily margin insights."
                    </p>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar">💊</div>
                        <div class="testimonial-info">
                            <h4>Chantrea Keo / កែវ ចាន់ទ្រា</h4>
                            <p>General Manager, Angkor Pharmacy (Battambang)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="faq-section">
        <div class="container">
            <div class="section-header">
                <div class="hero-pill" style="margin-bottom: 1rem;">
                    <i class="ph-bold ph-question" style="margin-right: 6px;"></i> Frequently Asked Questions
                </div>
                <h2>Got Questions? We Have Answers</h2>
                <p>Everything you need to know about setting up and using Mekong CyberUnit POS.</p>
            </div>
            
            <div class="faq-container">
                <!-- FAQ 1 -->
                <div class="faq-item">
                    <div class="faq-question">
                        <span>How long does the initial setup take? / តើការតំឡើងដំបូងចំណាយពេលប៉ុន្មាន?</span>
                        <i class="ph-bold ph-caret-down faq-icon"></i>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-inner">
                            Setting up your workspace is instant! After registration, you can configure your menu items, import products via CSV, and start selling in under 10 minutes.
                        </div>
                    </div>
                </div>
                
                <!-- FAQ 2 -->
                <div class="faq-item">
                    <div class="faq-question">
                        <span>What hardware is compatible? / តើឧបករណ៍ណាខ្លះដែលអាចប្រើប្រាស់បាន?</span>
                        <i class="ph-bold ph-caret-down faq-icon"></i>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-inner">
                            Mekong CyberUnit POS is cloud-based and runs on any modern browser. It is compatible with iPad, Android tablets, Windows PCs, and macOS. It also connects to standard Bluetooth/USB receipt printers and cash drawers.
                        </div>
                    </div>
                </div>
                
                <!-- FAQ 3 -->
                <div class="faq-item">
                    <div class="faq-question">
                        <span>Does it support offline sales? / តើវាគាំទ្រការលក់ក្រៅប្រព័ន្ធ (Offline) ដែរឬទេ?</span>
                        <i class="ph-bold ph-caret-down faq-icon"></i>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-inner">
                            Yes! Our offline mode allows you to continue taking orders and printing receipts. Once your internet connection is restored, all data automatically syncs back to the cloud.
                        </div>
                    </div>
                </div>
                
                <!-- FAQ 4 -->
                <div class="faq-item">
                    <div class="faq-question">
                        <span>How does the Bakong KHQR integration work? / តើការទូទាត់តាម KHQR បាគងដំណើរការដូចម្តេច?</span>
                        <i class="ph-bold ph-caret-down faq-icon"></i>
                    </div>
                    <div class="faq-answer">
                        <div class="faq-answer-inner">
                            Our platform generates dynamic KHQR codes including transaction amount and store metadata. Your customer scans it with any mobile banking app, and our system receives instant confirmation webhook to complete the order without manual verification.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact-section">
        <div class="container">
            <div class="section-header">
                <div class="hero-pill" style="margin-bottom: 1rem;">
                    <i class="ph-bold ph-envelope" style="margin-right: 6px;"></i> Contact Us
                </div>
                <h2>Get in Touch with Our Team</h2>
                <p>Have questions about pricing, features, or custom integrations? We're here to help.</p>
            </div>
            
            <div class="contact-grid">
                <!-- Contact Info Card -->
                <div class="contact-info-card">
                    <h3>Contact Information</h3>
                    <p>Reach out to us directly or visit our office. We usually respond within a few hours.</p>
                    
                    <ul class="contact-method-list">
                        <!-- Email -->
                        <li class="contact-method-item">
                            <div class="contact-method-icon">
                                <i class="ph-bold ph-envelope-simple"></i>
                            </div>
                            <div class="contact-method-text">
                                <h4>Email Support</h4>
                                <p>support@mekongcyberunit.app</p>
                            </div>
                        </li>
                        
                        <!-- Telegram -->
                        <li class="contact-method-item">
                            <div class="contact-method-icon">
                                <i class="ph-bold ph-telegram-logo"></i>
                            </div>
                            <div class="contact-method-text">
                                <h4>Telegram Channels</h4>
                                <p><a href="https://t.me/SOCHEAT_DOEM" target="_blank" rel="noopener" style="color: #22d3ee; text-decoration: underline;">@SOCHEAT_DOEM</a></p>
                            </div>
                        </li>
                        
                        <!-- Office Location -->
                        <li class="contact-method-item">
                            <div class="contact-method-icon">
                                <i class="ph-bold ph-map-pin"></i>
                            </div>
                            <div class="contact-method-text">
                                <h4>Phnom Penh Headquarters</h4>
                                <p>Sensok District, Phnom Penh, Cambodia</p>
                            </div>
                        </li>
                    </ul>
                </div>
                
                <!-- Contact Form Card -->
                <div class="contact-form-card">
                    <form class="contact-form" action="#" method="POST" onsubmit="event.preventDefault(); alert('Thank you for contacting us! We will get back to you shortly.'); this.reset();">
                        <div class="contact-form-group">
                            <label for="contact-firstname">First Name</label>
                            <input type="text" id="contact-firstname" name="firstname" placeholder="John" required>
                        </div>
                        
                        <div class="contact-form-group">
                            <label for="contact-lastname">Last Name</label>
                            <input type="text" id="contact-lastname" name="lastname" placeholder="Doe" required>
                        </div>
                        
                        <div class="contact-form-group full-width">
                            <label for="contact-email">Email Address</label>
                            <input type="email" id="contact-email" name="email" placeholder="john.doe@example.com" required>
                        </div>
                        
                        <div class="contact-form-group full-width">
                            <label for="contact-message">Message</label>
                            <textarea id="contact-message" name="message" rows="5" placeholder="How can we help your business?" required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary full-width" style="grid-column: 1 / -1; margin-top: 1rem; width: 100%;">
                            Send Message <i class="ph-bold ph-paper-plane-right" style="margin-left: 8px;"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Features / CTA -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-box">
                <h2>Ready to transform your business?</h2>
                <p>Join hundreds of businesses using Mekong CyberUnit to streamline operations.</p>
                <div class="btn-group">
                     <a href="register.php" class="btn" style="background: white; color: var(--text-main);">
                        Create Free Account
                    </a>
                    <a href="https://t.me/SOCHEAT_DOEM" target="_blank" rel="noopener" class="btn" style="background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.2);">
                        Contact Sales
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="logo">
                        <div class="logo-icon" style="width: 28px; height: 28px;">
                            <i class="ph-bold ph-cube"></i>
                        </div>
                        <span>Mekong CyberUnit</span>
                    </div>
                    <p>Empowering businesses with enterprise-grade tools at a fraction of the cost.</p>
                </div>
                
                <div class="footer-col">
                    <h4>Product</h4>
                    <ul class="footer-links">
                        <li><a href="#">POS</a></li>
                        <li><a href="#">Inventory</a></li>
                        <li><a href="#">HR Management</a></li>
                        <li><a href="#">Pricing</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h4>Company</h4>
                    <ul class="footer-links">
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Careers</a></li>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h4>Legal</h4>
                    <ul class="footer-links">
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Security</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="copyright">
                &copy; 2026 Mekong CyberUnit. All rights reserved.
            </div>
        </div>
    </footer>
    <!-- Payment Modal -->
    <div id="paymentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header" style="background: #E31E26; color: white;">
                <h3 style="font-weight: 600;">
                    <div style="background: white; border-radius: 4px; padding: 2px;">
                        <i class="ph-bold ph-qr-code" style="color: #E31E26;"></i>
                    </div>
                    Scan to Pay (Bakong)
                </h3>
                <button type="button" class="modal-close" onclick="closeModal()" style="color: rgba(255,255,255,0.8);">&times;</button>
            </div>
            <div class="modal-body">
                <div class="payment-amount" id="modalAmount">$0.00</div>
                <div class="payment-instruction" style="color: #64748b; margin-bottom: 1.5rem;">Scan with Bakong or any Banking App</div>
                
                <div class="qr-code-container" style="border-color: #E31E26; min-height: 200px; display: flex; align-items: center; justify-content: center;">
                    <div id="qrPlaceholder" style="display: none;">
                         <i class="ph-bold ph-spinner ph-spin" style="font-size: 2rem; color: #E31E26;"></i>
                    </div>
                    <img id="qrImage" src="" alt="KHQR Payment" style="display: none;">
                </div>
                
                <div id="staticNotice" style="margin-top: 1rem; padding: 1rem; background: #ecfdf5; border: 1px solid #d1fae5; border-radius: 0.5rem; text-align: left; display: none;">
                    <p style="font-size: 0.85rem; color: #065f46; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="ph-bold ph-seal-check"></i>
                        Please click 'I Have Paid' after scanning.
                    </p>
                </div>

                <div id="pollingNotice" style="margin-top: 1rem; padding: 1rem; background: #fffbeb; border: 1px solid #fef3c7; border-radius: 0.5rem; text-align: left;">
                    <p style="font-size: 0.85rem; color: #92400e; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="ph-bold ph-spinner ph-spin"></i>
                        កំពុងរង់ចាំការទូទាត់... (Waiting for payment)
                    </p>
                    <div id="apiStatus" style="font-size: 11px; color: #666; margin-top: 5px; font-family: monospace;">Status: INITIALIZING...</div>
                    <button type="button" id="manualCheckBtn" onclick="if(window.currentMd5) checkStatusManual(window.currentMd5)" style="margin-top: 8px; font-size: 0.75rem; background: #92400e; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; display: none;">
                        <i class="ph-bold ph-arrows-clockwise"></i> ពិនិត្យឡើងវិញ (Check Now)
                    </button>
                </div>
                
                <p style="font-size: 0.9rem; color: #64748b; margin-top: 1rem;">
                    Payment for <span id="planName" style="font-weight: 700; color: #0f172a;">Plan</span>
                </p>
            </div>
            <div class="modal-footer" style="padding: 1.5rem; border-top: 1px solid #e2e8f0; display: flex; gap: 1rem; background: #f8fafc;">
                <button type="button" id="confirmBtn" class="btn btn-primary" style="flex: 2; display: none; background: #16a34a; border-color: #16a34a;" onclick="confirmStaticPayment()">
                    <i class="ph-bold ph-check-circle"></i> I Have Paid
                </button>
                <button type="button" class="btn btn-outline" style="flex: 1;" onclick="closeModal()">Cancel</button>
            </div>
        </div>
    </div>

    <script src="js/loader.js"></script>
    <script>
        const mobileToggle = document.getElementById('mobileNavToggle');
        const mobileDrawer = document.getElementById('mobileNavDrawer');

        function closeMobileDrawer() {
            if (mobileDrawer) {
                mobileDrawer.classList.remove('active');
            }
            document.body.classList.remove('mobile-nav-open');
            if (mobileToggle) {
                mobileToggle.setAttribute('aria-expanded', 'false');
            }
        }

        function toggleMobileDrawer() {
            if (!mobileDrawer || !mobileToggle) return;
            const willOpen = !mobileDrawer.classList.contains('active');
            mobileDrawer.classList.toggle('active', willOpen);
            document.body.classList.toggle('mobile-nav-open', willOpen);
            mobileToggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        }

        if (mobileToggle) {
            mobileToggle.addEventListener('click', toggleMobileDrawer);
        }

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeMobileDrawer();
            }
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                closeMobileDrawer();
            }
        });

        window.closeMobileDrawer = closeMobileDrawer;

        let currentPlan = '';
        let currentAmount = 0;
        let pollingInterval = null;
        const modal = document.getElementById('paymentModal');
        const qrImage = document.getElementById('qrImage');
        const qrPlaceholder = document.getElementById('qrPlaceholder');

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
            
            qrImage.style.display = 'none';
            qrPlaceholder.style.display = 'block';
            confirmBtn.style.display = 'none';
            staticNotice.style.display = 'none';
            pollingNotice.innerHTML = '<p style="font-size: 0.85rem; color: #92400e; margin: 0; display: flex; align-items: center; gap: 0.5rem;"><i class="ph-bold ph-spinner ph-spin"></i> កំពុងភ្ជាប់ទៅកាន់ប្រព័ន្ធទូទាត់... (Connecting...)</p>';
            
            modal.classList.add('active');

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
                    document.getElementById('waitingModal').classList.add('active');
                    
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
                         document.getElementById('successModal').classList.add('active');
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
                            document.getElementById('successModal').classList.add('active');
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

        function closeModal() {
            modal.classList.remove('active');
            if (pollingInterval) {
                clearInterval(pollingInterval);
                pollingInterval = null;
            }
        }

        // Auth Modal Functions
        function openAuthModal() {
            document.getElementById('authModal').classList.add('active');
        }

        function closeAuthModal() {
            document.getElementById('authModal').classList.remove('active');
            document.getElementById('authError').style.display = 'none';
        }

        async function handleAuthSubmit(event) {
            event.preventDefault();
            const form = event.target;
            const btn = document.getElementById('signInBtn');
            const errorDiv = document.getElementById('authError');
            
            btn.disabled = true;
            btn.innerHTML = '<i class="ph-bold ph-spinner ph-spin"></i> Signing In...';
            errorDiv.style.display = 'none';
            
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
                    errorDiv.style.display = 'block';
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
                document.getElementById('waitingModal').classList.remove('active');
                if (pollingInterval) clearInterval(pollingInterval);
                if (countdownInterval) clearInterval(countdownInterval);
            }
        };

        // FAQ Accordion Toggle
        document.querySelectorAll('.faq-question').forEach(question => {
            question.addEventListener('click', () => {
                const item = question.parentElement;
                
                // Toggle current item
                item.classList.toggle('active');
                
                // Close other items
                document.querySelectorAll('.faq-item').forEach(otherItem => {
                    if (otherItem !== item) {
                        otherItem.classList.remove('active');
                    }
                });
            });
        });

        window.onclick = function(event) {
            if (event.target == modal) {
                closeModal();
            }
            if (event.target == document.getElementById('authModal')) {
                closeAuthModal();
            }
        }
    </script>
</body>
</html>
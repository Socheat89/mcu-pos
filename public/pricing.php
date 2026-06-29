<?php
session_start();
require_once __DIR__ . '/../core/helpers/url.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pricing Plans - Mekong CyberUnit</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<<<<<<< HEAD
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
=======
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@400;500;600;700&family=Sora:wght@300;400;500;600;700&family=Battambang:wght@300;400;700&display=swap" rel="stylesheet">
>>>>>>> 062e3cc8d9b9f40dc40c6d6c6835e28f6f8a0d77
    
    <!-- Styles -->
    <link rel="stylesheet" href="<?php echo mc_url('public/css/landing.css'); ?>">
    
    <!-- Favicon -->
    <link rel="icon" href="<?php echo mc_url('public/images/logo.png'); ?>" type="image/png">
    <link rel="shortcut icon" href="<?php echo mc_url('public/images/logo.png'); ?>" type="image/png">
    
    <!-- Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
<<<<<<< HEAD
    <style>
        body { background: #f8fafc; }
        .pricing-header {
            text-align: center;
            padding: 80px 20px 40px;
            background: white;
            border-bottom: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
=======
    
</head>
<body class="landing-page">
>>>>>>> 062e3cc8d9b9f40dc40c6d6c6835e28f6f8a0d77
    <div class="page-loader" id="pageLoader">
        <div class="loader-card">
            <div class="loader-logo">
                <i class="ph-bold ph-cube"></i>
            </div>
            <p class="loader-title">Mekong CyberUnit</p>
            <p class="loader-caption">Fetching pricing plans</p>
            <div class="loader-spinner"></div>
            <div class="loader-progress"><span></span></div>
        </div>
    </div>
    
    <!-- Header -->
    <header class="main-header">
        <div class="container nav-container">
            <a href="<?php echo mc_url('public/index.php'); ?>" class="logo">
                <div class="logo-icon">
                    <i class="ph-bold ph-cube"></i>
                </div>
                <span>Mekong CyberUnit</span>
            </a>
            
            <nav class="nav-links">
<<<<<<< HEAD
                <a href="<?php echo mc_url('public/index.php#systems'); ?>" class="nav-item">Solutions</a>
                <a href="<?php echo mc_url('public/index.php#features'); ?>" class="nav-item">Features</a>
                <a href="#" class="nav-item active">Pricing</a>
=======
                <a href="<?php echo mc_url('public/index.php#features'); ?>" class="nav-item">Features</a>
                <a href="<?php echo mc_url('public/index.php#pricing'); ?>" class="nav-item">Pricing</a>
                <a href="<?php echo mc_url('public/index.php#contact'); ?>" class="nav-item">Contact</a>
>>>>>>> 062e3cc8d9b9f40dc40c6d6c6835e28f6f8a0d77
            </nav>
            
            <div class="flex items-center gap-4">
                <a href="<?php echo mc_url('public/login.php'); ?>" class="nav-item">Sign In</a>
<<<<<<< HEAD
                <a href="<?php echo mc_url('public/register.php'); ?>" class="btn btn-primary" style="padding: 0.5rem 1.25rem; font-size: 0.9rem;">Get Started</a>
=======
                <a href="<?php echo mc_url('public/register.php'); ?>" class="btn btn-primary">Get Started</a>
>>>>>>> 062e3cc8d9b9f40dc40c6d6c6835e28f6f8a0d77
            </div>
        </div>
    </header>

<<<<<<< HEAD
    <div class="pricing-header">
        <div class="container">
            <div class="hero-pill" style="margin: 0 auto 1.5rem;">Choose Your Path</div>
            <h1 style="font-size: 3rem; font-weight: 800; color: #0f172a; margin-bottom: 1rem;">Simple, Scalable Pricing</h1>
            <p style="color: #64748b; font-size: 1.1rem; max-width: 600px; margin: 0 auto;">
                Select the plan that fits your business stage. No hidden fees, cancel anytime.
            </p>
        </div>
    </div>

    <!-- Cloud POS Pricing Section -->
    <section class="pricing-section" id="pricing" style="padding: 80px 0;">
        <div class="container">
            <div class="systems-grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                <!-- Starter Plan -->
                <div class="system-card" style="border-top: 4px solid #94a3b8; background: white;">
                    <h3 class="system-title" style="margin-bottom: 0.5rem;">Starter</h3>
                    <p class="system-desc" style="margin-bottom: 1rem; min-height: auto;">Perfect for small businesses just getting started.</p>
                    <div class="price-tag" style="margin-bottom: 2rem;">
                        <span class="price-amount">$10</span>
                        <span class="price-period">/month</span>
                    </div>
                    
                    <ul style="list-style: none; padding: 0; margin-bottom: 2rem; color: var(--text-muted); text-align: left;">
                        <li style="margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="ph-bold ph-check" style="color: var(--primary);"></i> Basic POS features
                        </li>
                        <li style="margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="ph-bold ph-check" style="color: var(--primary);"></i> Single User
                        </li>
                        <li style="margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="ph-bold ph-check" style="color: var(--primary);"></i> Basic Reporting
                        </li>
                    </ul>
                    
                    <a href="<?php echo mc_url('public/register.php?plan=starter'); ?>" class="btn btn-outline" style="width: 100%; text-align: center; text-decoration: none; display: block;">Choose Starter</a>
                </div>

                <!-- Professional Plan -->
                <div class="system-card" style="border-top: 4px solid var(--primary); transform: scale(1.05); box-shadow: var(--shadow-xl); z-index: 1; background: white;">
                    <div style="position: absolute; top: 0; right: 0; background: var(--primary); color: white; padding: 0.25rem 0.75rem; font-size: 0.75rem; font-weight: 600; border-bottom-left-radius: 0.5rem;">POPULAR</div>
                    <h3 class="system-title" style="margin-bottom: 0.5rem;">Professional</h3>
                    <p class="system-desc" style="margin-bottom: 1rem; min-height: auto;">Advanced features for growing businesses.</p>
                    <div class="price-tag" style="margin-bottom: 2rem;">
                        <span class="price-amount">$50</span>
                        <span class="price-period">/month</span>
                    </div>
                    
                    <ul style="list-style: none; padding: 0; margin-bottom: 2rem; color: var(--text-muted); text-align: left;">
                        <li style="margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="ph-bold ph-check" style="color: var(--primary);"></i> All Starter features
                        </li>
                        <li style="margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="ph-bold ph-check" style="color: var(--primary);"></i> 5 Staff Logins
                        </li>
                        <li style="margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="ph-bold ph-check" style="color: var(--primary);"></i> Inventory Management
                        </li>
                    </ul>
                    
                    <a href="<?php echo mc_url('public/register.php?plan=professional'); ?>" class="btn btn-primary" style="width: 100%; text-align: center; text-decoration: none; display: block;">Choose Professional</a>
                </div>

                <!-- Enterprise Plan -->
                <div class="system-card" style="border-top: 4px solid #0f172a; background: white;">
                    <h3 class="system-title" style="margin-bottom: 0.5rem;">Enterprise</h3>
                    <p class="system-desc" style="margin-bottom: 1rem; min-height: auto;">Full functionality for large operations.</p>
                    <div class="price-tag" style="margin-bottom: 2rem;">
                        <span class="price-amount">$100</span>
                        <span class="price-period">/month</span>
                    </div>
                    
                    <ul style="list-style: none; padding: 0; margin-bottom: 2rem; color: var(--text-muted); text-align: left;">
                        <li style="margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="ph-bold ph-check" style="color: var(--primary);"></i> All Pro features
                        </li>
                        <li style="margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="ph-bold ph-check" style="color: var(--primary);"></i> Unlimited Staff
                        </li>
                        <li style="margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="ph-bold ph-check" style="color: var(--primary);"></i> 24/7 Phone Support
                        </li>
                    </ul>
                    
                    <a href="<?php echo mc_url('public/register.php?plan=enterprise'); ?>" class="btn btn-outline" style="width: 100%; text-align: center; text-decoration: none; display: block;">Choose Enterprise</a>
=======
    <!-- Pricing Overview -->
    <section class="pricing-section" id="pricing">
        <div class="container">
            <div class="section-header">
                <div class="section-kicker">Choose Your Path</div>
                <h1>Simple, Scalable Pricing</h1>
                <p>Select the plan that fits your business stage. No hidden fees, cancel anytime.</p>
            </div>

            <div class="systems-grid">
                <!-- Starter Plan -->
                <div class="system-card">
                    <h3 class="system-title">Starter</h3>
                    <p class="system-desc">Perfect for small businesses just getting started.</p>
                    <div class="price-tag">
                        <span class="price-amount">$10</span>
                        <span class="price-period">/month</span>
                    </div>
                    <ul class="plan-list">
                        <li><i class="ph-bold ph-check"></i> Basic POS features</li>
                        <li><i class="ph-bold ph-check"></i> Single User</li>
                        <li><i class="ph-bold ph-check"></i> Basic Reporting</li>
                    </ul>
                    <a href="<?php echo mc_url('public/register.php?plan=starter'); ?>" class="btn btn-outline full-width">Choose Starter</a>
                </div>

                <!-- Professional Plan -->
                <div class="system-card popular-card">
                    <div class="plan-badge">Popular</div>
                    <h3 class="system-title">Professional</h3>
                    <p class="system-desc">Advanced features for growing businesses.</p>
                    <div class="price-tag">
                        <span class="price-amount">$50</span>
                        <span class="price-period">/month</span>
                    </div>
                    <ul class="plan-list">
                        <li><i class="ph-bold ph-check"></i> All Starter features</li>
                        <li><i class="ph-bold ph-check"></i> 5 Staff Logins</li>
                        <li><i class="ph-bold ph-check"></i> Inventory Management</li>
                    </ul>
                    <a href="<?php echo mc_url('public/register.php?plan=professional'); ?>" class="btn btn-primary full-width">Choose Professional</a>
                </div>

                <!-- Enterprise Plan -->
                <div class="system-card">
                    <h3 class="system-title">Enterprise</h3>
                    <p class="system-desc">Full functionality for large operations.</p>
                    <div class="price-tag">
                        <span class="price-amount">$100</span>
                        <span class="price-period">/month</span>
                    </div>
                    <ul class="plan-list">
                        <li><i class="ph-bold ph-check"></i> All Pro features</li>
                        <li><i class="ph-bold ph-check"></i> Unlimited Staff</li>
                        <li><i class="ph-bold ph-check"></i> 24/7 Phone Support</li>
                    </ul>
                    <a href="<?php echo mc_url('public/register.php?plan=enterprise'); ?>" class="btn btn-outline full-width">Choose Enterprise</a>
>>>>>>> 062e3cc8d9b9f40dc40c6d6c6835e28f6f8a0d77
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
<<<<<<< HEAD
    <footer style="background: white; border-top: 1px solid #e2e8f0; padding: 40px 0; text-align: center; color: #64748b;">
        <div class="container">
            <div class="logo" style="justify-content: center; margin-bottom: 1rem;">
=======
    <footer>
        <div class="container text-center">
            <div class="logo footer-brand footer-brand--center">
>>>>>>> 062e3cc8d9b9f40dc40c6d6c6835e28f6f8a0d77
                <div class="logo-icon">
                    <i class="ph-bold ph-cube"></i>
                </div>
                <span>Mekong CyberUnit</span>
            </div>
<<<<<<< HEAD
            <p>&copy; 2026 Mekong CyberUnit. All rights reserved.</p>
=======
            <p class="copyright">&copy; 2026 Mekong CyberUnit. All rights reserved.</p>
>>>>>>> 062e3cc8d9b9f40dc40c6d6c6835e28f6f8a0d77
        </div>
    </footer>

    <script src="<?php echo mc_url('public/js/loader.js'); ?>"></script>
</body>
</html>

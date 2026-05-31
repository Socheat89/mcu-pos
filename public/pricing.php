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
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@400;500;600;700&family=Sora:wght@300;400;500;600;700&family=Battambang:wght@300;400;700&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link rel="stylesheet" href="<?php echo mc_url('public/css/landing.css'); ?>">
    
    <!-- Favicon -->
    <link rel="icon" href="<?php echo mc_url('public/images/logo.png'); ?>" type="image/png">
    <link rel="shortcut icon" href="<?php echo mc_url('public/images/logo.png'); ?>" type="image/png">
    
    <!-- Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    
</head>
<body class="landing-page">
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
                <a href="<?php echo mc_url('public/index.php#features'); ?>" class="nav-item">Features</a>
                <a href="<?php echo mc_url('public/index.php#pricing'); ?>" class="nav-item">Pricing</a>
                <a href="<?php echo mc_url('public/index.php#contact'); ?>" class="nav-item">Contact</a>
            </nav>
            
            <div class="flex items-center gap-4">
                <a href="<?php echo mc_url('public/login.php'); ?>" class="nav-item">Sign In</a>
                <a href="<?php echo mc_url('public/register.php'); ?>" class="btn btn-primary">Get Started</a>
            </div>
        </div>
    </header>

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
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container text-center">
            <div class="logo footer-brand footer-brand--center">
                <div class="logo-icon">
                    <i class="ph-bold ph-cube"></i>
                </div>
                <span>Mekong CyberUnit</span>
            </div>
            <p class="copyright">&copy; 2026 Mekong CyberUnit. All rights reserved.</p>
        </div>
    </footer>

    <script src="<?php echo mc_url('public/js/loader.js'); ?>"></script>
</body>
</html>

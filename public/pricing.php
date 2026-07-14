<?php
session_start();
require_once __DIR__ . '/../core/helpers/url.php';
require_once __DIR__ . '/../core/classes/Database.php';

// Load plans and their features from DB
$db = Database::getInstance();
$systems = $db->fetchAll("SELECT * FROM systems WHERE status = 'active' ORDER BY price ASC");

// Load module features mapping
$existing = $db->fetchAll("SELECT * FROM system_modules");
$mappings = [];
foreach ($existing as $m) {
    $mappings[$m['system_id']][$m['module_name']][] = $m['feature_key'];
}

// Feature labels
$featureLabels = [
    'pos_core'           => 'POS Terminal & Dashboard',
    'pos_orders'         => 'Order History & Management',
    'pos_inventory'      => 'Product & Inventory Management',
    'pos_customers'      => 'Customer Management',
    'pos_reports'        => 'Sales Reports & Analytics',
    'pos_holds'          => 'Hold Orders (Suspend)',
    'pos_digital_menu'   => 'Digital Menu (QR Code)',
    'pos_settings'       => 'POS Settings',
    'pos_sessions'       => 'Cash Control Sessions',
    'pos_cashiers'       => 'Cashier Management',
    'cloud_storage'      => 'Cloud Storage',
    'priority_support'   => '24/7 Priority Support',
];

// Build feature lists for each plan
$planFeatures = [];
foreach ($systems as $system) {
    $sid = $system['id'];
    $features = [];
    $systemModules = $mappings[$sid] ?? [];

    // POS features
    if (isset($systemModules['pos'])) {
        foreach ($systemModules['pos'] as $feat) {
            $key = 'pos_' . $feat;
            if (isset($featureLabels[$key])) {
                $features[] = $featureLabels[$key];
            }
        }
    }

    // Store limit info
    $storeLimit = (int)($system['store_limit'] ?? 1);
    $cashierLimit = (int)($system['cashier_limit'] ?? 1);
    if ($storeLimit > 0) {
        $features[] = "Up to {$storeLimit} Store" . ($storeLimit > 1 ? 's' : '');
    } else {
        $features[] = "Unlimited Stores";
    }
    if ($cashierLimit > 0) {
        $features[] = "Up to {$cashierLimit} Cashier" . ($cashierLimit > 1 ? 's' : '');
    } else {
        $features[] = "Unlimited Cashiers";
    }

    // Add cloud storage & support for higher plans
    if ($system['price'] >= 30) {
        $features[] = $featureLabels['cloud_storage'];
    }
    if ($system['price'] >= 50) {
        $features[] = $featureLabels['priority_support'];
    }

    $planFeatures[$sid] = $features;
}

// Plan card colors & badges
$planMeta = [];
foreach ($systems as $i => $system) {
    $meta = ['badge' => '', 'accent' => '#06b6d4', 'btn' => 'btn-outline'];
    $price = (float)$system['price'];
    if ($price === 0.00) {
        // Free Trial
        $meta = ['badge' => 'Free Trial', 'accent' => '#059669', 'btn' => 'btn-primary'];
    } elseif ($i === 1 && count($systems) >= 3) {
        $meta = ['badge' => 'Popular', 'accent' => '#8b5cf6', 'btn' => 'btn-primary'];
    }
    $planMeta[$system['id']] = $meta;
}
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
    <link rel="icon" href="<?php echo mc_url('public/images/my-logo.jpg'); ?>" type="image/jpeg">
    <link rel="shortcut icon" href="<?php echo mc_url('public/images/my-logo.jpg'); ?>" type="image/jpeg">
    
    <!-- Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    
</head>
<body class="landing-page">

    <div class="page-loader" id="pageLoader">
        <div class="loader-card">
            <div class="loader-logo">
                <img src="<?php echo mc_url('public/images/my-logo.jpg'); ?>" alt="MCU" style="width:100%;height:100%;object-fit:contain;">
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
                    <img src="<?php echo mc_url('public/images/my-logo.jpg'); ?>" alt="MCU" style="width:100%;height:100%;object-fit:contain;">
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
        <div class="container py-5">
            <div class="text-center mb-4">
                <div class="section-kicker"><i class="ph-bold ph-credit-card"></i> Transparent Pricing</div>
                <h1 class="fw-bold">Plans that scale with you</h1>
                <p class="text-muted mx-auto" style="max-width:560px">No hidden fees, no per-transaction charges. Every plan includes unlimited transactions & free updates.</p>
                
                <!-- Monthly / Annual Toggle -->
                <div class="pricing-toggle" id="pricingToggle">
                    <button class="active" onclick="switchPricing('monthly', this)">Monthly</button>
                    <button onclick="switchPricing('annual', this)">Annual <span class="save-badge">Save up to 25%</span></button>
                </div>
            </div>

            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 align-items-stretch" id="pricingGrid">
                <?php foreach ($systems as $system):
                    $meta = $planMeta[$system['id']];
                    $features = $planFeatures[$system['id']] ?? [];
                    $name = htmlspecialchars($system['name']);
                    $sysPrice = (float)$system['price'];
                    $isFreeTrial = ($sysPrice === 0.00);
                    $isPopular = ($sysPrice > 0 && $sysPrice <= 30 && !$isFreeTrial);
                    
                    // Annual pricing
                    $annualPrice = $sysPrice * 12;
                    $annualBonus = 0;
                    if ($sysPrice >= 99) $annualBonus = 3;
                    elseif ($sysPrice >= 30) $annualBonus = 1;
                    $annualMonthly = $annualPrice / (12 + $annualBonus);
                    
                    $cardClass = '';
                    if ($isFreeTrial) $cardClass = 'trial-card';
                    elseif ($isPopular) $cardClass = 'popular';
                ?>
                <div class="col">
                <div class="pricing-card <?php echo $cardClass; ?>">
                    <?php if ($isPopular): ?>
                    <div class="pricing-badge">Popular</div>
                    <?php elseif ($isFreeTrial): ?>
                    <div class="pricing-badge" style="background:linear-gradient(135deg,#059669,#047857);">Free</div>
                    <?php endif; ?>
                    
                    <div class="pricing-plan-name"><?php echo $name; ?></div>
                    <div class="pricing-plan-desc"><?php echo htmlspecialchars($system['description'] ?: 'Perfect for growing businesses.'); ?></div>
                    
                    <!-- Monthly Price -->
                    <div class="pricing-price-row monthly-price">
                        <?php if ($isFreeTrial): ?>
                            <span class="price-amount" style="color:#059669;">Free</span>
                        <?php else: ?>
                            <span class="price-amount">$<?php echo number_format($sysPrice, 0); ?></span>
                            <span class="price-period">/mo</span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Annual Price -->
                    <?php if (!$isFreeTrial): ?>
                    <div class="pricing-price-row annual-price" style="display:none;">
                        <span class="price-amount">$<?php echo number_format($annualMonthly, 0); ?></span>
                        <span class="price-period">/mo</span>
                    </div>
                    <div class="annual-save annual-price" style="display:none;">
                        <i class="ph-bold ph-arrow-down"></i> $<?php echo number_format($sysPrice, 0); ?>/mo when billed annually
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($annualBonus > 0): ?>
                    <div class="annual-bonus annual-price" style="display:none;">
                        <i class="ph-bold ph-gift"></i>
                        <span><strong><?php echo $annualBonus; ?> month<?php echo $annualBonus>1?'s':''; ?> free</strong> with annual plan</span>
                    </div>
                    <?php endif; ?>
                    
                    <hr class="pricing-divider">
                    
                    <ul class="feature-list mb-3">
                        <?php foreach ($features as $feat): ?>
                            <li><i class="ph-bold ph-check-circle"></i> <?php echo htmlspecialchars($feat); ?></li>
                        <?php endforeach; ?>
                        <?php if (empty($features)): ?>
                            <li><i class="ph-bold ph-check-circle"></i> Basic POS Access</li>
                        <?php endif; ?>
                    </ul>
                    
                    <a href="<?php echo mc_url('public/register.php?plan=' . urlencode($system['name'])); ?>" class="btn <?php echo ($isPopular||$isFreeTrial) ? 'btn-primary' : 'btn-outline-primary'; ?> w-100">
                        <?php echo $isFreeTrial ? 'Start Free Trial' : 'Get Started'; ?>
                    </a>
                </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container text-center">
            <div class="logo footer-brand footer-brand--center">

                <div class="logo-icon">
                    <img src="<?php echo mc_url('public/images/my-logo.jpg'); ?>" alt="MCU" style="width:100%;height:100%;object-fit:contain;">
                </div>
                <span>Mekong CyberUnit</span>
            </div>
            <p class="copyright">&copy; 2026 Mekong CyberUnit. All rights reserved.</p>

        </div>
    </footer>

    <script src="<?php echo mc_url('public/js/loader.js'); ?>"></script>
    <script>
        // ═══════════════════════════════════════════
        // PRICING TOGGLE — Monthly / Annual
        // ═══════════════════════════════════════════
        window.switchPricing = function(mode, btn) {
            document.querySelectorAll('#pricingToggle button').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            document.querySelectorAll('.monthly-price').forEach(el => {
                el.style.display = mode === 'monthly' ? '' : 'none';
            });
            document.querySelectorAll('.annual-price').forEach(el => {
                el.style.display = mode === 'annual' ? '' : 'none';
            });
        };
    </script>
</body>
</html>

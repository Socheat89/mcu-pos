<?php
require_once __DIR__ . '/../core/bootstrap_session.php';
require_once __DIR__ . '/../core/helpers/url.php';
require_once __DIR__ . '/../core/classes/Database.php';
require_once __DIR__ . '/../core/classes/Language.php';

// Detect current language
$currentLang = Language::getLanguage();

// Annual promo definition: plan price => [free_months, promo_text_en, promo_text_km]
$annualPromos = [
    30.00 => [
        'free_months' => 1,
        'label_en'    => 'Buy 1 Year, Get 1 Month FREE',
        'label_km'    => 'ទិញ ១ ឆ្នាំ ទទួលបាន ១ ខែ ដោយឥតគិតថ្លៃ',
    ],
    99.99 => [
        'free_months' => 3,
        'label_en'    => 'Buy 1 Year, Get 3 Months FREE',
        'label_km'    => 'ទិញ ១ ឆ្នាំ ទទួលបាន ៣ ខែ ដោយឥតគិតថ្លៃ',
    ],
];

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
                <!-- Language Switcher -->
                <div class="lang-switcher">
                    <button class="lang-btn" id="langBtn" type="button">
                        <i class="ph-bold ph-translate"></i>
                        <?php
                        $langLabels = ['en' => '🇺🇸 EN', 'km' => '🇰🇭 ខ្មែរ', 'zh' => '🇨🇳 中文'];
                        echo $langLabels[$currentLang] ?? '🌐 EN';
                        ?>
                        <i class="ph-bold ph-caret-down" style="font-size:0.65rem;"></i>
                    </button>
                    <div class="lang-dropdown" id="langDropdown">
                        <a href="<?php echo mc_url('public/set_lang.php?lang=en'); ?>" class="lang-option <?php echo $currentLang === 'en' ? 'active' : ''; ?>">
                            <span class="lang-flag">🇺🇸</span> English
                        </a>
                        <a href="<?php echo mc_url('public/set_lang.php?lang=km'); ?>" class="lang-option <?php echo $currentLang === 'km' ? 'active' : ''; ?>">
                            <span class="lang-flag">🇰🇭</span> ខ្មែរ
                        </a>
                        <a href="<?php echo mc_url('public/set_lang.php?lang=zh'); ?>" class="lang-option <?php echo $currentLang === 'zh' ? 'active' : ''; ?>">
                            <span class="lang-flag">🇨🇳</span> 中文
                        </a>
                    </div>
                </div>
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
                <?php foreach ($systems as $system):
                    $meta = $planMeta[$system['id']];
                    $features = $planFeatures[$system['id']] ?? [];
                    $name = htmlspecialchars($system['name']);
                    $sysPrice = (float)$system['price'];
                    $price = number_format($sysPrice, 2);
                    $desc = htmlspecialchars($system['description'] ?: 'Perfect for growing businesses.');
                    $sid = $system['id'];
                    $isFreeTrial = ($sysPrice === 0.00);

                    // Determine annual promo
                    $promo = null;
                    foreach ($annualPromos as $promoPrice => $promoDef) {
                        if (abs($sysPrice - $promoPrice) < 0.01) {
                            $promo = $promoDef;
                            break;
                        }
                    }
                    $promoLabel = '';
                    if ($promo) {
                        $promoLabel = ($currentLang === 'km') ? $promo['label_km'] : $promo['label_en'];
                    }
                ?>
                <div class="system-card <?php echo $meta['badge'] ? 'popular-card' : ''; ?>" style="<?php echo $meta['badge'] ? '' : 'border-top: 3px solid ' . $meta['accent']; ?>">
                    <?php if ($meta['badge']): ?>
                        <div class="plan-badge" style="<?php echo $isFreeTrial ? 'background: #d1fae5; color: #065f46;' : ''; ?>"><?php echo $meta['badge']; ?></div>
                    <?php endif; ?>

                    <?php if ($promo): ?>
                        <div class="annual-badge"><i class="ph-bold ph-gift"></i>
                            <?php echo ($currentLang === 'km') ? 'ប្រូម៉ូ ប្រចាំឆ្នាំ' : 'Annual Promo'; ?>
                        </div>
                    <?php endif; ?>

                    <h3 class="system-title"><?php echo $name; ?></h3>
                    <p class="system-desc"><?php echo $desc; ?></p>

                    <?php if ($promo): ?>
                    <div class="promo-callout">
                        <i class="ph-bold ph-tag"></i>
                        <span><?php echo htmlspecialchars($promoLabel); ?></span>
                    </div>
                    <?php endif; ?>

                    <div class="price-tag">
                        <?php if ($isFreeTrial): ?>
                            <span class="price-amount" style="font-size: 2.5rem;">Free</span>
                            <span class="price-period">for 7 days</span>
                        <?php else: ?>
                            <span class="price-amount">$<?php echo $price; ?></span>
                            <span class="price-period">/month</span>
                        <?php endif; ?>
                    </div>
                    <ul class="plan-list">
                        <?php foreach ($features as $feat): ?>
                            <li><i class="ph-bold ph-check-circle" style="color:#06b6d4;"></i> <?php echo htmlspecialchars($feat); ?></li>
                        <?php endforeach; ?>
                        <?php if (empty($features)): ?>
                            <li><i class="ph-bold ph-check-circle" style="color:#06b6d4;"></i> Basic POS Access</li>
                        <?php endif; ?>
                        <?php if ($promo): ?>
                            <li style="color:#d97706; font-weight:700;">
                                <i class="ph-bold ph-star" style="color:#F59E0B;"></i>
                                <?php
                                if ($currentLang === 'km') {
                                    echo '១ ឆ្នាំ = ' . (12 + $promo['free_months']) . ' ខែ (' . $promo['free_months'] . ' ខែ ឥតគិតថ្លៃ)';
                                } else {
                                    echo '1 Year = ' . (12 + $promo['free_months']) . ' Months (' . $promo['free_months'] . ' FREE)';
                                }
                                ?>
                            </li>
                        <?php endif; ?>
                    </ul>
                    <a href="<?php echo mc_url('public/register.php?plan=' . urlencode($system['name'])); ?>" class="btn <?php echo $meta['btn']; ?> full-width" <?php echo $isFreeTrial ? 'style="background: #059669; border-color: #059669;"' : ''; ?>>
                        <?php echo $isFreeTrial ? 'Start Free Trial' : ('Choose ' . $name); ?>
                    </a>
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
</body>
</html>

<?php
session_start();
require_once __DIR__ . '/../core/helpers/url.php';
require_once __DIR__ . '/../core/classes/Database.php';
require_once __DIR__ . '/../core/classes/Language.php';

// Initialize language
Language::init();
$lang = Language::getCurrentLang();

// Load plans and their features from DB
$db = Database::getInstance();
$systems = $db->fetchAll("SELECT * FROM systems WHERE status = 'active' ORDER BY price ASC");

// Load module features mapping
$existing = $db->fetchAll("SELECT * FROM system_modules");
$mappings = [];
foreach ($existing as $m) {
    $mappings[$m['system_id']][$m['module_name']][] = $m['feature_key'];
}

// Feature labels (translatable)
$featureLabels = [
    'pos_core'           => __('pricing_feature_pos_core'),
    'pos_orders'         => __('pricing_feature_pos_orders'),
    'pos_inventory'      => __('pricing_feature_pos_inventory'),
    'pos_customers'      => __('pricing_feature_pos_customers'),
    'pos_reports'        => __('pricing_feature_pos_reports'),
    'pos_holds'          => __('pricing_feature_pos_holds'),
    'pos_digital_menu'   => __('pricing_feature_pos_digital_menu'),
    'pos_settings'       => __('pricing_feature_pos_settings'),
    'pos_sessions'       => __('pricing_feature_pos_sessions'),
    'pos_cashiers'       => __('pricing_feature_pos_cashiers'),
    'cloud_storage'      => __('pricing_feature_cloud_storage'),
    'priority_support'   => __('pricing_feature_priority_support'),
];

// Build feature lists for each plan
$planFeatures = [];
foreach ($systems as $system) {
    $sid = $system['id'];
    $features = [];
    $systemModules = $mappings[$sid] ?? [];

    if (isset($systemModules['pos'])) {
        foreach ($systemModules['pos'] as $feat) {
            $key = 'pos_' . $feat;
            if (isset($featureLabels[$key])) {
                $features[] = $featureLabels[$key];
            }
        }
    }

    $storeLimit = (int)($system['store_limit'] ?? 1);
    $cashierLimit = (int)($system['cashier_limit'] ?? 1);
    if ($storeLimit > 0) {
        $features[] = __('pricing_up_to_stores', ['count' => $storeLimit]);
    } else {
        $features[] = __('pricing_unlimited_stores');
    }
    if ($cashierLimit > 0) {
        $features[] = __('pricing_up_to_cashiers', ['count' => $cashierLimit]);
    } else {
        $features[] = __('pricing_unlimited_cashiers');
    }

    if ($system['price'] >= 30) {
        $features[] = $featureLabels['cloud_storage'];
    }
    if ($system['price'] >= 50) {
        $features[] = $featureLabels['priority_support'];
    }

    $planFeatures[$sid] = $features;
}

// Define yearly promo plans
$yearlyPromos = [
    'pos' => ['free_months' => 1, 'yearly_price' => 30.00],
    'full_pos' => ['free_months' => 3, 'yearly_price' => 99.99],
];

// Plan card colors & badges
$planMeta = [];
foreach ($systems as $i => $system) {
    $meta = ['badge' => '', 'accent' => '#06b6d4', 'btn' => 'btn-outline', 'borderColor' => '#06b6d4'];
    $price = (float)$system['price'];
    $nameLower = strtolower(str_replace(' ', '_', $system['name']));
    
    if ($price === 0.00) {
        $meta = ['badge' => __('pricing_badge_free_trial'), 'accent' => '#10b981', 'btn' => 'btn-primary', 'borderColor' => '#10b981'];
    } elseif (isset($yearlyPromos[$nameLower])) {
        $meta = ['badge' => __('pricing_badge_best_value'), 'accent' => '#8b5cf6', 'btn' => 'btn-primary', 'borderColor' => '#8b5cf6'];
    } elseif ($i === 1 && count($systems) >= 3) {
        $meta = ['badge' => __('pricing_badge_popular'), 'accent' => '#f59e0b', 'btn' => 'btn-primary', 'borderColor' => '#f59e0b'];
    }
    $planMeta[$system['id']] = $meta;
}

// Determine if a plan has yearly promo
function getYearlyPromo($systemName) {
    $nameLower = strtolower(str_replace(' ', '_', $systemName));
    $promos = [
        'pos' => ['free_months' => 1, 'yearly_price' => 30.00],
        'full_pos' => ['free_months' => 3, 'yearly_price' => 99.99],
    ];
    return $promos[$nameLower] ?? null;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('pricing_page_title'); ?> - Mekong CyberUnit</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@400;500;600;700&family=Sora:wght@300;400;500;600;700&family=Battambang:wght@300;400;700;900&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="<?php echo mc_url('public/css/landing.css'); ?>">
    
    <!-- Favicon -->
    <link rel="icon" href="<?php echo mc_url('public/images/my-logo.jpg'); ?>" type="image/jpeg">
    <link rel="shortcut icon" href="<?php echo mc_url('public/images/my-logo.jpg'); ?>" type="image/jpeg">
    
    <!-- Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <style>
        /* ═══════════════════════════════════════════
           Pricing Page v2 — Modern & Premium
           ═══════════════════════════════════════════ */
        :root {
            --pricing-bg: #f0f5ff;
            --card-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            --card-shadow-hover: 0 20px 50px rgba(0,0,0,0.1);
            --card-radius: 1.25rem;
            --transition-smooth: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body.pricing-page {
            background: 
                radial-gradient(ellipse 80% 50% at 50% -10%, rgba(139, 92, 246, 0.06), transparent),
                radial-gradient(ellipse 60% 40% at 85% 60%, rgba(13, 148, 136, 0.05), transparent),
                linear-gradient(180deg, #f8fafc 0%, #f1f5f9 40%, #e8ecf1 100%);
            background-attachment: fixed;
            min-height: 100vh;
        }

        /* ── Language Switcher ── */
        .lang-switcher {
            display: flex;
            align-items: center;
            gap: 2px;
            background: rgba(255,255,255,0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 50px;
            padding: 3px;
        }
        .lang-switcher a {
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 0.78rem;
            font-weight: 600;
            text-decoration: none;
            color: #64748b;
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        .lang-switcher a.active {
            background: #0D9488;
            color: #fff;
            box-shadow: 0 2px 8px rgba(13,148,136,0.3);
        }
        .lang-switcher a:hover:not(.active) {
            background: rgba(13,148,136,0.1);
            color: #0D9488;
        }

        /* ── Billing Toggle ── */
        .billing-toggle-wrapper {
            display: flex;
            justify-content: center;
            margin-bottom: 2.5rem;
        }
        .billing-toggle {
            display: flex;
            background: #fff;
            border-radius: 50px;
            padding: 5px;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(0,0,0,0.06);
        }
        .billing-option {
            padding: 10px 24px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.88rem;
            cursor: pointer;
            transition: var(--transition-smooth);
            border: none;
            background: transparent;
            color: #64748b;
            position: relative;
            white-space: nowrap;
        }
        .billing-option.active {
            background: #0D9488;
            color: #fff;
            box-shadow: 0 4px 14px rgba(13,148,136,0.3);
        }
        .billing-option .save-badge {
            display: inline-block;
            background: #fef3c7;
            color: #92400e;
            font-size: 0.68rem;
            padding: 2px 8px;
            border-radius: 50px;
            margin-left: 6px;
            font-weight: 700;
        }
        .billing-option.active .save-badge {
            background: rgba(255,255,255,0.25);
            color: #fff;
        }

        /* ── Pricing Grid ── */
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            max-width: 1100px;
            margin: 0 auto;
            align-items: start;
        }
        @media (min-width: 900px) {
            .pricing-grid.cols-3 { grid-template-columns: repeat(3, 1fr); }
            .pricing-grid.cols-4 { grid-template-columns: repeat(4, 1fr); }
        }

        /* ── Pricing Card ── */
        .price-card {
            background: #fff;
            border-radius: var(--card-radius);
            padding: 2rem 1.6rem 1.8rem;
            position: relative;
            transition: var(--transition-smooth);
            box-shadow: var(--card-shadow);
            border: 1.5px solid transparent;
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow: hidden;
        }
        .price-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, #06b6d4, #0D9488);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .price-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--card-shadow-hover);
            border-color: rgba(13,148,136,0.15);
        }
        .price-card:hover::before { opacity: 1; }
        
        .price-card.featured {
            border-color: #8b5cf6;
            box-shadow: 0 8px 32px rgba(139,92,246,0.12);
            transform: translateY(-4px);
        }
        .price-card.featured:hover {
            transform: translateY(-12px);
            box-shadow: 0 20px 50px rgba(139,92,246,0.18);
        }
        .price-card.featured::before {
            background: linear-gradient(90deg, #8b5cf6, #a78bfa);
            opacity: 1;
            height: 5px;
        }
        
        .price-card.free-trial {
            border-color: #10b981;
            background: linear-gradient(180deg, #f0fdf4 0%, #fff 30%);
        }
        .price-card.free-trial::before {
            background: linear-gradient(90deg, #10b981, #34d399);
            opacity: 1;
        }

        /* ── Card Badge ── */
        .card-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            padding: 5px 14px;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            z-index: 2;
        }
        .card-badge.best-value {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: #fff;
        }
        .card-badge.free-trial-badge {
            background: #d1fae5;
            color: #065f46;
        }
        .card-badge.popular-badge {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #fff;
        }

        /* ── Card Content ── */
        .price-card .plan-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            margin-bottom: 1rem;
        }
        .price-card .plan-icon.pos-icon {
            background: linear-gradient(135deg, rgba(13,148,136,0.12), rgba(13,148,136,0.04));
            color: #0D9488;
        }
        .price-card .plan-icon.premium-icon {
            background: linear-gradient(135deg, rgba(139,92,246,0.12), rgba(139,92,246,0.04));
            color: #8b5cf6;
        }
        .price-card .plan-icon.free-icon {
            background: linear-gradient(135deg, rgba(16,185,129,0.12), rgba(16,185,129,0.04));
            color: #10b981;
        }

        .price-card .plan-name {
            font-family: 'Unbounded', 'Battambang', sans-serif;
            font-weight: 700;
            font-size: 1.2rem;
            margin-bottom: 0.3rem;
            color: #0f172a;
        }
        .price-card .plan-desc {
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 1.2rem;
            line-height: 1.5;
        }

        /* ── Price Display ── */
        .price-display {
            margin-bottom: 0.3rem;
        }
        .price-display .monthly-price,
        .price-display .yearly-price {
            display: none;
        }
        .price-display .monthly-price.show,
        .price-display .yearly-price.show {
            display: block;
        }
        .price-amount-large {
            font-family: 'Unbounded', 'Battambang', sans-serif;
            font-size: 2.6rem;
            font-weight: 700;
            line-height: 1;
            letter-spacing: -0.03em;
            color: #0f172a;
        }
        .price-amount-large.free {
            font-size: 2rem;
            color: #10b981;
        }
        .price-period {
            font-size: 0.85rem;
            color: #94a3b8;
            font-weight: 500;
            margin-left: 2px;
        }
        .price-period-sm {
            font-size: 0.75rem;
            color: #94a3b8;
            display: block;
            margin-top: 2px;
        }

        /* ── Yearly Promo Badge ── */
        .yearly-promo {
            display: none;
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            border: 1px solid #fcd34d;
            color: #92400e;
            padding: 8px 14px;
            border-radius: 10px;
            font-size: 0.78rem;
            font-weight: 700;
            margin-top: 0.6rem;
            margin-bottom: 0.4rem;
            text-align: center;
            animation: pulse-promo 2s ease-in-out infinite;
        }
        .yearly-promo.show { display: block; }
        
        @keyframes pulse-promo {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.03); }
        }

        .yearly-original {
            text-decoration: line-through;
            color: #94a3b8;
            font-size: 0.8rem;
            margin-right: 6px;
        }

        /* ── Feature List ── */
        .feature-list-v2 {
            list-style: none;
            padding: 0;
            margin: 1.2rem 0 1.5rem;
            flex: 1;
        }
        .feature-list-v2 li {
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
            padding: 0.45rem 0;
            font-size: 0.88rem;
            color: #334155;
            line-height: 1.4;
        }
        .feature-list-v2 li i {
            color: #10b981;
            font-size: 1.1rem;
            margin-top: 1px;
            flex-shrink: 0;
        }
        .feature-list-v2 li.disabled {
            color: #cbd5e1;
        }
        .feature-list-v2 li.disabled i {
            color: #e2e8f0;
        }

        /* ── CTA Button ── */
        .btn-cta {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.85rem 1.5rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.92rem;
            text-decoration: none;
            transition: var(--transition-smooth);
            border: none;
            cursor: pointer;
            font-family: inherit;
        }
        .btn-cta-primary {
            background: linear-gradient(135deg, #0D9488, #0F766E);
            color: #fff;
            box-shadow: 0 4px 16px rgba(13,148,136,0.3);
        }
        .btn-cta-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(13,148,136,0.4);
        }
        .btn-cta-outline {
            background: #fff;
            color: #0D9488;
            border: 2px solid rgba(13,148,136,0.2);
        }
        .btn-cta-outline:hover {
            background: rgba(13,148,136,0.04);
            border-color: #0D9488;
        }
        .btn-cta-purple {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: #fff;
            box-shadow: 0 4px 16px rgba(139,92,246,0.3);
        }
        .btn-cta-purple:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(139,92,246,0.4);
        }
        .btn-cta-green {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
            box-shadow: 0 4px 16px rgba(16,185,129,0.3);
        }
        .btn-cta-green:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(16,185,129,0.4);
        }

        /* ── Section Header ── */
        .pricing-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        .pricing-header .kicker {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 1rem;
            border-radius: 50px;
            background: rgba(139,92,246,0.08);
            color: #7c3aed;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            margin-bottom: 1rem;
            border: 1px solid rgba(139,92,246,0.12);
        }
        .pricing-header h1 {
            font-family: 'Unbounded', 'Battambang', sans-serif;
            font-size: clamp(1.8rem, 3.5vw, 2.6rem);
            font-weight: 700;
            letter-spacing: -0.03em;
            color: #0f172a;
            margin-bottom: 0.6rem;
        }
        .pricing-header p {
            color: #64748b;
            font-size: 1rem;
            max-width: 560px;
            margin: 0 auto;
        }

        /* ── Guarantee Strip ── */
        .guarantee-strip {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2.5rem;
            padding: 1rem 1.5rem;
            background: #fff;
            border-radius: 50px;
            box-shadow: var(--card-shadow);
            font-size: 0.85rem;
            color: #64748b;
            max-width: 420px;
            margin-left: auto;
            margin-right: auto;
        }
        .guarantee-strip i {
            color: #10b981;
            font-size: 1.2rem;
        }
        .guarantee-strip strong {
            color: #0f172a;
        }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .pricing-grid {
                grid-template-columns: 1fr;
                max-width: 420px;
                margin: 0 auto;
            }
            .price-card.featured { transform: none; }
            .price-card.featured:hover { transform: translateY(-8px); }
            .pricing-header h1 { font-size: 1.6rem; }
        }
    </style>
</head>
<body class="pricing-page">

    <!-- Page Loader -->
    <div class="page-loader" id="pageLoader">
        <div class="loader-card">
            <div class="loader-logo">
                <img src="<?php echo mc_url('public/images/my-logo.jpg'); ?>" alt="MCU" style="width:100%;height:100%;object-fit:contain;">
            </div>
            <p class="loader-title">Mekong CyberUnit</p>
            <p class="loader-caption"><?php echo __('pricing_loading'); ?></p>
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
                <a href="<?php echo mc_url('public/index.php#features'); ?>" class="nav-item"><?php echo __('nav_features'); ?></a>
                <a href="<?php echo mc_url('public/pricing.php'); ?>" class="nav-item active"><?php echo __('nav_pricing'); ?></a>
                <a href="<?php echo mc_url('public/index.php#contact'); ?>" class="nav-item"><?php echo __('nav_contact'); ?></a>
            </nav>
            
            <div class="flex items-center gap-3">
                <!-- Language Switcher -->
                <div class="lang-switcher">
                    <a href="<?php echo mc_url('public/set_lang.php?lang=km'); ?>" class="<?php echo $lang === 'km' ? 'active' : ''; ?>" title="ខ្មែរ">🇰🇭 ខ្មែរ</a>
                    <a href="<?php echo mc_url('public/set_lang.php?lang=en'); ?>" class="<?php echo $lang === 'en' ? 'active' : ''; ?>" title="English">🇬🇧 EN</a>
                    <a href="<?php echo mc_url('public/set_lang.php?lang=zh'); ?>" class="<?php echo $lang === 'zh' ? 'active' : ''; ?>" title="中文">🇨🇳 中文</a>
                </div>
                <a href="<?php echo mc_url('public/login.php'); ?>" class="nav-item"><?php echo __('nav_sign_in'); ?></a>
                <a href="<?php echo mc_url('public/register.php'); ?>" class="btn btn-primary"><?php echo __('nav_get_started'); ?></a>
            </div>
        </div>
    </header>

    <!-- Pricing Section -->
    <section class="pricing-section" id="pricing" style="padding: 5rem 0 4rem;">
        <div class="container">
            <div class="pricing-header">
                <div class="kicker"><i class="ph-bold ph-credit-card"></i> <?php echo __('pricing_kicker'); ?></div>
                <h1><?php echo __('pricing_heading'); ?></h1>
                <p><?php echo __('pricing_subheading'); ?></p>
            </div>

            <!-- Billing Toggle -->
            <div class="billing-toggle-wrapper">
                <div class="billing-toggle" role="group">
                    <button class="billing-option active" onclick="toggleBilling('monthly', this)" id="btnMonthly">
                        <?php echo __('pricing_monthly'); ?>
                    </button>
                    <button class="billing-option" onclick="toggleBilling('yearly', this)" id="btnYearly">
                        <?php echo __('pricing_yearly'); ?>
                        <span class="save-badge"><?php echo __('pricing_save_badge'); ?></span>
                    </button>
                </div>
            </div>

            <!-- Pricing Cards Grid -->
            <div class="pricing-grid <?php echo count($systems) <= 3 ? 'cols-3' : 'cols-4'; ?>">
                <?php foreach ($systems as $system):
                    $meta = $planMeta[$system['id']];
                    $features = $planFeatures[$system['id']] ?? [];
                    $name = htmlspecialchars($system['name']);
                    $sysPrice = (float)$system['price'];
                    $desc = htmlspecialchars($system['description'] ?: __('pricing_default_desc'));
                    $sid = $system['id'];
                    $isFreeTrial = ($sysPrice === 0.00);
                    $promo = getYearlyPromo($system['name']);
                    $nameLower = strtolower(str_replace(' ', '_', $system['name']));
                    
                    // Card style classes
                    $cardClass = 'price-card';
                    $btnClass = 'btn-cta btn-cta-outline';
                    $iconClass = 'plan-icon pos-icon';
                    $badgeHtml = '';
                    $featuredStyle = '';
                    
                    if ($isFreeTrial) {
                        $cardClass .= ' free-trial';
                        $btnClass = 'btn-cta btn-cta-green';
                        $iconClass = 'plan-icon free-icon';
                        $badgeHtml = '<div class="card-badge free-trial-badge">' . __('pricing_badge_free_trial') . '</div>';
                    } elseif ($promo) {
                        $cardClass .= ' featured';
                        $btnClass = 'btn-cta btn-cta-purple';
                        $iconClass = 'plan-icon premium-icon';
                        $badgeHtml = '<div class="card-badge best-value">' . __('pricing_badge_best_value') . '</div>';
                    } elseif ($meta['badge'] === __('pricing_badge_popular')) {
                        $badgeHtml = '<div class="card-badge popular-badge">' . $meta['badge'] . '</div>';
                    }
                    
                    // Monthly price display
                    $monthlyPrice = $sysPrice;
                    
                    // Yearly price = monthly * 12, but for promo plans use the yearly price
                    if ($promo) {
                        $yearlyPrice = $promo['yearly_price'];
                        $freeMonths = $promo['free_months'];
                    } else {
                        $yearlyPrice = $sysPrice * 12;
                        $freeMonths = 0;
                    }
                    $monthlyEquivalent = $yearlyPrice / 12;
                ?>
                <div class="<?php echo $cardClass; ?>" style="<?php echo !$isFreeTrial && !$promo && $meta['badge'] !== __('pricing_badge_popular') ? 'border-top: 3px solid ' . $meta['borderColor'] . ';' : ''; ?>">
                    <?php echo $badgeHtml; ?>
                    
                    <div class="<?php echo $iconClass; ?>">
                        <?php if ($isFreeTrial): ?>
                            <i class="ph-bold ph-gift"></i>
                        <?php elseif ($promo): ?>
                            <i class="ph-bold ph-crown"></i>
                        <?php else: ?>
                            <i class="ph-bold ph-storefront"></i>
                        <?php endif; ?>
                    </div>
                    
                    <h3 class="plan-name"><?php echo $name; ?></h3>
                    <p class="plan-desc"><?php echo $desc; ?></p>
                    
                    <!-- Price Display -->
                    <div class="price-display">
                        <!-- Monthly -->
                        <div class="monthly-price show">
                            <?php if ($isFreeTrial): ?>
                                <span class="price-amount-large free"><?php echo __('pricing_free'); ?></span>
                                <span class="price-period-sm"><?php echo __('pricing_free_period'); ?></span>
                            <?php else: ?>
                                <span class="price-amount-large">$<?php echo number_format($monthlyPrice, 2); ?></span>
                                <span class="price-period"><?php echo __('pricing_per_month'); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Yearly -->
                        <div class="yearly-price">
                            <?php if ($isFreeTrial): ?>
                                <span class="price-amount-large free"><?php echo __('pricing_free'); ?></span>
                                <span class="price-period-sm"><?php echo __('pricing_free_period'); ?></span>
                            <?php else: ?>
                                <span class="price-amount-large">$<?php echo number_format($yearlyPrice, 2); ?></span>
                                <span class="price-period"><?php echo __('pricing_per_year'); ?></span>
                                <span class="price-period-sm">
                                    (~$<?php echo number_format($monthlyEquivalent, 2); ?><?php echo __('pricing_per_month_equiv'); ?>)
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Yearly Promo Badge -->
                    <?php if ($promo && $freeMonths > 0): ?>
                    <div class="yearly-promo" id="promo-<?php echo $sid; ?>">
                        🎁 <?php echo __('pricing_buy_year_free', ['months' => $freeMonths]); ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Feature List -->
                    <ul class="feature-list-v2">
                        <?php foreach ($features as $feat): ?>
                            <li><i class="ph-bold ph-check-circle"></i> <?php echo htmlspecialchars($feat); ?></li>
                        <?php endforeach; ?>
                        <?php if (empty($features)): ?>
                            <li><i class="ph-bold ph-check-circle"></i> <?php echo __('pricing_basic_pos'); ?></li>
                        <?php endif; ?>
                    </ul>
                    
                    <!-- CTA Button -->
                    <a href="<?php echo mc_url('public/register.php?plan=' . urlencode($system['name'])); ?>" class="<?php echo $btnClass; ?>" id="cta-<?php echo $sid; ?>">
                        <?php if ($isFreeTrial): ?>
                            <?php echo __('pricing_cta_free_trial'); ?>
                        <?php else: ?>
                            <?php echo __('pricing_cta_choose', ['plan' => $name]); ?>
                        <?php endif; ?>
                        <i class="ph-bold ph-arrow-right"></i>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Guarantee -->
            <div class="guarantee-strip">
                <i class="ph-bold ph-shield-check"></i>
                <span><?php echo __('pricing_guarantee_prefix'); ?> <strong><?php echo __('pricing_guarantee_highlight'); ?></strong> <?php echo __('pricing_guarantee_suffix'); ?></span>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer style="background: #0f172a; padding: 2.5rem 0; margin-top: 3rem;">
        <div class="container text-center">
            <div class="logo footer-brand footer-brand--center" style="justify-content:center; margin-bottom:1rem;">
                <div class="logo-icon">
                    <img src="<?php echo mc_url('public/images/my-logo.jpg'); ?>" alt="MCU" style="width:100%;height:100%;object-fit:contain;">
                </div>
                <span style="color:#fff;">Mekong CyberUnit</span>
            </div>
            <p class="copyright" style="color: rgba(255,255,255,0.45);">&copy; 2026 Mekong CyberUnit. <?php echo __('pricing_rights'); ?></p>
        </div>
    </footer>

    <script src="<?php echo mc_url('public/js/loader.js'); ?>"></script>
    <script>
        // Billing toggle
        let currentBilling = 'monthly';
        
        function toggleBilling(mode, btn) {
            currentBilling = mode;
            
            // Update toggle buttons
            document.getElementById('btnMonthly').classList.toggle('active', mode === 'monthly');
            document.getElementById('btnYearly').classList.toggle('active', mode === 'yearly');
            
            // Show/hide prices
            document.querySelectorAll('.monthly-price').forEach(el => el.classList.toggle('show', mode === 'monthly'));
            document.querySelectorAll('.yearly-price').forEach(el => el.classList.toggle('show', mode === 'yearly'));
            
            // Show/hide yearly promos
            document.querySelectorAll('.yearly-promo').forEach(el => el.classList.toggle('show', mode === 'yearly'));
        }
        
        // Page loader
        window.addEventListener('load', function() {
            setTimeout(function() {
                const loader = document.getElementById('pageLoader');
                if (loader) loader.classList.add('hidden');
            }, 600);
        });
    </script>
</body>
</html>

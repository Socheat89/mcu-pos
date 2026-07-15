<?php
require_once __DIR__ . '/../core/bootstrap_session.php';
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
    
    <style>
        :root {
            --brand: #308AC6;
            --brand-strong: #1F6896;
            --brand-light: #52A2D4;
            --mc-primary: #308AC6;
        }

        * { box-sizing: border-box; }

        body.landing-page {
            font-family: 'Sora', 'Battambang', sans-serif;
            margin: 0;
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
            color: #0f172a;
        }

        .container { max-width: 1200px; margin: 0 auto; padding: 0 1.5rem; }

        /* ── Header / Nav ── */
        .main-header {
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border-bottom: 1px solid rgba(0,0,0,0.06);
            position: sticky; top: 0; z-index: 100;
            padding: 0.75rem 0;
        }
        .nav-container {
            display: flex; align-items: center; justify-content: space-between;
            gap: 1.5rem; flex-wrap: wrap;
        }
        .logo {
            display: inline-flex; align-items: center; gap: 10px;
            text-decoration: none; flex-shrink: 0;
        }
        .logo-icon {
            width: 40px; height: 40px; border-radius: 10px;
            overflow: hidden; display: grid; place-items: center;
            box-shadow: 0 4px 12px rgba(48,138,198,0.12);
        }
        .logo span {
            font-family: 'Unbounded', sans-serif; font-weight: 700;
            font-size: 1rem; color: #0f172a; letter-spacing: -0.03em;
        }
        .nav-links { display: flex; gap: 0.25rem; flex-wrap: wrap; }
        .nav-item {
            font-weight: 600; font-size: 0.88rem; color: #475569;
            text-decoration: none; padding: 0.5rem 1rem; border-radius: 50px;
            transition: all 0.2s; white-space: nowrap;
        }
        .nav-item:hover { color: #0f172a; background: #f1f5f9; }
        .flex { display: flex; }
        .items-center { align-items: center; }
        .gap-4 { gap: 1rem; }

        /* ── Buttons ── */
        .btn {
            display: inline-flex; align-items: center; justify-content: center;
            gap: 8px; font-weight: 700; border-radius: 50px;
            padding: 0.7rem 1.6rem; font-size: 0.9rem;
            transition: all 0.25s ease; cursor: pointer; border: none;
            text-decoration: none; white-space: nowrap;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--brand), var(--brand-strong));
            color: #fff; box-shadow: 0 4px 16px rgba(48,138,198,0.25);
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(48,138,198,0.35); }
        .btn-outline {
            background: transparent; border: 2px solid rgba(48,138,198,0.2);
            color: var(--brand);
        }
        .btn-outline:hover { background: rgba(48,138,198,0.05); border-color: var(--brand); }
        .full-width { width: 100%; }

        /* ── Pricing Section ── */
        .pricing-section { padding: 5rem 0; }
        .section-header { text-align: center; margin-bottom: 3.5rem; }
        .section-kicker {
            display: inline-block; padding: 0.3rem 1rem;
            background: rgba(48,138,198,0.08); color: var(--brand);
            border-radius: 50px; font-weight: 700; font-size: 0.8rem;
            text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem;
        }
        .section-header h1 {
            font-family: 'Unbounded', sans-serif; font-size: 2.2rem;
            font-weight: 700; margin: 0.5rem 0; letter-spacing: -0.03em;
        }
        .section-header p { color: #64748b; font-size: 1rem; max-width: 500px; margin: 0 auto; }

        /* ── Pricing Grid ── */
        .systems-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            align-items: start;
        }
        .system-card {
            background: #fff; border-radius: 20px; padding: 2rem 1.8rem;
            border: 1px solid #e2e8f0; position: relative;
            transition: all 0.3s ease; display: flex; flex-direction: column;
        }
        .system-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
        }
        .popular-card {
            border: 2px solid #8b5cf6;
            box-shadow: 0 8px 30px rgba(139,92,246,0.12);
        }
        .plan-badge {
            position: absolute; top: -12px; right: 20px;
            padding: 0.25rem 0.9rem; border-radius: 50px;
            font-weight: 700; font-size: 0.72rem; letter-spacing: 0.02em;
            background: #ede9fe; color: #7c3aed;
        }
        .system-title {
            font-family: 'Unbounded', sans-serif; font-size: 1.2rem;
            font-weight: 700; margin: 0 0 0.5rem;
        }
        .system-desc { color: #64748b; font-size: 0.88rem; margin-bottom: 1.2rem; line-height: 1.5; }
        .price-tag { margin-bottom: 1.5rem; }
        .price-amount { font-family: 'Unbounded', sans-serif; font-size: 2.5rem; font-weight: 800; color: #0f172a; }
        .price-period { color: #64748b; font-size: 0.9rem; font-weight: 500; margin-left: 2px; }
        .plan-list {
            list-style: none; padding: 0; margin: 0 0 1.8rem;
            flex: 1; display: flex; flex-direction: column; gap: 0.6rem;
        }
        .plan-list li {
            display: flex; align-items: center; gap: 0.6rem;
            font-size: 0.88rem; color: #334155;
        }

        /* ── Footer ── */
        footer {
            text-align: center; padding: 3rem 1.5rem;
            border-top: 1px solid #e2e8f0; background: #fff;
        }
        .footer-brand {
            display: inline-flex; align-items: center; gap: 8px;
            margin-bottom: 0.8rem;
        }
        .footer-brand span {
            font-family: 'Unbounded', sans-serif; font-weight: 700;
            font-size: 0.95rem; color: #0f172a;
        }
        .copyright { font-size: 0.82rem; color: #94a3b8; margin: 0; }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .nav-container { justify-content: center; text-align: center; }
            .nav-links { justify-content: center; }
            .pricing-section { padding: 3rem 0; }
            .section-header h1 { font-size: 1.6rem; }
            .systems-grid {
                grid-template-columns: 1fr;
                max-width: 420px; margin: 0 auto;
            }
            .system-card { padding: 1.5rem 1.3rem; }
            .price-amount { font-size: 2rem; }
        }

        @media (max-width: 480px) {
            .main-header { padding: 0.5rem 0; }
            .logo span { font-size: 0.85rem; }
            .logo-icon { width: 34px; height: 34px; }
            .nav-item { font-size: 0.78rem; padding: 0.4rem 0.7rem; }
            .btn { padding: 0.6rem 1.2rem; font-size: 0.82rem; }
            .section-header h1 { font-size: 1.4rem; }
            .section-header p { font-size: 0.85rem; }
            .system-card { padding: 1.3rem 1rem; border-radius: 16px; }
            .plan-list li { font-size: 0.82rem; }
            footer { padding: 2rem 1rem; }
        }
    </style>
    
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
                    $price = number_format($sysPrice, 0);
                    $desc = htmlspecialchars($system['description'] ?: 'Perfect for growing businesses.');
                    $sid = $system['id'];
                    $isFreeTrial = ($sysPrice === 0.00);
                ?>
                <div class="system-card <?php echo $meta['badge'] ? 'popular-card' : ''; ?>" style="<?php echo $meta['badge'] ? '' : 'border-top: 3px solid ' . $meta['accent']; ?>">
                    <?php if ($meta['badge']): ?>
                        <div class="plan-badge" style="<?php echo $isFreeTrial ? 'background: #d1fae5; color: #065f46;' : ''; ?>"><?php echo $meta['badge']; ?></div>
                    <?php endif; ?>
                    <h3 class="system-title"><?php echo $name; ?></h3>
                    <p class="system-desc"><?php echo $desc; ?></p>
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

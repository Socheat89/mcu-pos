<?php
// tenant/dashboard.php
require_once __DIR__ . '/../core/classes/Database.php';
require_once __DIR__ . '/../core/classes/Tenant.php';
require_once __DIR__ . '/../core/classes/Auth.php';
require_once __DIR__ . '/../core/helpers/url.php';
require_once __DIR__ . '/../middleware/TenantMiddleware.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

TenantMiddleware::handle();
AuthMiddleware::handle();

$db = Database::getInstance();
$tenantId = Tenant::getId();
$user = Auth::user();

$urlPrefix = mc_base_path();

$subdomain = Tenant::getCurrent()['subdomain'];

// Get subscribed systems
$systems = $db->fetchAll(
    "SELECT s.name, ts.subscribed_at FROM tenant_systems ts 
     JOIN systems s ON ts.system_id = s.id 
     WHERE ts.tenant_id = ? AND ts.status = 'active'",
    [$tenantId]
);

// Get recent orders (if POS subscribed)
$hasPOS = Tenant::hasSystem('POS System');
$recentOrders = [];
if ($hasPOS) {
    $recentOrders = $db->fetchAll(
        "SELECT id, total, status, created_at FROM orders 
         WHERE tenant_id = ? ORDER BY created_at DESC LIMIT 5",
        [$tenantId]
    );
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo htmlspecialchars(Tenant::getCurrent()['name']); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@100;300;400;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6a5cff;
            --primary-dark: #5648d4;
            --secondary: #8a3ffc;
            --accent: #2dd4ff;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --bg: #f6f7fb;
            --card-bg: #ffffff;
            --text: #1e293b;
            --text-muted: #64748b;
            --border: rgba(30, 41, 59, 0.08);
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            --shadow-hover: 0 15px 40px rgba(0, 0, 0, 0.12);
        }

        * { 
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: "Battambang", ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
            background: 
                radial-gradient(900px 600px at 15% -10%, rgba(106, 92, 255, 0.15), transparent 60%),
                radial-gradient(900px 600px at 110% 10%, rgba(138, 63, 252, 0.12), transparent 60%),
                var(--bg);
            color: var(--text);
            min-height: 100vh;
            line-height: 1.6;
        }

        /* Navbar */
        .navbar {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        
        .navbar-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 72px;
        }
        
        .nav-brand {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .nav-brand i {
            font-size: 1.75rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .nav-links {
            display: flex;
            list-style: none;
            gap: 8px;
            align-items: center;
        }
        
        .nav-links a {
            color: var(--text);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            padding: 10px 18px;
            border-radius: 10px;
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .nav-links a:hover {
            background: rgba(106, 92, 255, 0.08);
            color: var(--primary);
        }
        
        .nav-links a.active {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
        }
        
        .nav-links .logout-btn {
            background: rgba(239, 68, 68, 0.08);
            color: var(--danger);
        }
        
        .nav-links .logout-btn:hover {
            background: var(--danger);
            color: white;
        }

        /* Language Switcher */
        .lang-switcher {
            position: relative;
            display: inline-block;
            margin-left: 10px;
        }
        
        .lang-btn {
            background: rgba(106, 92, 255, 0.08);
            color: var(--primary);
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }
        
        .lang-btn:hover {
            background: var(--primary);
            color: white;
        }
        
        .lang-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            padding-top: 15px; /* Bridge gap */
            display: none;
            z-index: 1100;
        }

        .lang-dropdown-inner {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            border: 1px solid var(--border);
            min-width: 160px;
            overflow: hidden;
        }
        
        .lang-switcher:hover .lang-dropdown,
        .lang-switcher.active .lang-dropdown {
            display: block;
        }
        
        .lang-dropdown a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            text-decoration: none;
            color: var(--text);
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .lang-dropdown a:hover {
            background: rgba(106, 92, 255, 0.05);
            color: var(--primary);
        }

        .lang-dropdown a.active {
            color: var(--primary);
            font-weight: 700;
            background: rgba(106, 92, 255, 0.08);
        }

        /* Main Container */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 32px;
        }

        /* Welcome Header */
        .welcome-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 48px;
            border-radius: 20px;
            margin-bottom: 32px;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
        }
        
        .welcome-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }
        
        .welcome-header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 8px;
            position: relative;
        }
        
        .welcome-header p {
            font-size: 1.1rem;
            opacity: 0.95;
            position: relative;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }
        
        .stat-card {
            background: var(--card-bg);
            padding: 28px;
            border-radius: 16px;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-hover);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            flex-shrink: 0;
        }
        
        .stat-icon.purple {
            background: linear-gradient(135deg, rgba(106, 92, 255, 0.12), rgba(138, 63, 252, 0.12));
            color: var(--primary);
        }
        
        .stat-icon.green {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.12), rgba(5, 150, 105, 0.12));
            color: var(--success);
        }
        
        .stat-icon.orange {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.12), rgba(217, 119, 6, 0.12));
            color: var(--warning);
        }
        
        .stat-icon.blue {
            background: linear-gradient(135deg, rgba(45, 212, 255, 0.12), rgba(59, 130, 246, 0.12));
            color: var(--accent);
        }
        
        .stat-content h3 {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 4px;
        }
        
        .stat-content p {
            color: var(--text-muted);
            font-size: 0.95rem;
            font-weight: 600;
        }

        /* Main Grid */
        .main-grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 24px;
        }
        
        .card {
            background: var(--card-bg);
            border-radius: 16px;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            border: 1px solid var(--border);
        }
        
        .card:hover {
            box-shadow: var(--shadow-hover);
        }
        
        .card-full { grid-column: span 12; }
        .card-half { grid-column: span 6; }
        .card-third { grid-column: span 4; }
        
        .card-header {
            padding: 24px 28px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .card-header h3 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card-header i {
            font-size: 1.4rem;
            color: var(--primary);
        }
        
        .card-body {
            padding: 28px;
        }

        /* Systems List */
        .systems-list {
            list-style: none;
        }
        
        .system-item {
            padding: 16px 0;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.2s ease;
        }
        
        .system-item:last-child { border-bottom: none; }
        
        .system-item:hover {
            padding-left: 8px;
        }
        
        .system-info {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        
        .system-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: linear-gradient(135deg, rgba(106, 92, 255, 0.12), rgba(138, 63, 252, 0.12));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 1.2rem;
        }
        
        .system-name {
            font-weight: 600;
            color: var(--text);
            font-size: 1rem;
        }
        
        .system-date {
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Action Buttons */
        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 14px;
        }
        
        .btn {
            padding: 14px 24px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            text-align: center;
            transition: all 0.25s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(106, 92, 255, 0.3);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }
        
        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(245, 158, 11, 0.3);
        }
        
        .btn-info {
            background: linear-gradient(135deg, #2dd4ff 0%, #3b82f6 100%);
            color: white;
        }
        
        .btn-info:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(45, 212, 255, 0.3);
        }

        /* POS module: remove gradient backgrounds on Quick Actions */
        .pos-actions .btn {
            background: transparent !important;
            border: 1px solid var(--border);
            color: var(--text) !important;
            box-shadow: none !important;
        }

        .pos-actions .btn:hover {
            background: rgba(106, 92, 255, 0.08) !important;
            box-shadow: none !important;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 16px 12px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        
        th {
            background: rgba(106, 92, 255, 0.04);
            font-weight: 700;
            color: var(--text);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }
        
        tbody tr {
            transition: background 0.2s ease;
        }
        
        tbody tr:hover {
            background: rgba(106, 92, 255, 0.03);
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 700;
            gap: 6px;
        }
        
        .status-completed {
            background: rgba(16, 185, 129, 0.12);
            color: #059669;
        }
        
        .status-pending {
            background: rgba(245, 158, 11, 0.12);
            color: #d97706;
        }
        
        .status-cancelled {
            background: rgba(239, 68, 68, 0.12);
            color: #dc2626;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 48px 24px;
        }
        
        .empty-state i {
            font-size: 3.5rem;
            color: var(--text-muted);
            opacity: 0.5;
            margin-bottom: 16px;
        }
        
        .empty-state p {
            color: var(--text-muted);
            font-size: 1.05rem;
            margin-bottom: 16px;
        }
        
        .empty-state a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
        }
        
        .empty-state a:hover {
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .card-half, .card-third {
                grid-column: span 12;
            }
        }
        
        @media (max-width: 768px) {
            .navbar-container {
                padding: 0 16px;
            }
            
            .container {
                padding: 16px;
            }
            
            .welcome-header {
                padding: 32px 24px;
            }
            
            .welcome-header h1 {
                font-size: 1.75rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .nav-links a span {
                display: none;
            }
            
            .action-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="navbar-container">
            <div class="nav-brand">
                <i class="fas fa-building"></i>
                <?php echo htmlspecialchars(Tenant::getCurrent()['name']); ?>
            </div>
            <ul class="nav-links">
                <li><a href="<?php echo $urlPrefix; ?>/<?php echo $subdomain; ?>/dashboard" class="active"><i class="fas fa-home"></i><span><?php echo __('dashboard'); ?></span></a></li>
                <li><a href="<?php echo $urlPrefix; ?>/<?php echo $subdomain; ?>/users"><i class="fas fa-users"></i><span><?php echo __('profile'); ?></span></a></li>
                <li><a href="<?php echo $urlPrefix; ?>/<?php echo $subdomain; ?>/settings"><i class="fas fa-cog"></i><span><?php echo __('settings'); ?></span></a></li>
                <li><a href="<?php echo $urlPrefix; ?>/<?php echo $subdomain; ?>/logout" class="logout-btn"><i class="fas fa-sign-out-alt"></i><span><?php echo __('logout'); ?></span></a></li>
                <li class="lang-switcher" id="langSwitcher">
                    <button class="lang-btn" onclick="toggleLangDropdown(event)">
                        <i class="fas fa-globe"></i>
                        <?php 
                        $curr = Language::getCurrentLang();
                        echo $curr == 'en' ? 'English' : ($curr == 'km' ? 'ភាសាខ្មែរ' : '中文');
                        ?>
                        <i class="fas fa-chevron-down" style="font-size: 0.7rem;"></i>
                    </button>
                    <div class="lang-dropdown">
                        <div class="lang-dropdown-inner">
                            <a href="<?php echo mc_url('public/set_lang.php?lang=en'); ?>" class="<?php echo $curr == 'en' ? 'active' : ''; ?>">
                                <img src="https://flagcdn.com/w20/gb.png" width="20" alt="English"> English
                            </a>
                            <a href="<?php echo mc_url('public/set_lang.php?lang=km'); ?>" class="<?php echo $curr == 'km' ? 'active' : ''; ?>">
                                <img src="https://flagcdn.com/w20/kh.png" width="20" alt="Khmer"> ភាសាខ្មែរ
                            </a>
                            <a href="<?php echo mc_url('public/set_lang.php?lang=zh'); ?>" class="<?php echo $curr == 'zh' ? 'active' : ''; ?>">
                                <img src="https://flagcdn.com/w20/cn.png" width="20" alt="Chinese"> 中文
                            </a>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container">
        <!-- Welcome Header -->
        <div class="welcome-header">
            <h1><?php echo __('welcome_back'); ?>, <?php echo htmlspecialchars($user['username']); ?>! 👋</h1>
            <p><?php echo __('business_happening'); ?></p>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo count($systems); ?></h3>
                    <p><?php echo __('active_systems'); ?></p>
                </div>
            </div>

            <?php if ($hasPOS): ?>
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo count($recentOrders); ?></h3>
                    <p><?php echo __('recent_orders'); ?></p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-content">
                    <?php
                    $totalRevenue = array_sum(array_column($recentOrders, 'total'));
                    ?>
                    <h3>$<?php echo number_format($totalRevenue, 0); ?></h3>
                    <p><?php echo __('total_revenue'); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo date('H:i'); ?></h3>
                    <p><?php echo date('l, M j'); ?></p>
                </div>
            </div>
        </div>

        <!-- Main Grid -->
        <div class="main-grid">
            <!-- Your Systems Card -->
            <div class="card <?php echo ($hasPOS && Auth::isTenantAdmin()) ? 'card-half' : 'card-full'; ?>">
                <div class="card-header">
                    <h3><i class="fas fa-layer-group"></i> <?php echo __('your_systems'); ?></h3>
                </div>
                <div class="card-body">
                    <?php if (empty($systems)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p><?php echo __('no_systems'); ?></p>
                        </div>
                    <?php else: ?>
                        <ul class="systems-list">
                            <?php foreach ($systems as $system): ?>
                                <li class="system-item">
                                    <div class="system-info">
                                        <div class="system-icon">
                                            <i class="fas fa-cube"></i>
                                        </div>
                                        <span class="system-name"><?php echo htmlspecialchars($system['name']); ?></span>
                                    </div>
                                    <span class="system-date"><?php echo __('since'); ?> <?php echo date('M j, Y', strtotime($system['subscribed_at'])); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Administration Card (Admin Only) -->
            <?php if (Auth::isTenantAdmin()): ?>
            <div class="card card-half">
                <div class="card-header">
                    <h3><i class="fas fa-tools"></i> <?php echo __('administration'); ?></h3>
                </div>
                <div class="card-body">
                    <div class="action-grid">
                        <a href="<?php echo $urlPrefix; ?>/tenant/users.php" class="btn btn-info">
                            <i class="fas fa-users"></i> <?php echo __('manage_users'); ?>
                        </a>
                        <a href="<?php echo $urlPrefix; ?>/tenant/settings.php" class="btn btn-warning">
                            <i class="fas fa-cog"></i> <?php echo __('settings'); ?>
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- POS Quick Actions (If POS Subscribed) -->
            <?php if ($hasPOS): ?>
            <div class="card card-full">
                <div class="card-header">
                    <h3><i class="fas fa-rocket"></i> <?php echo __('quick_actions'); ?></h3>
                </div>
                <div class="card-body">
                    <div class="action-grid pos-actions">
                        <a href="<?php echo $urlPrefix; ?>/<?php echo $subdomain; ?>/pos/dashboard" class="btn btn-success">
                            <i class="fas fa-chart-line"></i> <?php echo __('pos_dashboard'); ?>
                        </a>
                        <a href="<?php echo $urlPrefix; ?>/<?php echo $subdomain; ?>/pos/order" class="btn btn-primary">
                            <i class="fas fa-cash-register"></i> <?php echo __('new_order'); ?>
                        </a>
                        <a href="<?php echo $urlPrefix; ?>/<?php echo $subdomain; ?>/pos/products" class="btn btn-info">
                            <i class="fas fa-box"></i> <?php echo __('products'); ?>
                        </a>
                        <a href="<?php echo $urlPrefix; ?>/<?php echo $subdomain; ?>/pos/customers" class="btn btn-warning">
                            <i class="fas fa-users"></i> <?php echo __('customers'); ?>
                        </a>
                        <a href="<?php echo $urlPrefix; ?>/<?php echo $subdomain; ?>/pos/reports" class="btn btn-info">
                            <i class="fas fa-chart-bar"></i> <?php echo __('reports'); ?>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="card card-full">
                <div class="card-header">
                    <h3><i class="fas fa-shopping-cart"></i> <?php echo __('recent_orders'); ?></h3>
                </div>
                <div class="card-body">
                    <?php if (empty($recentOrders)): ?>
                        <div class="empty-state">
                            <i class="fas fa-receipt"></i>
                            <p>No orders yet. <a href="<?php echo $urlPrefix; ?>/<?php echo $subdomain; ?>/pos/order">Create your first order</a></p>
                        </div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th><?php echo __('order_id'); ?></th>
                                    <th><?php echo __('total'); ?></th>
                                    <th><?php echo __('status'); ?></th>
                                    <th><?php echo __('date'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $order): ?>
                                    <tr>
                                        <td><strong>#<?php echo $order['id']; ?></strong></td>
                                        <td><strong>$<?php echo number_format($order['total'], 2); ?></strong></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                                <?php
                                                $icons = [
                                                    'completed' => '✓',
                                                    'pending' => '⏳',
                                                    'cancelled' => '✗'
                                                ];
                                                echo isset($icons[$order['status']]) ? $icons[$order['status']] . ' ' : '';
                                                echo __($order['status']);
                                                ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y H:i', strtotime($order['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <script id="langToggleScript">
        function toggleLangDropdown(e) {
            e.stopPropagation();
            document.getElementById('langSwitcher').classList.toggle('active');
        }
        
        document.addEventListener('click', function(e) {
            const switcher = document.getElementById('langSwitcher');
            if (switcher && !switcher.contains(e.target)) {
                switcher.classList.remove('active');
            }
        });
    </script>
</body>
</html>

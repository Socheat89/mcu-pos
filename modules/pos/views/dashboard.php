<?php
require_once __DIR__ . '/../../../core/helpers/url.php';
$stats = $stats ?? [];
$recentOrders = $recentOrders ?? [];
$salesByMonth = $salesByMonth ?? [];
$topProducts = $topProducts ?? [];
$lowStockItems = $lowStockItems ?? [];
$subscriptionPlans = $subscriptionPlans ?? [];
$subscriptionExpiringSoon = $subscriptionExpiringSoon ?? false;

$tenant = class_exists('Tenant') ? (Tenant::getCurrent() ?? []) : [];
$tenantName = is_array($tenant) && !empty($tenant['name']) ? $tenant['name'] : 'Tenant';
$tenantSlug = is_array($tenant) && !empty($tenant['subdomain']) ? $tenant['subdomain'] : '';

$urlPrefix = mc_base_path();

$subscriptionManageUrl = $tenantSlug
    ? $urlPrefix . '/' . $tenantSlug . '/settings'
    : $urlPrefix . '/tenant/settings.php';
$subscriptionManageUrl .= (strpos($subscriptionManageUrl, '?') === false ? '?' : '&') . 'section=subscription';
$subscriptionPricingUrl = $urlPrefix . '/public/pricing.php';

$fmtMoney = function($value): string {
    return '$' . number_format((float)$value, 2);
};

$labels = array_keys($salesByMonth ?? []);
$values = array_values($salesByMonth ?? []);

$friendlyLabels = [];
foreach ($labels as $ym) {
    if (!$ym) continue;
    $dt = DateTime::createFromFormat('Y-m', $ym);
    $friendlyLabels[] = $dt ? $dt->format('M Y') : $ym;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('dashboard'); ?> - <?php echo htmlspecialchars($tenantName); ?></title>
    <link href="<?php echo mc_base_path(); ?>/public/css/pos_template.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        .dashboard-hero {
            background: var(--pos-gradient-dark);
            border-radius: var(--pos-radius-xl);
            padding: 48px;
            color: white;
            margin-bottom: 32px;
            position: relative;
            overflow: hidden;
            border: 1px solid var(--pos-border);
            box-shadow: var(--pos-shadow-lg), var(--pos-shadow-glow);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .dashboard-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 30% 40%, rgba(var(--pos-primary-rgb), 0.12) 0%, transparent 50%),
                        radial-gradient(circle at 80% 20%, rgba(var(--pos-primary-rgb), 0.08) 0%, transparent 40%);
            pointer-events: none;
        }
        .dashboard-hero::after {
            content: ''; position: absolute; top: -50%; right: -20%; width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(var(--pos-primary-rgb), 0.15) 0%, transparent 65%); border-radius: 50%; pointer-events: none;
        }
        .dashboard-hero h1 { font-size: 40px; font-weight: 800; margin: 0; line-height: 1.1; letter-spacing: -1px; position: relative; z-index: 1; }
        .dashboard-hero p { color: rgba(255,255,255,0.5); font-size: 16px; margin: 12px 0 28px; font-weight: 500; max-width: 500px; position: relative; z-index: 1; }
        .dashboard-hero .hero-actions { display: flex; gap: 12px; position: relative; z-index: 1; flex-wrap: wrap; }
        .dashboard-hero .hero-right { position: relative; z-index: 1; text-align: right; flex-shrink: 0; }
        .dashboard-hero .hero-right .big-number { font-size: 52px; font-weight: 900; font-family: 'JetBrains Mono', monospace; line-height: 1; }
        .dashboard-hero .hero-right .big-label { font-size: 12px; font-weight: 700; color: rgba(255,255,255,0.45); text-transform: uppercase; letter-spacing: 1px; margin-top: 6px; }

        .notification-card {
            display: flex; gap: 16px; padding: 18px; background: var(--pos-card); backdrop-filter: blur(12px); border-radius: var(--pos-radius);
            border: 1px solid var(--pos-border); margin-bottom: 12px; transition: all 0.3s ease;
        }
        .notification-card:hover { transform: translateY(-3px); border-color: rgba(var(--pos-primary-rgb), 0.15); box-shadow: var(--pos-shadow-md); }
        .notification-icon { width: 48px; height: 48px; border-radius: 12px; display: grid; place-items: center; flex-shrink: 0; font-size: 18px; }

        .leaderboard-item { display: flex; align-items: center; gap: 14px; padding: 14px; border-radius: var(--pos-radius); background: var(--pos-card); border: 1px solid var(--pos-border); transition: all 0.25s ease; }
        .leaderboard-item:hover { border-color: rgba(var(--pos-primary-rgb), 0.15); transform: translateX(6px); box-shadow: var(--pos-shadow-sm); }
        .rank-number { width: 38px; height: 38px; border-radius: 10px; background: var(--pos-primary-light); border: 1px solid rgba(var(--pos-primary-rgb), 0.15); display: grid; place-items: center; font-weight: 800; color: var(--pos-primary); font-size: 14px; }
        .subscription-card .subscription-helper { color: var(--pos-text-muted); font-size: 12px; margin-top: 6px; font-weight: 600; }
        .subscription-card .subscription-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 20px; }
        .subscription-list { display: flex; flex-direction: column; gap: 14px; margin-bottom: 16px; }
        .subscription-plan { border: 1px solid var(--pos-border); border-radius: var(--pos-radius); padding: 18px; background: var(--pos-card); transition: all 0.25s ease; }
        .subscription-plan:hover { border-color: rgba(var(--pos-primary-rgb), 0.15); box-shadow: var(--pos-shadow-sm); }
        .subscription-plan .plan-row { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 14px; }
        .plan-name { font-weight: 800; font-size: 15px; margin: 0; color: var(--pos-text); }
        .plan-meta { font-size: 11px; color: var(--pos-text-muted); font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; }
        .plan-stats { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
        .plan-stats .stat { background: rgba(255,255,255,0.02); border: 1px dashed var(--pos-border); border-radius: 12px; padding: 12px 14px; display: flex; flex-direction: column; gap: 6px; }
        .plan-stats .stat span { font-size: 10px; font-weight: 700; color: var(--pos-text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
        .plan-stats .stat strong { font-size: 17px; font-weight: 800; color: var(--pos-text); font-family: 'JetBrains Mono', monospace; }
        .subscription-empty { border: 1.5px dashed var(--pos-border); border-radius: var(--pos-radius); padding: 32px 20px; background: rgba(255,255,255,0.02); display: flex; flex-direction: column; align-items: center; gap: 12px; text-align: center; }
        .subscription-empty i { font-size: 36px; color: var(--pos-text-dim); }
        .subscription-empty p { margin: 0; }
        .subscription-empty-hint { font-size: 12px; color: var(--pos-text-muted); font-weight: 600; }
        .subscription-cta { padding: 10px 24px; font-size: 13px; font-weight: 700; border-radius: 999px; }
        .subscription-manage-btn { width: 100%; justify-content: center; border-radius: 14px; font-weight: 700; padding: 12px 24px; }

        .chart-wrapper { position: relative; height: 260px; }
        .dash-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 24px; margin-bottom: 28px; }
        .dash-grid-2-1 { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 28px; }
        @media (max-width: 1100px) {
            .dash-grid-3 { grid-template-columns: 1fr 1fr; }
            .dash-grid-2-1 { grid-template-columns: 1fr; }
            .dashboard-hero { flex-direction: column; align-items: flex-start; gap: 24px; }
            .dashboard-hero .hero-right { text-align: left; }
        }
        @media (max-width: 680px) {
            .dash-grid-3 { grid-template-columns: 1fr; }
            .dashboard-hero { padding: 28px; }
            .dashboard-hero h1 { font-size: 28px; }
        }
    </style>
</head>
<body class="pos-app">
    <?php $activeNav = 'dashboard'; include __DIR__ . '/partials/navbar.php'; ?>

    <div class="fade-in">
        <!-- Dashboard Hero -->
        <div class="dashboard-hero">
            <div>
                <h1><?php echo __('dashboard'); ?></h1>
                <p><?php echo __('business_happening'); ?></p>
                <div class="hero-actions">
                    <a href="<?php echo htmlspecialchars($posUrl('pos')); ?>" class="btn btn-primary">
                        <i class="fas fa-cash-register"></i> <?php echo __('initiate_sale'); ?>
                    </a>
                    <a href="<?php echo htmlspecialchars($posUrl('products')); ?>" class="btn btn-outline" style="color:#fff; border-color: rgba(255,255,255,0.25);">
                        <i class="fas fa-boxes-stacked"></i> <?php echo __('products'); ?>
                    </a>
                </div>
            </div>
            <div class="hero-right">
                <div class="big-number"><?php echo $fmtMoney($stats['total_sales'] ?? 0); ?></div>
                <div class="big-label"><?php echo __('total_revenue'); ?></div>
            </div>
        </div>

        <!-- KPI Stats -->
        <div class="pos-grid cols-4" style="margin-bottom: 28px;">
            <div class="pos-stat">
                <span class="k"><?php echo __('total_revenue'); ?></span>
                <p class="v"><?php echo $fmtMoney($stats['total_sales'] ?? 0); ?></p>
                <div class="chip" style="background: rgba(99,102,241,0.1); color: var(--pos-primary);"><i class="fas fa-dollar-sign"></i></div>
            </div>
            <div class="pos-stat">
                <span class="k"><?php echo __('total_orders'); ?></span>
                <p class="v"><?php echo number_format($stats['total_orders'] ?? 0); ?></p>
                <div class="chip" style="background: rgba(34,197,94,0.1); color: #22c55e;"><i class="fas fa-receipt"></i></div>
            </div>
            <div class="pos-stat">
                <span class="k"><?php echo __('products'); ?></span>
                <p class="v"><?php echo number_format($stats['total_products'] ?? 0); ?></p>
                <div class="chip" style="background: rgba(139,92,246,0.1); color: var(--pos-secondary);"><i class="fas fa-box"></i></div>
            </div>
            <div class="pos-stat">
                <span class="k"><?php echo __('low_stock'); ?></span>
                <p class="v"><?php echo number_format($stats['low_stock_count'] ?? 0); ?></p>
                <div class="chip" style="background: rgba(234,179,8,0.1); color: #eab308;"><i class="fas fa-triangle-exclamation"></i></div>
            </div>
        </div>

        <!-- Sales Chart + Leaderboard -->
        <div class="dash-grid-2-1">
            <!-- Monthly Revenue Chart -->
            <div class="pos-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
                    <div>
                        <h3 class="pos-card-title" style="margin:0;"><?php echo __('monthly_revenue_trends'); ?></h3>
                        <p style="margin:4px 0 0; font-size:12px; color:var(--pos-text-muted);"><?php echo __('past_6_months'); ?></p>
                    </div>
                    <a href="<?php echo htmlspecialchars($posUrl('reports')); ?>" class="btn btn-outline" style="font-size:12px; padding: 7px 14px;">
                        <i class="fas fa-chart-line"></i> <?php echo __('analytical_overview'); ?>
                    </a>
                </div>
                <div class="chart-wrapper">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <!-- Top Products Leaderboard -->
            <div class="pos-card">
                <div class="pos-card-header" style="margin-bottom:16px;">
                    <h3 class="pos-card-title"><?php echo __('growth_leaderboard'); ?></h3>
                    <span class="pos-card-badge"><?php echo __('volume_ranking'); ?></span>
                </div>
                <div style="display:flex; flex-direction:column; gap:10px;">
                    <?php if (empty($topProducts)): ?>
                        <div style="text-align:center; padding: 32px 0; color: var(--pos-text-muted);">
                            <i class="fas fa-chart-bar" style="font-size:28px; margin-bottom:8px; display:block; opacity:0.3;"></i>
                            <p style="margin:0; font-size:13px; font-weight:600;"><?php echo __('data_aggregation'); ?></p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($topProducts as $i => $prod): ?>
                        <div class="leaderboard-item">
                            <div class="rank-number">#<?php echo $i + 1; ?></div>
                            <div style="flex:1; min-width:0;">
                                <div style="font-weight:800; font-size:13px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?php echo htmlspecialchars($prod['name']); ?></div>
                                <div style="font-size:11px; color:var(--pos-text-muted); font-weight:600; margin-top:2px;">
                                    <?php echo str_replace(':count', number_format($prod['qty']), __('sold_count')); ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Orders + Alerts + Subscription -->
        <div class="dash-grid-3">
            <!-- Recent Orders -->
            <div class="pos-card" style="grid-column: span 2;">
                <div class="pos-card-header" style="margin-bottom:16px;">
                    <h3 class="pos-card-title"><?php echo __('latest_transactions'); ?></h3>
                    <a href="<?php echo htmlspecialchars($posUrl('orders')); ?>" class="btn btn-outline" style="font-size:12px; padding:7px 14px;">
                        <?php echo __('review_ledger'); ?> <i class="fas fa-arrow-right" style="margin-left:6px;"></i>
                    </a>
                </div>
                <?php if (empty($recentOrders)): ?>
                    <div style="text-align:center; padding:40px 0; color:var(--pos-text-muted);">
                        <i class="fas fa-receipt" style="font-size:32px; margin-bottom:12px; display:block; opacity:0.25;"></i>
                        <p style="margin:0; font-size:13px; font-weight:600;"><?php echo __('awaiting_transactions'); ?></p>
                    </div>
                <?php else: ?>
                    <div class="pos-table-container">
                        <table class="pos-table">
                            <thead>
                                <tr>
                                    <th><?php echo __('ref_id'); ?></th>
                                    <th><?php echo __('stakeholder'); ?></th>
                                    <th><?php echo __('status'); ?></th>
                                    <th style="text-align:right;"><?php echo __('valuation'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo htmlspecialchars($posUrl('orders/' . $order['id'])); ?>" style="font-weight:800; color:var(--pos-primary);">
                                            #<?php echo $order['id']; ?>
                                        </a>
                                        <div style="font-size:11px; color:var(--pos-text-muted); margin-top:2px; font-weight:600;">
                                            <?php echo date('d M, H:i', strtotime($order['created_at'])); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo $order['customer_name'] ? htmlspecialchars($order['customer_name']) : __('walk_in_client'); ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $order['status'] === 'completed' ? 'badge-success' : ($order['status'] === 'pending' ? 'badge-warning' : 'badge-danger'); ?>">
                                            <?php echo htmlspecialchars($order['status']); ?>
                                        </span>
                                    </td>
                                    <td style="text-align:right; font-weight:900; color:var(--pos-primary);">
                                        <?php echo $fmtMoney($order['total']); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right column: Alerts + Subscription -->
            <div style="display:flex; flex-direction:column; gap:24px;">
                <!-- Intelligence Alerts -->
                <div class="pos-card">
                    <div class="pos-card-header" style="margin-bottom:14px;">
                        <h3 class="pos-card-title"><?php echo __('intelligence_alerts'); ?></h3>
                    </div>
                    <?php if (empty($lowStockItems)): ?>
                        <div class="notification-card" style="border-color: rgba(34,197,94,0.2); background: rgba(34,197,94,0.03);">
                            <div class="notification-icon" style="background: rgba(34,197,94,0.1); color:#22c55e;">
                                <i class="fas fa-circle-check"></i>
                            </div>
                            <div>
                                <div style="font-weight:800; font-size:13px; color:var(--pos-text);"><?php echo __('operational_health_optimal'); ?></div>
                                <div style="font-size:12px; color:var(--pos-text-muted); margin-top:4px; font-weight:600;"><?php echo __('inventory_stabilized'); ?></div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($lowStockItems as $item): ?>
                        <div class="notification-card" style="border-color: rgba(234,179,8,0.2); background: rgba(234,179,8,0.03);">
                            <div class="notification-icon" style="background: rgba(234,179,8,0.1); color:#eab308;">
                                <i class="fas fa-triangle-exclamation"></i>
                            </div>
                            <div>
                                <div style="font-weight:800; font-size:13px; color:var(--pos-text);"><?php echo __('inventory_deficit'); ?></div>
                                <div style="font-size:12px; color:var(--pos-text-muted); margin-top:4px; font-weight:600;">
                                    <?php echo htmlspecialchars($item['name']); ?> — <?php echo $item['stock_quantity']; ?> <?php echo __('units_remaining'); ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Subscription Overview -->
                <div class="pos-card subscription-card">
                    <div class="subscription-header">
                        <div>
                            <h3 class="pos-card-title" style="margin:0;"><?php echo __('subscription_overview'); ?></h3>
                            <p class="subscription-helper"><?php echo __('subscription_helper_text'); ?></p>
                        </div>
                        <?php if ($subscriptionExpiringSoon): ?>
                        <span class="badge badge-warning"><?php echo __('expiring_soon'); ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if (empty($subscriptionPlans)): ?>
                        <div class="subscription-empty">
                            <i class="fas fa-layer-group"></i>
                            <p style="font-weight:800; color:var(--pos-text);"><?php echo __('no_subscriptions'); ?></p>
                            <p class="subscription-empty-hint"><?php echo __('subscription_empty_hint'); ?></p>
                            <a href="<?php echo htmlspecialchars($subscriptionPricingUrl); ?>" class="btn btn-primary subscription-cta">
                                <?php echo __('subscribe_now'); ?>
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="subscription-list">
                            <?php foreach ($subscriptionPlans as $plan): ?>
                            <div class="subscription-plan">
                                <div class="plan-row">
                                    <div>
                                        <p class="plan-name"><?php echo htmlspecialchars($plan['system_name'] ?? '—'); ?></p>
                                        <p class="plan-meta"><?php echo htmlspecialchars($plan['status'] ?? ''); ?></p>
                                    </div>
                                    <?php if (!empty($plan['is_expired'])): ?>
                                        <span class="badge badge-danger"><?php echo __('expired'); ?></span>
                                    <?php elseif (isset($plan['days_remaining']) && $plan['days_remaining'] !== null && $plan['days_remaining'] <= 7): ?>
                                        <span class="badge badge-warning"><?php echo $plan['days_remaining']; ?>d</span>
                                    <?php else: ?>
                                        <span class="badge badge-success"><?php echo __('active'); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="plan-stats">
                                    <div class="stat">
                                        <span><?php echo __('since'); ?></span>
                                        <strong><?php echo !empty($plan['subscribed_at']) ? date('d M Y', strtotime($plan['subscribed_at'])) : '—'; ?></strong>
                                    </div>
                                    <div class="stat">
                                        <span><?php echo __('expires_on'); ?></span>
                                        <strong>
                                            <?php if (!empty($plan['expires_at'])): ?>
                                                <?php echo date('d M Y', strtotime($plan['expires_at'])); ?>
                                            <?php else: ?>
                                                <?php echo __('no_expiration'); ?>
                                            <?php endif; ?>
                                        </strong>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <a href="<?php echo htmlspecialchars($subscriptionManageUrl); ?>" class="btn btn-outline subscription-manage-btn">
                            <i class="fas fa-gear"></i> <?php echo __('manage_subscription'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function() {
        var labels = <?php echo json_encode(array_values($friendlyLabels)); ?>;
        var data   = <?php echo json_encode(array_values($values)); ?>;

        var ctx = document.getElementById('salesChart');
        if (!ctx || typeof Chart === 'undefined') return;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '<?php echo __('total_revenue'); ?>',
                    data: data,
                    backgroundColor: 'rgba(99, 102, 241, 0.15)',
                    borderColor: 'rgba(99, 102, 241, 0.8)',
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        padding: 14,
                        cornerRadius: 12,
                        backgroundColor: '#141830',
                        borderColor: 'rgba(255,255,255,0.06)',
                        borderWidth: 1,
                        titleColor: '#f1f5f9',
                        bodyColor: '#94a3b8',
                        bodyFont: { size: 13, weight: 'bold' },
                        callbacks: {
                            label: function(context) {
                                return ' $' + context.parsed.y.toFixed(2);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { weight: '700', size: 11 }, color: '#64748b' },
                        border: { display: false }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(255,255,255,0.04)', drawBorder: false },
                        ticks: {
                            font: { weight: '700', size: 11 },
                            color: '#64748b',
                            callback: function(value) { return '$' + value; }
                        },
                        border: { display: false }
                    }
                }
            }
        });
    })();
    </script>

    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

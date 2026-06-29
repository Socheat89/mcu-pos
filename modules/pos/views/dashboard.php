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
    if ($dt) {
        $friendlyLabels[] = $ym; // We'll format this in JS for better localization
    } else {
        $friendlyLabels[] = $ym;
    }
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Outfit:wght@300;400;500;600;700;800;900&family=Battambang:wght@100;300;400;700;900&display=swap" rel="stylesheet">
    <style>
        .dashboard-hero {
            background: var(--pos-gradient-dark);
            border-radius: 32px;
            padding: 56px;
            color: white;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 30px 60px rgba(15, 23, 42, 0.25);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .dashboard-hero::after {
            content: ''; position: absolute; top: -50%; right: -20%; width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.2) 0%, transparent 70%); border-radius: 50%; pointer-events: none;
        }
        .dashboard-hero h1 { font-size: 44px; font-weight: 900; margin: 0; line-height: 1.1; letter-spacing: -1px; }
        .dashboard-hero p { color: rgba(255,255,255,0.6); font-size: 18px; margin: 12px 0 32px; font-weight: 500; max-width: 500px; }
        
        .notification-card {
            display: flex; gap: 20px; padding: 20px; background: white; border-radius: 20px;
            border: 1.5px solid var(--pos-border); margin-bottom: 16px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .notification-card:hover { transform: translateY(-4px) scale(1.01); border-color: var(--pos-primary); box-shadow: var(--pos-shadow-xl); }
        .notification-icon { width: 52px; height: 52px; border-radius: 14px; display: grid; place-items: center; flex-shrink: 0; font-size: 20px; }

        .leaderboard-item { display: flex; align-items: center; gap: 16px; padding: 16px; border-radius: 20px; background: #f8fafc; border: 1.5px solid var(--pos-border); transition: all 0.2s; }
        .leaderboard-item:hover { background: white; border-color: var(--pos-primary); transform: translateX(8px); }
        .rank-number { width: 40px; height: 40px; border-radius: 12px; background: white; border: 1px solid var(--pos-border); display: grid; place-items: center; font-weight: 900; color: var(--pos-primary); font-size: 15px; box-shadow: var(--pos-shadow-sm); }
        .subscription-card .subscription-helper { color: var(--pos-text-muted); font-size: 13px; margin-top: 6px; font-weight: 600; }
        .subscription-card .subscription-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 20px; }
        .subscription-list { display: flex; flex-direction: column; gap: 18px; margin-bottom: 16px; }
        .subscription-plan { border: 1.5px solid var(--pos-border); border-radius: 22px; padding: 20px; background: #f8fafc; transition: all 0.2s ease; }
        .subscription-plan:hover { border-color: var(--pos-primary); background: #fff; box-shadow: var(--pos-shadow-sm); }
        .subscription-plan .plan-row { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 16px; }
        .plan-name { font-weight: 850; font-size: 16px; margin: 0; color: var(--pos-text); }
        .plan-meta { font-size: 12px; color: var(--pos-text-muted); font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; }
        .plan-stats { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        .plan-stats .stat { background: #fff; border: 1px dashed var(--pos-border); border-radius: 16px; padding: 12px 14px; display: flex; flex-direction: column; gap: 6px; }
        .plan-stats .stat span { font-size: 11px; font-weight: 800; color: var(--pos-text-muted); text-transform: uppercase; }
        .plan-stats .stat strong { font-size: 18px; font-weight: 900; color: var(--pos-text); }
        .subscription-empty { border: 1.5px dashed var(--pos-border); border-radius: 20px; padding: 32px 20px; background: #f8fafc; display: flex; flex-direction: column; align-items: center; gap: 12px; text-align: center; }
        .subscription-empty i { font-size: 36px; color: var(--pos-border); }
        .subscription-empty p { margin: 0; }
        .subscription-empty-hint { font-size: 13px; color: var(--pos-text-muted); font-weight: 600; }
        .subscription-cta { padding: 12px 28px; font-size: 13px; font-weight: 800; border-radius: 999px; }
        .subscription-manage-btn { width: 100%; justify-content: center; border-radius: 18px; font-weight: 800; padding: 14px 24px; }
    </style>
</head>
<body class="pos-app">
    <?php $activeNav = 'dashboard'; include __DIR__ . '/partials/navbar.php'; ?>

    <div class="fade-in">
        <!-- Dashboard Hero -->
        <div class="dashboard-hero">
            <div style="position: relative; z-index: 2;">
                <h1><?php echo __('powering_growth'); ?></h1>
                <p><?php echo __('welcome_back'); ?>, <?php echo htmlspecialchars($tenantName); ?>. <?php echo __('ecosystem_performing'); ?></p>
                <div style="display: flex; gap: 16px;">
                    <a href="<?php echo htmlspecialchars($posUrl('pos')); ?>" class="btn btn-primary" style="padding: 16px 36px; font-weight: 800; font-size: 16px;">
                        <i class="fas fa-plus"></i> <?php echo __('initiate_sale'); ?>
                    </a>
                    <a href="<?php echo htmlspecialchars($posUrl('reports')); ?>" class="btn btn-outline" style="background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.2); color: white; padding: 16px 36px; border-radius: 20px;">
                        <i class="fas fa-chart-pie"></i> <?php echo __('analytical_overview'); ?>
                    </a>
                </div>
            </div>
            <div style="width: 200px; height: 200px; background: rgba(99, 102, 241, 0.1); border-radius: 40px; display: grid; place-items: center; transform: rotate(15deg); border: 2px solid rgba(255,255,255,0.1); position: relative; z-index: 1;">
                 <i class="fas fa-rocket" style="font-size: 80px; color: var(--pos-primary); opacity: 0.8;"></i>
            </div>
        </div>

        <!-- Vital Statistics -->
        <div class="pos-grid cols-4" style="margin-bottom: 40px;">
            <div class="pos-stat">
                <span class="k"><?php echo __('recent_orders'); ?></span>
                <p class="v"><?php echo (int)($stats['total_orders'] ?? 0); ?></p>
                <div class="chip" style="background: rgba(99, 102, 241, 0.1); color: var(--pos-primary);"><i class="fas fa-shopping-basket"></i></div>
            </div>
            <div class="pos-stat">
                <span class="k"><?php echo __('total_revenue'); ?></span>
                <p class="v"><?php echo htmlspecialchars($fmtMoney($stats['total_sales'] ?? 0)); ?></p>
                <div class="chip" style="background: rgba(16, 185, 129, 0.1); color: var(--pos-success);"><i class="fas fa-money-bill-trend-up"></i></div>
            </div>
            <div class="pos-stat">
                <span class="k"><?php echo __('products'); ?></span>
                <p class="v"><?php echo (int)($stats['total_products'] ?? 0); ?></p>
                <div class="chip" style="background: rgba(245, 158, 11, 0.1); color: var(--pos-warning);"><i class="fas fa-layer-group"></i></div>
            </div>
            <div class="pos-stat">
                <span class="k"><?php echo __('low_stock'); ?></span>
                <p class="v"><?php echo (int)($stats['low_stock_count'] ?? 0); ?></p>
                <div class="chip" style="background: rgba(239, 68, 68, 0.1); color: var(--pos-danger);"><i class="fas fa-bolt"></i></div>
            </div>
        </div>

        <div class="pos-grid cols-2" style="align-items: stretch;">
            <!-- Analytical Core -->
            <div class="pos-card">
                <div class="pos-card-header" style="padding: 32px 32px 16px;">
                    <h3 class="pos-card-title"><?php echo __('economic_momentum'); ?></h3>
                    <div class="badge badge-primary"><?php echo __('annual_revenue'); ?></div>
                </div>
                <div style="height: 380px; padding: 20px;">
                    <canvas id="salesChart"></canvas>
                </div>
                
                <div style="padding: 32px; border-top: 1.5px solid var(--pos-border);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                        <h3 class="pos-card-title"><?php echo __('latest_transactions'); ?></h3>
                        <a href="<?php echo htmlspecialchars($posUrl('orders')); ?>" class="btn btn-outline" style="padding: 10px 20px; font-size: 13px; border-radius: 12px;"><?php echo __('review_ledger'); ?></a>
                    </div>
                    <div class="pos-table-container shadow-none" style="border: none;">
                        <table class="pos-table">
                            <thead>
                                <tr>
                                    <th><?php echo __('ref_id'); ?></th>
                                    <th><?php echo __('stakeholder'); ?></th>
                                    <th><?php echo __('valuation'); ?></th>
                                    <th><?php echo __('status'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentOrders)): ?>
                                    <tr><td colspan="4" style="text-align: center; padding: 60px; color: var(--pos-text-muted);"><?php echo __('awaiting_transactions'); ?></td></tr>
                                <?php else: ?>
                                    <?php foreach ($recentOrders as $o): 
                                        $badg = (($o['status'] ?? 'pending') === 'completed') ? 'badge-success' : 'badge-warning';
                                    ?>
                                    <tr>
                                        <td style="font-weight: 700;">#<?php echo (int)$o['id']; ?></td>
                                        <td style="font-weight: 600; color: var(--pos-text);"><?php echo htmlspecialchars($o['customer_name'] ?? __('walk_in_client')); ?></td>
                                        <td style="font-weight: 900; color: var(--pos-primary);"><?php echo htmlspecialchars($fmtMoney($o['total'] ?? 0)); ?></td>
                                        <td><span class="badge <?php echo $badg; ?>"><?php echo __($o['status'] ?? 'pending'); ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Intelligence Hub -->
            <div style="display: grid; gap: 32px; align-content: start;">
                <!-- Subscription Intelligence -->
                <div class="pos-card pad subscription-card">
                    <div class="subscription-header">
                        <div>
                            <h3 class="pos-card-title" style="margin-bottom: 4px;"><?php echo __('subscription_overview'); ?></h3>
                            <p class="subscription-helper"><?php echo __('subscription_helper_text'); ?></p>
                        </div>
                        <?php if ($subscriptionExpiringSoon): ?>
                            <span class="badge badge-warning" style="font-size: 11px; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase;">
                                <?php echo __('expiring_soon'); ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if (empty($subscriptionPlans)): ?>
                        <div class="subscription-empty">
                            <i class="fas fa-bell-slash"></i>
                            <p style="font-weight: 800; color: var(--pos-text);"><?php echo __('no_subscriptions'); ?></p>
                            <p class="subscription-empty-hint"><?php echo __('subscription_empty_hint'); ?></p>
                            <a href="<?php echo htmlspecialchars($subscriptionPricingUrl); ?>" class="btn btn-primary subscription-cta">
                                <i class="fas fa-rocket"></i> <?php echo __('subscribe_now'); ?>
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="subscription-list">
                            <?php foreach ($subscriptionPlans as $plan): 
                                $statusKey = strtolower($plan['status'] ?? 'inactive');
                                $badgeClass = 'badge';
                                if ($statusKey === 'active') {
                                    $badgeClass .= ' badge-success';
                                } elseif ($statusKey === 'expired') {
                                    $badgeClass .= ' badge-danger';
                                } else {
                                    $badgeClass .= ' badge-warning';
                                }

                                $expiresLabel = __('no_expiration');
                                if (!empty($plan['expires_at'])) {
                                    $dt = DateTime::createFromFormat('Y-m-d H:i:s', $plan['expires_at']);
                                    if (!$dt) {
                                        $dt = date_create($plan['expires_at']);
                                    }
                                    if ($dt) {
                                        $expiresLabel = $dt->format('M d, Y');
                                    }
                                }

                                $daysRemaining = $plan['days_remaining'];
                                $isExpired = !empty($plan['is_expired']);
                            ?>
                                <div class="subscription-plan">
                                    <div class="plan-row">
                                        <div>
                                            <p class="plan-name"><?php echo htmlspecialchars($plan['system_name'] ?? $plan['name'] ?? ''); ?></p>
                                            <span class="plan-meta"><?php echo __('status'); ?> · <?php echo __($statusKey); ?></span>
                                        </div>
                                        <span class="<?php echo $badgeClass; ?>"><?php echo __($statusKey); ?></span>
                                    </div>
                                    <div class="plan-stats">
                                        <div class="stat">
                                            <span><?php echo __('expires_on'); ?></span>
                                            <strong><?php echo htmlspecialchars($expiresLabel); ?></strong>
                                        </div>
                                        <div class="stat">
                                            <span><?php echo __('days_remaining'); ?></span>
                                            <?php if ($daysRemaining === null): ?>
                                                <strong>—</strong>
                                            <?php elseif ($isExpired): ?>
                                                <strong style="color: var(--pos-danger);"><?php echo __('expired'); ?></strong>
                                            <?php else: ?>
                                                <strong><?php echo (int) $daysRemaining; ?></strong>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <a href="<?php echo htmlspecialchars($subscriptionManageUrl); ?>" class="btn btn-outline subscription-manage-btn">
                            <i class="fas fa-credit-card"></i> <?php echo __('manage_subscription'); ?>
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Inventory Intelligence -->
                <div class="pos-card pad">
                    <h3 class="pos-card-title" style="margin-bottom: 24px;"><?php echo __('intelligence_alerts'); ?></h3>
                    <div>
                        <?php if (empty($lowStockItems)): ?>
                            <div class="notification-card" style="background: #f0fdf4; border-color: #bbf7d0;">
                                <div class="notification-icon" style="background: #22c55e; color: white;">
                                    <i class="fas fa-check-double"></i>
                                </div>
                                <div>
                                    <p style="font-weight: 900; color: #166534; margin: 0; font-size: 15px;"><?php echo __('operational_health_optimal'); ?></p>
                                    <p style="font-size: 13px; color: #14532d; margin: 4px 0 0; font-weight: 500;"><?php echo __('inventory_stabilized'); ?></p>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach (array_slice($lowStockItems, 0, 4) as $p): ?>
                                <div class="notification-card">
                                    <div class="notification-icon" style="background: #fef2f2; color: #ef4444;">
                                        <i class="fas fa-arrow-trend-down"></i>
                                    </div>
                                    <div style="flex: 1;">
                                        <p style="font-weight: 850; color: var(--pos-text); margin: 0; font-size: 15px;"><?php echo htmlspecialchars($p['name']); ?></p>
                                        <p style="font-size: 13px; color: #b91c1c; margin: 4px 0 0; font-weight: 700;"><?php echo __('inventory_deficit'); ?>: <?php echo (int)$p['stock_quantity']; ?> <?php echo __('units_remaining'); ?></p>
                                    </div>
                                    <a href="<?php echo htmlspecialchars($posUrl('products/' . $p['id'] . '/edit')); ?>" class="pos-icon-btn"><i class="fas fa-arrow-right"></i></a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Product High-Performers -->
                <div class="pos-card pad">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                        <h3 class="pos-card-title"><?php echo __('growth_leaderboard'); ?></h3>
                        <span style="font-size: 11px; font-weight: 900; color: var(--pos-text-muted); text-transform: uppercase;"><?php echo __('volume_ranking'); ?></span>
                    </div>
                    <div style="display: grid; gap: 16px;">
                        <?php if (empty($topProducts)): ?>
                            <div style="text-align: center; padding: 48px; border: 2px dashed var(--pos-border); border-radius: 24px;">
                                <i class="fas fa-chart-bar" style="font-size: 40px; color: var(--pos-border); margin-bottom: 16px;"></i>
                                <p style="font-weight: 700; color: var(--pos-text-muted);"><?php echo __('data_aggregation'); ?></p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($topProducts as $idx => $tp): ?>
                                <div class="leaderboard-item">
                                    <div class="rank-number"><?php echo $idx + 1; ?></div>
                                    <div style="flex: 1;">
                                        <p style="font-weight: 800; color: var(--pos-text); font-size: 15px; margin: 0;"><?php echo htmlspecialchars($tp['name']); ?></p>
                                        <p style="font-size: 12px; color: var(--pos-text-muted); font-weight: 700; text-transform: uppercase; margin-top: 2px;"><?php echo (int)$tp['qty']; ?> <?php echo __('acquisitions_recorded'); ?></p>
                                    </div>
                                    <div style="font-weight: 900; color: var(--pos-primary); font-size: 16px;">$<?php echo number_format($tp['qty'] * 24.5, 2); ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        (function() {
            var el = document.getElementById('salesChart');
            if (!el) return;

            var labelsRaw = <?php echo json_encode($friendlyLabels, JSON_UNESCAPED_SLASHES); ?>;
            var data = <?php echo json_encode($values, JSON_UNESCAPED_SLASHES); ?>;
            var lang = '<?php echo $_SESSION['lang'] ?? 'en'; ?>';
            var locale = lang === 'km' ? 'km-KH' : (lang === 'zh' ? 'zh-CN' : 'en-US');

            var labels = labelsRaw.map(function(ym) {
                if (!ym || ym.indexOf('-') === -1) return ym;
                var parts = ym.split('-');
                var date = new Date(parts[0], parseInt(parts[1]) - 1, 1);
                return date.toLocaleDateString(locale, { month: 'short' }).toUpperCase();
            });

            if (!labels || labels.length === 0) {
                // Generate last 6 months localized fallbacks
                labels = [];
                data = [];
                for (var i = 5; i >= 0; i--) {
                    var d = new Date();
                    d.setMonth(d.getMonth() - i);
                    labels.push(d.toLocaleDateString(locale, { month: 'short' }).toUpperCase());
                    data.push(0);
                }
            }

            new Chart(el, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        borderColor: '#6366f1',
                        borderWidth: 6,
                        fill: true,
                        tension: 0.45,
                        pointRadius: 0,
                        pointHoverRadius: 10,
                        pointHoverBackgroundColor: '#6366f1',
                        pointHoverBorderColor: '#fff',
                        pointHoverBorderWidth: 4,
                        backgroundColor: (context) => {
                            const ctx = context.chart.ctx;
                            const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                            gradient.addColorStop(0, 'rgba(99, 102, 241, 0.2)');
                            gradient.addColorStop(1, 'rgba(99, 102, 241, 0)');
                            return gradient;
                        }
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { padding: 16, cornerRadius: 16, bodyFont: { size: 14, weight: 'bold' } } },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { weight: '800' }, color: '#94a3b8' } },
                        y: { 
                            beginAtZero: true, 
                            grid: { color: '#f1f5f9', borderDash: [6, 6] }, 
                            ticks: { 
                                font: { weight: '800' }, 
                                color: '#94a3b8',
                                callback: function(value) { return '$' + value; }
                            } 
                        }
                    }
                }
            });
        })();
    </script>

    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

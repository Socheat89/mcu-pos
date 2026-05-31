<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('reports'); ?> - <?php echo htmlspecialchars($tenantName ?? 'POS'); ?></title>
    <link href="<?php echo mc_base_path(); ?>/public/css/pos_template.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Outfit:wght@300;400;500;600;700;800;900&family=Battambang:wght@100;300;400;700;900&display=swap" rel="stylesheet">
    <style>
        body, h1, h2, h3, h4, h5, h6, p, span, a, button, input, select, textarea {
            font-family: 'Battambang', 'Outfit', 'Inter', sans-serif !important;
        }
        .report-card { background: var(--pos-card); backdrop-filter: blur(12px); border-radius: 24px; padding: 32px; border: 1.5px solid var(--pos-border); }
        .ranking-item { display: flex; align-items: center; gap: 16px; padding: 14px; border-radius: 18px; transition: all 0.2s; border: 1px solid transparent; }
        .ranking-item:hover { background: rgba(255, 255, 255, 0.03); border-color: var(--pos-border); transform: translateX(8px); }
        .ranking-badge { width: 32px; height: 32px; border-radius: 10px; background: var(--pos-primary-light); color: var(--pos-primary); display: grid; place-items: center; font-size: 14px; font-weight: 900; flex-shrink: 0; }
    </style>
</head>
<body class="pos-app">
    <?php $activeNav = 'reports'; include __DIR__ . '/partials/navbar.php'; ?>

    <div class="fade-in">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
            <div class="pos-title">
                <h1><?php echo __('business_analytics'); ?></h1>
                <p><?php echo __('performance_monitor_msg'); ?></p>
            </div>
            <button class="btn btn-outline" onclick="window.print()">
                <i class="fas fa-file-pdf"></i> <?php echo __('export_overview'); ?>
            </button>
        </div>

        <!-- Quick Summary -->
        <div class="pos-grid cols-4" style="margin-bottom: 32px;">
            <div class="pos-stat">
                <span class="k"><?php echo __('total_revenue'); ?></span>
                <p class="v">$<?php echo number_format($salesSummary['total_sales'] ?? 0, 2); ?></p>
                <div class="chip" style="background: rgba(99, 102, 241, 0.1); color: var(--pos-primary);"><i class="fas fa-dollar-sign"></i></div>
            </div>
            <div class="pos-stat">
                <span class="k"><?php echo __('orders_volume'); ?></span>
                <p class="v"><?php echo number_format($salesSummary['total_orders'] ?? 0); ?></p>
                <div class="chip" style="background: rgba(16, 185, 129, 0.1); color: var(--pos-success);"><i class="fas fa-shopping-bag"></i></div>
            </div>
            <div class="pos-stat">
                <span class="k"><?php echo __('avg_ticket_size'); ?></span>
                <p class="v">$<?php echo number_format($salesSummary['avg_order_value'] ?? 0, 2); ?></p>
                <div class="chip" style="background: rgba(139, 92, 246, 0.1); color: var(--pos-secondary);"><i class="fas fa-chart-line"></i></div>
            </div>
            <div class="pos-stat">
                <span class="k"><?php echo __('active_customers'); ?></span>
                <p class="v"><?php echo number_format($salesSummary['unique_customers'] ?? 0); ?></p>
                <div class="chip" style="background: rgba(245, 158, 11, 0.1); color: var(--pos-warning);"><i class="fas fa-users"></i></div>
            </div>
        </div>

        <div class="pos-grid cols-3" style="margin-bottom: 32px; align-items: stretch;">
            <!-- Sales Chart -->
            <div class="report-card" style="grid-column: span 2;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
                    <h3 class="pos-card-title"><?php echo __('daily_sales_performance'); ?></h3>
                    <div class="badge badge-primary"><?php echo __('past_7_days'); ?></div>
                </div>
                <div style="height: 380px;">
                    <canvas id="dailySalesChart"></canvas>
                </div>
            </div>

            <!-- Top Products -->
            <div class="report-card">
                <h3 class="pos-card-title" style="margin-bottom: 24px;"><?php echo __('growth_leaderboard'); ?></h3>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <?php if(empty($topProducts)): ?>
                        <div style="text-align: center; padding: 48px; color: var(--pos-text-muted);">
                            <i class="fas fa-layer-group" style="font-size: 32px; opacity: 0.2; margin-bottom: 12px; display: block;"></i>
                            <p style="font-weight: 700;"><?php echo __('no_sales_data_found'); ?></p>
                        </div>
                    <?php else: ?>
                        <?php $rank = 1; foreach($topProducts as $p): ?>
                            <div class="ranking-item">
                                <div class="ranking-badge"><?php echo $rank++; ?></div>
                                <div style="flex: 1;">
                                    <p style="font-weight: 800; color: var(--pos-text); font-size: 14px; margin: 0;"><?php echo htmlspecialchars($p['name']); ?></p>
                                    <p style="font-size: 12px; font-weight: 600; color: var(--pos-text-muted); margin-top: 2px; text-transform: uppercase;"><?php echo __('sold_count', ['count' => number_format($p['total_quantity'])]); ?></p>
                                </div>
                                <div style="font-weight: 900; color: var(--pos-primary); font-size: 15px;">$<?php echo number_format($p['total_revenue'], 2); ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Monthly Overview -->
        <div class="report-card" style="margin-bottom: 32px;">
             <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
                <h3 class="pos-card-title"><?php echo __('monthly_revenue_trends'); ?></h3>
                 <div class="badge" style="background: rgba(255,255,255,0.03); border: 1px solid var(--pos-border); color: var(--pos-text-muted);"><?php echo __('past_6_months'); ?></div>
            </div>
            <div style="height: 320px;">
                <canvas id="monthlySalesChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        // Common chart styling
        Chart.defaults.font.family = "'Battambang', 'Inter', sans-serif";
        Chart.defaults.color = '#94a3b8';

        // 1. Daily Evolution
        const dailyCtx = document.getElementById('dailySalesChart').getContext('2d');
        const dailyData = <?php echo json_encode(array_reverse($dailySales)); ?>;
        
        new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: dailyData.map(d => {
                    const date = new Date(d.date);
                    return date.toLocaleDateString('<?php echo $_SESSION['lang'] === 'km' ? 'km-KH' : ($_SESSION['lang'] === 'zh' ? 'zh-CN' : 'en-US'); ?>', { weekday: 'short', day: 'numeric' });
                }),
                datasets: [{
                    data: dailyData.map(d => parseFloat(d.daily_total)),
                    borderColor: '#6366f1',
                    borderWidth: 5,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 8,
                    pointHoverBackgroundColor: '#6366f1',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 4,
                    backgroundColor: (context) => {
                        const ctx = context.chart.ctx;
                        const g = ctx.createLinearGradient(0, 0, 0, 400);
                        g.addColorStop(0, 'rgba(99, 102, 241, 0.15)');
                        g.addColorStop(1, 'rgba(99, 102, 241, 0)');
                        return g;
                    }
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { padding: 12, cornerRadius: 12, bodyFont: { size: 14, weight: 'bold' } } },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [5, 5], color: 'rgba(255, 255, 255, 0.04)' }, ticks: { callback: v => '$' + v } },
                    x: { grid: { display: false } }
                }
            }
        });

        // 2. Monthly Stats
        const monthCtx = document.getElementById('monthlySalesChart').getContext('2d');
        const monthData = <?php echo json_encode(array_reverse($monthlySales)); ?>;
        
        new Chart(monthCtx, {
            type: 'bar',
            data: {
                labels: monthData.map(d => {
                    const [y, m] = d.month.split('-');
                    return new Date(y, m - 1).toLocaleDateString('<?php echo $_SESSION['lang'] === 'km' ? 'km-KH' : ($_SESSION['lang'] === 'zh' ? 'zh-CN' : 'en-US'); ?>', { month: 'long', year: '2-digit' });
                }),
                datasets: [{
                    data: monthData.map(d => parseFloat(d.monthly_total)),
                    backgroundColor: '#8b5cf6',
                    borderRadius: 12,
                    maxBarThickness: 50
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [5, 5], color: 'rgba(255, 255, 255, 0.04)' }, ticks: { callback: v => '$' + v } },
                    x: { grid: { display: false } }
                }
            }
        });
    </script>
    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('analytics'); ?> - <?php echo htmlspecialchars($tenantName ?? 'POS'); ?></title>

    <link href="<?php echo mc_base_path(); ?>/public/css/pos_template.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700;800&family=Battambang:wght@300;400;700;900&display=swap" rel="stylesheet">
    <style>
        body, h1, h2, h3, h4, h5, h6, p, span, a, button, input, select, textarea {
            font-family: 'Space Grotesk', 'Battambang', sans-serif !important;
        }
        .report-card { background: var(--pos-card); border-radius: 16px; padding: 24px; border: 1px solid var(--pos-border); }
        .ranking-item { display: flex; align-items: center; gap: 16px; padding: 10px; border-radius: 12px; transition: all 0.2s; border: 1px solid transparent; }
        .ranking-item:hover { background: rgba(var(--pos-primary-rgb), 0.04); border-color: var(--pos-border); }
        .ranking-badge { width: 32px; height: 32px; border-radius: 10px; background: var(--pos-primary-light); color: var(--pos-primary); display: grid; place-items: center; font-size: 14px; font-weight: 900; flex-shrink: 0; }
        .store-filter-bar {
            display: flex; align-items: center; gap: 12px; margin-bottom: 28px;
            padding: 8px 16px; background: var(--pos-card); border: 1px solid var(--pos-border);
            border-radius: 14px; flex-wrap: wrap;
        }
        .store-filter-bar select {
            padding: 10px 14px; border-radius: 10px; border: 1px solid var(--pos-border);
            background: #fff; font-weight: 700; font-size: 13px; color: var(--pos-text);
            font-family: 'Space Grotesk', 'Battambang', sans-serif; outline: none; cursor: pointer;
        }
        .store-filter-bar select:focus { border-color: var(--pos-primary); }
        .store-sales-card {
            background: var(--pos-card); border: 1px solid var(--pos-border);
            border-radius: 14px; padding: 18px 20px; transition: all 0.2s;
        }
        .store-sales-card:hover { border-color: var(--pos-primary); box-shadow: var(--pos-shadow-sm); }
        .store-sales-card .store-code-badge {
            display: inline-block; padding: 4px 10px; border-radius: 8px;
            font-size: 11px; font-weight: 800; letter-spacing: 0.5px;
            background: var(--pos-primary-light); color: var(--pos-primary);
        }
    </style>
</head>
<body class="pos-app">
    <?php $activeNav = 'reports'; include __DIR__ . '/partials/navbar.php'; ?>

    <div class="fade-in">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 24px;">
            <div class="pos-title">
                <h1><?php echo __('analytics'); ?></h1>
                <p><?php echo __('performance_monitor_msg'); ?></p>
            </div>
            <button class="btn btn-outline" onclick="window.print()">
                <i class="fas fa-file-pdf"></i> <?php echo __('export_overview'); ?>
            </button>
        </div>

        <!-- 🔽 Store Filter -->
        <?php if (!empty($allStores) && count($allStores) > 1): ?>
        <div class="store-filter-bar">
            <i class="fas fa-store-alt" style="color:var(--pos-primary);"></i>
            <span style="font-weight:700;font-size:13px;color:var(--pos-text-muted);"><?php echo __('filter_by_store'); ?>:</span>
            <select onchange="filterStore(this.value)">
                <option value="0" <?php echo $storeId == 0 ? 'selected' : ''; ?>><?php echo __('all_stores'); ?></option>
                <?php foreach ($allStores as $st): ?>
                    <option value="<?php echo $st['id']; ?>" <?php echo $storeId == $st['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($st['code'] ? '[' . $st['code'] . '] ' : '') . htmlspecialchars($st['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if ($storeId > 0): ?>
                <a href="?store_id=0" class="btn btn-sm" style="color:var(--pos-danger);font-size:12px;">
                    <i class="fas fa-times"></i> Clear
                </a>
            <?php endif; ?>
        </div>
        <script>
            function filterStore(id) {
                window.location.href = '?store_id=' + id;
            }
        </script>
        <?php endif; ?>

        <!-- Quick Summary -->
        <div class="pos-grid cols-4" style="margin-bottom: 32px;">
            <div class="pos-stat">
                <span class="k"><?php echo __('total_revenue'); ?></span>
                <p class="v">$<?php echo Settings::formatPrice($salesSummary['total_sales'] ?? 0); ?></p>
                <div class="chip" style="background: #ecfdf5; color: #2c8a3c;"><i class="fas fa-dollar-sign"></i></div>
            </div>
            <div class="pos-stat">
                <span class="k"><?php echo __('orders_volume'); ?></span>
                <p class="v"><?php echo number_format($salesSummary['total_orders'] ?? 0); ?></p>
                <div class="chip" style="background: #f7f4f7; color: #714B67;"><i class="fas fa-shopping-bag"></i></div>
            </div>
            <div class="pos-stat">
                <span class="k"><?php echo __('avg_ticket_size'); ?></span>
                <p class="v">$<?php echo Settings::formatPrice($salesSummary['avg_order_value'] ?? 0); ?></p>
                <div class="chip" style="background: #f0faf9; color: #00A09D;"><i class="fas fa-chart-line"></i></div>
            </div>
            <div class="pos-stat">
                <span class="k"><?php echo __('active_customers'); ?></span>
                <p class="v"><?php echo number_format($salesSummary['unique_customers'] ?? 0); ?></p>
                <div class="chip" style="background: #fffbeb; color: #ec9a29;"><i class="fas fa-users"></i></div>
            </div>
        </div>

        <!-- 🔥 Sales by Store -->
        <?php if (!empty($salesByStore) && $storeId == 0): ?>
        <div class="report-card" style="margin-bottom: 28px;">
            <h3 class="pos-card-title" style="margin-bottom: 20px;">
                <i class="fas fa-store-alt" style="color:var(--pos-primary);margin-right:8px;"></i>
                <?php echo __('sales_by_store'); ?>
            </h3>
            <div class="pos-grid cols-<?php echo min(count($salesByStore), 4); ?>" style="gap:16px;">
                <?php foreach ($salesByStore as $ss): ?>
                <div class="store-sales-card">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                        <span class="store-code-badge"><?php echo htmlspecialchars($ss['store_code'] ?: '—'); ?></span>
                        <span style="font-weight:700;font-size:13px;color:var(--pos-text);"><?php echo htmlspecialchars($ss['store_name']); ?></span>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:4px;">
                        <div style="display:flex;justify-content:space-between;">
                            <span style="font-size:11px;color:var(--pos-text-muted);"><?php echo __('revenue'); ?></span>
                            <span style="font-weight:800;font-size:15px;color:var(--pos-primary);">$<?php echo Settings::formatPrice($ss['total_sales']); ?></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;">
                            <span style="font-size:11px;color:var(--pos-text-muted);"><?php echo __('orders'); ?></span>
                            <span style="font-weight:700;font-size:13px;"><?php echo number_format($ss['total_orders']); ?></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;">
                            <span style="font-size:11px;color:var(--pos-text-muted);">Avg</span>
                            <span style="font-weight:700;font-size:13px;">$<?php echo Settings::formatPrice($ss['avg_order']); ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

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
                                    <p style="font-size: 11px; font-weight: 600; color: var(--pos-text-muted); margin-top: 2px;">
                                        <?php echo number_format($p['total_quantity']); ?> sold
                                        <?php if ($storeId == 0 && !empty($p['store_name'])): ?>
                                            · <i class="fas fa-store-alt"></i> <?php echo htmlspecialchars($p['store_name']); ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div style="font-weight: 900; color: var(--pos-primary); font-size: 15px;">$<?php echo Settings::formatPrice($p['total_revenue']); ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- 🛒 Products Sold by Store -->
        <?php if (!empty($productsByStore) && $storeId == 0): ?>
        <div class="report-card" style="margin-bottom: 28px;">
            <h3 class="pos-card-title" style="margin-bottom: 20px;">
                <i class="fas fa-boxes-stacked" style="color:var(--pos-primary);margin-right:8px;"></i>
                <?php echo __('products_sold_by_store'); ?>
            </h3>
            <div style="overflow-x:auto;">
                <table class="pos-table">
                    <thead>
                        <tr>
                            <th><?php echo __('store'); ?></th>
                            <th><?php echo __('product'); ?></th>
                            <th><?php echo __('quantity'); ?></th>
                            <th><?php echo __('revenue'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $lastStore = '';
                        foreach ($productsByStore as $ps): 
                            $isNewStore = $ps['store_name'] !== $lastStore;
                            $lastStore = $ps['store_name'];
                        ?>
                            <tr>
                                <td>
                                    <?php if ($isNewStore): ?>
                                        <span class="store-code-badge"><?php echo htmlspecialchars($ps['store_code'] ?: '—'); ?></span>
                                        <strong style="margin-left:6px;"><?php echo htmlspecialchars($ps['store_name']); ?></strong>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($ps['product_name']); ?></td>
                                <td style="font-weight:700;"><?php echo number_format($ps['qty_sold']); ?></td>
                                <td style="font-weight:800;color:var(--pos-primary);">$<?php echo Settings::formatPrice($ps['revenue']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Monthly Overview -->
        <div class="report-card" style="margin-bottom: 32px;">
             <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
                <h3 class="pos-card-title"><?php echo __('monthly_revenue_trends'); ?></h3>
                  <div class="badge" style="background: rgba(0, 0, 0, 0.03); border: 1px solid var(--pos-border); color: var(--pos-text-muted);"><?php echo __('past_6_months'); ?></div>
            </div>
            <div style="height: 320px;">
                <canvas id="monthlySalesChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        Chart.defaults.font.family = "'Space Grotesk', 'Battambang', sans-serif";
        Chart.defaults.color = '#4b5563';

        const dailyCtx = document.getElementById('dailySalesChart').getContext('2d');
        const dailyData = <?php echo json_encode(array_reverse($dailySales)); ?>;
        
        new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: dailyData.map(d => {
                    const date = new Date(d.date);
                    return date.toLocaleDateString('en-US', { weekday: 'short', day: 'numeric' });
                }),
                datasets: [{
                    data: dailyData.map(d => parseFloat(d.daily_total)),
                    borderColor: '#714B67',
                    borderWidth: 4,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#714B67',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 3,
                    backgroundColor: (context) => {
                        const ctx = context.chart.ctx;
                        const g = ctx.createLinearGradient(0, 0, 0, 400);
                        g.addColorStop(0, 'rgba(113, 75, 103, 0.12)');
                        g.addColorStop(1, 'rgba(113, 75, 103, 0)');
                        return g;
                    }
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { grid: { color: 'rgba(0,0,0,0.06)' }, ticks: { callback: v => '$' + v } },
                    x: { grid: { display: false } }
                }
            }
        });

        const monthlyCtx = document.getElementById('monthlySalesChart').getContext('2d');
        const monthlyData = <?php echo json_encode(array_reverse($monthlySales)); ?>;
        
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: monthlyData.map(d => {
                    const [y, m] = d.month.split('-');
                    return new Date(y, m-1).toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                }),
                datasets: [{
                    data: monthlyData.map(d => parseFloat(d.monthly_total)),
                    backgroundColor: 'rgba(113, 75, 103, 0.7)',
                    borderColor: '#714B67',
                    borderWidth: 2,
                    borderRadius: 8,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { grid: { color: 'rgba(0,0,0,0.06)' }, ticks: { callback: v => '$' + v } },
                    x: { grid: { display: false } }
                }
            }
        });
    </script>

    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
                        g.addColorStop(1, 'rgba(113, 75, 103, 0)');

                        return g;
                    }
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { padding: 12, cornerRadius: 6, bodyFont: { size: 14, weight: 'bold' } } },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [5, 5], color: '#e5e7eb' }, ticks: { callback: v => '$' + v } },

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
                    backgroundColor: '#00A09D',
                    borderRadius: 6,

                    maxBarThickness: 50
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [5, 5], color: '#e5e7eb' }, ticks: { callback: v => '$' + v } },

                    x: { grid: { display: false } }
                }
            }
        });
    </script>
    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

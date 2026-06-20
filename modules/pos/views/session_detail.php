<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('session_details'); ?> - <?php echo htmlspecialchars($tenantName ?? 'POS'); ?></title>
    <link href="<?php echo mc_base_path(); ?>/public/css/pos_template.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Outfit:wght@300;400;500;600;700;800;900&family=Battambang:wght@100;300;400;700;900&display=swap" rel="stylesheet">
    <style>
        body, h1, h2, h3, h4, h5, h6, p, span, a, button, input, select, textarea {
            font-family: 'Battambang', 'Outfit', 'Inter', sans-serif !important;
        }
        .avatar-box { width: 36px; height: 36px; border-radius: var(--pos-radius); background: var(--pos-primary-light); display: grid; place-items: center; font-size: 14px; font-weight: 900; color: var(--pos-primary); border: 1px solid rgba(var(--pos-primary-rgb), 0.2); }
    </style>
</head>
<body class="pos-app">
    <?php $activeNav = 'sessions'; include __DIR__ . '/partials/navbar.php'; ?>

    <div class="fade-in">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
            <div class="pos-title">
                <h1><?php echo __('session_details'); ?> #<?php echo (int)$session['id']; ?></h1>
                <p>
                    Opened by <strong><?php echo htmlspecialchars($session['username']); ?></strong> on 
                    <?php echo date('M d, Y H:i A', strtotime($session['opened_at'])); ?> 
                    <?php if ($session['closed_at']): ?>
                        | Closed on <?php echo date('M d, Y H:i A', strtotime($session['closed_at'])); ?>
                    <?php else: ?>
                        | <span class="badge badge-success"><?php echo __('status_open'); ?></span>
                    <?php endif; ?>
                </p>
            </div>
            <a href="<?php echo htmlspecialchars($posUrl('sessions')); ?>" class="btn btn-outline" style="text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Back to Sessions
            </a>
        </div>

        <!-- Cash Control Stat Cards -->
        <div class="pos-grid cols-4" style="margin-bottom: 32px;">
            <div class="pos-stat">
                <span class="k"><?php echo __('opening_balance'); ?></span>
                <p class="v">$<?php echo number_format($session['opening_balance'], 2); ?></p>
                <div class="chip" style="background: rgba(6, 182, 212, 0.1); color: var(--pos-primary);"><i class="fas fa-key"></i></div>
            </div>
            <div class="pos-stat">
                <span class="k"><?php echo __('total_sales'); ?></span>
                <p class="v">$<?php echo number_format($session['total_sales'], 2); ?></p>
                <div class="chip" style="background: rgba(99, 102, 241, 0.1); color: var(--pos-secondary);"><i class="fas fa-chart-line"></i></div>
            </div>
            
            <?php if ($session['status'] === 'closed'): 
                $cashSales = $paymentSummary['cash'] ?? 0.0;
                $expectedCash = (float)$session['opening_balance'] + $cashSales;
                $difference = (float)$session['closing_balance'] - $expectedCash;
                $diffColor = $difference >= 0 ? 'var(--pos-success)' : 'var(--pos-danger)';
                $diffSign = $difference >= 0 ? '+' : '';
            ?>
                <div class="pos-stat">
                    <span class="k">Expected Drawer Cash</span>
                    <p class="v">$<?php echo number_format($expectedCash, 2); ?></p>
                    <div class="chip" style="background: rgba(16, 185, 129, 0.1); color: var(--pos-success);"><i class="fas fa-calculator"></i></div>
                </div>
                <div class="pos-stat">
                    <span class="k">Actual Cash / Difference</span>
                    <p class="v" style="font-size: 20px; font-weight: 800;">
                        $<?php echo number_format($session['closing_balance'], 2); ?> 
                        <span style="color: <?php echo $diffColor; ?>; font-size: 13px; font-weight: 700; display: block; margin-top: 4px;">
                            (<?php echo $diffSign . number_format($difference, 2); ?> diff)
                        </span>
                    </p>
                    <div class="chip" style="background: rgba(244, 63, 94, 0.1); color: var(--pos-danger);"><i class="fas fa-scale-balanced"></i></div>
                </div>
            <?php else: ?>
                <div class="pos-stat" style="grid-column: span 2;">
                    <span class="k">Current Session Status</span>
                    <p class="v" style="color: var(--pos-success);">ACTIVE / RUNNING</p>
                    <div class="chip" style="background: rgba(16, 185, 129, 0.1); color: var(--pos-success);"><i class="fas fa-spinner fa-spin"></i></div>
                </div>
            <?php endif; ?>
        </div>

        <div class="pos-grid cols-3" style="align-items: start; margin-bottom: 32px;">
            <!-- Sold Products Breakdown (Odoo POS style) -->
            <div class="pos-card pad" style="grid-column: span 2;">
                <h3 class="pos-card-title" style="margin-bottom: 20px;"><i class="fas fa-boxes-stacked"></i> <?php echo __('items_sold'); ?></h3>
                <div class="pos-table-container" style="border: none;">
                    <table class="pos-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>SKU</th>
                                <th style="text-align: center;"><?php echo __('sold_qty'); ?></th>
                                <th style="text-align: right;"><?php echo __('revenue'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($soldProducts)): ?>
                                <tr>
                                    <td colspan="4" style="padding: 40px; text-align: center; color: var(--pos-text-muted);">No products sold in this session.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($soldProducts as $p): ?>
                                    <tr>
                                        <td style="font-weight: 700; color: var(--pos-text);"><?php echo htmlspecialchars($p['name']); ?></td>
                                        <td style="font-size: 12px; color: var(--pos-text-muted);"><?php echo htmlspecialchars($p['sku'] ?? 'N/A'); ?></td>
                                        <td style="text-align: center; font-weight: 700;"><?php echo (int)$p['qty_sold']; ?></td>
                                        <td style="text-align: right; font-weight: 800; color: var(--pos-primary);">$<?php echo number_format($p['total_revenue'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Revenue by Payment Method -->
            <div class="pos-card pad">
                <h3 class="pos-card-title" style="margin-bottom: 20px;"><i class="fas fa-money-check-dollar"></i> Payments Breakdown</h3>
                <div class="pos-table-container" style="border: none;">
                    <table class="pos-table">
                        <thead>
                            <tr>
                                <th>Method</th>
                                <th style="text-align: right;"><?php echo __('revenue'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($paymentSummary)): ?>
                                <tr>
                                    <td colspan="2" style="padding: 40px; text-align: center; color: var(--pos-text-muted);">No payments recorded.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($paymentSummary as $method => $amount): ?>
                                    <tr>
                                        <td style="font-weight: 700; text-transform: uppercase; color: var(--pos-text);"><?php echo htmlspecialchars($method); ?></td>
                                        <td style="text-align: right; font-weight: 800; color: var(--pos-primary);">$<?php echo number_format($amount, 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Orders completed in this session -->
        <div class="pos-card pad">
            <h3 class="pos-card-title" style="margin-bottom: 20px;"><i class="fas fa-list"></i> Completed Orders</h3>
            <div class="pos-table-container" style="border: none;">
                <table class="pos-table">
                    <thead>
                        <tr>
                            <th style="width: 120px;"><?php echo __('reference'); ?></th>
                            <th><?php echo __('customer'); ?></th>
                            <th>Order Date</th>
                            <th><?php echo __('amount'); ?></th>
                            <th><?php echo __('status'); ?></th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="6" style="padding: 40px; text-align: center; color: var(--pos-text-muted);">No orders created during this session.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $o): ?>
                                <tr>
                                    <td style="font-weight: 800; color: var(--pos-primary);">#<?php echo $o['id']; ?></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <div class="avatar-box" style="width: 28px; height: 28px; font-size: 11px;">
                                                <?php echo strtoupper(substr($o['customer_name'] ?? 'W', 0, 1)); ?>
                                            </div>
                                            <div style="font-weight: 700; color: var(--pos-text);"><?php echo htmlspecialchars($o['customer_name'] ?? 'Walk-in Customer'); ?></div>
                                        </div>
                                    </td>
                                    <td><?php echo date('M d, Y h:i A', strtotime($o['created_at'])); ?></td>
                                    <td style="font-weight: 800; color: var(--pos-text); font-size: 15px;">$<?php echo number_format($o['total'], 2); ?></td>
                                    <td><span class="badge badge-success"><?php echo ucfirst($o['status']); ?></span></td>
                                    <td style="text-align: right;">
                                        <a href="<?php echo htmlspecialchars($posUrl('orders/' . $o['id'])); ?>" class="pos-icon-btn" title="View Details">
                                            <i class="fas fa-eye" style="font-size: 12px;"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

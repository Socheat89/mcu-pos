<?php
$cashSales       = $paymentSummary['cash'] ?? 0.0;
$expectedCash    = (float)$session['opening_balance'] + $cashSales;
$difference      = isset($session['closing_balance']) ? (float)$session['closing_balance'] - $expectedCash : null;
$isClosed        = $session['status'] === 'closed';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('session_details'); ?> #<?php echo (int)$session['id']; ?> - <?php echo htmlspecialchars($tenantName ?? 'POS'); ?></title>
    <link href="<?php echo mc_base_path(); ?>/public/css/pos_template.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Outfit:wght@300;400;500;600;700;800;900&family=Battambang:wght@100;300;400;700;900&display=swap" rel="stylesheet">
    <style>
        body, h1, h2, h3, h4, h5, h6, p, span, a, button, input, select, textarea {
            font-family: 'Battambang', 'Outfit', 'Inter', sans-serif !important;
        }
        .avatar-box { width: 32px; height: 32px; border-radius: var(--pos-radius); background: var(--pos-primary-light); display: grid; place-items: center; font-size: 13px; font-weight: 900; color: var(--pos-primary); border: 1px solid rgba(var(--pos-primary-rgb), 0.2); flex-shrink: 0; }

        /* ── Tab system ── */
        .sd-tabs { display: flex; gap: 4px; padding: 6px; background: rgba(255,255,255,0.04); border: 1px solid var(--pos-border); border-radius: 18px; margin-bottom: 24px; flex-wrap: wrap; }
        .sd-tab-btn { padding: 9px 18px; border-radius: 12px; border: none; background: transparent; color: var(--pos-text-muted); font-size: 13px; font-weight: 700; cursor: pointer; transition: all 0.22s; display: flex; align-items: center; gap: 8px; white-space: nowrap; }
        .sd-tab-btn:hover { background: rgba(255,255,255,0.06); color: var(--pos-text); }
        .sd-tab-btn.active { background: var(--pos-primary); color: #fff; box-shadow: 0 4px 14px rgba(6,182,212,0.35); }
        .sd-tab-btn .tab-badge { background: rgba(255,255,255,0.25); color: inherit; border-radius: 8px; font-size: 10px; font-weight: 900; padding: 1px 7px; }
        .sd-tab-btn.active .tab-badge { background: rgba(255,255,255,0.3); }

        .sd-tab-pane { display: none; animation: fadeInTab 0.25s ease; }
        .sd-tab-pane.active { display: block; }
        @keyframes fadeInTab { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:translateY(0); } }

        /* ── Payment method sub-panel ── */
        .pm-icon { width: 38px; height: 38px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 17px; flex-shrink: 0; }
        .pm-icon.cash    { background: rgba(16,185,129,0.12); color: #10b981; }
        .pm-icon.khqr   { background: rgba(6,182,212,0.12);  color: var(--pos-primary); }
        .pm-icon.card   { background: rgba(99,102,241,0.12); color: #6366f1; }
        .pm-icon.default{ background: rgba(255,255,255,0.06); color: var(--pos-text-muted); }
    </style>
</head>
<body class="pos-app">
    <?php $activeNav = 'sessions'; include __DIR__ . '/partials/navbar.php'; ?>

    <div class="fade-in">

        <!-- ── Page Header ── -->
        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:32px; flex-wrap:wrap; gap:16px;">
            <div class="pos-title">
                <h1><?php echo __('session_details'); ?> #<?php echo (int)$session['id']; ?></h1>
                <p>
                    <?php echo __('opened_by'); ?>: <strong><?php echo htmlspecialchars($session['username']); ?></strong>
                    &nbsp;·&nbsp; <?php echo date('d M Y, H:i', strtotime($session['opened_at'])); ?>
                    <?php if ($isClosed): ?>
                        &nbsp;·&nbsp; <?php echo __('closed_at'); ?>: <?php echo date('d M Y, H:i', strtotime($session['closed_at'])); ?>
                    <?php else: ?>
                        &nbsp;·&nbsp; <span class="badge badge-success"><i class="fas fa-circle fa-beat" style="font-size:8px;"></i> <?php echo __('status_open'); ?></span>
                    <?php endif; ?>
                </p>
            </div>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <?php if (!$isClosed): ?>
                    <a href="<?php echo htmlspecialchars($posUrl('sessions/close')); ?>" class="btn" style="background:rgba(244,63,94,0.15); border:1px solid rgba(244,63,94,0.3); color:#fda4af; text-decoration:none;">
                        <i class="fas fa-power-off"></i> <?php echo __('close_session'); ?>
                    </a>
                <?php endif; ?>
                <a href="<?php echo htmlspecialchars($posUrl('sessions')); ?>" class="btn btn-outline" style="text-decoration:none;">
                    <i class="fas fa-arrow-left"></i> <?php echo __('session_list'); ?>
                </a>
            </div>
        </div>

        <!-- ── Stat Cards ── -->
        <div class="pos-grid cols-4" style="margin-bottom:32px;">
            <div class="pos-stat">
                <span class="k"><?php echo __('opening_balance'); ?></span>
                <p class="v">$<?php echo number_format($session['opening_balance'], 2); ?></p>
                <div class="chip" style="background:rgba(6,182,212,0.1); color:var(--pos-primary);"><i class="fas fa-key"></i></div>
            </div>
            <div class="pos-stat">
                <span class="k"><?php echo __('total_sales'); ?></span>
                <p class="v">$<?php echo number_format($session['total_sales'], 2); ?></p>
                <div class="chip" style="background:rgba(99,102,241,0.1); color:var(--pos-secondary);"><i class="fas fa-chart-line"></i></div>
            </div>
            <?php if ($isClosed && $difference !== null): ?>
                <div class="pos-stat">
                    <span class="k">Expected Cash</span>
                    <p class="v">$<?php echo number_format($expectedCash, 2); ?></p>
                    <div class="chip" style="background:rgba(16,185,129,0.1); color:var(--pos-success);"><i class="fas fa-calculator"></i></div>
                </div>
                <div class="pos-stat">
                    <span class="k"><?php echo __('closing_balance'); ?> / Diff</span>
                    <p class="v" style="font-size:18px;">
                        $<?php echo number_format($session['closing_balance'], 2); ?>
                        <span style="color:<?php echo $difference >= 0 ? 'var(--pos-success)' : 'var(--pos-danger)'; ?>; font-size:12px; font-weight:700; display:block; margin-top:4px;">
                            (<?php echo ($difference >= 0 ? '+' : '') . number_format($difference, 2); ?> diff)
                        </span>
                    </p>
                    <div class="chip" style="background:rgba(244,63,94,0.1); color:var(--pos-danger);"><i class="fas fa-scale-balanced"></i></div>
                </div>
            <?php else: ?>
                <div class="pos-stat" style="grid-column:span 2;">
                    <span class="k">Session Status</span>
                    <p class="v" style="color:var(--pos-success);">ACTIVE / RUNNING</p>
                    <div class="chip" style="background:rgba(16,185,129,0.1); color:var(--pos-success);"><i class="fas fa-spinner fa-spin"></i></div>
                </div>
            <?php endif; ?>
        </div>

        <!-- ── Main Tabs ── -->
        <?php
        $methodIcons = ['cash' => 'fas fa-money-bill-wave', 'khqr' => 'fas fa-qrcode', 'card' => 'fas fa-credit-card'];
        $methodLabels= ['cash' => 'Cash', 'khqr' => 'KHQR', 'card' => 'Card'];
        ?>

        <div class="sd-tabs" id="sdTabs" role="tablist">
            <button class="sd-tab-btn active" data-tab="tab-items" role="tab">
                <i class="fas fa-boxes-stacked"></i> <?php echo __('items_sold'); ?>
                <span class="tab-badge"><?php echo count($soldProducts); ?></span>
            </button>

            <?php foreach ($paymentSummary as $method => $amount): ?>
                <button class="sd-tab-btn" data-tab="tab-pm-<?php echo htmlspecialchars($method); ?>" role="tab">
                    <i class="<?php echo $methodIcons[$method] ?? 'fas fa-dollar-sign'; ?>"></i>
                    <?php echo $methodLabels[$method] ?? strtoupper($method); ?>
                    <span class="tab-badge">$<?php echo number_format($amount, 2); ?></span>
                </button>
            <?php endforeach; ?>

            <button class="sd-tab-btn" data-tab="tab-orders" role="tab">
                <i class="fas fa-receipt"></i> Orders
                <span class="tab-badge"><?php echo count($orders); ?></span>
            </button>
        </div>

        <!-- ─── Tab: Items Sold (all methods combined) ─── -->
        <div class="sd-tab-pane active" id="tab-items">
            <div class="pos-card pad">
                <h3 class="pos-card-title" style="margin-bottom:20px;">
                    <i class="fas fa-boxes-stacked"></i> <?php echo __('items_sold'); ?>
                    <span style="font-size:12px; font-weight:600; color:var(--pos-text-muted); margin-left:8px;">— ទំនិញបានលក់ (រាល់ការបង់ប្រាក់)</span>
                </h3>
                <?php if (empty($soldProducts)): ?>
                    <div style="padding:60px; text-align:center; color:var(--pos-text-muted);">
                        <i class="fas fa-box-open" style="font-size:36px; opacity:0.3; display:block; margin-bottom:12px;"></i>
                        No products sold in this session.
                    </div>
                <?php else: ?>
                    <div class="pos-table-container" style="border:none;">
                        <table class="pos-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th style="text-align:center;"><?php echo __('sold_qty'); ?></th>
                                    <th style="text-align:right;"><?php echo __('revenue'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($soldProducts as $p): ?>
                                    <tr>
                                        <td style="font-weight:700; color:var(--pos-text);"><?php echo htmlspecialchars($p['name']); ?></td>
                                        <td style="font-size:12px; color:var(--pos-text-muted);"><?php echo htmlspecialchars($p['sku'] ?? 'N/A'); ?></td>
                                        <td style="text-align:center;">
                                            <span style="display:inline-block; background:rgba(6,182,212,0.1); color:var(--pos-primary); font-weight:800; border-radius:8px; padding:2px 12px; font-size:13px;">
                                                <?php echo (int)$p['qty_sold']; ?>
                                            </span>
                                        </td>
                                        <td style="text-align:right; font-weight:800; color:var(--pos-primary);">$<?php echo number_format($p['total_revenue'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr style="border-top:2px solid var(--pos-border);">
                                    <td colspan="2" style="font-weight:800; color:var(--pos-text);">Total</td>
                                    <td style="text-align:center; font-weight:800; color:var(--pos-primary);">
                                        <?php echo array_sum(array_column($soldProducts, 'qty_sold')); ?>
                                    </td>
                                    <td style="text-align:right; font-weight:900; color:var(--pos-primary); font-size:16px;">
                                        $<?php echo number_format(array_sum(array_column($soldProducts, 'total_revenue')), 2); ?>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ─── Tab: Per Payment Method ─── -->
        <?php foreach ($paymentSummary as $method => $totalAmount): ?>
            <div class="sd-tab-pane" id="tab-pm-<?php echo htmlspecialchars($method); ?>">
                <div class="pos-card pad">
                    <?php
                    $iconCls  = $methodIcons[$method]  ?? 'fas fa-dollar-sign';
                    $pmLabel  = $methodLabels[$method] ?? strtoupper($method);
                    $pmItems  = $itemsByPayment[$method] ?? [];
                    $iconType = array_key_exists($method, $methodIcons) ? $method : 'default';
                    ?>
                    <div style="display:flex; align-items:center; gap:14px; margin-bottom:24px;">
                        <div class="pm-icon <?php echo $iconType; ?>">
                            <i class="<?php echo $iconCls; ?>"></i>
                        </div>
                        <div>
                            <h3 class="pos-card-title" style="margin:0;"><?php echo $pmLabel; ?> — Items Sold</h3>
                            <p style="margin:4px 0 0; font-size:13px; color:var(--pos-text-muted);">
                                Total collected via <?php echo $pmLabel; ?>:
                                <strong style="color:var(--pos-primary);">$<?php echo number_format($totalAmount, 2); ?></strong>
                            </p>
                        </div>
                    </div>

                    <?php if (empty($pmItems)): ?>
                        <div style="padding:60px; text-align:center; color:var(--pos-text-muted);">
                            <i class="fas fa-box-open" style="font-size:36px; opacity:0.3; display:block; margin-bottom:12px;"></i>
                            No items found for this payment method.
                        </div>
                    <?php else: ?>
                        <div class="pos-table-container" style="border:none;">
                            <table class="pos-table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>SKU</th>
                                        <th style="text-align:center;"><?php echo __('sold_qty'); ?></th>
                                        <th style="text-align:right;"><?php echo __('revenue'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pmItems as $item): ?>
                                        <tr>
                                            <td style="font-weight:700; color:var(--pos-text);"><?php echo htmlspecialchars($item['name']); ?></td>
                                            <td style="font-size:12px; color:var(--pos-text-muted);"><?php echo htmlspecialchars($item['sku'] ?? 'N/A'); ?></td>
                                            <td style="text-align:center;">
                                                <span style="display:inline-block; background:rgba(6,182,212,0.1); color:var(--pos-primary); font-weight:800; border-radius:8px; padding:2px 12px; font-size:13px;">
                                                    <?php echo (int)$item['qty_sold']; ?>
                                                </span>
                                            </td>
                                            <td style="text-align:right; font-weight:800; color:var(--pos-primary);">$<?php echo number_format($item['total_revenue'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr style="border-top:2px solid var(--pos-border);">
                                        <td colspan="2" style="font-weight:800; color:var(--pos-text);">Total (<?php echo $pmLabel; ?>)</td>
                                        <td style="text-align:center; font-weight:800; color:var(--pos-primary);">
                                            <?php echo array_sum(array_column($pmItems, 'qty_sold')); ?>
                                        </td>
                                        <td style="text-align:right; font-weight:900; color:var(--pos-primary); font-size:16px;">
                                            $<?php echo number_format($totalAmount, 2); ?>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- ─── Tab: Orders List ─── -->
        <div class="sd-tab-pane" id="tab-orders">
            <div class="pos-card pad">
                <h3 class="pos-card-title" style="margin-bottom:20px;"><i class="fas fa-receipt"></i> Completed Orders</h3>
                <?php if (empty($orders)): ?>
                    <div style="padding:60px; text-align:center; color:var(--pos-text-muted);">
                        <i class="fas fa-receipt" style="font-size:36px; opacity:0.3; display:block; margin-bottom:12px;"></i>
                        No orders created during this session.
                    </div>
                <?php else: ?>
                    <div class="pos-table-container" style="border:none;">
                        <table class="pos-table">
                            <thead>
                                <tr>
                                    <th style="width:100px;"><?php echo __('reference'); ?></th>
                                    <th><?php echo __('customer'); ?></th>
                                    <th>Order Date</th>
                                    <th><?php echo __('amount'); ?></th>
                                    <th><?php echo __('status'); ?></th>
                                    <th style="text-align:right;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $o): ?>
                                    <tr>
                                        <td style="font-weight:800; color:var(--pos-primary);">#<?php echo $o['id']; ?></td>
                                        <td>
                                            <div style="display:flex; align-items:center; gap:10px;">
                                                <div class="avatar-box"><?php echo strtoupper(substr($o['customer_name'] ?? 'W', 0, 1)); ?></div>
                                                <span style="font-weight:700; color:var(--pos-text);"><?php echo htmlspecialchars($o['customer_name'] ?? 'Walk-in Customer'); ?></span>
                                            </div>
                                        </td>
                                        <td style="font-size:13px; color:var(--pos-text-muted);"><?php echo date('d M Y, h:i A', strtotime($o['created_at'])); ?></td>
                                        <td style="font-weight:800; color:var(--pos-text); font-size:15px;">$<?php echo number_format($o['total'], 2); ?></td>
                                        <td><span class="badge badge-success"><?php echo ucfirst($o['status']); ?></span></td>
                                        <td style="text-align:right;">
                                            <a href="<?php echo htmlspecialchars($posUrl('orders/' . $o['id'])); ?>" class="pos-icon-btn" title="View Details">
                                                <i class="fas fa-eye" style="font-size:13px;"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- /fade-in -->

    <script>
        // ── Tab switcher ──
        document.querySelectorAll('.sd-tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const target = btn.dataset.tab;
                document.querySelectorAll('.sd-tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.sd-tab-pane').forEach(p => p.classList.remove('active'));
                btn.classList.add('active');
                document.getElementById(target).classList.add('active');
            });
        });
    </script>

    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

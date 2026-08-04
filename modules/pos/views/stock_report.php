<?php
require_once __DIR__ . '/../../../core/helpers/url.php';
require_once __DIR__ . '/../../../core/classes/Settings.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock In-Out - <?php echo htmlspecialchars($tenantName ?? 'POS'); ?></title>

    <link href="<?php echo mc_base_path(); ?>/public/css/pos_template.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700;800&family=Battambang:wght@300;400;700;900&display=swap" rel="stylesheet">
    <style>
        body, h1, h2, h3, h4, h5, h6, p, span, a, button, input, select, textarea {
            font-family: 'Space Grotesk', 'Battambang', sans-serif !important;
        }

        /* ── Stock Badge ── */
        .stock-badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 800;
        }
        .stock-badge.in-stock  { background: rgba(16,185,129,0.1); color: #10b981; border: 1px solid rgba(16,185,129,0.25); }
        .stock-badge.low-stock { background: rgba(245,158,11,0.1); color: #f59e0b; border: 1px solid rgba(245,158,11,0.25); }
        .stock-badge.no-stock  { background: rgba(239,68,68,0.1);  color: #ef4444; border: 1px solid rgba(239,68,68,0.25); }

        /* ── Print styles ── */
        @media screen {
            .print-only-container { display: none !important; }
        }
        @media print {
            .pos-sidebar, .pos-topbar, .no-print, .pos-title, .pos-grid, .pos-card, .stock-adjust-modal { display: none !important; }
            .pos-main { margin: 0 !important; padding: 0 !important; background: #fff !important; }
            .pos-page { padding: 0 !important; }
            .print-only-container { display: block !important; }
            @page { size: portrait; margin: 10mm; }
        }

        /* ── Adjust Modal ── */
        .stock-adjust-modal {
            position: fixed; inset: 0; background: rgba(0,0,0,0.55);
            backdrop-filter: blur(6px); z-index: 9999;
            display: none; align-items: center; justify-content: center;
        }
        .stock-adjust-modal.open { display: flex; }
        .stock-adjust-box {
            background: #fff; border-radius: 24px; width: 90%; max-width: 460px;
            box-shadow: 0 30px 80px rgba(0,0,0,0.3);
            overflow: hidden; animation: scaleIn 0.25s ease-out;
        }
        @keyframes scaleIn {
            from { transform: scale(0.9); opacity: 0; }
            to   { transform: scale(1);   opacity: 1; }
        }
        .stock-adjust-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 20px 24px; border-bottom: 1px solid var(--pos-border);
        }
        .stock-adjust-header h3 {
            margin: 0; font-size: 17px; font-weight: 900; color: var(--pos-text);
        }
        .stock-adjust-body   { padding: 24px; }
        .stock-adjust-footer {
            display: flex; justify-content: flex-end; gap: 10px;
            padding: 16px 24px; border-top: 1px solid var(--pos-border); background: #f9fafb;
        }

        /* ── Movement type pills ── */
        .movement-pill {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 20px; border-radius: 12px; font-weight: 800; font-size: 14px;
            cursor: pointer; transition: all 0.2s; border: 2px solid var(--pos-border);
            background: #fff; color: var(--pos-text-muted);
        }
        .movement-pill.active-in  { border-color: #10b981; background: rgba(16,185,129,0.08); color: #10b981; }
        .movement-pill.active-out { border-color: #ef4444; background: rgba(239,68,68,0.08);  color: #ef4444; }

        /* ── Stock Log Table ── */
        .log-direction-in  { color: #10b981; font-weight: 800; }
        .log-direction-out { color: #ef4444; font-weight: 800; }

        /* ── Product search in modal ── */
        .stock-search-dropdown {
            max-height: 220px; overflow-y: auto;
            border: 1.5px solid var(--pos-border); border-radius: 12px;
            margin-top: 6px; background: #fff;
        }
        .stock-search-item {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 14px; cursor: pointer; font-size: 13px;
            font-weight: 700; color: var(--pos-text); border-bottom: 1px solid #f1f5f9;
            transition: background 0.15s;
        }
        .stock-search-item:hover { background: rgba(99,102,241,0.06); }
        .stock-search-item:last-child { border-bottom: none; }

        /* ── Transfer Modal ── */
        .transfer-modal {
            position: fixed; inset: 0; background: rgba(0,0,0,0.55);
            backdrop-filter: blur(6px); z-index: 9999;
            display: none; align-items: center; justify-content: center;
        }
        .transfer-modal.open { display: flex; }
        .transfer-box {
            background: #fff; border-radius: 24px; width: 90%; max-width: 500px;
            box-shadow: 0 30px 80px rgba(0,0,0,0.3);
            overflow: hidden; animation: scaleIn 0.25s ease-out;
        }
        .transfer-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 20px 24px; border-bottom: 1px solid var(--pos-border);
            background: linear-gradient(135deg, rgba(99,102,241,0.08), rgba(168,85,247,0.06));
        }
        .transfer-header h3 { margin: 0; font-size: 17px; font-weight: 900; color: var(--pos-text); }
        .transfer-body   { padding: 24px; display: flex; flex-direction: column; gap: 16px; }
        .transfer-footer {
            display: flex; justify-content: flex-end; gap: 10px;
            padding: 16px 24px; border-top: 1px solid var(--pos-border); background: #f9fafb;
        }
        .store-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 800;
            background: rgba(99,102,241,0.1); color: var(--pos-primary); border: 1px solid rgba(99,102,241,0.2);
        }
    </style>
</head>
<body class="pos-app">
    <?php $activeNav = 'stock_report'; include __DIR__ . '/partials/navbar.php'; ?>

    <div class="fade-in">
        <!-- Header -->
        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:28px;">
            <div class="pos-title">
                <h1><i class="fas fa-boxes-stacked" style="color:var(--pos-primary);margin-right:8px;"></i>Stock In-Out</h1>
                <p>ការ​គ្រប់​គ្រង​ស្តុក​ចូល​ចេញ — Manage inventory stock movements</p>
            </div>
            <div style="display:flex; gap:10px; flex-wrap:wrap;" class="no-print">
                <button class="btn btn-primary" onclick="openStockModal()">
                    <i class="fas fa-plus"></i> Stock In / Out
                </button>
                <?php if (!empty($allStores) && count($allStores) > 1): ?>
                <a href="<?php echo htmlspecialchars($posUrl('stock-transfer')); ?>" class="btn btn-outline" style="border-color:rgba(99,102,241,0.4); color:var(--pos-primary); text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
                    <i class="fas fa-arrows-left-right"></i> Transfer Stock
                </a>
                <?php endif; ?>
                <button class="btn btn-outline" onclick="window.print()">
                    <i class="fas fa-print"></i> Print Details
                </button>
                <a href="<?php echo htmlspecialchars($posUrl('reports')); ?>" class="btn btn-outline">
                    <i class="fas fa-chart-line"></i> Analytics
                </a>
            </div>
        </div>

        <!-- Current Store Banner -->
        <?php if (!empty($currentStore)): ?>
        <div class="no-print" style="display:flex; align-items:center; gap:12px; background:rgba(99,102,241,0.06); border:1px solid rgba(99,102,241,0.18); border-radius:14px; padding:12px 18px; margin-bottom:20px;">
            <i class="fas fa-store" style="color:var(--pos-primary); font-size:18px;"></i>
            <div>
                <div style="font-size:11px; font-weight:700; color:var(--pos-text-muted); text-transform:uppercase; letter-spacing:0.5px;">Stock shown for active store</div>
                <div style="font-size:15px; font-weight:900; color:var(--pos-text);"><?php echo htmlspecialchars($currentStore['name']); ?> <span style="font-size:12px; color:var(--pos-text-muted); font-weight:600;">(<?php echo htmlspecialchars($currentStore['code'] ?? ''); ?>)</span></div>
            </div>
            <?php
            // Use allowed stores (respects store lock)
            $displayStores = class_exists('Store') ? Store::getAllowedStores() : $allStores;
            $isUserLocked = class_exists('Store') && Store::isUserLocked();
            if (!$isUserLocked && !empty($displayStores) && count($displayStores) > 1):
            ?>
            <div style="margin-left:auto; display:flex; gap:8px; flex-wrap:wrap;">
                <?php foreach ($displayStores as $s): ?>
                <a href="<?php echo htmlspecialchars($posUrl('stores/switch') . '?store_id=' . $s['id'] . '&redirect=' . urlencode($posUrl('stock-report'))); ?>"
                   style="display:inline-flex; align-items:center; gap:5px; padding:5px 12px; border-radius:20px; font-size:12px; font-weight:800; text-decoration:none;
                   <?php echo $s['id'] == $currentStore['id'] ? 'background:var(--pos-primary); color:#fff;' : 'background:#f1f5f9; color:var(--pos-text); border:1px solid var(--pos-border);'; ?>">
                    <?php echo htmlspecialchars($s['code'] ?? $s['name']); ?>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Summary Cards -->
        <?php
        $totalProducts  = count($products);
        $inStockCount   = count(array_filter($products, fn($p) => ($p['display_stock'] ?? 0) > 10));
        $lowStockCount  = count(array_filter($products, fn($p) => ($p['display_stock'] ?? 0) > 0 && ($p['display_stock'] ?? 0) <= 10));
        $noStockCount   = count(array_filter($products, fn($p) => ($p['display_stock'] ?? 0) <= 0));
        $totalUnits     = array_sum(array_column($products, 'display_stock'));
        ?>
        <div class="pos-grid cols-4" style="margin-bottom:28px;">
            <div class="pos-stat">
                <span class="k">Total Products</span>
                <p class="v"><?php echo $totalProducts; ?></p>
                <div class="chip" style="background:rgba(99,102,241,0.1);color:var(--pos-primary);"><i class="fas fa-box"></i></div>
            </div>
            <div class="pos-stat">
                <span class="k">In Stock (>10)</span>
                <p class="v" style="color:#10b981;"><?php echo $inStockCount; ?></p>
                <div class="chip" style="background:rgba(16,185,129,0.1);color:#10b981;"><i class="fas fa-check"></i></div>
            </div>
            <div class="pos-stat">
                <span class="k">Low Stock (≤10)</span>
                <p class="v" style="color:#f59e0b;"><?php echo $lowStockCount; ?></p>
                <div class="chip" style="background:rgba(245,158,11,0.1);color:#f59e0b;"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
            <div class="pos-stat">
                <span class="k">Out of Stock</span>
                <p class="v" style="color:#ef4444;"><?php echo $noStockCount; ?></p>
                <div class="chip" style="background:rgba(239,68,68,0.1);color:#ef4444;"><i class="fas fa-times-circle"></i></div>
            </div>
        </div>

        <!-- Print Details Area (Active on window.print()) -->
        <div class="print-only-container" id="printStockDetails">
            <div style="text-align:center; margin-bottom:20px; border-bottom:2px solid #000; padding-bottom:12px;">
                <h2 style="font-size:22px; font-weight:900; margin:0; text-transform:uppercase;"><?php echo htmlspecialchars($tenantName); ?></h2>
                <h3 style="font-size:16px; font-weight:900; margin:6px 0; letter-spacing:1px;">III. STOCK IN AND OUT</h3>
                <p style="font-size:12px; margin:0; color:#444;">កាលបរិច្ឆេទ / Date: <?php echo date('d/m/Y H:i'); ?></p>
            </div>

            <table style="width:100%; border-collapse:collapse; font-size:13px; margin-bottom:20px;" class="official-print-table">
                <thead>
                    <tr style="background:#eef2f6; border-top:2px solid #000; border-bottom:2px solid #000;">
                        <th style="width:40px; padding:8px; border:1px solid #000; text-align:center;">ល.រ</th>
                        <th style="padding:8px; border:1px solid #000; text-align:left;">ផលិតផល ឬទំនិញ</th>
                        <th style="padding:8px; border:1px solid #000; text-align:center;">ចំនួនដើម</th>
                        <th style="padding:8px; border:1px solid #000; text-align:center;">ចំនួនបានប្រើ</th>
                        <th style="padding:8px; border:1px solid #000; text-align:center;">ចំនួនសល់</th>
                        <th style="padding:8px; border:1px solid #000; text-align:center;">សរុប</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr><td colspan="6" style="padding:20px; text-align:center; border:1px solid #000;">គ្មានទិន្នន័យផលិតផល</td></tr>
                    <?php else: $k = 0; $sumOp = 0; $sumUsed = 0; $sumRem = 0;
                        foreach ($products as $p): $k++;
                            $used = (int)($p['qty_sold'] ?? 0);
                            $remaining = (int)$p['stock_quantity'];
                            $opening = $remaining + $used;
                            $sumOp += $opening;
                            $sumUsed += $used;
                            $sumRem += $remaining;
                    ?>
                        <tr>
                            <td style="padding:7px; border:1px solid #000; text-align:center; font-weight:700;"><?php echo $k; ?></td>
                            <td style="padding:7px; border:1px solid #000; font-weight:700;"><?php echo htmlspecialchars($p['name']); ?></td>
                            <td style="padding:7px; border:1px solid #000; text-align:center;"><?php echo number_format($opening); ?></td>
                            <td style="padding:7px; border:1px solid #000; text-align:center; font-weight:700;"><?php echo number_format($used); ?></td>
                            <td style="padding:7px; border:1px solid #000; text-align:center; font-weight:700;"><?php echo number_format($remaining); ?></td>
                            <td style="padding:7px; border:1px solid #000; text-align:center;"><?php echo number_format($opening); ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    <tr style="border-top:2px solid #000; font-weight:700;">
                        <td colspan="2" style="padding:8px; border:1px solid #000; text-align:center; font-weight:800;">សរុបរួម / Total</td>
                        <td style="padding:8px; border:1px solid #000; text-align:center; font-weight:800;"><?php echo number_format($sumOp ?? 0); ?></td>
                        <td style="padding:8px; border:1px solid #000; text-align:center; font-weight:800;"><?php echo number_format($sumUsed ?? 0); ?></td>
                        <td style="padding:8px; border:1px solid #000; text-align:center; font-weight:800;"><?php echo number_format($sumRem ?? 0); ?></td>
                        <td style="padding:8px; border:1px solid #000; text-align:center; font-weight:800;"><?php echo number_format($sumOp ?? 0); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Stock Table (On Screen) -->
        <div class="pos-card" style="margin-bottom:28px; overflow:hidden;">
            <div style="padding:20px 24px; border-bottom:1px solid var(--pos-border); display:flex; align-items:center; justify-content:space-between;">
                <h3 class="pos-card-title" style="margin:0;"><i class="fas fa-warehouse" style="color:var(--pos-primary);margin-right:8px;"></i>Current Stock Status</h3>
                <input type="text" id="stockSearch" placeholder="Search product..." oninput="filterStockTable()" style="padding:8px 14px; border-radius:10px; border:1.5px solid var(--pos-border); font-size:13px; font-weight:600; outline:none; width:220px;" class="no-print">
            </div>
            <div class="pos-table-container">
                <table class="pos-table" id="stockTable">
                    <thead>
                        <tr>
                            <th style="width:48px;">ល.រ</th>
                            <th>ឈ្មោះផលិតផល / Product</th>
                            <th style="text-align:center;">ចំនួនដើម / Opening</th>
                            <th style="text-align:center;">ចំនួនបានប្រើ / Used</th>
                            <th style="text-align:center;">ចំនួនសល់ / Current</th>
                            <th style="text-align:center;" class="no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding:80px; color:var(--pos-text-muted);">
                                <i class="fas fa-box-open" style="font-size:40px; opacity:0.2; display:block; margin-bottom:12px;"></i>
                                No products found. Add products first.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php $rowNum = 1; foreach ($products as $p):
                            $stock   = (int)($p['display_stock'] ?? $p['store_stock_qty'] ?? $p['stock_quantity'] ?? 0);
                            $used    = (int)($p['qty_sold'] ?? 0);
                            $opening = $stock + $used;
                            $badgeClass = $stock > 10 ? 'in-stock' : ($stock > 0 ? 'low-stock' : 'no-stock');
                        ?>
                        <tr class="stock-row">
                            <td style="font-weight:700; color:var(--pos-text-muted);"><?php echo $rowNum++; ?></td>
                            <td>
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <?php if (!empty($p['image'])): ?>
                                        <img src="<?php echo htmlspecialchars(mc_url('uploads/products/' . $p['image'])); ?>" style="width:36px;height:36px;border-radius:8px;object-fit:cover;border:1px solid var(--pos-border);">
                                    <?php else: ?>
                                        <div style="width:36px;height:36px;border-radius:8px;background:#f1f5f9;border:1px solid var(--pos-border);display:grid;place-items:center;color:#cbd5e1;font-size:14px;"><i class="fas fa-image"></i></div>
                                    <?php endif; ?>
                                    <div>
                                        <div style="font-weight:800; font-size:13px; color:var(--pos-text);"><?php echo htmlspecialchars($p['name']); ?></div>
                                        <?php if (!empty($p['category_name'])): ?>
                                        <div style="font-size:11px; color:var(--pos-text-muted); font-weight:600;"><?php echo htmlspecialchars($p['category_name']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td style="text-align:center; font-weight:700; color:var(--pos-text);">
                                <?php echo number_format($opening); ?>
                            </td>
                            <td style="text-align:center;">
                                <span style="display:inline-block; background:rgba(244,63,94,0.08); color:var(--pos-danger); padding:2px 10px; border-radius:8px; font-weight:800; font-size:13px;">
                                    <?php echo number_format($used); ?>
                                </span>
                            </td>
                            <td style="text-align:center;">
                                <span class="stock-badge <?php echo $badgeClass; ?>" id="stock-qty-<?php echo $p['id']; ?>">
                                    <?php if ($stock > 0): ?>
                                        <i class="fas fa-check-circle"></i>
                                    <?php else: ?>
                                        <i class="fas fa-times-circle"></i>
                                    <?php endif; ?>
                                    <?php echo number_format($stock); ?>
                                </span>
                            </td>
                            <td style="text-align:center;" class="no-print">
                                <div style="display:flex; justify-content:center; gap:6px; flex-wrap:wrap;">
                                    <button type="button" class="btn btn-sm" onclick="openStockModal(<?php echo $p['id']; ?>, '<?php echo htmlspecialchars(addslashes($p['name'])); ?>', <?php echo $stock; ?>, 'in')"
                                        style="background:rgba(16,185,129,0.1); color:#10b981; border:1px solid rgba(16,185,129,0.3); padding:6px 12px; border-radius:8px; font-size:12px; font-weight:800; cursor:pointer;">
                                        <i class="fas fa-arrow-up"></i> In
                                    </button>
                                    <button type="button" class="btn btn-sm" onclick="openStockModal(<?php echo $p['id']; ?>, '<?php echo htmlspecialchars(addslashes($p['name'])); ?>', <?php echo $stock; ?>, 'out')"
                                        style="background:rgba(239,68,68,0.1); color:#ef4444; border:1px solid rgba(239,68,68,0.3); padding:6px 12px; border-radius:8px; font-size:12px; font-weight:800; cursor:pointer;">
                                        <i class="fas fa-arrow-down"></i> Out
                                    </button>
                                    <?php if (!empty($allStores) && count($allStores) > 1): ?>
                                    <a href="<?php echo htmlspecialchars($posUrl('stock-transfer') . '?product_id=' . $p['id']); ?>"
                                        style="background:rgba(99,102,241,0.1); color:var(--pos-primary); border:1px solid rgba(99,102,241,0.3); padding:6px 12px; border-radius:8px; font-size:12px; font-weight:800; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:4px;">
                                        <i class="fas fa-arrows-left-right"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Stock Movements -->
        <?php if (!empty($stockLogs)): ?>
        <div class="pos-card" style="margin-bottom:28px; overflow:hidden;">
            <div style="padding:20px 24px; border-bottom:1px solid var(--pos-border);">
                <h3 class="pos-card-title" style="margin:0;"><i class="fas fa-history" style="color:var(--pos-primary);margin-right:8px;"></i>Recent Movements (Last 80)</h3>
            </div>
            <div class="pos-table-container">
                <table class="pos-table">
                    <thead>
                        <tr>
                            <th>ផលិតផល / Product</th>
                            <th>Store</th>
                            <th style="text-align:center;">ប្រភេទ / Type</th>
                            <th style="text-align:center;">ចំនួន / Qty</th>
                            <th style="text-align:center;">មូលហេតុ / Reason</th>
                            <th>កាលបរិច្ឆេទ / Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stockLogs as $log):
                            $isIn = $log['change_quantity'] > 0;
                            $reason = $log['reason'] ?? '';
                            $isTransferIn  = ($reason === 'transfer_in');
                            $isTransferOut = ($reason === 'transfer_out');
                        ?>
                        <tr>
                            <td style="font-weight:700;"><?php echo htmlspecialchars($log['product_name'] ?? 'N/A'); ?></td>
                            <td>
                                <?php if (!empty($log['store_name'])): ?>
                                <span class="store-badge"><?php echo htmlspecialchars($log['store_name']); ?></span>
                                <?php else: ?>
                                <span style="color:var(--pos-text-muted); font-size:12px;">—</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:center;">
                                <?php if ($isTransferIn): ?>
                                    <span class="stock-badge in-stock"><i class="fas fa-arrows-left-right"></i> Transfer In</span>
                                <?php elseif ($isTransferOut): ?>
                                    <span class="stock-badge low-stock"><i class="fas fa-arrows-left-right"></i> Transfer Out</span>
                                <?php elseif ($isIn): ?>
                                    <span class="stock-badge in-stock"><i class="fas fa-arrow-up"></i> Stock In</span>
                                <?php else: ?>
                                    <span class="stock-badge no-stock"><i class="fas fa-arrow-down"></i> Stock Out</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:center;" class="<?php echo $isIn ? 'log-direction-in' : 'log-direction-out'; ?>">
                                <?php echo ($isIn ? '+' : '') . number_format($log['change_quantity']); ?>
                            </td>
                            <td style="text-align:center;">
                                <span style="font-size:12px; font-weight:600; color:var(--pos-text-muted);"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $reason))); ?></span>
                            </td>
                            <td style="font-size:12px; color:var(--pos-text-muted); font-weight:600;">
                                <?php echo date('d/m/Y H:i', strtotime($log['created_at'])); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- Stock Adjust Modal -->
    <div class="stock-adjust-modal" id="stockAdjustModal">
        <div class="stock-adjust-box">
            <div class="stock-adjust-header">
                <h3><i class="fas fa-boxes-stacked" style="color:var(--pos-primary);margin-right:8px;"></i>Adjust Stock</h3>
                <button type="button" onclick="closeStockModal()" style="width:32px;height:32px;border-radius:50%;border:1px solid var(--pos-border);background:#fff;cursor:pointer;font-size:18px;color:var(--pos-text-muted);display:flex;align-items:center;justify-content:center;">&times;</button>
            </div>
            <div class="stock-adjust-body">
                <input type="hidden" id="modal_product_id" value="">

                <!-- Product name display -->
                <div style="background:rgba(99,102,241,0.05); border:1px solid rgba(99,102,241,0.15); border-radius:12px; padding:12px 16px; margin-bottom:16px;">
                    <div style="font-size:11px; font-weight:700; color:var(--pos-text-muted); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px;">Product</div>
                    <div id="modal_product_name" style="font-size:15px; font-weight:900; color:var(--pos-text);">—</div>
                    <div id="modal_current_stock_line" style="font-size:12px; color:var(--pos-text-muted); font-weight:600; margin-top:2px;">Current: — units</div>
                </div>

                <!-- Movement Type -->
                <div style="margin-bottom:16px;">
                    <div style="font-size:12px; font-weight:800; color:var(--pos-text); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:10px;">Movement Type</div>
                    <div style="display:flex; gap:10px;">
                        <button type="button" class="movement-pill active-in" id="pill_in" onclick="setMovementType('in')">
                            <i class="fas fa-arrow-up"></i> Stock In
                        </button>
                        <button type="button" class="movement-pill" id="pill_out" onclick="setMovementType('out')">
                            <i class="fas fa-arrow-down"></i> Stock Out
                        </button>
                    </div>
                    <input type="hidden" id="modal_movement_type" value="in">
                </div>

                <!-- Quantity -->
                <div style="margin-bottom:16px;">
                    <label style="display:block; font-size:12px; font-weight:800; color:var(--pos-text); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px;">Quantity</label>
                    <input type="number" id="modal_quantity" min="1" value="1" class="pos-form-control" style="max-width:180px; font-size:20px; font-weight:900; text-align:center;" placeholder="0">
                </div>

                <div id="modal_result_msg" style="font-size:12px; font-weight:700; min-height:20px;"></div>
            </div>
            <div class="stock-adjust-footer">
                <button type="button" onclick="closeStockModal()" style="padding:10px 20px; border-radius:10px; border:1px solid var(--pos-border); background:#fff; font-weight:700; cursor:pointer; color:var(--pos-text);">Cancel</button>
                <button type="button" onclick="submitStockAdjust()" id="modal_submit_btn" style="padding:10px 24px; border-radius:10px; background:var(--pos-primary); color:#fff; border:none; font-weight:800; cursor:pointer;">
                    <i class="fas fa-check"></i> Confirm
                </button>
            </div>
        </div>
    </div>

    <!-- ──────────────────────────────────────────────────────────────────── -->
    <!-- Transfer Stock Modal                                                  -->
    <!-- ──────────────────────────────────────────────────────────────────── -->
    <?php if (!empty($allStores) && count($allStores) > 1): ?>
    <div class="transfer-modal" id="transferModal">
        <div class="transfer-box">
            <div class="transfer-header">
                <h3><i class="fas fa-arrows-left-right" style="color:var(--pos-primary);margin-right:8px;"></i>Transfer Stock Between Stores</h3>
                <button type="button" onclick="closeTransferModal()" style="width:32px;height:32px;border-radius:50%;border:1px solid var(--pos-border);background:#fff;cursor:pointer;font-size:18px;color:var(--pos-text-muted);display:flex;align-items:center;justify-content:center;">&times;</button>
            </div>
            <div class="transfer-body">
                <input type="hidden" id="tr_product_id" value="">

                <!-- Product Info -->
                <div style="background:rgba(99,102,241,0.05); border:1px solid rgba(99,102,241,0.15); border-radius:12px; padding:12px 16px;">
                    <div style="font-size:11px; font-weight:700; color:var(--pos-text-muted); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px;">Product</div>
                    <div id="tr_product_name" style="font-size:15px; font-weight:900; color:var(--pos-text);">—</div>
                    <div id="tr_avail_line" style="font-size:12px; color:var(--pos-text-muted); font-weight:600; margin-top:2px;">Available in source: —</div>
                </div>

                <!-- From Store -->
                <div>
                    <label style="display:block; font-size:12px; font-weight:800; color:var(--pos-text); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px;">From Store (Source)</label>
                    <select id="tr_from_store" class="pos-form-control" onchange="onTransferStoreChange()" style="font-weight:700;">
                        <?php foreach ($allStores as $s): ?>
                        <option value="<?php echo $s['id']; ?>" <?php echo ($currentStore && $s['id'] == $currentStore['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars(($s['code'] ?? '') . ' — ' . $s['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Arrow -->
                <div style="text-align:center; color:var(--pos-primary); font-size:20px;">
                    <i class="fas fa-arrow-down"></i>
                </div>

                <!-- To Store -->
                <div>
                    <label style="display:block; font-size:12px; font-weight:800; color:var(--pos-text); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px;">To Store (Destination)</label>
                    <select id="tr_to_store" class="pos-form-control" style="font-weight:700;">
                        <?php foreach ($allStores as $s): ?>
                        <option value="<?php echo $s['id']; ?>" <?php echo ($currentStore && $s['id'] != $currentStore['id']) ? '' : ''; ?>>
                            <?php echo htmlspecialchars(($s['code'] ?? '') . ' — ' . $s['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Quantity -->
                <div>
                    <label style="display:block; font-size:12px; font-weight:800; color:var(--pos-text); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px;">Quantity to Transfer</label>
                    <input type="number" id="tr_quantity" min="1" value="1" class="pos-form-control" style="max-width:180px; font-size:20px; font-weight:900; text-align:center;" placeholder="0">
                </div>

                <!-- Note -->
                <div>
                    <label style="display:block; font-size:12px; font-weight:800; color:var(--pos-text); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px;">Note (optional)</label>
                    <input type="text" id="tr_note" class="pos-form-control" placeholder="e.g. Weekly resupply" style="font-weight:600;">
                </div>

                <div id="tr_result_msg" style="font-size:13px; font-weight:700; min-height:20px;"></div>
            </div>
            <div class="transfer-footer">
                <button type="button" onclick="closeTransferModal()" style="padding:10px 20px; border-radius:10px; border:1px solid var(--pos-border); background:#fff; font-weight:700; cursor:pointer; color:var(--pos-text);">Cancel</button>
                <button type="button" onclick="submitTransfer()" id="tr_submit_btn"
                    style="padding:10px 24px; border-radius:10px; background:var(--pos-primary); color:#fff; border:none; font-weight:800; cursor:pointer;">
                    <i class="fas fa-arrows-left-right"></i> Transfer
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>
        const API_URL = '<?php echo htmlspecialchars($posUrl('stock-report')); ?>';
        const CURRENT_STORE_ID = <?php echo json_encode($currentStoreId ?? null); ?>;

        // ── Stock Adjust Modal ────────────────────────────────────────────────
        function openStockModal(productId, productName, currentStock, defaultType) {
            document.getElementById('modal_product_id').value = productId || '';
            document.getElementById('modal_product_name').textContent = productName || '— Select product above —';
            document.getElementById('modal_current_stock_line').textContent = currentStock !== undefined ? 'Current: ' + currentStock + ' units' : '';
            document.getElementById('modal_quantity').value = 1;
            document.getElementById('modal_result_msg').innerHTML = '';
            setMovementType(defaultType || 'in');
            document.getElementById('stockAdjustModal').classList.add('open');
            setTimeout(() => document.getElementById('modal_quantity').focus(), 100);
        }

        function closeStockModal() {
            document.getElementById('stockAdjustModal').classList.remove('open');
        }

        function setMovementType(type) {
            document.getElementById('modal_movement_type').value = type;
            const pillIn  = document.getElementById('pill_in');
            const pillOut = document.getElementById('pill_out');
            pillIn.className  = 'movement-pill' + (type === 'in'  ? ' active-in'  : '');
            pillOut.className = 'movement-pill' + (type === 'out' ? ' active-out' : '');
        }

        function submitStockAdjust() {
            const productId = document.getElementById('modal_product_id').value;
            const qty       = parseInt(document.getElementById('modal_quantity').value, 10);
            const type      = document.getElementById('modal_movement_type').value;
            const msgEl     = document.getElementById('modal_result_msg');

            if (!productId) { msgEl.innerHTML = '<span style="color:#ef4444;">Please select a product.</span>'; return; }
            if (!qty || qty <= 0) { msgEl.innerHTML = '<span style="color:#ef4444;">Quantity must be at least 1.</span>'; return; }

            const btn = document.getElementById('modal_submit_btn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            msgEl.innerHTML = '';

            const formData = new FormData();
            formData.append('ajax_stock_adjust', '1');
            formData.append('product_id', productId);
            formData.append('quantity', qty);
            formData.append('movement_type', type);
            if (CURRENT_STORE_ID) formData.append('store_id', CURRENT_STORE_ID);

            fetch(API_URL, { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        msgEl.innerHTML = '<span style="color:#10b981;">✅ Stock updated! New qty: <strong>' + data.new_stock + '</strong></span>';
                        updateStockBadge(productId, data.new_stock);
                        document.getElementById('modal_current_stock_line').textContent = 'Current: ' + data.new_stock + ' units';
                        setTimeout(() => { closeStockModal(); location.reload(); }, 1200);
                    } else {
                        msgEl.innerHTML = '<span style="color:#ef4444;">❌ ' + (data.error || 'Error') + '</span>';
                    }
                })
                .catch(() => {
                    msgEl.innerHTML = '<span style="color:#ef4444;">❌ Network error.</span>';
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check"></i> Confirm';
                });
        }

        function updateStockBadge(productId, newQty) {
            const badge = document.getElementById('stock-qty-' + productId);
            if (!badge) return;
            const icon = newQty > 0 ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-times-circle"></i>';
            badge.innerHTML = icon + ' ' + newQty;
            badge.className = 'stock-badge ' + (newQty > 10 ? 'in-stock' : (newQty > 0 ? 'low-stock' : 'no-stock'));
        }

        // Close modal on backdrop click
        document.getElementById('stockAdjustModal').addEventListener('click', function(e) {
            if (e.target === this) closeStockModal();
        });

        // ── Transfer Modal ────────────────────────────────────────────────────
        function openTransferModal(productId, productName, currentStock) {
            const modal = document.getElementById('transferModal');
            if (!modal) return;
            document.getElementById('tr_product_id').value = productId || '';
            document.getElementById('tr_product_name').textContent = productName || '—';
            document.getElementById('tr_avail_line').textContent = 'Available in source: ' + (currentStock ?? '—') + ' units';
            document.getElementById('tr_quantity').value = 1;
            document.getElementById('tr_note').value = '';
            document.getElementById('tr_result_msg').innerHTML = '';
            // Reset from/to to avoid same store selected
            onTransferStoreChange();
            modal.classList.add('open');
            setTimeout(() => document.getElementById('tr_quantity').focus(), 100);
        }

        function closeTransferModal() {
            const modal = document.getElementById('transferModal');
            if (modal) modal.classList.remove('open');
        }

        function onTransferStoreChange() {
            const fromSel = document.getElementById('tr_from_store');
            const toSel   = document.getElementById('tr_to_store');
            if (!fromSel || !toSel) return;
            const fromVal = fromSel.value;
            // Auto-select first different store for "to"
            for (let opt of toSel.options) {
                if (opt.value !== fromVal) {
                    toSel.value = opt.value;
                    break;
                }
            }
        }

        function submitTransfer() {
            const productId   = document.getElementById('tr_product_id').value;
            const fromStoreId = document.getElementById('tr_from_store')?.value;
            const toStoreId   = document.getElementById('tr_to_store')?.value;
            const qty         = parseInt(document.getElementById('tr_quantity').value, 10);
            const note        = document.getElementById('tr_note').value;
            const msgEl       = document.getElementById('tr_result_msg');

            if (!productId)   { msgEl.innerHTML = '<span style="color:#ef4444;">No product selected.</span>'; return; }
            if (!fromStoreId || !toStoreId) { msgEl.innerHTML = '<span style="color:#ef4444;">Select both stores.</span>'; return; }
            if (fromStoreId === toStoreId)  { msgEl.innerHTML = '<span style="color:#ef4444;">Source and destination must be different.</span>'; return; }
            if (!qty || qty <= 0) { msgEl.innerHTML = '<span style="color:#ef4444;">Quantity must be at least 1.</span>'; return; }

            const btn = document.getElementById('tr_submit_btn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Transferring...';
            msgEl.innerHTML = '';

            const formData = new FormData();
            formData.append('ajax_stock_transfer', '1');
            formData.append('product_id',   productId);
            formData.append('from_store_id', fromStoreId);
            formData.append('to_store_id',   toStoreId);
            formData.append('quantity',      qty);
            formData.append('note',          note);

            fetch(API_URL, { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        msgEl.innerHTML = '<span style="color:#10b981;">✅ Transferred ' + data.qty + ' units successfully!</span>';
                        // Update source badge if visible
                        if (data.from_store_id == CURRENT_STORE_ID) {
                            updateStockBadge(productId, data.new_from);
                            document.getElementById('tr_avail_line').textContent = 'Available in source: ' + data.new_from + ' units';
                        }
                        setTimeout(() => { closeTransferModal(); location.reload(); }, 1400);
                    } else {
                        msgEl.innerHTML = '<span style="color:#ef4444;">❌ ' + (data.error || 'Transfer failed') + '</span>';
                    }
                })
                .catch(() => {
                    msgEl.innerHTML = '<span style="color:#ef4444;">❌ Network error.</span>';
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-arrows-left-right"></i> Transfer';
                });
        }

        // Close modals on backdrop click / Escape
        document.addEventListener('click', function(e) {
            if (e.target && e.target.id === 'transferModal') closeTransferModal();
        });
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') { closeStockModal(); closeTransferModal(); }
        });

        // Table filter
        function filterStockTable() {
            const q = document.getElementById('stockSearch').value.toUpperCase();
            document.querySelectorAll('.stock-row').forEach(row => {
                row.style.display = row.innerText.toUpperCase().includes(q) ? '' : 'none';
            });
        }

        // Print
        window.addEventListener('beforeprint', () => {
            const h = document.getElementById('printHeader');
            if (h) h.style.display = 'block';
        });
        window.addEventListener('afterprint', () => {
            const h = document.getElementById('printHeader');
            if (h) h.style.display = 'none';
        });
    </script>

    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

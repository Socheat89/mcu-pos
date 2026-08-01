<?php
require_once __DIR__ . '/../../../core/helpers/url.php';
require_once __DIR__ . '/../../../core/classes/Settings.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Transfer — <?php echo htmlspecialchars($tenantName ?? 'POS'); ?></title>

    <link href="<?php echo mc_base_path(); ?>/public/css/pos_template.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700;800&family=Battambang:wght@300;400;700;900&display=swap" rel="stylesheet">

    <style>
        body, h1, h2, h3, h4, h5, h6, p, span, a, button, input, select, textarea, td, th {
            font-family: 'Space Grotesk', 'Battambang', sans-serif !important;
        }

        /* ── Status Banner ─────────────────────────────────────────────────── */
        .transfer-status {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 800;
            letter-spacing: 0.3px; text-transform: uppercase;
        }
        .status-draft    { background: rgba(148,163,184,0.15); color: #64748b; border: 1px solid rgba(148,163,184,0.3); }
        .status-done     { background: rgba(16,185,129,0.1);   color: #10b981; border: 1px solid rgba(16,185,129,0.25); }
        .status-waiting  { background: rgba(245,158,11,0.1);   color: #f59e0b; border: 1px solid rgba(245,158,11,0.25); }

        /* ── Transfer Form Header ──────────────────────────────────────────── */
        .transfer-form-card {
            background: #fff; border-radius: 20px; border: 1.5px solid var(--pos-border);
            box-shadow: 0 2px 16px rgba(0,0,0,0.06); overflow: hidden; margin-bottom: 24px;
        }
        .transfer-form-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 20px 28px; border-bottom: 1.5px solid var(--pos-border);
            background: linear-gradient(135deg, rgba(99,102,241,0.04), rgba(168,85,247,0.03));
        }
        .transfer-form-header h2 {
            margin: 0; font-size: 20px; font-weight: 900; color: var(--pos-text);
            display: flex; align-items: center; gap: 10px;
        }
        .transfer-form-body { padding: 28px; }

        /* ── Form Fields Grid ──────────────────────────────────────────────── */
        .tf-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;
        }
        .tf-grid-3 {
            display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 24px;
        }
        @media (max-width: 768px) {
            .tf-grid, .tf-grid-3 { grid-template-columns: 1fr; }
        }
        .tf-field label {
            display: block; font-size: 11px; font-weight: 800; color: var(--pos-text-muted);
            text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 7px;
        }
        .tf-field select, .tf-field input[type="text"], .tf-field input[type="date"], .tf-field textarea {
            width: 100%; padding: 10px 14px; border: 1.5px solid var(--pos-border);
            border-radius: 10px; font-size: 14px; font-weight: 600; color: var(--pos-text);
            background: #fff; outline: none; transition: border-color 0.2s, box-shadow 0.2s;
            box-sizing: border-box;
        }
        .tf-field select:focus, .tf-field input:focus, .tf-field textarea:focus {
            border-color: var(--pos-primary); box-shadow: 0 0 0 3px rgba(99,102,241,0.12);
        }
        .tf-field textarea { resize: vertical; min-height: 60px; }

        /* ── Store Selector Cards ──────────────────────────────────────────── */
        .store-flow {
            display: flex; align-items: center; gap: 16px; margin-bottom: 28px;
            background: rgba(99,102,241,0.04); border: 1.5px solid rgba(99,102,241,0.14);
            border-radius: 16px; padding: 20px 24px;
        }
        .store-flow-item { flex: 1; }
        .store-flow-arrow {
            display: flex; flex-direction: column; align-items: center; gap: 4px;
            color: var(--pos-primary); font-size: 24px; flex-shrink: 0;
        }
        .store-flow-arrow span { font-size: 10px; font-weight: 700; color: var(--pos-text-muted); text-transform: uppercase; }

        /* ── Lines Table ───────────────────────────────────────────────────── */
        .lines-card {
            background: #fff; border-radius: 20px; border: 1.5px solid var(--pos-border);
            box-shadow: 0 2px 16px rgba(0,0,0,0.06); overflow: hidden; margin-bottom: 24px;
        }
        .lines-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 16px 24px; border-bottom: 1.5px solid var(--pos-border);
            background: #fafafa;
        }
        .lines-header h3 { margin: 0; font-size: 14px; font-weight: 800; color: var(--pos-text); }

        .lines-table { width: 100%; border-collapse: collapse; }
        .lines-table thead tr { background: #f8fafc; }
        .lines-table th {
            padding: 10px 16px; text-align: left; font-size: 11px; font-weight: 800;
            color: var(--pos-text-muted); text-transform: uppercase; letter-spacing: 0.5px;
            border-bottom: 1.5px solid var(--pos-border);
        }
        .lines-table th.right { text-align: right; }
        .lines-table th.center { text-align: center; }
        .lines-table td {
            padding: 12px 16px; border-bottom: 1px solid #f1f5f9; vertical-align: middle;
        }
        .lines-table tbody tr:last-child td { border-bottom: none; }
        .lines-table tbody tr:hover { background: rgba(99,102,241,0.02); }

        /* ── Product Search Input in table ─────────────────────────────────── */
        .product-search-wrap { position: relative; min-width: 200px; }
        .product-search-input {
            width: 100%; padding: 8px 12px 8px 36px; border: 1.5px solid var(--pos-border);
            border-radius: 10px; font-size: 13px; font-weight: 600; color: var(--pos-text);
            outline: none; transition: border-color 0.2s; box-sizing: border-box;
        }
        .product-search-input:focus { border-color: var(--pos-primary); }
        .product-search-icon {
            position: absolute; left: 11px; top: 50%; transform: translateY(-50%);
            color: var(--pos-text-muted); font-size: 12px; pointer-events: none;
        }
        .product-dropdown {
            position: absolute; top: calc(100% + 4px); left: 0; right: 0; z-index: 999;
            background: #fff; border: 1.5px solid var(--pos-border); border-radius: 12px;
            max-height: 240px; overflow-y: auto;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12); display: none;
        }
        .product-dropdown.open { display: block; }
        .product-dropdown-item {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 14px; cursor: pointer; border-bottom: 1px solid #f1f5f9;
            transition: background 0.15s;
        }
        .product-dropdown-item:last-child { border-bottom: none; }
        .product-dropdown-item:hover { background: rgba(99,102,241,0.06); }
        .product-dropdown-item .item-img {
            width: 32px; height: 32px; border-radius: 8px; object-fit: cover;
            border: 1px solid var(--pos-border); flex-shrink: 0; background: #f1f5f9;
            display: flex; align-items: center; justify-content: center; color: #cbd5e1; font-size: 12px;
        }
        .product-dropdown-item .item-info { flex: 1; min-width: 0; }
        .product-dropdown-item .item-name { font-size: 13px; font-weight: 800; color: var(--pos-text); }
        .product-dropdown-item .item-meta { font-size: 11px; color: var(--pos-text-muted); font-weight: 600; }
        .product-dropdown-item .item-stock {
            font-size: 12px; font-weight: 800; padding: 2px 8px; border-radius: 8px; flex-shrink: 0;
        }
        .stock-ok  { background: rgba(16,185,129,0.1); color: #10b981; }
        .stock-low { background: rgba(245,158,11,0.1); color: #f59e0b; }
        .stock-nil { background: rgba(239,68,68,0.1);  color: #ef4444; }

        /* ── Qty Input in table ─────────────────────────────────────────────── */
        .qty-input {
            width: 80px; padding: 7px 10px; border: 1.5px solid var(--pos-border);
            border-radius: 8px; font-size: 14px; font-weight: 800; text-align: center;
            outline: none; transition: border-color 0.2s;
        }
        .qty-input:focus { border-color: var(--pos-primary); }

        /* ── Available Badge ───────────────────────────────────────────────── */
        .avail-badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 4px 10px; border-radius: 8px; font-size: 12px; font-weight: 800;
        }

        /* ── Add Line Button ───────────────────────────────────────────────── */
        .add-line-btn {
            display: flex; align-items: center; gap: 8px;
            padding: 10px 20px; border-radius: 10px; border: 1.5px dashed var(--pos-border);
            background: transparent; color: var(--pos-text-muted); font-size: 13px; font-weight: 700;
            cursor: pointer; transition: all 0.2s; width: 100%; justify-content: center; margin-top: 8px;
        }
        .add-line-btn:hover {
            border-color: var(--pos-primary); color: var(--pos-primary);
            background: rgba(99,102,241,0.04);
        }

        /* ── Action Buttons ────────────────────────────────────────────────── */
        .action-bar {
            display: flex; gap: 12px; align-items: center; flex-wrap: wrap;
        }
        .btn-validate {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 12px 28px; border-radius: 12px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff; font-size: 15px; font-weight: 900; border: none; cursor: pointer;
            box-shadow: 0 4px 16px rgba(16,185,129,0.3); transition: all 0.2s;
        }
        .btn-validate:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(16,185,129,0.4); }
        .btn-validate:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
        .btn-discard {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 12px 24px; border-radius: 12px; border: 1.5px solid var(--pos-border);
            background: #fff; color: var(--pos-text); font-size: 14px; font-weight: 700; cursor: pointer;
            transition: all 0.2s;
        }
        .btn-discard:hover { background: #f8fafc; border-color: #cbd5e1; }

        /* ── Success / Error Toast ─────────────────────────────────────────── */
        .transfer-toast {
            position: fixed; bottom: 30px; right: 30px; z-index: 9999;
            padding: 14px 22px; border-radius: 14px; font-size: 14px; font-weight: 800;
            display: none; align-items: center; gap: 10px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.18); animation: slideUp 0.3s ease-out;
        }
        .transfer-toast.success { background: #10b981; color: #fff; }
        .transfer-toast.error   { background: #ef4444; color: #fff; }
        .transfer-toast.show    { display: flex; }
        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to   { transform: translateY(0);    opacity: 1; }
        }

        /* ── History Table ─────────────────────────────────────────────────── */
        .history-pill {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 800;
        }
        .pill-in  { background: rgba(16,185,129,0.1); color: #10b981; }
        .pill-out { background: rgba(245,158,11,0.1); color: #f59e0b; }

        /* ── Success card ──────────────────────────────────────────────────── */
        .success-overlay {
            display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.55);
            backdrop-filter: blur(6px); z-index: 9999; align-items: center; justify-content: center;
        }
        .success-overlay.open { display: flex; }
        .success-card {
            background: #fff; border-radius: 24px; padding: 40px; text-align: center;
            max-width: 440px; width: 90%; box-shadow: 0 40px 80px rgba(0,0,0,0.25);
            animation: scaleIn 0.3s ease-out;
        }
        @keyframes scaleIn {
            from { transform: scale(0.9); opacity: 0; }
            to   { transform: scale(1); opacity: 1; }
        }
        .success-icon {
            width: 72px; height: 72px; border-radius: 50%; background: rgba(16,185,129,0.12);
            border: 3px solid #10b981; display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px; font-size: 32px; color: #10b981;
        }
        .reference-code {
            display: inline-block; background: #f8fafc; border: 1.5px solid var(--pos-border);
            padding: 8px 20px; border-radius: 10px; font-size: 18px; font-weight: 900;
            color: var(--pos-primary); letter-spacing: 1px; margin: 12px 0 20px;
        }
    </style>
</head>
<body class="pos-app">
    <?php $activeNav = 'stock_report'; include __DIR__ . '/partials/navbar.php'; ?>

    <div class="fade-in">

        <!-- ── Page Header ────────────────────────────────────────────────── -->
        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:24px;">
            <div>
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:6px;">
                    <a href="<?php echo htmlspecialchars($posUrl('stock-report')); ?>" style="color:var(--pos-text-muted); font-size:13px; font-weight:700; text-decoration:none; display:flex; align-items:center; gap:5px;">
                        <i class="fas fa-arrow-left"></i> Stock Report
                    </a>
                    <span style="color:var(--pos-text-muted); font-size:13px;">/</span>
                    <span style="font-size:13px; font-weight:700; color:var(--pos-text);">Internal Transfer</span>
                </div>
                <h1 class="pos-title" style="margin:0; font-size:26px; display:flex; align-items:center; gap:10px;">
                    <i class="fas fa-arrows-left-right" style="color:var(--pos-primary);"></i>
                    Stock Transfer
                </h1>
                <p style="margin:4px 0 0; color:var(--pos-text-muted); font-size:14px; font-weight:600;">
                    Transfer stock between stores — ការផ្ទេរស្តុករវាង store
                </p>
            </div>
            <div class="action-bar" id="topActionBar">
                <button class="btn-discard" onclick="discardTransfer()">
                    <i class="fas fa-rotate-left"></i> Discard
                </button>
                <button class="btn-validate" id="validateBtn" onclick="validateTransfer()">
                    <i class="fas fa-check-double"></i> Validate Transfer
                </button>
            </div>
        </div>

        <!-- ── Transfer Form Card ─────────────────────────────────────────── -->
        <div class="transfer-form-card">
            <div class="transfer-form-header">
                <h2>
                    <i class="fas fa-file-lines" style="color:var(--pos-primary); font-size:18px;"></i>
                    New Internal Transfer
                </h2>
                <span class="transfer-status status-draft" id="statusBadge">
                    <i class="fas fa-circle" style="font-size:8px;"></i> Draft
                </span>
            </div>
            <div class="transfer-form-body">

                <!-- Store Flow -->
                <div class="store-flow">
                    <div class="store-flow-item">
                        <label style="font-size:11px; font-weight:800; color:var(--pos-text-muted); text-transform:uppercase; letter-spacing:0.5px; display:block; margin-bottom:8px;">
                            <i class="fas fa-warehouse" style="margin-right:4px;"></i> From (Source)
                        </label>
                        <select id="fromStoreSelect" class="tf-field" style="width:100%; padding:10px 14px; border:1.5px solid var(--pos-border); border-radius:10px; font-size:14px; font-weight:700; color:var(--pos-text); outline:none; background:#fff; cursor:pointer;"
                            onchange="onFromStoreChange()">
                            <?php foreach ($allStores as $s): ?>
                            <option value="<?php echo $s['id']; ?>" <?php echo ($currentStore && $s['id'] == $currentStore['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(($s['code'] ? '[' . $s['code'] . '] ' : '') . $s['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="store-flow-arrow">
                        <i class="fas fa-arrow-right"></i>
                        <span>Transfer</span>
                    </div>
                    <div class="store-flow-item">
                        <label style="font-size:11px; font-weight:800; color:var(--pos-text-muted); text-transform:uppercase; letter-spacing:0.5px; display:block; margin-bottom:8px;">
                            <i class="fas fa-store" style="margin-right:4px;"></i> To (Destination)
                        </label>
                        <select id="toStoreSelect" style="width:100%; padding:10px 14px; border:1.5px solid var(--pos-border); border-radius:10px; font-size:14px; font-weight:700; color:var(--pos-text); outline:none; background:#fff; cursor:pointer;"
                            onchange="onToStoreChange()">
                            <?php foreach ($allStores as $s): ?>
                            <option value="<?php echo $s['id']; ?>" <?php echo ($currentStore && $s['id'] != $currentStore['id']) ? '' : ''; ?>>
                                <?php echo htmlspecialchars(($s['code'] ? '[' . $s['code'] . '] ' : '') . $s['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Ref / Date / Note row -->
                <div class="tf-grid-3">
                    <div class="tf-field">
                        <label>Reference</label>
                        <input type="text" id="refInput" placeholder="Auto-generated (e.g. TRF/20260801/A3F2)"
                            style="font-family:monospace; letter-spacing:1px;">
                    </div>
                    <div class="tf-field">
                        <label>Scheduled Date</label>
                        <input type="date" id="dateInput" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="tf-field">
                        <label>Note / Remarks</label>
                        <input type="text" id="noteInput" placeholder="e.g. Weekly branch resupply">
                    </div>
                </div>

            </div>
        </div>

        <!-- ── Transfer Lines ─────────────────────────────────────────────── -->
        <div class="lines-card">
            <div class="lines-header">
                <h3><i class="fas fa-list" style="color:var(--pos-primary); margin-right:6px;"></i>Transfer Lines</h3>
                <span id="lineCountBadge" style="font-size:12px; color:var(--pos-text-muted); font-weight:700;">0 line(s)</span>
            </div>

            <div style="padding: 0 0 8px;">
                <table class="lines-table" id="linesTable">
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>Product</th>
                            <th class="center" style="width:130px;">Available (From)</th>
                            <th class="center" style="width:110px;">Qty to Transfer</th>
                            <th class="center" style="width:50px;"></th>
                        </tr>
                    </thead>
                    <tbody id="linesBody">
                        <!-- dynamic rows -->
                    </tbody>
                </table>

                <div style="padding: 0 16px 8px;">
                    <button class="add-line-btn" onclick="addLine()">
                        <i class="fas fa-plus"></i> Add a line
                    </button>
                </div>
            </div>
        </div>

        <!-- ── Bottom Action Bar ──────────────────────────────────────────── -->
        <div style="display:flex; justify-content:flex-end; gap:12px; margin-bottom:32px;">
            <button class="btn-discard" onclick="discardTransfer()">
                <i class="fas fa-rotate-left"></i> Discard
            </button>
            <button class="btn-validate" id="validateBtnBottom" onclick="validateTransfer()">
                <i class="fas fa-check-double"></i> Validate Transfer
            </button>
        </div>

        <!-- ── Transfer History ───────────────────────────────────────────── -->
        <?php if (!empty($transferHistory)): ?>
        <div class="transfer-form-card">
            <div class="transfer-form-header">
                <h2 style="font-size:16px;"><i class="fas fa-clock-rotate-left" style="color:var(--pos-primary); font-size:16px;"></i> Transfer History</h2>
                <span style="font-size:12px; color:var(--pos-text-muted); font-weight:700;"><?php echo count($transferHistory); ?> record(s)</span>
            </div>
            <div style="overflow-x:auto;">
                <table class="lines-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Store</th>
                            <th class="center">Type</th>
                            <th class="center">Qty</th>
                            <th>Reference / Note</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transferHistory as $h):
                            $isIn = ($h['reason'] === 'transfer_in');
                        ?>
                        <tr>
                            <td style="font-weight:800; font-size:13px;"><?php echo htmlspecialchars($h['product_name'] ?? 'N/A'); ?></td>
                            <td>
                                <?php if (!empty($h['store_name'])): ?>
                                <span style="background:rgba(99,102,241,0.1); color:var(--pos-primary); padding:3px 10px; border-radius:8px; font-size:12px; font-weight:800;">
                                    <?php echo htmlspecialchars($h['store_name']); ?>
                                </span>
                                <?php else: ?>
                                <span style="color:var(--pos-text-muted);">—</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:center;">
                                <span class="history-pill <?php echo $isIn ? 'pill-in' : 'pill-out'; ?>">
                                    <i class="fas <?php echo $isIn ? 'fa-arrow-down' : 'fa-arrow-up'; ?>"></i>
                                    <?php echo $isIn ? 'Transfer In' : 'Transfer Out'; ?>
                                </span>
                            </td>
                            <td style="text-align:center; font-weight:900; color:<?php echo $isIn ? '#10b981' : '#f59e0b'; ?>;">
                                <?php echo ($isIn ? '+' : '-') . number_format(abs($h['change_quantity'])); ?>
                            </td>
                            <td style="font-size:12px; color:var(--pos-text-muted); font-weight:600; max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                <?php echo htmlspecialchars($h['note'] ?? '—'); ?>
                            </td>
                            <td style="font-size:12px; color:var(--pos-text-muted); font-weight:600; white-space:nowrap;">
                                <?php echo date('d/m/Y H:i', strtotime($h['created_at'])); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

    </div><!-- /.fade-in -->

    <!-- ── Success Overlay ────────────────────────────────────────────────── -->
    <div class="success-overlay" id="successOverlay">
        <div class="success-card">
            <div class="success-icon"><i class="fas fa-check"></i></div>
            <h2 style="margin:0 0 4px; font-size:22px; font-weight:900; color:var(--pos-text);">Transfer Complete!</h2>
            <p style="margin:0; color:var(--pos-text-muted); font-size:14px; font-weight:600;">Stock has been moved successfully</p>
            <div class="reference-code" id="successRef">—</div>
            <p id="successLines" style="margin:0 0 24px; color:var(--pos-text-muted); font-size:13px; font-weight:700;"></p>
            <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
                <button onclick="newTransfer()" style="padding:11px 22px; border-radius:10px; background:var(--pos-primary); color:#fff; border:none; font-size:14px; font-weight:800; cursor:pointer;">
                    <i class="fas fa-plus"></i> New Transfer
                </button>
                <a href="<?php echo htmlspecialchars($posUrl('stock-report')); ?>" style="padding:11px 22px; border-radius:10px; border:1.5px solid var(--pos-border); background:#fff; color:var(--pos-text); font-size:14px; font-weight:800; text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
                    <i class="fas fa-warehouse"></i> Stock Report
                </a>
            </div>
        </div>
    </div>

    <!-- ── Toast ─────────────────────────────────────────────────────────── -->
    <div class="transfer-toast" id="toastEl">
        <i class="fas fa-circle-info"></i>
        <span id="toastMsg"></span>
    </div>

    <script>
        const API_URL      = '<?php echo htmlspecialchars($posUrl('stock-transfer')); ?>';
        const STORES       = <?php echo json_encode(array_values($allStores)); ?>;
        const IMG_BASE     = '<?php echo mc_base_path(); ?>/uploads/products/';
        let   lineCounter  = 0;

        // ── On page load: auto-select different "to" store ────────────────────
        window.addEventListener('DOMContentLoaded', () => {
            ensureDifferentToStore();
            addLine(); // start with one blank line
        });

        function getFromStoreId() { return parseInt(document.getElementById('fromStoreSelect').value); }
        function getToStoreId()   { return parseInt(document.getElementById('toStoreSelect').value); }

        function onFromStoreChange() {
            ensureDifferentToStore();
            refreshAllLines();
        }
        function onToStoreChange() {
            const fromId = getFromStoreId();
            const toId   = getToStoreId();
            if (fromId === toId) {
                // pick next different
                const toSel = document.getElementById('toStoreSelect');
                for (let opt of toSel.options) {
                    if (parseInt(opt.value) !== fromId) { toSel.value = opt.value; break; }
                }
            }
        }

        function ensureDifferentToStore() {
            const fromId = getFromStoreId();
            const toSel  = document.getElementById('toStoreSelect');
            if (parseInt(toSel.value) === fromId) {
                for (let opt of toSel.options) {
                    if (parseInt(opt.value) !== fromId) { toSel.value = opt.value; break; }
                }
            }
        }

        // ── Add a new product line ────────────────────────────────────────────
        function addLine() {
            lineCounter++;
            const id   = lineCounter;
            const tbody = document.getElementById('linesBody');

            const tr = document.createElement('tr');
            tr.id = 'line-' + id;
            tr.dataset.productId   = '';
            tr.dataset.productName = '';
            tr.dataset.available   = '0';
            tr.innerHTML = `
                <td style="color:var(--pos-text-muted); font-weight:700; font-size:13px;" id="linenum-${id}">${tbody.children.length + 1}</td>
                <td>
                    <div class="product-search-wrap">
                        <i class="fas fa-search product-search-icon"></i>
                        <input type="text" class="product-search-input" id="psearch-${id}"
                            placeholder="Search product name or SKU…"
                            autocomplete="off"
                            oninput="searchProducts(${id}, this.value)"
                            onfocus="openDropdown(${id})"
                        >
                        <div class="product-dropdown" id="pdrop-${id}"></div>
                    </div>
                    <div id="selected-name-${id}" style="font-size:11px; color:var(--pos-primary); font-weight:700; margin-top:3px; display:none;"></div>
                </td>
                <td style="text-align:center;">
                    <span class="avail-badge stock-nil" id="avail-${id}">— units</span>
                </td>
                <td style="text-align:center;">
                    <input type="number" class="qty-input" id="qty-${id}" min="1" value="1"
                        onchange="validateLineQty(${id})">
                </td>
                <td style="text-align:center;">
                    <button onclick="removeLine(${id})" style="width:28px;height:28px;border-radius:50%;border:1px solid rgba(239,68,68,0.3);background:rgba(239,68,68,0.08);color:#ef4444;cursor:pointer;font-size:13px;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
            updateLineNumbers();
            updateLineCount();
            document.getElementById('psearch-' + id).focus();

            // Close dropdowns when clicking outside
            document.addEventListener('click', (e) => {
                if (!e.target.closest('#line-' + id)) closeDropdown(id);
            }, { once: false });
        }

        function removeLine(id) {
            const el = document.getElementById('line-' + id);
            if (el) el.remove();
            updateLineNumbers();
            updateLineCount();
        }

        function updateLineNumbers() {
            document.querySelectorAll('#linesBody tr').forEach((tr, i) => {
                const numEl = tr.querySelector('[id^="linenum-"]');
                if (numEl) numEl.textContent = i + 1;
            });
        }
        function updateLineCount() {
            const n = document.querySelectorAll('#linesBody tr').length;
            document.getElementById('lineCountBadge').textContent = n + ' line(s)';
        }

        // ── Product search ────────────────────────────────────────────────────
        let searchTimers = {};
        function searchProducts(lineId, q) {
            clearTimeout(searchTimers[lineId]);
            const fromId = getFromStoreId();
            searchTimers[lineId] = setTimeout(() => {
                fetch(`${API_URL}?ajax_products=1&store_id=${fromId}&q=${encodeURIComponent(q)}`)
                    .then(r => r.json())
                    .then(products => renderDropdown(lineId, products))
                    .catch(() => {});
            }, 220);
        }

        function openDropdown(lineId) {
            searchProducts(lineId, document.getElementById('psearch-' + lineId)?.value || '');
        }

        function closeDropdown(lineId) {
            const drop = document.getElementById('pdrop-' + lineId);
            if (drop) drop.classList.remove('open');
        }

        function renderDropdown(lineId, products) {
            const drop = document.getElementById('pdrop-' + lineId);
            if (!drop) return;
            drop.innerHTML = '';

            if (!products.length) {
                drop.innerHTML = '<div style="padding:14px 16px; color:var(--pos-text-muted); font-size:13px; font-weight:600; text-align:center;">No products found</div>';
                drop.classList.add('open');
                return;
            }

            products.forEach(p => {
                const avail   = parseInt(p.available ?? 0);
                const stockCls = avail > 10 ? 'stock-ok' : (avail > 0 ? 'stock-low' : 'stock-nil');
                const imgHtml = p.image
                    ? `<img src="${IMG_BASE}${p.image}" class="item-img" style="width:32px;height:32px;border-radius:8px;object-fit:cover;border:1px solid #e2e8f0;">`
                    : `<div class="item-img"><i class="fas fa-box"></i></div>`;

                const item = document.createElement('div');
                item.className = 'product-dropdown-item';
                item.innerHTML = `
                    ${imgHtml}
                    <div class="item-info">
                        <div class="item-name">${escHtml(p.name)}</div>
                        <div class="item-meta">${p.sku ? 'SKU: ' + escHtml(p.sku) : ''} ${p.category_name ? '· ' + escHtml(p.category_name) : ''}</div>
                    </div>
                    <span class="item-stock ${stockCls}">${avail} units</span>
                `;
                item.addEventListener('click', () => selectProduct(lineId, p));
                drop.appendChild(item);
            });
            drop.classList.add('open');
        }

        function selectProduct(lineId, p) {
            const avail = parseInt(p.available ?? 0);
            const tr    = document.getElementById('line-' + lineId);
            tr.dataset.productId   = p.id;
            tr.dataset.productName = p.name;
            tr.dataset.available   = avail;

            // Update search input
            const inp = document.getElementById('psearch-' + lineId);
            if (inp) inp.value = p.name;

            // Show selected name tag
            const nameTag = document.getElementById('selected-name-' + lineId);
            if (nameTag) {
                nameTag.textContent = p.sku ? 'SKU: ' + p.sku : '';
                nameTag.style.display = p.sku ? 'block' : 'none';
            }

            // Update available badge
            const badge = document.getElementById('avail-' + lineId);
            if (badge) {
                const cls = avail > 10 ? 'stock-ok' : (avail > 0 ? 'stock-low' : 'stock-nil');
                badge.className = 'avail-badge ' + cls;
                badge.textContent = avail + ' units';
            }

            // Set max on qty input
            const qtyInp = document.getElementById('qty-' + lineId);
            if (qtyInp) {
                qtyInp.max   = avail;
                qtyInp.value = Math.min(parseInt(qtyInp.value) || 1, avail || 1);
            }

            closeDropdown(lineId);
        }

        function validateLineQty(lineId) {
            const tr    = document.getElementById('line-' + lineId);
            const avail = parseInt(tr?.dataset.available ?? 0);
            const inp   = document.getElementById('qty-' + lineId);
            if (!inp) return;
            let v = parseInt(inp.value) || 1;
            if (v < 1) v = 1;
            if (avail > 0 && v > avail) v = avail;
            inp.value = v;
        }

        // ── Refresh all lines when from-store changes ─────────────────────────
        function refreshAllLines() {
            // Clear all selected products since available qty may change
            document.querySelectorAll('#linesBody tr').forEach(tr => {
                const id = tr.id.replace('line-', '');
                tr.dataset.productId   = '';
                tr.dataset.productName = '';
                tr.dataset.available   = '0';
                const inp = document.getElementById('psearch-' + id);
                if (inp) inp.value = '';
                const badge = document.getElementById('avail-' + id);
                if (badge) { badge.className = 'avail-badge stock-nil'; badge.textContent = '— units'; }
                const nameTag = document.getElementById('selected-name-' + id);
                if (nameTag) nameTag.style.display = 'none';
            });
        }

        // ── Validate Transfer ─────────────────────────────────────────────────
        function validateTransfer() {
            const fromId = getFromStoreId();
            const toId   = getToStoreId();

            if (fromId === toId) {
                showToast('Source and destination stores must be different.', 'error');
                return;
            }

            // Build lines
            const lines = [];
            let hasError = false;
            document.querySelectorAll('#linesBody tr').forEach(tr => {
                const id = tr.id.replace('line-', '');
                const productId = tr.dataset.productId;
                const qty       = parseInt(document.getElementById('qty-' + id)?.value) || 0;
                const avail     = parseInt(tr.dataset.available ?? 0);

                if (!productId) return; // skip unselected
                if (qty <= 0) { showToast('Quantity must be at least 1.', 'error'); hasError = true; return; }
                if (qty > avail) { showToast(`Not enough stock. Available: ${avail}`, 'error'); hasError = true; return; }
                lines.push({ product_id: productId, qty });
            });

            if (hasError) return;
            if (!lines.length) { showToast('Add at least one product line.', 'error'); return; }

            const btnT = document.getElementById('validateBtn');
            const btnB = document.getElementById('validateBtnBottom');
            [btnT, btnB].forEach(b => { if(b) { b.disabled = true; b.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Validating…'; }});

            const formData = new FormData();
            formData.append('ajax_validate_transfer', '1');
            formData.append('from_store_id', fromId);
            formData.append('to_store_id',   toId);
            formData.append('reference',     document.getElementById('refInput').value.trim());
            formData.append('note',          document.getElementById('noteInput').value.trim());
            lines.forEach((l, i) => {
                formData.append(`lines[${i}][product_id]`, l.product_id);
                formData.append(`lines[${i}][qty]`,        l.qty);
            });

            fetch(API_URL, { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('successRef').textContent   = data.reference;
                        document.getElementById('successLines').textContent = data.lines + ' product line(s) transferred';
                        document.getElementById('statusBadge').className    = 'transfer-status status-done';
                        document.getElementById('statusBadge').innerHTML    = '<i class="fas fa-circle" style="font-size:8px;"></i> Done';
                        document.getElementById('successOverlay').classList.add('open');
                    } else {
                        showToast(data.error || 'Transfer failed', 'error');
                        [btnT, btnB].forEach(b => { if(b) { b.disabled = false; b.innerHTML = '<i class="fas fa-check-double"></i> Validate Transfer'; }});
                    }
                })
                .catch(() => {
                    showToast('Network error. Please try again.', 'error');
                    [btnT, btnB].forEach(b => { if(b) { b.disabled = false; b.innerHTML = '<i class="fas fa-check-double"></i> Validate Transfer'; }});
                });
        }

        // ── Reset form ────────────────────────────────────────────────────────
        function discardTransfer() {
            if (!confirm('Discard this transfer and clear all lines?')) return;
            document.getElementById('linesBody').innerHTML = '';
            lineCounter = 0;
            updateLineCount();
            document.getElementById('refInput').value  = '';
            document.getElementById('noteInput').value = '';
            document.getElementById('statusBadge').className = 'transfer-status status-draft';
            document.getElementById('statusBadge').innerHTML = '<i class="fas fa-circle" style="font-size:8px;"></i> Draft';
            addLine();
        }

        function newTransfer() {
            document.getElementById('successOverlay').classList.remove('open');
            setTimeout(() => location.reload(), 200);
        }

        // ── Toast ─────────────────────────────────────────────────────────────
        let toastTimer;
        function showToast(msg, type = 'error') {
            const el = document.getElementById('toastEl');
            el.className = 'transfer-toast show ' + type;
            document.getElementById('toastMsg').textContent = msg;
            clearTimeout(toastTimer);
            toastTimer = setTimeout(() => { el.classList.remove('show'); }, 4000);
        }

        function escHtml(str) {
            return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }
    </script>

    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

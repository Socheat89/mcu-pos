<?php
require_once __DIR__ . '/../../../core/helpers/url.php';
require_once __DIR__ . '/../../../core/classes/Settings.php';
$__isCoffee   = ($businessType === 'coffee');
$__itemLabel  = $__isCoffee ? 'Ingredient' : 'Product';
$__itemsLabel = $__isCoffee ? 'Ingredients' : 'Products';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Transfer<?php echo $__isCoffee ? ' (Ingredients)' : ''; ?> — <?php echo htmlspecialchars($tenantName ?? 'POS'); ?></title>
    <link href="<?php echo mc_base_path(); ?>/public/css/pos_template.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700;800&family=Battambang:wght@300;400;700;900&display=swap" rel="stylesheet">
<style>
* { box-sizing: border-box; }
body, h1,h2,h3,h4,h5,h6,p,span,a,button,input,select,textarea,td,th {
    font-family: 'Space Grotesk','Battambang',sans-serif !important;
}

/* ── Document wrapper ─────────────────────────────────────────────────── */
.doc-wrap {
    background: #fff;
    border-radius: 20px;
    border: 1.5px solid #e2e8f0;
    box-shadow: 0 4px 24px rgba(0,0,0,0.07);
    overflow: visible;
    margin-bottom: 24px;
}

/* ── Document top bar ─────────────────────────────────────────────────── */
.doc-topbar {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 28px;
    border-bottom: 1.5px solid #f1f5f9;
    background: linear-gradient(to right, rgba(99,102,241,0.04), transparent);
    border-radius: 20px 20px 0 0;
    flex-wrap: wrap; gap: 12px;
}
.doc-breadcrumb {
    display: flex; align-items: center; gap: 8px;
    font-size: 13px; font-weight: 700;
}
.doc-breadcrumb a { color: #94a3b8; text-decoration: none; transition: color .15s; }
.doc-breadcrumb a:hover { color: var(--pos-primary); }
.doc-breadcrumb .sep { color: #cbd5e1; }
.doc-breadcrumb .current { color: var(--pos-text); }
.doc-actions { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }

/* Status pill */
.doc-status {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 5px 14px; border-radius: 20px; font-size: 12px; font-weight: 800;
    text-transform: uppercase; letter-spacing: 0.4px;
}
.status-draft { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }
.status-done  { background: rgba(16,185,129,.12); color: #059669; border: 1px solid rgba(16,185,129,.25); }

/* Buttons */
.btn-act {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 9px 20px; border-radius: 10px; font-size: 13px; font-weight: 800;
    cursor: pointer; transition: all .18s; border: none;
}
.btn-discard { background: #f8fafc; color: #64748b; border: 1.5px solid #e2e8f0; }
.btn-discard:hover { background: #f1f5f9; border-color: #cbd5e1; }
.btn-validate {
    background: linear-gradient(135deg,#10b981,#059669);
    color: #fff; box-shadow: 0 4px 14px rgba(16,185,129,.28);
}
.btn-validate:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(16,185,129,.38); }
.btn-validate:disabled { opacity: .55; cursor: not-allowed; transform: none; }

/* ── Document body ─────────────────────────────────────────────────────── */
.doc-body { padding: 28px 28px 0; }

/* Store row */
.store-row {
    display: grid; grid-template-columns: 1fr auto 1fr; align-items: end;
    gap: 16px; margin-bottom: 24px;
    background: #fafbff; border: 1.5px solid rgba(99,102,241,.14);
    border-radius: 14px; padding: 18px 22px;
}
@media(max-width:700px){ .store-row { grid-template-columns: 1fr; } }
.store-lbl {
    font-size: 10.5px; font-weight: 800; color: #94a3b8;
    text-transform: uppercase; letter-spacing: .7px; margin-bottom: 7px;
    display: flex; align-items: center; gap: 5px;
}
.store-select {
    width: 100%; padding: 10px 14px; border: 1.5px solid #e2e8f0;
    border-radius: 10px; font-size: 14px; font-weight: 700; color: #1e293b;
    background: #fff; outline: none; cursor: pointer; transition: border-color .2s;
}
.store-select:focus { border-color: var(--pos-primary); box-shadow: 0 0 0 3px rgba(99,102,241,.1); }
.arrow-mid {
    display: flex; flex-direction: column; align-items: center;
    gap: 3px; padding-bottom: 4px;
}
.arrow-mid i { font-size: 22px; color: var(--pos-primary); }
.arrow-mid span { font-size: 9px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; }

/* Meta row */
.meta-row {
    display: grid; grid-template-columns: 1.5fr 1fr 1.5fr;
    gap: 16px; margin-bottom: 0;
}
@media(max-width:700px){ .meta-row { grid-template-columns: 1fr; } }
.meta-field label {
    display: block; font-size: 10.5px; font-weight: 800; color: #94a3b8;
    text-transform: uppercase; letter-spacing: .6px; margin-bottom: 6px;
}
.meta-field input, .meta-field select {
    width: 100%; padding: 9px 13px; border: 1.5px solid #e2e8f0;
    border-radius: 10px; font-size: 13px; font-weight: 600; color: #1e293b;
    background: #fff; outline: none; transition: border-color .2s;
}
.meta-field input:focus { border-color: var(--pos-primary); box-shadow: 0 0 0 3px rgba(99,102,241,.1); }
.meta-field input::placeholder { color: #cbd5e1; font-weight: 500; }

.doc-divider {
    margin: 24px 0 0; height: 1.5px;
    background: linear-gradient(to right, #f1f5f9 0%, #e2e8f0 40%, #f1f5f9 100%);
}

/* Lines section */
.lines-section { padding: 0 28px 20px; }
.lines-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 0 12px;
}
.lines-head-title {
    font-size: 13px; font-weight: 900; color: #1e293b;
    display: flex; align-items: center; gap: 8px;
}
.lines-count {
    font-size: 12px; font-weight: 700; color: #94a3b8;
    background: #f1f5f9; padding: 3px 10px; border-radius: 20px;
}

.lines-tbl { width: 100%; border-collapse: collapse; }
.lines-tbl thead tr { background: #f8fafc; }
.lines-tbl th {
    padding: 9px 14px; text-align: left; font-size: 10.5px; font-weight: 800;
    color: #94a3b8; text-transform: uppercase; letter-spacing: .5px;
    border-bottom: 1.5px solid #f1f5f9; border-top: 1.5px solid #f1f5f9;
}
.lines-tbl th.c { text-align: center; }
.lines-tbl td {
    padding: 10px 14px; border-bottom: 1px solid #f8fafc; vertical-align: middle;
}
.lines-tbl tbody tr:last-child td { border-bottom: none; }
.lines-tbl tbody tr:hover { background: rgba(99,102,241,.018); }

.lines-section, .lines-tbl, .lines-tbl td, .lines-tbl tr, .lines-tbl tbody {
    overflow: visible !important;
}

.psearch-wrap { position: relative; width: 100%; }
.psearch-inp {
    width: 100%; min-width: 220px;
    padding: 8px 12px 8px 34px; border: 1.5px solid #e2e8f0;
    border-radius: 9px; font-size: 13px; font-weight: 600; color: #1e293b;
    outline: none; transition: border-color .2s; background: #fff;
}
.psearch-inp:focus { border-color: var(--pos-primary); box-shadow: 0 0 0 3px rgba(99,102,241,.1); }
.psearch-inp::placeholder { color: #cbd5e1; font-weight: 500; }
.psearch-icon { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: #cbd5e1; font-size: 12px; pointer-events: none; }

.pdrop {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    width: 100%;
    z-index: 9999; background: #fff;
    border: 1.5px solid #e2e8f0; border-radius: 14px;
    max-height: 250px; overflow-y: auto;
    box-shadow: 0 16px 40px rgba(15,23,42,.16);
    display: none;
}
.pdrop.open { display: block; }
.pdrop-item {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 14px; cursor: pointer;
    border-bottom: 1px solid #f8fafc; transition: all .15s ease;
}
.pdrop-item:last-child { border-bottom: none; }
.pdrop-item:hover { background: rgba(99,102,241,.06); }
.pdrop-thumb {
    width: 36px; height: 36px; border-radius: 10px; object-fit: cover;
    border: 1px solid #e2e8f0; flex-shrink: 0;
    background: rgba(99,102,241,.08); color: var(--pos-primary);
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; overflow: hidden;
}
.pdrop-thumb img { width: 100%; height: 100%; object-fit: cover; }
.pdrop-name { font-size: 13.5px; font-weight: 800; color: #1e293b; line-height: 1.3; }
.pdrop-meta { font-size: 11px; color: #94a3b8; font-weight: 600; margin-top: 2px; }
.pdrop-stock {
    margin-left: auto; font-size: 12px; font-weight: 800; flex-shrink: 0;
    padding: 4px 10px; border-radius: 8px; display: inline-flex; align-items: center; gap: 4px;
}
.s-ok  { background: rgba(16,185,129,.1); color: #10b981; }
.s-low { background: rgba(245,158,11,.1); color: #f59e0b; }
.s-nil { background: rgba(239,68,68,.1);  color: #ef4444; }

.avail {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 5px 12px; border-radius: 9px; font-size: 13px; font-weight: 800;
}
.qty-inp {
    width: 86px; padding: 7px 10px; border: 1.5px solid #e2e8f0;
    border-radius: 9px; font-size: 15px; font-weight: 800; text-align: center;
    outline: none; transition: border-color .2s; color: #1e293b;
}
.qty-inp:focus { border-color: var(--pos-primary); box-shadow: 0 0 0 3px rgba(99,102,241,.1); }

.rm-btn {
    width: 28px; height: 28px; border-radius: 50%;
    border: 1.5px solid rgba(239,68,68,.25); background: rgba(239,68,68,.07);
    color: #ef4444; cursor: pointer; font-size: 12px;
    display: flex; align-items: center; justify-content: center; transition: all .15s;
}
.rm-btn:hover { background: rgba(239,68,68,.15); border-color: rgba(239,68,68,.4); }

.add-line {
    width: 100%; padding: 11px; border: 1.5px dashed #e2e8f0;
    border-radius: 10px; background: transparent; cursor: pointer;
    font-size: 13px; font-weight: 700; color: #94a3b8;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    transition: all .18s; margin-top: 8px;
}
.add-line:hover { border-color: var(--pos-primary); color: var(--pos-primary); background: rgba(99,102,241,.04); }

.doc-footer {
    display: flex; justify-content: flex-end; gap: 10px; align-items: center;
    padding: 18px 28px; border-top: 1.5px solid #f1f5f9;
    background: #fafbfc; border-radius: 0 0 20px 20px;
}

/* ── Discard Modal ─────────────────────────────────────────────────────── */
.custom-modal {
    position: fixed; inset: 0; background: rgba(15,23,42,.55);
    backdrop-filter: blur(6px); z-index: 9999;
    display: none; align-items: center; justify-content: center;
}
.custom-modal.open { display: flex; }
.custom-modal-card {
    background: #fff; border-radius: 24px; padding: 36px 32px; text-align: center;
    max-width: 400px; width: 90%;
    box-shadow: 0 30px 60px rgba(0,0,0,.2);
    animation: popIn .25s ease-out;
}
.discard-icon {
    width: 64px; height: 64px; border-radius: 50%;
    background: rgba(239,68,68,.1); border: 2px solid #ef4444;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 16px; font-size: 26px; color: #ef4444;
}

/* ── Success overlay ───────────────────────────────────────────────────── */
.success-overlay {
    position: fixed; inset: 0; background: rgba(15,23,42,.55);
    backdrop-filter: blur(8px); z-index: 9999;
    display: none; align-items: center; justify-content: center;
}
.success-overlay.open { display: flex; }
.success-card {
    background: #fff; border-radius: 24px; padding: 44px 40px; text-align: center;
    max-width: 460px; width: 90%;
    box-shadow: 0 40px 80px rgba(0,0,0,.22);
    animation: popIn .3s cubic-bezier(.175,.885,.32,1.275);
}
@keyframes popIn { from{transform:scale(.85);opacity:0} to{transform:scale(1);opacity:1} }
.success-icon {
    width: 70px; height: 70px; border-radius: 50%;
    background: rgba(16,185,129,.1); border: 2.5px solid #10b981;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 18px; font-size: 30px; color: #10b981;
}
.ref-pill {
    display: inline-block; background: #f8fafc; border: 1.5px solid #e2e8f0;
    padding: 8px 22px; border-radius: 10px; font-size: 17px; font-weight: 900;
    color: var(--pos-primary); letter-spacing: 1.5px; font-family: monospace !important;
    margin: 14px 0 22px;
}

/* ── Toast ─────────────────────────────────────────────────────────────── */
.toast {
    position: fixed; bottom: 28px; right: 28px; z-index: 10000;
    padding: 13px 20px; border-radius: 12px; font-size: 13px; font-weight: 800;
    display: none; align-items: center; gap: 9px;
    box-shadow: 0 8px 28px rgba(0,0,0,.18); animation: slideUp .25s ease-out;
    max-width: 340px;
}
.toast.show { display: flex; }
.toast.ok  { background: #10b981; color: #fff; }
.toast.err { background: #ef4444; color: #fff; }
@keyframes slideUp { from{transform:translateY(16px);opacity:0} to{transform:translateY(0);opacity:1} }

/* ── History card ──────────────────────────────────────────────────────── */
.hist-card {
    background: #fff; border-radius: 20px; border: 1.5px solid #e2e8f0;
    box-shadow: 0 4px 24px rgba(0,0,0,.05); overflow: hidden; margin-bottom: 24px;
}
.hist-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 24px; border-bottom: 1.5px solid #f1f5f9; background: #fafafa;
}
.hist-head h3 { margin: 0; font-size: 14px; font-weight: 900; color: #1e293b; }
.hist-tbl { width: 100%; border-collapse: collapse; }
.hist-tbl th {
    padding: 9px 16px; font-size: 10.5px; font-weight: 800; color: #94a3b8;
    text-transform: uppercase; letter-spacing: .5px; text-align: left;
    border-bottom: 1.5px solid #f1f5f9; background: #f8fafc;
}
.hist-tbl td { padding: 11px 16px; border-bottom: 1px solid #f8fafc; font-size: 13px; }
.hist-tbl tbody tr:last-child td { border-bottom: none; }
.hist-tbl tbody tr:hover { background: rgba(99,102,241,.018); }
.hist-pill {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 800;
}
.hp-in  { background: rgba(16,185,129,.1); color: #10b981; }
.hp-out { background: rgba(245,158,11,.1); color: #f59e0b; }
.store-tag {
    background: rgba(99,102,241,.1); color: var(--pos-primary);
    padding: 3px 10px; border-radius: 8px; font-size: 11px; font-weight: 800;
}

/* ── Print area styling ───────────────────────────────────────────────── */
#printTransferArea { display: none; }
@media print {
    body * { visibility: hidden !important; }
    #printTransferArea, #printTransferArea * { visibility: visible !important; }
    #printTransferArea {
        position: absolute; left: 0; top: 0; width: 100%; display: block !important;
        background: #fff; padding: 20px; font-family: 'Space Grotesk','Battambang',sans-serif;
    }
    .print-voucher {
        border: 2px solid #000; padding: 24px; border-radius: 8px; color: #000;
    }
    .print-voucher-header {
        text-align: center; border-bottom: 2px dashed #000; padding-bottom: 14px; margin-bottom: 16px;
    }
    .print-voucher-header h2 { margin: 0 0 4px; font-size: 22px; font-weight: 900; text-transform: uppercase; }
    .print-voucher-header h3 { margin: 0 0 6px; font-size: 16px; font-weight: 800; letter-spacing: 1px; }
    .print-meta-grid {
        display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 13px; margin-bottom: 18px;
        border-bottom: 1px solid #000; padding-bottom: 12px;
    }
    .print-table {
        width: 100%; border-collapse: collapse; margin-bottom: 30px; font-size: 13px;
    }
    .print-table th, .print-table td {
        border: 1px solid #000; padding: 8px 12px; text-align: left;
    }
    .print-table th { background: #f0f0f0; font-weight: 800; }
    .print-signatures {
        display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; text-align: center; margin-top: 40px; font-size: 12px; font-weight: 700;
    }
    .print-sig-line { margin-top: 50px; border-top: 1px solid #000; padding-top: 6px; }
}
</style>
</head>
<body class="pos-app">
<?php $activeNav = 'stock_transfer'; include __DIR__ . '/partials/navbar.php'; ?>

<div class="fade-in">

<!-- ════════════════════════════════════════════════════════════════════════
     Main Document Card
═════════════════════════════════════════════════════════════════════════ -->
<div class="doc-wrap">

    <!-- Top bar -->
    <div class="doc-topbar">
        <div class="doc-breadcrumb">
            <a href="<?php echo htmlspecialchars($posUrl('stock-report')); ?>">
                <i class="fas fa-warehouse"></i> Stock
            </a>
            <span class="sep">/</span>
            <span class="current">Internal Transfer</span>
        </div>
        <div style="display:flex; align-items:center; gap:10px;">
            <span class="doc-status status-draft" id="statusBadge">
                <i class="fas fa-circle" style="font-size:7px;"></i> Draft
            </span>
            <div class="doc-actions">
                <button class="btn-act btn-discard" onclick="discardTransfer()">
                    <i class="fas fa-rotate-left"></i> Discard
                </button>
                <button class="btn-act btn-validate" id="validateBtn" onclick="validateTransfer()">
                    <i class="fas fa-check-double"></i> Validate Transfer
                </button>
            </div>
        </div>
    </div>

    <!-- Title row inside body -->
    <div class="doc-body">
        <div style="margin-bottom:22px;">
            <h2 style="margin:0 0 2px; font-size:21px; font-weight:900; color:#1e293b; display:flex; align-items:center; gap:9px;">
                <i class="fas fa-arrows-left-right" style="color:var(--pos-primary); font-size:18px;"></i>
                New Internal Transfer
                <?php if ($__isCoffee): ?>
                <span style="font-size:12px; font-weight:700; background:rgba(99,102,241,0.1); color:var(--pos-primary); padding:3px 10px; border-radius:20px;"><i class="fas fa-flask" style="margin-right:4px;"></i>Ingredients</span>
                <?php endif; ?>
            </h2>
            <p style="margin:0; font-size:13px; color:#94a3b8; font-weight:600;">
                <?php if ($__isCoffee): ?>
                    Transfer ingredients between stores — ការផ្ទេរ Ingredients រវាង store
                <?php else: ?>
                    Transfer stock between stores — ការផ្ទេរស្តុករវាង store
                <?php endif; ?>
            </p>
        </div>

        <!-- Store From → To -->
        <div class="store-row">
            <div>
                <div class="store-lbl"><i class="fas fa-warehouse"></i> From (Source)</div>
                <select class="store-select" id="fromStoreSelect" onchange="onFromStoreChange()">
                    <?php foreach ($allStores as $s): ?>
                    <option value="<?php echo $s['id']; ?>" <?php echo ($currentStore && $s['id'] == $currentStore['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars('[' . ($s['code'] ?? '--') . '] ' . $s['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="arrow-mid">
                <i class="fas fa-arrow-right"></i>
                <span>Transfer</span>
            </div>
            <div>
                <div class="store-lbl"><i class="fas fa-store"></i> To (Destination)</div>
                <select class="store-select" id="toStoreSelect" onchange="onToStoreChange()">
                    <?php foreach ($allStores as $s): ?>
                    <option value="<?php echo $s['id']; ?>">
                        <?php echo htmlspecialchars('[' . ($s['code'] ?? '--') . '] ' . $s['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Reference / Date / Note -->
        <div class="meta-row">
            <div class="meta-field">
                <label>Reference</label>
                <input type="text" id="refInput" placeholder="Auto-generated (e.g. TRF/20260801/A3F2)" style="font-family:monospace!important;">
            </div>
            <div class="meta-field">
                <label>Scheduled Date</label>
                <input type="date" id="dateInput" value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="meta-field">
                <label>Note / Remarks</label>
                <input type="text" id="noteInput" placeholder="e.g. Weekly branch resupply">
            </div>
        </div>
    </div><!-- /.doc-body -->

    <!-- Divider -->
    <div class="doc-divider"></div>

    <!-- Transfer Lines -->
    <div class="lines-section">
        <div class="lines-head">
            <div class="lines-head-title">
                <i class="fas fa-list-ul" style="color:var(--pos-primary);"></i>
                <?php echo $__itemsLabel; ?> to Transfer
            </div>
            <span class="lines-count" id="lineCountBadge">0 line(s)</span>
        </div>

        <table class="lines-tbl" id="linesTable">
            <thead>
                <tr>
                    <th style="width:36px;">#</th>
                    <th><?php echo $__itemLabel; ?></th>
                    <th class="c" style="width:150px;">Available (From)</th>
                    <th class="c" style="width:120px;">Qty to Transfer</th>
                    <th class="c" style="width:44px;"></th>
                </tr>
            </thead>
            <tbody id="linesBody"></tbody>
        </table>

        <button class="add-line" onclick="addLine()">
            <i class="fas fa-plus"></i> Add a line
        </button>
    </div>

    <!-- Footer actions -->
    <div class="doc-footer">
        <button class="btn-act btn-discard" onclick="discardTransfer()">
            <i class="fas fa-rotate-left"></i> Discard
        </button>
        <button class="btn-act btn-validate" id="validateBtnB" onclick="validateTransfer()">
            <i class="fas fa-check-double"></i> Validate Transfer
        </button>
    </div>

</div><!-- /.doc-wrap -->


<!-- ════════════════════════════════════════════════════════════════════════
     Transfer History
═════════════════════════════════════════════════════════════════════════ -->
<?php if (!empty($transferHistory)): ?>
<div class="hist-card">
    <div class="hist-head">
        <h3><i class="fas fa-clock-rotate-left" style="color:var(--pos-primary); margin-right:6px;"></i>Transfer History</h3>
        <span style="font-size:12px; font-weight:700; color:#94a3b8; background:#f1f5f9; padding:3px 10px; border-radius:20px;"><?php echo count($transferHistory); ?> records</span>
    </div>
    <div style="overflow-x:auto;">
        <table class="hist-tbl">
            <thead>
                <tr>
                    <th>Item / Name</th>
                    <th>Store</th>
                    <th>ពី → ទៅ / From → To</th>
                    <th style="text-align:center;">Type</th>
                    <th style="text-align:center;">Qty</th>
                    <th>Reference / Note</th>
                    <th>Date</th>
                    <th style="text-align:center;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transferHistory as $h):
                    $isIn = ($h['reason'] === 'transfer_in');
                    $qtyDisplay = ($isIn ? '+' : '−') . abs($h['change_quantity']) . ($h['unit'] ? ' ' . $h['unit'] : '');
                    $counterName = $h['counterpart_store_name'] ?? null;
                ?>
                <tr>
                    <td style="font-weight:800;"><?php echo htmlspecialchars($h['item_name'] ?? 'N/A'); ?></td>
                    <td>
                        <?php if (!empty($h['store_name'])): ?>
                        <span class="store-tag"><?php echo htmlspecialchars($h['store_name']); ?></span>
                        <?php else: ?><span style="color:#cbd5e1;">—</span><?php endif; ?>
                    </td>
                    <td>
                        <?php if ($counterName): ?>
                            <?php if ($isIn): ?>
                            <span style="font-size:11px; font-weight:700; color:#6366f1;">
                                <i class="fas fa-arrow-right" style="font-size:9px;"></i> From <?php echo htmlspecialchars($counterName); ?>
                            </span>
                            <?php else: ?>
                            <span style="font-size:11px; font-weight:700; color:#f59e0b;">
                                <i class="fas fa-arrow-right" style="font-size:9px;"></i> To <?php echo htmlspecialchars($counterName); ?>
                            </span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color:#cbd5e1;">—</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center;">
                        <span class="hist-pill <?php echo $isIn ? 'hp-in' : 'hp-out'; ?>">
                            <i class="fas <?php echo $isIn ? 'fa-arrow-down-to-line' : 'fa-arrow-up-from-line'; ?>"></i>
                            <?php echo $isIn ? 'Transfer In' : 'Transfer Out'; ?>
                        </span>
                    </td>
                    <td style="text-align:center; font-weight:900; color:<?php echo $isIn ? '#10b981' : '#f59e0b'; ?>; font-size:14px;">
                        <?php echo $qtyDisplay; ?>
                    </td>
                    <td style="color:#94a3b8; font-size:12px; font-weight:600; max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                        <?php echo htmlspecialchars($h['note'] ?? '—'); ?>
                    </td>
                    <td style="color:#94a3b8; font-size:12px; font-weight:600; white-space:nowrap;">
                        <?php echo date('d/m/Y H:i', strtotime($h['created_at'])); ?>
                    </td>
                    <td style="text-align:center;">
                        <button class="btn-act btn-discard" style="padding:4px 10px; font-size:11px;"
                            onclick="printHistoryRecord('<?php echo htmlspecialchars(addslashes($h['item_name'] ?? '')); ?>', '<?php echo htmlspecialchars(addslashes($h['store_name'] ?? '')); ?>', '<?php echo $isIn ? 'Transfer In' : 'Transfer Out'; ?>', '<?php echo htmlspecialchars(addslashes($qtyDisplay)); ?>', '<?php echo htmlspecialchars(addslashes($h['note'] ?? '')); ?>', '<?php echo date('d/m/Y H:i', strtotime($h['created_at'])); ?>')"
                            title="Print Voucher">
                            <i class="fas fa-print"></i> Print
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

</div><!-- /.fade-in -->


<!-- ════════════════════════════════════════════════════════════════════════
     Discard Modal
═════════════════════════════════════════════════════════════════════════ -->
<div class="custom-modal" id="discardModal">
    <div class="custom-modal-card">
        <div class="discard-icon"><i class="fas fa-triangle-exclamation"></i></div>
        <h3 style="margin:0 0 6px; font-size:18px; font-weight:900; color:#1e293b;">Discard Transfer?</h3>
        <p style="margin:0 0 20px; color:#64748b; font-size:13px; font-weight:600;">
            Are you sure you want to discard this transfer? All unsaved lines will be cleared.
        </p>
        <div style="display:flex; gap:10px; justify-content:center;">
            <button onclick="closeDiscardModal()" class="btn-act btn-discard">Cancel</button>
            <button onclick="confirmDiscardTransfer()" class="btn-act" style="background:#ef4444; color:#fff; border:none; box-shadow: 0 4px 14px rgba(239,68,68,0.3);">
                <i class="fas fa-trash"></i> Yes, Discard
            </button>
        </div>
    </div>
</div>


<!-- ════════════════════════════════════════════════════════════════════════
     Success Overlay
═════════════════════════════════════════════════════════════════════════ -->
<div class="success-overlay" id="successOverlay">
    <div class="success-card">
        <div class="success-icon"><i class="fas fa-check"></i></div>
        <h2 style="margin:0 0 4px; font-size:22px; font-weight:900; color:#1e293b;">Transfer Complete!</h2>
        <p style="margin:0; color:#94a3b8; font-size:13px; font-weight:600;">Stock moved successfully between stores</p>
        <div class="ref-pill" id="successRef">—</div>
        <p id="successLines" style="margin:0 0 24px; color:#64748b; font-size:13px; font-weight:700;"></p>
        <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
            <button onclick="printTransferReceipt()" class="btn-act btn-discard" style="font-size:13px; background:#f1f5f9; color:#1e293b;">
                <i class="fas fa-print"></i> Print Voucher
            </button>
            <button onclick="newTransfer()" class="btn-act btn-validate" style="font-size:13px;">
                <i class="fas fa-plus"></i> New Transfer
            </button>
            <a href="<?php echo htmlspecialchars($posUrl('stock-report')); ?>"
               class="btn-act btn-discard" style="font-size:13px; text-decoration:none;">
                <i class="fas fa-warehouse"></i> Stock Report
            </a>
        </div>
    </div>
</div>


<!-- ════════════════════════════════════════════════════════════════════════
     Printable Area for Transfer Voucher
═════════════════════════════════════════════════════════════════════════ -->
<div id="printTransferArea">
    <div class="print-voucher">
        <div class="print-voucher-header">
            <h2><?php echo htmlspecialchars($tenantName ?? 'POS'); ?></h2>
            <h3>STOCK TRANSFER VOUCHER</h3>
            <p id="printVoucherDate" style="margin:0; font-size:12px;">Date: <?php echo date('d/m/Y H:i'); ?></p>
        </div>
        <div class="print-meta-grid">
            <div><strong>Reference:</strong> <span id="printVoucherRef">—</span></div>
            <div><strong>Type:</strong> <span id="printVoucherType">Internal Transfer</span></div>
            <div><strong>From Store:</strong> <span id="printVoucherFrom">—</span></div>
            <div><strong>To Store:</strong> <span id="printVoucherTo">—</span></div>
            <div style="grid-column: span 2;"><strong>Note:</strong> <span id="printVoucherNote">—</span></div>
        </div>
        <table class="print-table">
            <thead>
                <tr>
                    <th style="width:40px;">#</th>
                    <th>Item / Description</th>
                    <th style="text-align:center; width:140px;">Transferred Qty</th>
                </tr>
            </thead>
            <tbody id="printVoucherBody"></tbody>
        </table>
        <div class="print-signatures">
            <div>
                <div>Issued By</div>
                <div class="print-sig-line">( Handover Staff )</div>
            </div>
            <div>
                <div>Received By</div>
                <div class="print-sig-line">( Receiving Staff )</div>
            </div>
            <div>
                <div>Approved By</div>
                <div class="print-sig-line">( Manager / Admin )</div>
            </div>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="toast" id="toastEl"><i class="fas fa-circle-info"></i><span id="toastMsg"></span></div>


<!-- ════════════════════════════════════════════════════════════════════════
     JavaScript
═════════════════════════════════════════════════════════════════════════ -->
<script>
const API_URL      = '<?php echo htmlspecialchars($posUrl('stock-transfer')); ?>';
const IMG_BASE     = '<?php echo mc_base_path(); ?>/uploads/products/';
const IS_COFFEE    = <?php echo $__isCoffee ? 'true' : 'false'; ?>;
const ITEM_LABEL   = '<?php echo $__itemLabel; ?>';
const TENANT_NAME  = <?php echo json_encode($tenantName ?? 'POS'); ?>;
const STORES_MAP   = <?php
    $map = [];
    foreach ($allStores as $s) { $map[$s['id']] = '[' . ($s['code'] ?? '--') . '] ' . $s['name']; }
    echo json_encode($map);
?>;

let lineCounter      = 0;
let openDropId       = null;
let lastTransferData = null;

// ── Init ─────────────────────────────────────────────────────────────────
window.addEventListener('DOMContentLoaded', () => {
    ensureDiffTo();
    addLine();

    // close dropdown on outside click
    document.addEventListener('click', e => {
        if (openDropId !== null && !e.target.closest('.psearch-wrap') && !e.target.closest('.pdrop')) {
            closeDrop(openDropId);
        }
    });

    // close on Escape
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            if (openDropId !== null) closeDrop(openDropId);
            closeDiscardModal();
        }
    });
});

function gFrom() { return parseInt(document.getElementById('fromStoreSelect').value); }
function gTo()   { return parseInt(document.getElementById('toStoreSelect').value); }

function onFromStoreChange() { ensureDiffTo(); refreshLines(); }
function onToStoreChange()   {
    if (gTo() === gFrom()) {
        const sel = document.getElementById('toStoreSelect');
        for (let o of sel.options) { if (parseInt(o.value) !== gFrom()) { sel.value = o.value; break; } }
    }
}
function ensureDiffTo() {
    const sel = document.getElementById('toStoreSelect');
    if (parseInt(sel.value) === gFrom()) {
        for (let o of sel.options) { if (parseInt(o.value) !== gFrom()) { sel.value = o.value; break; } }
    }
}

// ── Lines ──────────────────────────────────────────────────────────────────
function addLine() {
    lineCounter++;
    const id = lineCounter;
    const tbody = document.getElementById('linesBody');
    const qtyStep = IS_COFFEE ? '0.001' : '1';
    const tr = document.createElement('tr');
    tr.id = 'ln-' + id;
    tr.dataset.itemId = '';
    tr.dataset.itemName = '';
    tr.dataset.avail  = '0';
    tr.dataset.unit   = '';
    tr.innerHTML = `
        <td style="color:#94a3b8;font-weight:700;font-size:13px;" id="lnum-${id}">${tbody.children.length+1}</td>
        <td>
            <div class="psearch-wrap" id="pwrap-${id}">
                <i class="fas fa-search psearch-icon"></i>
                <input type="text" class="psearch-inp" id="pinp-${id}"
                    placeholder="Search ${ITEM_LABEL} name…"
                    autocomplete="off"
                    oninput="doSearch(${id},this.value)"
                    onfocus="doSearch(${id},this.value)"
                >
                <div class="pdrop" id="pdrop-${id}"></div>
            </div>
            <div id="psku-${id}" style="font-size:10.5px;color:var(--pos-primary);font-weight:700;margin-top:2px;display:none;"></div>
        </td>
        <td style="text-align:center;">
            <span class="avail s-nil" id="pavail-${id}">—</span>
        </td>
        <td style="text-align:center;">
            <input type="number" class="qty-inp" id="pqty-${id}" min="0.001" step="${qtyStep}" value="1" onchange="clampQty(${id})">
        </td>
        <td style="text-align:center;">
            <button class="rm-btn" onclick="removeLine(${id})" title="Remove"><i class="fas fa-times"></i></button>
        </td>
    `;
    tbody.appendChild(tr);
    renum(); countLines();
    document.getElementById('pinp-'+id).focus();
}

function removeLine(id) {
    const el = document.getElementById('ln-'+id);
    if (el) el.remove();
    renum(); countLines();
}
function renum() {
    document.querySelectorAll('#linesBody tr').forEach((tr,i) => {
        const n = tr.querySelector('[id^="lnum-"]');
        if (n) n.textContent = i+1;
    });
}
function countLines() {
    const n = document.querySelectorAll('#linesBody tr').length;
    document.getElementById('lineCountBadge').textContent = n + ' line(s)';
}
function refreshLines() {
    document.querySelectorAll('#linesBody tr').forEach(tr => {
        const id = tr.id.replace('ln-','');
        tr.dataset.itemId = '';
        tr.dataset.itemName = '';
        tr.dataset.avail  = '0';
        tr.dataset.unit   = '';
        const inp = document.getElementById('pinp-'+id);
        if (inp) inp.value = '';
        const avail = document.getElementById('pavail-'+id);
        if (avail) { avail.className='avail s-nil'; avail.textContent='—'; }
        const sku = document.getElementById('psku-'+id);
        if (sku) sku.style.display='none';
    });
}

// ── Search & Select ───────────────────────────────────────────────────────
let timers = {};
function doSearch(id, q) {
    clearTimeout(timers[id]);
    timers[id] = setTimeout(() => {
        fetch(`${API_URL}?ajax_items=1&store_id=${gFrom()}&q=${encodeURIComponent(q)}`)
            .then(r => r.json())
            .then(items => showDrop(id, items))
            .catch(() => {});
    }, 200);
}

function showDrop(id, items) {
    const drop = document.getElementById('pdrop-'+id);
    if (!drop) return;

    drop.innerHTML = '';
    if (!items.length) {
        drop.innerHTML = '<div style="padding:14px 16px;color:#94a3b8;font-size:13px;font-weight:600;text-align:center;"><i class="fas fa-search-minus" style="margin-right:6px;opacity:0.5;"></i>No items found</div>';
        drop.classList.add('open');
        openDropId = id;
        return;
    }

    items.forEach(p => {
        const av   = parseFloat(p.available ?? 0);
        const cls  = av > 10 ? 's-ok' : (av > 0 ? 's-low' : 's-nil');
        const unit = p.unit || 'units';
        const div  = document.createElement('div');
        div.className = 'pdrop-item';

        if (IS_COFFEE) {
            div.innerHTML = `
                <div class="pdrop-thumb"><i class="fas fa-flask"></i></div>
                <div style="flex:1;min-width:0;">
                    <div class="pdrop-name">${esc(p.name)}</div>
                </div>
                <span class="pdrop-stock ${cls}"><i class="fas fa-cubes" style="font-size:10px;"></i> ${av} ${esc(unit)}</span>
            `;
        } else {
            div.innerHTML = `
                <div class="pdrop-thumb">
                    ${p.image ? `<img src="${IMG_BASE}${esc(p.image)}">` : '<i class="fas fa-box"></i>'}
                </div>
                <div style="flex:1;min-width:0;">
                    <div class="pdrop-name">${esc(p.name)}</div>
                    ${p.sku || p.category_name ? `<div class="pdrop-meta">${p.sku ? 'SKU: '+esc(p.sku) : ''}${p.category_name ? ' · '+esc(p.category_name) : ''}</div>` : ''}
                </div>
                <span class="pdrop-stock ${cls}">${av} units</span>
            `;
        }
        div.addEventListener('mousedown', e => { e.preventDefault(); pickItem(id, p); });
        drop.appendChild(div);
    });
    drop.classList.add('open');
    openDropId = id;
}

function closeDrop(id) {
    const drop = document.getElementById('pdrop-'+id);
    if (drop) drop.classList.remove('open');
    if (openDropId === id) openDropId = null;
}

function pickItem(id, p) {
    const av   = parseFloat(p.available ?? 0);
    const unit = p.unit || 'units';
    const tr   = document.getElementById('ln-'+id);
    tr.dataset.itemId   = p.id;
    tr.dataset.itemName = p.name;
    tr.dataset.avail    = av;
    tr.dataset.unit     = unit;

    const inp = document.getElementById('pinp-'+id);
    if (inp) inp.value = p.name;

    const sku = document.getElementById('psku-'+id);
    if (sku) {
        const meta = (!IS_COFFEE && p.sku) ? 'SKU: ' + p.sku : '';
        sku.textContent = meta;
        sku.style.display = meta ? 'block' : 'none';
    }

    const avBadge = document.getElementById('pavail-'+id);
    if (avBadge) {
        avBadge.className = 'avail ' + (av > 10 ? 's-ok' : av > 0 ? 's-low' : 's-nil');
        avBadge.textContent = av + ' ' + unit;
    }

    const qInp = document.getElementById('pqty-'+id);
    if (qInp) {
        const minStep = IS_COFFEE ? 0.001 : 1;
        qInp.step = IS_COFFEE ? '0.001' : '1';
        if (parseFloat(qInp.value) > av) qInp.value = av > 0 ? av : minStep;
    }

    closeDrop(id);
}

function clampQty(id) {
    const tr   = document.getElementById('ln-'+id);
    const av   = parseFloat(tr?.dataset.avail ?? 0);
    const inp  = document.getElementById('pqty-'+id);
    if (!inp) return;
    let v = parseFloat(inp.value)||0.001;
    if (v <= 0) v = IS_COFFEE ? 0.001 : 1;
    if (av > 0 && v > av) v = av;
    inp.value = IS_COFFEE ? parseFloat(v.toFixed(3)) : Math.round(v);
}

// ── Validate Transfer ──────────────────────────────────────────────────────
function validateTransfer() {
    const fromId = gFrom(), toId = gTo();
    if (fromId === toId) { toast('Source and destination must be different.','err'); return; }

    const lines = []; let err = false;
    document.querySelectorAll('#linesBody tr').forEach(tr => {
        const id     = tr.id.replace('ln-','');
        const itemId = tr.dataset.itemId;
        if (!itemId) return;
        const itemName = tr.dataset.itemName || document.getElementById('pinp-'+id)?.value || '';
        const qty      = parseFloat(document.getElementById('pqty-'+id)?.value)||0;
        const av       = parseFloat(tr.dataset.avail??0);
        const unit     = tr.dataset.unit || 'units';
        if (qty <= 0)  { toast('Quantity must be > 0.','err'); err=true; return; }
        if (qty > av)  { toast(`Not enough stock (available: ${av} ${unit}).`,'err'); err=true; return; }
        lines.push({item_id: itemId, item_name: itemName, qty, unit});
    });
    if (err) return;
    if (!lines.length) { toast('Add at least one ' + ITEM_LABEL.toLowerCase() + ' line.','err'); return; }

    const btns = [document.getElementById('validateBtn'), document.getElementById('validateBtnB')];
    btns.forEach(b=>{ if(b){b.disabled=true; b.innerHTML='<i class="fas fa-spinner fa-spin"></i> Validating…';} });

    const ref = document.getElementById('refInput').value.trim();
    const note = document.getElementById('noteInput').value.trim();

    const fd = new FormData();
    fd.append('ajax_validate_transfer','1');
    fd.append('from_store_id', fromId);
    fd.append('to_store_id',   toId);
    fd.append('reference',     ref);
    fd.append('note',          note);
    lines.forEach((l,i)=>{ fd.append(`lines[${i}][item_id]`,l.item_id); fd.append(`lines[${i}][qty]`,l.qty); });

    fetch(API_URL,{method:'POST',body:fd})
        .then(r=>r.json())
        .then(data=>{
            if (data.success) {
                lastTransferData = {
                    ref:       data.reference,
                    fromName:  STORES_MAP[fromId] || 'Source Store',
                    toName:    STORES_MAP[toId]   || 'Destination Store',
                    date:      new Date().toLocaleString(),
                    note:      note || 'Internal Stock Transfer',
                    lines:     lines
                };

                document.getElementById('successRef').textContent   = data.reference;
                document.getElementById('successLines').textContent = data.lines + ' line(s) transferred';
                document.getElementById('statusBadge').className    = 'doc-status status-done';
                document.getElementById('statusBadge').innerHTML    = '<i class="fas fa-circle" style="font-size:7px;"></i> Done';
                document.getElementById('successOverlay').classList.add('open');
            } else {
                toast(data.error||'Transfer failed','err');
                btns.forEach(b=>{ if(b){b.disabled=false; b.innerHTML='<i class="fas fa-check-double"></i> Validate Transfer';} });
            }
        })
        .catch(()=>{
            toast('Network error. Please try again.','err');
            btns.forEach(b=>{ if(b){b.disabled=false; b.innerHTML='<i class="fas fa-check-double"></i> Validate Transfer';} });
        });
}

// ── Discard Modal ─────────────────────────────────────────────────────────
function discardTransfer() {
    document.getElementById('discardModal').classList.add('open');
}
function closeDiscardModal() {
    document.getElementById('discardModal').classList.remove('open');
}
function confirmDiscardTransfer() {
    closeDiscardModal();
    document.getElementById('linesBody').innerHTML = '';
    lineCounter = 0; countLines();
    document.getElementById('refInput').value='';
    document.getElementById('noteInput').value='';
    document.getElementById('statusBadge').className='doc-status status-draft';
    document.getElementById('statusBadge').innerHTML='<i class="fas fa-circle" style="font-size:7px;"></i> Draft';
    addLine();
    toast('Transfer form reset','ok');
}

function newTransfer() {
    document.getElementById('successOverlay').classList.remove('open');
    setTimeout(()=>location.reload(),150);
}

// ── Print Transfer Voucher ────────────────────────────────────────────────
function printTransferReceipt() {
    if (!lastTransferData) return;
    fillAndPrintVoucher(
        lastTransferData.ref,
        lastTransferData.fromName,
        lastTransferData.toName,
        lastTransferData.note,
        lastTransferData.date,
        lastTransferData.lines
    );
}

function printHistoryRecord(itemName, storeName, type, qtyDisplay, note, date) {
    fillAndPrintVoucher(
        'TRF-HIST',
        type === 'Transfer In' ? 'External / Source' : storeName,
        type === 'Transfer In' ? storeName : 'Destination Store',
        note,
        date,
        [{ item_name: itemName, qtyDisplay: qtyDisplay }]
    );
}

function fillAndPrintVoucher(ref, fromName, toName, note, date, lines) {
    document.getElementById('printVoucherRef').textContent  = ref;
    document.getElementById('printVoucherFrom').textContent = fromName;
    document.getElementById('printVoucherTo').textContent   = toName;
    document.getElementById('printVoucherNote').textContent = note || '—';
    document.getElementById('printVoucherDate').textContent = 'Date: ' + date;

    const tbody = document.getElementById('printVoucherBody');
    tbody.innerHTML = '';
    lines.forEach((l, i) => {
        const qtyTxt = l.qtyDisplay ? l.qtyDisplay : (l.qty + ' ' + (l.unit || ''));
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td style="text-align:center;">${i+1}</td>
            <td style="font-weight:700;">${esc(l.item_name || 'Item')}</td>
            <td style="text-align:center; font-weight:800;">${esc(qtyTxt)}</td>
        `;
        tbody.appendChild(tr);
    });

    window.print();
}

// ── Toast ─────────────────────────────────────────────────────────────────
let tTimer;
function toast(msg, type='err') {
    const el = document.getElementById('toastEl');
    el.className = 'toast show '+type;
    document.getElementById('toastMsg').textContent = msg;
    clearTimeout(tTimer);
    tTimer = setTimeout(()=>el.classList.remove('show'), 4000);
}

function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

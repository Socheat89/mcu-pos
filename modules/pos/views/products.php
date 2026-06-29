<?php
require_once __DIR__ . '/../../../core/helpers/url.php';
$urlPrefix = mc_base_path();

// Flash message from previous import action
$importFlash = null;
if (!empty($_SESSION['product_import_flash'])) {
    $importFlash = $_SESSION['product_import_flash'];
    unset($_SESSION['product_import_flash']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('products'); ?> - <?php echo htmlspecialchars($tenantName ?? 'POS'); ?></title>
    <link href="<?php echo mc_base_path(); ?>/public/css/pos_template.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Outfit:wght@300;400;500;600;700;800;900&family=Battambang:wght@100;300;400;700;900&display=swap" rel="stylesheet">
    <style>
        body, h1, h2, h3, h4, h5, h6, p, span, a, button, input, select, textarea {
            font-family: 'Battambang', 'Outfit', 'Inter', sans-serif !important;
        }
        .search-container { position: relative; margin-bottom: 24px; }
        .search-container i { position: absolute; left: 20px; top: 16px; color: var(--pos-primary); font-size: 18px; }
        .search-container input { width: 100%; padding: 14px 20px 14px 54px; border-radius: var(--pos-radius); border: 1.5px solid var(--pos-border); background: #ffffff; color: var(--pos-text); font-size: 15px; font-weight: 600; outline: none; transition: all 0.3s; }
        .search-container input:focus { border-color: var(--pos-primary); background: #ffffff; box-shadow: 0 0 0 4px rgba(var(--pos-primary-rgb), 0.15); }
        
        .product-img { width: 44px; height: 44px; border-radius: var(--pos-radius); background: #ffffff; display: grid; place-items: center; overflow: hidden; border: 1px solid var(--pos-border); }

        /* Import Panel */
        .import-panel {
            background: var(--pos-card);
            border: 1px solid var(--pos-border);
            border-radius: var(--pos-radius-xl);
            margin-bottom: 28px;
            overflow: hidden;
        }
        .import-panel__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 24px;
            cursor: pointer;
            transition: background 0.2s;
            user-select: none;
        }
        .import-panel__header:hover { background: rgba(var(--pos-primary-rgb), 0.03); }
        .import-panel__header-left { display: flex; align-items: center; gap: 12px; }
        .import-panel__icon { width: 40px; height: 40px; border-radius: 10px; background: rgba(var(--pos-primary-rgb), 0.1); color: var(--pos-primary); display: grid; place-items: center; font-size: 16px; flex-shrink: 0; }
        .import-panel__title { font-weight: 800; font-size: 15px; color: var(--pos-text); margin: 0; }
        .import-panel__subtitle { font-size: 12px; color: var(--pos-text-muted); font-weight: 600; margin: 2px 0 0; }
        .import-panel__chevron { font-size: 14px; color: var(--pos-text-muted); transition: transform 0.3s; }
        .import-panel.open .import-panel__chevron { transform: rotate(180deg); }
        .import-panel__body { display: none; padding: 0 24px 24px; border-top: 1px solid var(--pos-border); }
        .import-panel.open .import-panel__body { display: block; }

        .import-tabs { display: flex; gap: 8px; margin: 20px 0 16px; }
        .import-tab {
            display: flex; align-items: center; gap: 8px;
            padding: 10px 18px; border-radius: var(--pos-radius);
            border: 1.5px solid var(--pos-border);
            background: transparent;
            color: var(--pos-text-muted);
            font-size: 13px; font-weight: 700;
            cursor: pointer; transition: all 0.2s;
        }
        .import-tab.active {
            border-color: var(--pos-primary);
            background: var(--pos-primary-light);
            color: var(--pos-primary);
        }
        .import-tab:hover:not(.active) {
            border-color: rgba(var(--pos-primary-rgb), 0.3);
            color: var(--pos-primary);
        }

        .import-pane { display: none; }
        .import-pane.active { display: block; }

        .import-upload-zone {
            border: 2px dashed var(--pos-border);
            border-radius: var(--pos-radius);
            padding: 32px 24px;
            text-align: center;
            cursor: pointer;
            transition: all 0.25s;
            background: rgba(var(--pos-primary-rgb), 0.01);
            position: relative;
        }
        .import-upload-zone:hover,
        .import-upload-zone.dragging {
            border-color: var(--pos-primary);
            background: var(--pos-primary-light);
        }
        .import-upload-zone input[type="file"] {
            position: absolute; inset: 0; opacity: 0; width: 100%; height: 100%; cursor: pointer;
        }
        .import-upload-icon { font-size: 32px; color: var(--pos-primary); margin-bottom: 12px; }
        .import-upload-title { font-weight: 800; font-size: 15px; color: var(--pos-text); margin: 0 0 6px; }
        .import-upload-hint { font-size: 12px; color: var(--pos-text-muted); font-weight: 600; margin: 0; }
        .import-file-name { margin-top: 10px; font-size: 13px; font-weight: 700; color: var(--pos-primary); display: none; }
        .import-file-name.visible { display: block; }

        .import-sheet-input {
            width: 100%; padding: 13px 18px;
            border-radius: var(--pos-radius);
            border: 1.5px solid var(--pos-border);
            background: var(--pos-bg);
            color: var(--pos-text);
            font-size: 14px; font-weight: 600;
            outline: none; transition: all 0.25s;
            box-sizing: border-box;
        }
        .import-sheet-input:focus {
            border-color: var(--pos-primary);
            box-shadow: 0 0 0 4px rgba(var(--pos-primary-rgb), 0.12);
        }

        .import-help {
            margin-top: 12px; padding: 12px 16px;
            background: rgba(var(--pos-primary-rgb), 0.04);
            border: 1px solid rgba(var(--pos-primary-rgb), 0.12);
            border-radius: var(--pos-radius);
            font-size: 12px; font-weight: 600; color: var(--pos-text-muted);
            line-height: 1.7;
        }
        .import-help a { color: var(--pos-primary); font-weight: 700; text-decoration: none; }
        .import-help a:hover { text-decoration: underline; }

        .import-actions { display: flex; align-items: center; gap: 12px; margin-top: 16px; flex-wrap: wrap; }
        .import-btn-submit {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 11px 24px;
            border-radius: var(--pos-radius);
            background: var(--pos-primary);
            color: white;
            font-size: 14px; font-weight: 800;
            border: none; cursor: pointer; transition: all 0.2s;
        }
        .import-btn-submit:hover { opacity: 0.88; transform: translateY(-1px); }
        .import-btn-template {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 11px 18px;
            border-radius: var(--pos-radius);
            background: transparent;
            color: var(--pos-text-muted);
            font-size: 13px; font-weight: 700;
            border: 1.5px solid var(--pos-border); cursor: pointer;
            text-decoration: none; transition: all 0.2s;
        }
        .import-btn-template:hover { border-color: var(--pos-primary); color: var(--pos-primary); }

        /* Flash message */
        .import-flash {
            padding: 16px 20px; border-radius: var(--pos-radius);
            margin-bottom: 24px; font-size: 14px; font-weight: 700;
            display: flex; align-items: flex-start; gap: 12px;
            border: 1px solid;
        }
        .import-flash.success { background: rgba(34,197,94,0.08); border-color: rgba(34,197,94,0.25); color: #15803d; }
        .import-flash.warning { background: rgba(234,179,8,0.08); border-color: rgba(234,179,8,0.25); color: #92400e; }
        .import-flash.danger { background: rgba(239,68,68,0.08); border-color: rgba(239,68,68,0.25); color: #991b1b; }
        .import-flash i { font-size: 18px; flex-shrink: 0; margin-top: 1px; }
        .import-flash-details { margin-top: 8px; padding: 8px 12px; background: rgba(0,0,0,0.04); border-radius: 8px; font-size: 12px; line-height: 1.6; }
    </style>
</head>
<body class="pos-app">
    <?php $activeNav = 'products'; include __DIR__ . '/partials/navbar.php'; ?>
    
    <div class="fade-in">
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
            <div class="pos-title">
                <h1><?php echo __('products'); ?></h1>
                <p><?php echo __('track_stock_msg'); ?></p>
            </div>
            <a href="<?php echo htmlspecialchars($posUrl('products/create')); ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> <?php echo __('add'); ?>
            </a>
        </div>

        <!-- Import Flash Message -->
        <?php if ($importFlash): ?>
        <div class="import-flash <?php echo htmlspecialchars($importFlash['type']); ?>">
            <i class="fas fa-<?php echo $importFlash['type'] === 'success' ? 'circle-check' : ($importFlash['type'] === 'warning' ? 'triangle-exclamation' : 'circle-xmark'); ?>"></i>
            <div>
                <div><?php echo htmlspecialchars($importFlash['title'] ?? ''); ?></div>
                <div style="font-weight: 600; opacity: 0.8; margin-top: 3px;"><?php echo htmlspecialchars($importFlash['message'] ?? ''); ?></div>
                <?php if (!empty($importFlash['details'])): ?>
                <div class="import-flash-details">
                    <?php foreach ($importFlash['details'] as $err): ?>
                    <div>⚠ <?php echo htmlspecialchars($err); ?></div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="pos-grid cols-4" style="margin-bottom: 28px;">
            <div class="pos-stat">
                <span class="k"><?php echo __('total_skus'); ?></span>
                <p class="v"><?php echo count($products); ?></p>
                <div class="chip" style="background: rgba(99, 102, 241, 0.1); color: var(--pos-primary);"><i class="fas fa-box"></i></div>
            </div>
            <div class="pos-stat">
                <span class="k"><?php echo __('active_categories'); ?></span>
                <p class="v"><?php echo count(array_unique(array_column($products, 'category_id'))); ?></p>
                <div class="chip" style="background: rgba(139, 92, 246, 0.1); color: var(--pos-secondary);"><i class="fas fa-tags"></i></div>
            </div>
            <div class="pos-stat">
                <span class="k"><?php echo __('low_stock'); ?></span>
                <p class="v"><?php echo count(array_filter($products, fn($p) => (int)$p['stock_quantity'] > 0 && (int)$p['stock_quantity'] <= 10)); ?></p>
                <div class="chip" style="background: rgba(234,179,8,0.1); color: #eab308;"><i class="fas fa-triangle-exclamation"></i></div>
            </div>
            <div class="pos-stat">
                <span class="k"><?php echo __('out_of_stock'); ?></span>
                <p class="v"><?php echo count(array_filter($products, fn($p) => (int)$p['stock_quantity'] <= 0)); ?></p>
                <div class="chip" style="background: rgba(239,68,68,0.1); color: #ef4444;"><i class="fas fa-ban"></i></div>
            </div>
        </div>

        <!-- Import Panel -->
        <div class="import-panel" id="importPanel">
            <div class="import-panel__header" onclick="toggleImportPanel()">
                <div class="import-panel__header-left">
                    <div class="import-panel__icon">
                        <i class="fas fa-file-import"></i>
                    </div>
                    <div>
                        <p class="import-panel__title">Import Products</p>
                        <p class="import-panel__subtitle">Upload Excel/CSV file or connect from Google Sheets</p>
                    </div>
                </div>
                <i class="fas fa-chevron-down import-panel__chevron"></i>
            </div>

            <div class="import-panel__body">
                <!-- Tabs -->
                <div class="import-tabs">
                    <button class="import-tab active" onclick="switchImportTab('file', this)" id="tabFile">
                        <i class="fas fa-file-excel"></i> Excel / CSV File
                    </button>
                    <button class="import-tab" onclick="switchImportTab('sheet', this)" id="tabSheet">
                        <i class="fab fa-google-drive"></i> Google Sheets
                    </button>
                </div>

                <!-- File Upload Pane -->
                <div class="import-pane active" id="paneFile">
                    <form method="POST" action="<?php echo htmlspecialchars($posUrl('products/import')); ?>" enctype="multipart/form-data" id="importFileForm">
                        <input type="hidden" name="import_source" value="file">
                        <div class="import-upload-zone" id="uploadZone">
                            <input type="file" name="product_file" id="productFileInput" accept=".csv,.xlsx,.xls,.txt" onchange="handleFileSelect(this)" required>
                            <div class="import-upload-icon"><i class="fas fa-cloud-arrow-up"></i></div>
                            <p class="import-upload-title">Drag & Drop your file here</p>
                            <p class="import-upload-hint">Supports .xlsx, .xls, .csv — Max 10MB</p>
                            <div class="import-file-name" id="selectedFileName"></div>
                        </div>
                        <div class="import-help">
                            <strong>Required columns:</strong> <code>name</code>, <code>price</code>, <code>stock_quantity</code><br>
                            <strong>Optional columns:</strong> <code>sku</code>, <code>barcode</code>, <code>category</code>, <code>description</code>, <code>status</code><br>
                            Products with a matching SKU will be <strong>updated</strong> instead of duplicated.
                        </div>
                        <div class="import-actions">
                            <button type="submit" class="import-btn-submit">
                                <i class="fas fa-file-import"></i> Import Products
                            </button>
                            <a href="<?php echo htmlspecialchars($posUrl('products/template')); ?>" class="import-btn-template">
                                <i class="fas fa-download"></i> Download Template
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Google Sheets Pane -->
                <div class="import-pane" id="paneSheet">
                    <form method="POST" action="<?php echo htmlspecialchars($posUrl('products/import')); ?>" id="importSheetForm">
                        <input type="hidden" name="import_source" value="sheet">
                        <div style="margin-bottom: 12px;">
                            <label style="font-size:13px; font-weight:700; color:var(--pos-text-muted); display:block; margin-bottom:8px;">
                                Google Sheets URL
                            </label>
                            <input
                                type="url"
                                name="google_sheet_url"
                                class="import-sheet-input"
                                placeholder="https://docs.google.com/spreadsheets/d/..."
                                required
                            >
                        </div>
                        <div class="import-help">
                            <strong>How to connect Google Sheets:</strong><br>
                            1. Open your Google Sheet → <strong>File → Share → Publish to web</strong><br>
                            2. Select <strong>Sheet 1</strong> and <strong>Comma-separated values (.csv)</strong>, then click Publish<br>
                            3. Paste the published CSV link above (format: <code>docs.google.com/spreadsheets/d/.../pub?gid=...&output=csv</code>)<br><br>
                            Or share the sheet publicly and paste the regular URL — we'll auto-convert it.<br>
                            <strong>Required columns:</strong> <code>name</code>, <code>price</code>, <code>stock_quantity</code> (same as Excel import)
                        </div>
                        <div class="import-actions">
                            <button type="submit" class="import-btn-submit">
                                <i class="fab fa-google-drive"></i> Connect &amp; Import
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Search -->
        <div class="search-container">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="<?php echo __('search_product_placeholder'); ?>" onkeyup="searchProducts()">
        </div>

        <!-- Products Table -->
        <div class="pos-table-container">
            <table class="pos-table" id="productsTable">
                <thead>
                    <tr>
                        <th style="width: 60px;"><?php echo __('pic'); ?></th>
                        <th><?php echo __('products'); ?></th>
                        <th><?php echo __('status'); ?></th>
                        <th><?php echo __('price'); ?></th>
                        <th style="text-align: right;"><?php echo __('actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="5" style="padding: 100px; text-align: center;">
                                <div style="width: 80px; height: 80px; background: rgba(255,255,255,0.03); border: 1px solid var(--pos-border); border-radius: 50%; display: grid; place-items: center; margin: 0 auto 20px;">
                                    <i class="fas fa-box-open" style="font-size: 32px; color: var(--pos-text-dim);"></i>
                                </div>
                                <h3 style="color: var(--pos-text); font-weight: 800; margin: 0;"><?php echo __('no_products_found'); ?></h3>
                                <p style="color: var(--pos-text-muted); margin-top: 8px;"><?php echo __('start_adding_products'); ?></p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $p): 
                            $stock = (int)$p['stock_quantity'];
                            $badge = 'badge-success';
                            if ($stock <= 0) $badge = 'badge-danger';
                            elseif ($stock <= 10) $badge = 'badge-warning';
                        ?>
                            <tr class="product-row">
                                <td>
                                    <div class="product-img">
                                        <?php if (!empty($p['image'])): ?>
                                            <img src="<?php echo htmlspecialchars(mc_url('uploads/products/' . $p['image'])); ?>" style="width:100%; height:100%; object-fit:cover;">
                                        <?php else: ?>
                                            <i class="fas fa-image" style="color: #cbd5e1; font-size: 18px;"></i>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 800; font-size: 15px; color: var(--pos-text);"><?php echo htmlspecialchars($p['name']); ?></div>
                                    <div style="font-size: 12px; font-weight: 600; color: var(--pos-text-muted); margin-top: 2px;">SKU: <?php echo htmlspecialchars($p['sku'] ?: 'N/A'); ?></div>
                                </td>
                                <td>
                                    <span class="badge <?php echo $badge; ?>">
                                        <?php echo __('in_stock_msg', ['count' => $stock]); ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight: 900; color: var(--pos-primary); font-size: 16px;">$<?php echo number_format($p['price'], 2); ?></div>
                                </td>
                                <td>
                                    <div style="display: flex; justify-content: flex-end; gap: 10px;">
                                        <a href="<?php echo htmlspecialchars($posUrl('products/' . $p['id'] . '/edit')); ?>" class="pos-icon-btn" title="Edit">
                                            <i class="fas fa-pencil-alt" style="font-size: 14px;"></i>
                                        </a>
                                        <a href="<?php echo htmlspecialchars($posUrl('products/' . $p['id'] . '/delete')); ?>" class="pos-icon-btn" style="color: var(--pos-danger);" data-pos-confirm="<?php echo __('confirm_delete_product'); ?>" title="Delete">
                                            <i class="fas fa-trash-alt" style="font-size: 14px;"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Search
        function searchProducts() {
            const filter = document.getElementById('searchInput').value.toUpperCase();
            const rows = document.querySelectorAll('.product-row');
            rows.forEach(row => {
                const text = row.innerText.toUpperCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        }

        // Import Panel Toggle
        function toggleImportPanel() {
            document.getElementById('importPanel').classList.toggle('open');
        }

        // Auto-open if we just had an import (flash message visible)
        <?php if ($importFlash): ?>
        document.getElementById('importPanel').classList.add('open');
        <?php endif; ?>

        // Import Tabs
        function switchImportTab(tab, btn) {
            document.querySelectorAll('.import-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.import-pane').forEach(p => p.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById('pane' + tab.charAt(0).toUpperCase() + tab.slice(1)).classList.add('active');
        }

        // File selection display
        function handleFileSelect(input) {
            const nameEl = document.getElementById('selectedFileName');
            if (input.files && input.files[0]) {
                nameEl.textContent = '📄 ' + input.files[0].name;
                nameEl.classList.add('visible');
            } else {
                nameEl.classList.remove('visible');
            }
        }

        // Drag and drop
        const uploadZone = document.getElementById('uploadZone');
        if (uploadZone) {
            uploadZone.addEventListener('dragover', e => { e.preventDefault(); uploadZone.classList.add('dragging'); });
            uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('dragging'));
            uploadZone.addEventListener('drop', e => {
                e.preventDefault();
                uploadZone.classList.remove('dragging');
                const fileInput = document.getElementById('productFileInput');
                if (e.dataTransfer.files.length > 0) {
                    fileInput.files = e.dataTransfer.files;
                    handleFileSelect(fileInput);
                }
            });
        }
    </script>
    
    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

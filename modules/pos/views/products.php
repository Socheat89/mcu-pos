<?php
require_once __DIR__ . '/../../../core/helpers/url.php';
$urlPrefix = mc_base_path();
$pageTitle = __('inventory');
$importFlash = $_SESSION['product_import_flash'] ?? null;
unset($_SESSION['product_import_flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('inventory'); ?> - <?php echo htmlspecialchars($tenantName ?? 'POS'); ?></title>
    <link href="<?php echo mc_base_path(); ?>/public/css/pos_template.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Outfit:wght@300;400;500;600;700;800;900&family=Battambang:wght@100;300;400;700;900&display=swap" rel="stylesheet">
    <style>
        .product-img { width: 48px; height: 48px; border-radius: 14px; background: #f1f5f9; display: grid; place-items: center; overflow: hidden; border: 1px solid var(--pos-border); }
        .product-import-toggle { padding: 14px 22px; border-radius: 18px; background: rgba(255,255,255,0.08); border-color: rgba(255,255,255,0.18); color: white; }
        .product-import-panel { background: white; border: 1.5px solid var(--pos-border); border-radius: 20px; padding: 24px; margin-bottom: 24px; box-shadow: var(--pos-shadow-sm); }
        .product-import-panel__head { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 20px; }
        .product-import-panel__title { margin: 0; color: var(--pos-text); font-size: 18px; font-weight: 900; }
        .product-import-panel__meta { margin: 4px 0 0; color: var(--pos-text-muted); font-size: 13px; font-weight: 600; line-height: 1.5; }
        .product-import-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; }
        .product-import-form { min-width: 0; }
        .product-import-form + .product-import-form { border-left: 1.5px solid var(--pos-border); padding-left: 20px; }
        .product-import-form h3 { display: flex; align-items: center; gap: 10px; margin: 0 0 16px; color: var(--pos-text); font-size: 14px; font-weight: 900; }
        .product-import-actions { display: flex; align-items: center; justify-content: flex-end; gap: 12px; margin-top: 16px; }
        .product-import-alert { display: flex; gap: 14px; align-items: flex-start; border: 1.5px solid var(--pos-border); border-radius: 18px; background: white; padding: 18px 20px; margin-bottom: 24px; box-shadow: var(--pos-shadow-sm); }
        .product-import-alert__icon { width: 36px; height: 36px; border-radius: 12px; display: grid; place-items: center; flex: 0 0 auto; }
        .product-import-alert h3 { margin: 0; color: var(--pos-text); font-size: 15px; font-weight: 900; }
        .product-import-alert p { margin: 4px 0 0; color: var(--pos-text-muted); font-size: 13px; font-weight: 700; line-height: 1.5; }
        .product-import-alert ul { margin: 10px 0 0; padding-left: 18px; color: var(--pos-text-muted); font-size: 12px; font-weight: 600; line-height: 1.6; }
        .product-import-alert--success .product-import-alert__icon { background: #dcfce7; color: #15803d; }
        .product-import-alert--warning .product-import-alert__icon { background: #fef3c7; color: #b45309; }
        .product-import-alert--danger .product-import-alert__icon { background: #fee2e2; color: #be123c; }
        @media (max-width: 860px) {
            .product-import-panel__head { flex-direction: column; }
            .product-import-grid { grid-template-columns: 1fr; }
            .product-import-form + .product-import-form { border-left: 0; border-top: 1.5px solid var(--pos-border); padding-left: 0; padding-top: 20px; }
            .product-import-actions { justify-content: stretch; flex-wrap: wrap; }
            .product-import-actions .btn { flex: 1 1 auto; }
        }
    </style>
</head>
<body class="pos-app">
    <?php $activeNav = 'products'; include __DIR__ . '/partials/navbar.php'; ?>
    
    <div class="fade-in">
        <div class="pos-page-hero">
            <div style="position: relative; z-index: 2;">
                <div class="pos-kicker"><i class="fas fa-boxes-stacked"></i> <?php echo __('inventory'); ?></div>
                <h1 class="pos-page-hero__title" style="margin-top: 14px;"><?php echo __('inventory'); ?></h1>
                <p class="pos-page-hero__text"><?php echo __('track_stock_msg'); ?></p>
                <div class="pos-page-hero__actions">
                    <a href="<?php echo htmlspecialchars($posUrl('products/create')); ?>" class="btn btn-primary" style="padding: 14px 22px; border-radius: 18px;">
                        <i class="fas fa-plus"></i> <?php echo __('add'); ?>
                    </a>
                    <button type="button" class="btn btn-outline product-import-toggle" onclick="toggleProductImport()">
                        <i class="fas fa-file-import"></i> <?php echo __('import_products'); ?>
                    </button>
                    <a href="<?php echo htmlspecialchars($posUrl('pos')); ?>" class="btn btn-outline" style="padding: 14px 22px; border-radius: 18px; background: rgba(255,255,255,0.08); border-color: rgba(255,255,255,0.18); color: white;">
                        <i class="fas fa-desktop"></i> <?php echo __('open_terminal'); ?>
                    </a>
                </div>
            </div>
        </div>

        <?php if (!empty($importFlash)): ?>
            <div class="product-import-alert product-import-alert--<?php echo htmlspecialchars($importFlash['type'] ?? 'success'); ?>">
                <div class="product-import-alert__icon">
                    <i class="fas <?php echo (($importFlash['type'] ?? '') === 'danger') ? 'fa-triangle-exclamation' : ((($importFlash['type'] ?? '') === 'warning') ? 'fa-circle-info' : 'fa-circle-check'); ?>"></i>
                </div>
                <div>
                    <h3><?php echo htmlspecialchars($importFlash['title'] ?? ''); ?></h3>
                    <p><?php echo htmlspecialchars($importFlash['message'] ?? ''); ?></p>
                    <?php if (!empty($importFlash['details'])): ?>
                        <ul>
                            <?php foreach ($importFlash['details'] as $detail): ?>
                                <li><?php echo htmlspecialchars($detail); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="product-import-panel" id="productImportPanel" style="<?php echo !empty($importFlash) ? '' : 'display:none;'; ?>">
            <div class="product-import-panel__head">
                <div>
                    <h2 class="product-import-panel__title"><?php echo __('import_products'); ?></h2>
                    <p class="product-import-panel__meta"><?php echo __('product_import_sample_headers'); ?></p>
                </div>
                <a href="<?php echo htmlspecialchars($posUrl('products/template')); ?>" class="btn btn-outline" style="padding: 10px 16px; border-radius: 14px;">
                    <i class="fas fa-download"></i> <?php echo __('download_template'); ?>
                </a>
            </div>
            <div class="product-import-grid">
                <form class="product-import-form" action="<?php echo htmlspecialchars($posUrl('products/import')); ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="import_source" value="file">
                    <h3><i class="fas fa-file-excel"></i> <?php echo __('import_from_excel'); ?></h3>
                    <div class="pos-form-group">
                        <label class="pos-form-label"><?php echo __('excel_file'); ?></label>
                        <input type="file" name="product_file" class="pos-form-control" accept=".csv,.xlsx,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
                    </div>
                    <p class="product-import-panel__meta"><?php echo __('excel_import_hint'); ?></p>
                    <div class="product-import-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> <?php echo __('import_now'); ?>
                        </button>
                    </div>
                </form>

                <form class="product-import-form" action="<?php echo htmlspecialchars($posUrl('products/import')); ?>" method="POST">
                    <input type="hidden" name="import_source" value="sheet">
                    <h3><i class="fab fa-google-drive"></i> <?php echo __('connect_google_sheet'); ?></h3>
                    <div class="pos-form-group">
                        <label class="pos-form-label"><?php echo __('google_sheet_url'); ?></label>
                        <input type="url" name="google_sheet_url" class="pos-form-control" placeholder="https://docs.google.com/spreadsheets/d/..." required>
                    </div>
                    <p class="product-import-panel__meta"><?php echo __('google_sheet_hint'); ?></p>
                    <div class="product-import-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-link"></i> <?php echo __('connect_import'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="pos-grid cols-4" style="margin-bottom: 32px;">
            <div class="pos-stat">
                <span class="k"><?php echo __('total_skus'); ?></span>
                <p class="v"><?php echo count($products); ?></p>
                <div class="chip" style="background: rgba(99, 102, 241, 0.1); color: var(--pos-primary);"><i class="fas fa-box"></i></div>
            </div>
            <div class="pos-stat">
                <span class="k"><?php echo __('active_categories'); ?></span>
                <p class="v"><?php echo count($categories); ?></p>
                <div class="chip" style="background: rgba(139, 92, 246, 0.1); color: var(--pos-secondary);"><i class="fas fa-tags"></i></div>
            </div>
        </div>

        <div class="pos-input-shell" style="margin-bottom: 24px; max-width: 560px;">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="<?php echo __('search_product_placeholder'); ?>" onkeyup="searchProducts()">
        </div>

        <div class="pos-panel">
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
                            <td colspan="5" class="pos-empty-state">
                                <div class="pos-empty-state__icon">
                                    <i class="fas fa-box-open" style="font-size: 30px;"></i>
                                </div>
                                <h3 style="color: var(--pos-text); font-weight: 900; margin: 0;"><?php echo __('no_products_found'); ?></h3>
                                <p style="color: var(--pos-text-muted); margin-top: 8px; font-weight: 600;"><?php echo __('start_adding_products'); ?></p>
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
                                    <div style="font-weight: 900; font-size: 15px; color: var(--pos-text);"><?php echo htmlspecialchars($p['name']); ?></div>
                                    <div style="font-size: 12px; font-weight: 600; color: var(--pos-text-muted); margin-top: 3px;">SKU: <?php echo htmlspecialchars($p['sku'] ?: 'N/A'); ?></div>
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
        function searchProducts() {
            const filter = document.getElementById('searchInput').value.toUpperCase();
            const rows = document.querySelectorAll('.product-row');
            rows.forEach(row => {
                const text = row.innerText.toUpperCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        }

        function toggleProductImport() {
            const panel = document.getElementById('productImportPanel');
            if (!panel) return;
            const isHidden = panel.style.display === 'none';
            panel.style.display = isHidden ? '' : 'none';
            if (isHidden) panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    </script>
    
    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

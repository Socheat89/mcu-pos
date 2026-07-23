<?php
require_once __DIR__ . '/../../../core/helpers/url.php';
$urlPrefix = mc_base_path();

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
        body, h1, h2, h3, h4, h5, h6, p, span, a, button, input, select, textarea {
            font-family: 'Battambang', 'Outfit', 'Inter', sans-serif !important;
        }
        .search-container { position: relative; margin-bottom: 24px; }
        .search-container i { position: absolute; left: 20px; top: 16px; color: var(--pos-primary); font-size: 18px; }
        .search-container input { width: 100%; padding: 14px 20px 14px 54px; border-radius: var(--pos-radius); border: 1.5px solid var(--pos-border); background: #ffffff; color: var(--pos-text); font-size: 15px; font-weight: 600; outline: none; transition: all 0.3s; }
        .search-container input:focus { border-color: var(--pos-primary); background: #ffffff; box-shadow: 0 0 0 4px rgba(var(--pos-primary-rgb), 0.15); }
        
        .product-img { width: 44px; height: 44px; border-radius: var(--pos-radius); background: #ffffff; display: grid; place-items: center; overflow: hidden; border: 1px solid var(--pos-border); }

        /* ─── Inventory Dropdown ─── */
        .inventory-dropdown {
            position: absolute; top: 100%; right: 0; margin-top: 8px;
            background: #ffffff; border: 1.5px solid var(--pos-border);
            border-radius: 14px; box-shadow: 0 12px 40px rgba(0,0,0,0.12);
            min-width: 200px; z-index: 100; overflow: hidden;
            animation: fadeInDown 0.2s ease-out;
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .inventory-dropdown-item {
            display: flex; align-items: center; gap: 10px;
            width: 100%; padding: 12px 18px; font-size: 14px; font-weight: 700;
            color: var(--pos-text); text-decoration: none; background: none; border: none;
            cursor: pointer; transition: all 0.15s; text-align: left;
        }
        .inventory-dropdown-item:hover {
            background: rgba(99,102,241,0.06); color: var(--pos-primary);
        }
        .inventory-dropdown-item:not(:last-child) {
            border-bottom: 1px solid var(--pos-border);
        }
        .inventory-dropdown-item i { width: 18px; text-align: center; color: var(--pos-primary); }

        /* ─── Category Modal ─── */
        .category-modal-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px); z-index: 9999;
            display: flex; align-items: center; justify-content: center;
            animation: fadeIn 0.2s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; } to { opacity: 1; }
        }
        .category-modal {
            background: #ffffff; border-radius: 20px; width: 90%; max-width: 440px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25); overflow: hidden;
            animation: scaleIn 0.2s ease-out;
        }
        @keyframes scaleIn {
            from { transform: scale(0.95); opacity: 0; }
            to   { transform: scale(1); opacity: 1; }
        }
        .category-modal-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 20px 24px; border-bottom: 1px solid var(--pos-border);
        }
        .category-modal-header h3 {
            margin: 0; font-size: 16px; font-weight: 900; color: var(--pos-text);
            display: flex; align-items: center; gap: 8px;
        }
        .category-modal-close {
            width: 32px; height: 32px; border-radius: 50%; border: 1px solid var(--pos-border);
            background: #fff; font-size: 18px; cursor: pointer; color: var(--pos-text-muted);
            display: flex; align-items: center; justify-content: center; transition: all 0.2s;
        }
        .category-modal-close:hover { background: #fee2e2; color: #ef4444; border-color: #fecaca; }
        .category-modal-body { padding: 24px; }
        .category-modal-footer {
            display: flex; justify-content: flex-end; gap: 10px;
            padding: 16px 24px; border-top: 1px solid var(--pos-border);
            background: #f9fafb;
        }

    </style>
</head>
<body class="pos-app">
    <?php $activeNav = 'products'; include __DIR__ . '/partials/navbar.php'; ?>
    
    <div class="fade-in">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
            <div class="pos-title">
                <h1><?php echo __('inventory'); ?></h1>
                <p><?php echo __('track_stock_msg'); ?></p>
            </div>
            <div style="position: relative;">
                <button class="btn btn-primary" onclick="toggleInventoryDropdown()" style="display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-plus"></i> <?php echo __('add_new'); ?>
                    <i class="fas fa-chevron-down" style="font-size: 10px; margin-left: 4px;"></i>
                </button>
                <div id="inventory-dropdown" class="inventory-dropdown" style="display: none;">
                    <a href="<?php echo htmlspecialchars($posUrl('products/create')); ?>" class="inventory-dropdown-item">
                        <i class="fas fa-box-open"></i> <?php echo __('add_product'); ?>
                    </a>
                    <button type="button" class="inventory-dropdown-item" onclick="openCategoryModal()">
                        <i class="fas fa-tags"></i> <?php echo __('add_category'); ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- Category Modal -->
        <div id="category-modal-overlay" class="category-modal-overlay" style="display: none;" onclick="closeCategoryModal(event)">
            <div class="category-modal" onclick="event.stopPropagation()">
                <div class="category-modal-header">
                    <h3><i class="fas fa-tags"></i> <?php echo __('add_category'); ?></h3>
                    <button type="button" class="category-modal-close" onclick="closeCategoryModal()">&times;</button>
                </div>
                <form method="POST" action="<?php echo htmlspecialchars($posUrl('products/createCategory')); ?>">
                    <div class="category-modal-body">
                        <label class="pos-form-label"><?php echo __('category_name'); ?></label>
                        <input type="text" name="category_name" class="pos-form-control" placeholder="<?php echo __('category_name_placeholder'); ?>" required autofocus>
                        <?php if (!empty($categories)): ?>
                        <div style="margin-top: 16px;">
                            <label class="pos-form-label" style="font-size: 11px; color: var(--pos-text-muted);"><?php echo __('existing_categories'); ?></label>
                            <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px;">
                                <?php foreach ($categories as $cat): ?>
                                <span style="background: rgba(99,102,241,0.06); padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; color: var(--pos-primary);"><?php echo htmlspecialchars($cat['name']); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="category-modal-footer">
                        <button type="button" class="btn btn-outline" onclick="closeCategoryModal()"><?php echo __('cancel'); ?></button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> <?php echo __('save_category'); ?>
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

        <div class="search-container">

            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="<?php echo __('search_product_placeholder'); ?>" onkeyup="searchProducts()">
        </div>

        <div class="pos-table-container">

            <table class="pos-table" id="productsTable">
                <thead>
                    <tr>
                        <th style="width: 60px;"><?php echo __('pic'); ?></th>
                        <th><?php echo __('products'); ?></th>
                        <th><?php echo __('sizes'); ?></th>
                        <th><?php echo __('status'); ?></th>
                        <th><?php echo __('cost'); ?></th>
                        <th><?php echo __('price'); ?></th>
                        <th style="text-align: right;"><?php echo __('actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="7" style="padding: 100px; text-align: center;">
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
                                    <?php if (!empty($p['sizes'])): ?>
                                        <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                            <?php foreach ($p['sizes'] as $sz): ?>
                                                <span style="display: inline-block; background: rgba(99, 102, 241, 0.08); color: var(--pos-primary); padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 700; white-space: nowrap;">
                                                    <?php echo htmlspecialchars($sz['size_name']); ?>: $<?php echo number_format($sz['price'], 2); ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span style="font-size: 12px; color: var(--pos-text-muted); font-weight: 500;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo $badge; ?>">
                                        <?php echo __('in_stock_msg', ['count' => $stock]); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php $cost = (float)($p['cost_price'] ?? 0); ?>
                                    <div style="font-weight: 600; color: var(--pos-text-muted); font-size: 13px;">
                                        <?php if ($cost > 0): ?>
                                            $<?php echo number_format($cost, 2); ?>
                                            <?php $margin = (float)$p['price'] - $cost; ?>
                                            <span style="font-size: 10px; color: <?php echo $margin > 0 ? '#10b981' : '#ef4444'; ?>; margin-left: 4px;">
                                                (<?php echo $margin > 0 ? '+' : ''; ?><?php echo number_format($margin, 2); ?>)
                                            </span>
                                        <?php else: ?>
                                            <span style="color: #cbd5e1;">—</span>
                                        <?php endif; ?>
                                    </div>
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

        // ─── Inventory Dropdown ─────────────────────────────
        function toggleInventoryDropdown() {
            const dd = document.getElementById('inventory-dropdown');
            dd.style.display = dd.style.display === 'none' ? 'block' : 'none';
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const dd = document.getElementById('inventory-dropdown');
            const btn = e.target.closest('.btn-primary');
            if (dd && dd.style.display === 'block' && !btn) {
                dd.style.display = 'none';
            }
        });

        // ─── Category Modal ─────────────────────────────────
        function openCategoryModal() {
            document.getElementById('inventory-dropdown').style.display = 'none';
            document.getElementById('category-modal-overlay').style.display = 'flex';
            // Focus the input
            setTimeout(() => {
                const input = document.querySelector('#category-modal-overlay input[name="category_name"]');
                if (input) input.focus();
            }, 100);
        }

        function closeCategoryModal(e) {
            if (e && e.target !== document.getElementById('category-modal-overlay')) return;
            document.getElementById('category-modal-overlay').style.display = 'none';
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.getElementById('category-modal-overlay').style.display = 'none';
                document.getElementById('inventory-dropdown').style.display = 'none';
            }
        });
    </script>
    
    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

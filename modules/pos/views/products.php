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
            <a href="<?php echo htmlspecialchars($posUrl('products/create')); ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> <?php echo __('add'); ?>
            </a>
        </div>

        <!-- Quick Category Creation -->
        <div style="margin-bottom: 24px; padding: 16px 20px; background: #ffffff; border-radius: var(--pos-radius); border: 1.5px solid var(--pos-border); display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <span style="font-weight: 800; font-size: 13px; color: var(--pos-text); white-space: nowrap;">
                <i class="fas fa-tags" style="color: var(--pos-primary); margin-right: 6px;"></i><?php echo __('quick_add_category'); ?>
            </span>
            <form method="POST" action="<?php echo htmlspecialchars($posUrl('products/createCategory')); ?>" style="display: flex; gap: 8px; flex: 1; min-width: 250px;">
                <input type="text" name="category_name" class="pos-form-control" placeholder="<?php echo __('category_name_placeholder'); ?>" required style="margin-bottom: 0; flex: 1;">
                <button type="submit" class="btn btn-primary" style="padding: 10px 20px; font-size: 13px; white-space: nowrap;">
                    <i class="fas fa-plus"></i> <?php echo __('create'); ?>
                </button>
            </form>
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

    </script>
    
    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

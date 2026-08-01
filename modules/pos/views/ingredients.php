<?php
require_once __DIR__ . '/../../../core/helpers/url.php';
$urlPrefix = mc_base_path();
$subdomain = Tenant::getCurrent()['subdomain'];
?>
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>គ្រឿងផ្សំកាហ្វេ / Ingredient Inventory - <?php echo htmlspecialchars($tenantName ?? 'POS'); ?></title>
    <link href="<?php echo mc_base_path(); ?>/public/css/pos_template.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Outfit:wght@300;400;500;600;700;800;900&family=Battambang:wght@300;400;700;900&display=swap" rel="stylesheet">
    <style>
        body, h1, h2, h3, h4, h5, h6, p, span, a, button, input, select, textarea {
            font-family: 'Battambang', 'Outfit', 'Inter', sans-serif !important;
        }
        .modal-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px); z-index: 9999;
            display: none; align-items: center; justify-content: center;
            animation: fadeIn 0.2s ease-out;
        }
        .modal-card {
            background: #ffffff; border-radius: 20px; width: 90%; max-width: 450px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25); overflow: hidden;
            animation: scaleIn 0.2s ease-out;
        }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes scaleIn { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        
        .badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 4px 10px; border-radius: 8px; font-size: 11px; font-weight: 700;
        }
        .badge-success { background: rgba(16,185,129,0.1); color: #10b981; }
        .badge-danger { background: rgba(239,68,68,0.1); color: #ef4444; }
        
        .log-badge {
            font-size: 10px; text-transform: uppercase; font-weight: 800; padding: 2px 6px; border-radius: 4px;
        }
        .log-topup { background: #e0f2fe; color: #0284c7; }
        .log-adjust { background: #f3f4f6; color: #4b5563; }
        .log-sale { background: #fef2f2; color: #dc2626; }
    </style>
</head>
<body class="pos-app">
    <?php $activeNav = 'ingredients'; include __DIR__ . '/partials/navbar.php'; ?>
    
    <div class="fade-in">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
            <div>
                <h1 style="font-size: 26px; font-weight: 900; color: var(--pos-text); margin: 0 0 4px;">
                    គ្រឿងផ្សំ / Coffee Ingredients
                </h1>
                <p style="margin: 0; font-size: 14px; color: var(--pos-text-muted); font-weight: 500;">
                    គ្រប់គ្រងវត្ថុធាតុដើម និងគ្រឿងផ្សំសម្រាប់ផលិតផលកាហ្វេ / Manage raw materials and stock levels.
                </p>
            </div>
            
            <button class="btn" onclick="openAddModal()" style="display: flex; align-items: center; gap: 8px; box-shadow: 0 8px 20px rgba(99,102,241,0.25);">
                <i class="fas fa-plus"></i>
                <span>បន្ថែមគ្រឿងផ្សំ / Add Ingredient</span>
            </button>
        </div>

        <div class="pos-grid cols-3" style="align-items: start; gap: 24px;">
            <!-- Left Side: Ingredients List -->
            <div class="report-card" style="grid-column: span 2; padding: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
                    <h3 style="margin: 0; font-size: 16px; font-weight: 800; color: var(--pos-text); display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-cubes" style="color: var(--pos-primary);"></i>
                        ស្តុកគ្រឿងផ្សំ / Ingredient Stock levels
                        <span id="ingCountBadge" style="font-size: 12px; font-weight: 700; background: rgba(99,102,241,0.1); color: var(--pos-primary); padding: 2px 10px; border-radius: 20px;"><?php echo count($ingredients); ?></span>
                    </h3>
                    
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <div style="position: relative;">
                            <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--pos-text-muted); font-size: 12px;"></i>
                            <input type="text" id="ingSearch" placeholder="ស្វែងរកគ្រឿងផ្សំ... / Search..." 
                                style="padding: 7px 12px 7px 34px; border: 1.5px solid var(--pos-border); border-radius: 10px; font-size: 13px; font-weight: 600; outline: none; background: var(--pos-card); color: var(--pos-text); min-width: 190px;"
                                onkeyup="filterIngredients()">
                        </div>
                        
                        <select id="ingStatusFilter" onchange="filterIngredients()" 
                            style="padding: 7px 12px; border: 1.5px solid var(--pos-border); border-radius: 10px; font-size: 13px; font-weight: 700; background: var(--pos-card); color: var(--pos-text); outline: none; cursor: pointer;">
                            <option value="all">ទាំងអស់ / All Status</option>
                            <option value="low">⚠️ ស្តុកតិច / Low Stock</option>
                            <option value="ok">✅ គ្រប់គ្រាន់ / In Stock</option>
                        </select>

                        <?php if (!empty($allStores) && count($allStores) > 1): ?>
                        <form method="GET" action="" style="margin:0; display:inline-flex;">
                            <select name="store_id" onchange="this.form.submit()" 
                                style="padding: 7px 14px; border: 1.5px solid rgba(99,102,241,0.3); border-radius: 10px; font-size: 13px; font-weight: 800; background: rgba(99,102,241,0.08); color: var(--pos-primary); outline: none; cursor: pointer;">
                                <option value="0">🏪 All Stores (ហាងទាំងអស់)</option>
                                <?php foreach ($allStores as $s): ?>
                                <option value="<?php echo $s['id']; ?>" <?php echo ($selectedStoreId == $s['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars('[' . ($s['code'] ?? '--') . '] ' . $s['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="pos-table-container">
                    <table class="pos-table">
                        <thead>
                            <tr>
                                <th>ឈ្មោះគ្រឿងផ្សំ / Name</th>
                                <th style="text-align: right;">ចំនួនស្តុក / In Stock</th>
                                <th>ឯកតា / Unit</th>
                                <th>ព្រមានស្តុកតិច / Alert Qty</th>
                                <th>ស្ថានភាព / Status</th>
                                <th style="text-align: right;">សកម្មភាព / Actions</th>
                            </tr>
                        </thead>
                        <tbody id="ingTableBody">
                            <?php if (empty($ingredients)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 48px; color: var(--pos-text-muted);">
                                    <i class="fas fa-box-open" style="font-size: 32px; opacity: 0.3; display: block; margin-bottom: 8px;"></i>
                                    មិនទាន់មានគ្រឿងផ្សំនៅឡើយទេ / No ingredients added yet.
                                </td>
                            </tr>
                            <?php else: foreach ($ingredients as $ing): 
                                $isLow = (float)$ing['stock_quantity'] <= (float)$ing['min_stock_alert'];
                                $statusKey = $isLow ? 'low' : 'ok';
                            ?>
                            <tr class="ing-row" data-name="<?php echo htmlspecialchars(mb_strtolower($ing['name'])); ?>" data-unit="<?php echo htmlspecialchars(mb_strtolower($ing['unit'])); ?>" data-status="<?php echo $statusKey; ?>">
                                <td>
                                    <strong style="color: var(--pos-text);"><?php echo htmlspecialchars($ing['name']); ?></strong>
                                </td>
                                <td style="text-align: right; font-weight: 800; color: <?php echo $isLow ? '#ef4444' : 'var(--pos-text)'; ?>;">
                                    <?php echo (float)$ing['stock_quantity']; ?>
                                </td>
                                <td>
                                    <span style="font-size: 13px; color: var(--pos-text-muted); font-weight: 600;">
                                        <?php echo htmlspecialchars($ing['unit']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="font-size: 13px; color: var(--pos-text-muted); font-weight: 600;">
                                        <?php echo (float)$ing['min_stock_alert']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($isLow): ?>
                                    <span class="badge badge-danger">
                                        <i class="fas fa-triangle-exclamation"></i> ស្តុកតិច / Low
                                    </span>
                                    <?php else: ?>
                                    <span class="badge badge-success">
                                        <i class="fas fa-circle-check"></i> គ្រប់គ្រាន់ / In Stock
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 8px;">
                                        <button class="pos-icon-btn" onclick="openTopupModal(<?php echo $ing['id']; ?>, '<?php echo htmlspecialchars(addslashes($ing['name'])); ?>', '<?php echo htmlspecialchars(addslashes($ing['unit'])); ?>')" title="បំពេញស្តុក / Top Up" style="color: #0284c7; background: #e0f2fe; border: none;">
                                            <i class="fas fa-plus-circle"></i>
                                        </button>
                                        <button class="pos-icon-btn" onclick="openEditModal(<?php echo $ing['id']; ?>, '<?php echo htmlspecialchars(addslashes($ing['name'])); ?>', '<?php echo htmlspecialchars(addslashes($ing['unit'])); ?>', <?php echo (float)$ing['min_stock_alert']; ?>)" title="កែប្រែ / Edit" style="color: var(--pos-primary); background: rgba(99,102,241,0.06); border: none;">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="<?php echo htmlspecialchars($posUrl('ingredients/delete/' . $ing['id'])); ?>" class="pos-icon-btn" title="លុប / Delete" onclick="return confirm('តើអ្នកប្រាកដជាចង់លុបគ្រឿងផ្សំនេះមែនទេ? Delete this ingredient?');" style="color: #ef4444; background: #fee2e2; border: none; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;">
                                            <i class="fas fa-trash-can"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right Side: Logs/History -->
            <div class="report-card" style="padding: 24px;">
                <h3 style="margin: 0 0 20px; font-size: 16px; font-weight: 800; color: var(--pos-text); display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-clock-rotate-left" style="color: var(--pos-secondary);"></i>
                    ប្រវត្តិចលនាស្តុក / Stock Movement History
                </h3>
                
                <div style="max-height: 500px; overflow-y: auto; display: flex; flex-direction: column; gap: 14px; padding-right: 4px;">
                    <?php if (empty($logs)): ?>
                    <p style="text-align: center; color: var(--pos-text-muted); padding: 32px; font-size: 13px;">
                        មិនទាន់មានចលនាស្តុកនៅឡើយទេ / No logs found.
                    </p>
                    <?php else: foreach ($logs as $log): ?>
                    <div style="background: var(--pos-bg-body); border: 1.5px solid var(--pos-border); border-radius: 12px; padding: 12px; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong style="display: block; font-size: 13px; color: var(--pos-text);"><?php echo htmlspecialchars($log['ingredient_name']); ?></strong>
                            <span style="font-size: 11px; color: var(--pos-text-muted); font-weight: 600; display: block; margin-top: 4px;">
                                <?php echo date('d M h:i A', strtotime($log['created_at'])); ?>
                                <?php if (!empty($log['store_name'])): ?>
                                    · <span style="color:var(--pos-primary); font-weight:800;"><?php echo htmlspecialchars($log['store_name']); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($log['order_id'])): ?>
                                    · Order #<?php echo $log['order_id']; ?>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div style="text-align: right;">
                            <strong style="font-size: 14px; display: block; color: <?php echo $log['change_quantity'] > 0 ? '#10b981' : '#ef4444'; ?>;">
                                <?php echo ($log['change_quantity'] > 0 ? '+' : '') . (float)$log['change_quantity'] . ' ' . htmlspecialchars($log['unit']); ?>
                            </strong>
                            <span class="log-badge log-<?php echo htmlspecialchars($log['reason']); ?>" style="margin-top: 4px; display: inline-block;">
                                <?php echo htmlspecialchars($log['reason']); ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal 1: Add Ingredient -->
    <div id="addModal" class="modal-overlay" onclick="closeModalOnOutsideClick(event, 'addModal')">
        <div class="modal-card">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1.5px solid var(--pos-border);">
                <h3 style="margin: 0; font-size: 16px; font-weight: 900; color: var(--pos-text);">
                    បន្ថែមគ្រឿងផ្សំថ្មី / Add New Ingredient
                </h3>
                <button class="pos-icon-btn" onclick="closeModal('addModal')" style="border: 1px solid var(--pos-border); background: #fff; width:32px; height:32px; border-radius:50%; display:grid; place-items:center;">
                    <i class="fas fa-times" style="color: var(--pos-text-muted);"></i>
                </button>
            </div>
            <form action="<?php echo htmlspecialchars($posUrl('ingredients/create')); ?>" method="POST">
                <div style="padding: 24px;">
                    <div class="pos-form-group">
                        <label class="pos-form-label">ឈ្មោះគ្រឿងផ្សំ / Ingredient Name <span style="color:red;">*</span></label>
                        <input type="text" name="name" class="pos-form-control" placeholder="ឧ. ទឹកដោះគោខាប់, គ្រាប់កាហ្វេ" required>
                    </div>
                    
                    <div class="pos-grid cols-2" style="gap: 16px;">
                        <div class="pos-form-group">
                            <label class="pos-form-label">ស្តុកដើមគ្រា / Opening Stock <span style="color:red;">*</span></label>
                            <input type="number" step="0.01" name="stock_quantity" class="pos-form-control" value="0.00" min="0" required>
                        </div>
                        <div class="pos-form-group">
                            <label class="pos-form-label">ឯកតា / Unit <span style="color:red;">*</span></label>
                            <input type="text" name="unit" class="pos-form-control" placeholder="g, ml, pcs, kg, box" required>
                        </div>
                    </div>
                    
                    <div class="pos-form-group">
                        <label class="pos-form-label">ព្រមានពេលស្តុកតិចជាង / Low Stock Alert Threshold</label>
                        <input type="number" step="0.01" name="min_stock_alert" class="pos-form-control" value="0.00" min="0">
                    </div>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 12px; padding: 16px 24px; border-top: 1.5px solid var(--pos-border); background: #f9fafb;">
                    <button type="button" class="btn btn-outline" onclick="closeModal('addModal')">បោះបង់ / Cancel</button>
                    <button type="submit" class="btn">រក្សាទុក / Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2: Edit Ingredient -->
    <div id="editModal" class="modal-overlay" onclick="closeModalOnOutsideClick(event, 'editModal')">
        <div class="modal-card">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1.5px solid var(--pos-border);">
                <h3 style="margin: 0; font-size: 16px; font-weight: 900; color: var(--pos-text);">
                    កែប្រែគ្រឿងផ្សំ / Edit Ingredient
                </h3>
                <button class="pos-icon-btn" onclick="closeModal('editModal')" style="border: 1px solid var(--pos-border); background: #fff; width:32px; height:32px; border-radius:50%; display:grid; place-items:center;">
                    <i class="fas fa-times" style="color: var(--pos-text-muted);"></i>
                </button>
            </div>
            <form id="editForm" method="POST">
                <div style="padding: 24px;">
                    <div class="pos-form-group">
                        <label class="pos-form-label">ឈ្មោះគ្រឿងផ្សំ / Ingredient Name <span style="color:red;">*</span></label>
                        <input type="text" name="name" id="edit_name" class="pos-form-control" required>
                    </div>
                    
                    <div class="pos-form-group">
                        <label class="pos-form-label">ឯកតា / Unit <span style="color:red;">*</span></label>
                        <input type="text" name="unit" id="edit_unit" class="pos-form-control" required>
                    </div>
                    
                    <div class="pos-form-group">
                        <label class="pos-form-label">ព្រមានពេលស្តុកតិចជាង / Low Stock Alert Threshold</label>
                        <input type="number" step="0.01" name="min_stock_alert" id="edit_min_stock" class="pos-form-control" min="0">
                    </div>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 12px; padding: 16px 24px; border-top: 1.5px solid var(--pos-border); background: #f9fafb;">
                    <button type="button" class="btn btn-outline" onclick="closeModal('editModal')">បោះបង់ / Cancel</button>
                    <button type="submit" class="btn">រក្សាទុក / Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 3: Top Up Stock -->
    <div id="topupModal" class="modal-overlay" onclick="closeModalOnOutsideClick(event, 'topupModal')">
        <div class="modal-card">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1.5px solid var(--pos-border);">
                <h3 style="margin: 0; font-size: 16px; font-weight: 900; color: var(--pos-text);">
                    បំពេញស្តុក / Top Up Stock
                </h3>
                <button class="pos-icon-btn" onclick="closeModal('topupModal')" style="border: 1px solid var(--pos-border); background: #fff; width:32px; height:32px; border-radius:50%; display:grid; place-items:center;">
                    <i class="fas fa-times" style="color: var(--pos-text-muted);"></i>
                </button>
            </div>
            <form id="topupForm" method="POST">
                <input type="hidden" name="store_id" value="<?php echo (int)$selectedStoreId; ?>">
                <div style="padding: 24px;">
                    <p style="margin: 0 0 16px; font-size: 14px; font-weight: 600; color: var(--pos-text);">
                        គ្រឿងផ្សំ: <span id="topup_ing_name" style="color: var(--pos-primary);"></span>
                    </p>
                    <div class="pos-form-group">
                        <label class="pos-form-label">ចំនួនបំពេញបន្ថែម / Top Up Quantity <span style="color:red;">*</span></label>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <input type="number" step="0.01" name="quantity" class="pos-form-control" placeholder="0.00" min="0.01" required style="font-size: 20px; font-weight: 800; text-align: center; color: var(--pos-primary);">
                            <strong id="topup_unit" style="font-size: 16px; color: var(--pos-text-muted);"></strong>
                        </div>
                    </div>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 12px; padding: 16px 24px; border-top: 1.5px solid var(--pos-border); background: #f9fafb;">
                    <button type="button" class="btn btn-outline" onclick="closeModal('topupModal')">បោះបង់ / Cancel</button>
                    <button type="submit" class="btn" style="background: #0284c7; color: white;">បញ្ចូលស្តុក / Top Up</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('addModal').style.display = 'flex';
        }
        function openEditModal(id, name, unit, minStock) {
            const form = document.getElementById('editForm');
            form.action = '<?php echo htmlspecialchars($posUrl("ingredients/update/")); ?>' + id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_unit').value = unit;
            document.getElementById('edit_min_stock').value = minStock;
            document.getElementById('editModal').style.display = 'flex';
        }
        function openTopupModal(id, name, unit) {
            const form = document.getElementById('topupForm');
            form.action = '<?php echo htmlspecialchars($posUrl("ingredients/topup/")); ?>' + id;
            document.getElementById('topup_ing_name').innerText = name;
            document.getElementById('topup_unit').innerText = unit;
            document.getElementById('topupModal').style.display = 'flex';
        }
        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }
        function closeModalOnOutsideClick(event, id) {
            if (event.target === document.getElementById(id)) {
                closeModal(id);
            }
        }
        function filterIngredients() {
            const q = document.getElementById('ingSearch').value.toLowerCase().trim();
            const st = document.getElementById('ingStatusFilter').value;
            const rows = document.querySelectorAll('.ing-row');
            let visibleCount = 0;

            rows.forEach(tr => {
                const name = tr.dataset.name || '';
                const unit = tr.dataset.unit || '';
                const status = tr.dataset.status || '';

                const matchesSearch = !q || name.includes(q) || unit.includes(q);
                const matchesStatus = (st === 'all') || (status === st);

                if (matchesSearch && matchesStatus) {
                    tr.style.display = '';
                    visibleCount++;
                } else {
                    tr.style.display = 'none';
                }
            });

            const badge = document.getElementById('ingCountBadge');
            if (badge) badge.textContent = visibleCount;
        }
    </script>
</body>
</html>

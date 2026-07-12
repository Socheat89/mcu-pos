<?php
// modules/pos/views/stores.php
require_once __DIR__ . '/../../../core/helpers/url.php';
$pageTitle = $pageTitle ?? __('manage_stores');
$activeNav = $activeNav ?? 'stores';
$posUrl = $posUrl ?? function ($path) {
    $prefix = mc_base_path();
    $subdomain = Tenant::getCurrent()['subdomain'] ?? '';
    return $prefix . '/' . $subdomain . '/pos/' . ltrim($path, '/');
};
$tenantId = Tenant::getId();
$stores = $stores ?? Store::getAll($tenantId);
$currentStore = $currentStore ?? Store::getCurrent($tenantId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('manage_stores'); ?> - <?php echo htmlspecialchars(Tenant::getCurrent()['name']); ?></title>
    <link href="<?php echo mc_base_path(); ?>/public/css/pos_template.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700;800&family=Battambang:wght@300;400;700;900&display=swap" rel="stylesheet">
    <style>
        .store-card {
            background: var(--pos-card);
            backdrop-filter: blur(12px);
            border: 1px solid var(--pos-border);
            border-radius: 20px;
            padding: 24px;
            transition: all 0.25s ease;
            position: relative;
        }
        .store-card:hover {
            border-color: var(--pos-primary);
            box-shadow: var(--pos-shadow-md), var(--pos-shadow-glow);
            transform: translateY(-2px);
        }
        .store-card.default {
            border-color: var(--pos-primary);
            border-width: 2px;
            background: linear-gradient(135deg, rgba(var(--pos-primary-rgb), 0.03), var(--pos-card));
        }
        .store-card .default-badge {
            position: absolute;
            top: 16px;
            right: 16px;
            background: var(--pos-primary-light);
            color: var(--pos-primary);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .store-card .store-code {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: var(--pos-primary-light);
            color: var(--pos-primary);
            font-weight: 900;
            font-size: 16px;
            letter-spacing: 1px;
            margin-bottom: 16px;
        }
        .store-card h3 {
            font-size: 18px;
            font-weight: 800;
            color: var(--pos-text);
            margin: 0 0 8px;
        }
        .store-card .store-info {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 16px;
        }
        .store-card .store-info span {
            font-size: 13px;
            color: var(--pos-text-muted);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .store-card .store-info span i {
            width: 18px;
            color: var(--pos-primary);
            font-size: 12px;
        }
        .store-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .store-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }
        .add-store-card {
            background: transparent;
            border: 2px dashed var(--pos-border);
            border-radius: 20px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            cursor: pointer;
            transition: all 0.25s ease;
            min-height: 220px;
        }
        .add-store-card:hover {
            border-color: var(--pos-primary);
            background: rgba(var(--pos-primary-rgb), 0.02);
        }
        .add-store-card i {
            font-size: 40px;
            color: var(--pos-text-dim);
        }
        .add-store-card span {
            font-weight: 800;
            color: var(--pos-text-muted);
            font-size: 15px;
        }

        /* Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(4px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.active {
            display: flex;
        }
        .modal {
            background: var(--pos-card);
            border-radius: 24px;
            padding: 32px;
            max-width: 520px;
            width: 90%;
            border: 1px solid var(--pos-border);
            box-shadow: var(--pos-shadow-xl);
            animation: fadeUp 0.3s ease-out;
        }
        .modal h2 {
            font-size: 22px;
            font-weight: 800;
            color: var(--pos-text);
            margin: 0 0 24px;
        }
        .modal .form-group {
            margin-bottom: 16px;
        }
        .modal label {
            display: block;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--pos-text-muted);
            margin-bottom: 6px;
        }
        .modal input, .modal textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid var(--pos-border);
            border-radius: 14px;
            font-size: 14px;
            font-weight: 600;
            color: var(--pos-text);
            background: #fff;
            transition: all 0.2s;
            outline: none;
            box-sizing: border-box;
        }
        .modal input:focus, .modal textarea:focus {
            border-color: var(--pos-primary);
            box-shadow: 0 0 0 4px rgba(var(--pos-primary-rgb), 0.1);
        }
        .modal textarea {
            resize: vertical;
            min-height: 70px;
        }
        .modal .btn-row {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 24px;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="pos-app">
    <?php include __DIR__ . '/partials/navbar.php'; ?>

    <div class="fade-in" style="padding: 8px 0;">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
            <div class="pos-title">
                <h1><?php echo __('manage_stores'); ?></h1>
                <p><?php echo __('manage_stores_desc'); ?></p>
            </div>
            <button class="btn btn-primary" onclick="openAddModal()">
                <i class="fas fa-plus-circle"></i> <?php echo __('add_store'); ?>
            </button>
        </div>

        <!-- Store Cards -->
        <div class="store-grid">
            <?php foreach ($stores as $store): ?>
                <div class="store-card <?php echo $store['is_default'] ? 'default' : ''; ?>">
                    <?php if ($store['is_default']): ?>
                        <div class="default-badge"><i class="fas fa-star"></i> <?php echo __('default_store'); ?></div>
                    <?php endif; ?>
                    <div class="store-code"><?php echo htmlspecialchars($store['code'] ?: 'ST'); ?></div>
                    <h3><?php echo htmlspecialchars($store['name']); ?></h3>
                    <div class="store-info">
                        <?php if (!empty($store['address'])): ?>
                            <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($store['address']); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($store['phone'])): ?>
                            <span><i class="fas fa-phone-alt"></i> <?php echo htmlspecialchars($store['phone']); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($store['email'])): ?>
                            <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($store['email']); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="store-actions">
                        <?php if ((int)$store['id'] === (int)($currentStore['id'] ?? 0)): ?>
                            <span class="btn btn-sm" style="background: var(--pos-primary-light); color: var(--pos-primary); cursor: default;">
                                <i class="fas fa-check-circle"></i> <?php echo __('current_store'); ?>
                            </span>
                        <?php else: ?>
                            <a href="<?php echo htmlspecialchars($posUrl('stores/switch?store_id=' . $store['id'])); ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-exchange-alt"></i> <?php echo __('switch_to'); ?>
                            </a>
                        <?php endif; ?>
                        <button class="btn btn-sm btn-outline" onclick='openEditModal(<?php echo json_encode($store); ?>)'>
                            <i class="fas fa-edit"></i>
                        </button>
                        <?php if (!$store['is_default']): ?>
                            <form method="POST" action="<?php echo htmlspecialchars($posUrl('stores/delete')); ?>" style="display:inline;" onsubmit="return confirm('<?php echo __('confirm_delete_store'); ?>')">
                                <input type="hidden" name="store_id" value="<?php echo $store['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Add Store Trigger -->
            <div class="add-store-card" onclick="openAddModal()">
                <i class="fas fa-plus-circle"></i>
                <span><?php echo __('add_new_store'); ?></span>
            </div>
        </div>
    </div>

    <!-- Add/Edit Store Modal -->
    <div class="modal-overlay" id="storeModal">
        <div class="modal">
            <h2 id="modalTitle"><?php echo __('add_new_store'); ?></h2>
            <form method="POST" action="<?php echo htmlspecialchars($posUrl('stores/create')); ?>" id="storeForm">
                <input type="hidden" name="store_id" id="editStoreId">
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="form-group">
                    <label><?php echo __('store_name'); ?> *</label>
                    <input type="text" name="name" id="storeName" required placeholder="<?php echo __('store_name_placeholder'); ?>">
                </div>
                <div class="form-group">
                    <label><?php echo __('store_code'); ?></label>
                    <input type="text" name="code" id="storeCode" placeholder="e.g. MAIN, TKG" maxlength="10">
                </div>
                <div class="form-group">
                    <label><?php echo __('address'); ?></label>
                    <textarea name="address" id="storeAddress" placeholder="<?php echo __('store_address_placeholder'); ?>"></textarea>
                </div>
                <div class="form-group">
                    <label><?php echo __('phone_number'); ?></label>
                    <input type="text" name="phone" id="storePhone" placeholder="+855 XX XXX XXX">
                </div>
                <div class="form-group">
                    <label><?php echo __('email_address'); ?></label>
                    <input type="email" name="email" id="storeEmail" placeholder="store@example.com">
                </div>
                <div class="form-group" style="display:flex;align-items:center;gap:10px;">
                    <input type="checkbox" name="is_default" id="storeIsDefault" value="1" style="width:18px;height:18px;">
                    <label for="storeIsDefault" style="margin:0;cursor:pointer;"><?php echo __('set_as_default_store'); ?></label>
                </div>

                <div class="btn-row">
                    <button type="button" class="btn btn-outline" onclick="closeModal()"><?php echo __('cancel'); ?></button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?php echo __('save_store'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('modalTitle').textContent = '<?php echo __('add_new_store'); ?>';
            document.getElementById('storeForm').action = '<?php echo htmlspecialchars($posUrl('stores/create')); ?>';
            document.getElementById('editStoreId').value = '';
            document.getElementById('storeName').value = '';
            document.getElementById('storeCode').value = '';
            document.getElementById('storeAddress').value = '';
            document.getElementById('storePhone').value = '';
            document.getElementById('storeEmail').value = '';
            document.getElementById('storeIsDefault').checked = false;
            document.getElementById('storeModal').classList.add('active');
        }

        function openEditModal(store) {
            document.getElementById('modalTitle').textContent = '<?php echo __('edit_store'); ?>';
            document.getElementById('storeForm').action = '<?php echo htmlspecialchars($posUrl('stores/update')); ?>';
            document.getElementById('editStoreId').value = store.id;
            document.getElementById('storeName').value = store.name || '';
            document.getElementById('storeCode').value = store.code || '';
            document.getElementById('storeAddress').value = store.address || '';
            document.getElementById('storePhone').value = store.phone || '';
            document.getElementById('storeEmail').value = store.email || '';
            document.getElementById('storeIsDefault').checked = store.is_default == 1;
            document.getElementById('storeModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('storeModal').classList.remove('active');
        }

        document.getElementById('storeModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    </script>

    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

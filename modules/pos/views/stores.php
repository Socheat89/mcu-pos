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

        /* ── Store Creation / Edit Modal ── */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-overlay.active {
            display: flex;
        }
        .modal {
            background: var(--pos-card, #ffffff);
            border-radius: 24px;
            padding: 32px;
            max-width: 560px;
            width: 100%;
            border: 1px solid var(--pos-border, #e2e8f0);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
            animation: modalFadeScale 0.25s ease-out;
            position: relative;
        }
        @keyframes modalFadeScale {
            from { opacity: 0; transform: scale(0.95) translateY(10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
        .modal-header-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--pos-border, #f1f5f9);
        }
        .modal-title-group {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .modal-title-icon {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            background: var(--pos-primary-light, rgba(6,182,212,0.1));
            color: var(--pos-primary, #06b6d4);
            display: grid;
            place-items: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        .modal h2 {
            font-size: 20px;
            font-weight: 800;
            color: var(--pos-text, #0f172a);
            margin: 0;
        }
        .modal .sub {
            font-size: 13px;
            color: var(--pos-text-muted, #64748b);
            margin-top: 2px;
            font-weight: 500;
        }
        .modal-close-btn {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: 1px solid var(--pos-border, #e2e8f0);
            background: transparent;
            color: var(--pos-text-muted, #64748b);
            font-size: 18px;
            cursor: pointer;
            display: grid;
            place-items: center;
            transition: all 0.2s;
        }
        .modal-close-btn:hover {
            background: #fee2e2;
            color: #ef4444;
            border-color: #fecaca;
        }
        .modal .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        .modal .form-group {
            margin-bottom: 18px;
        }
        .modal .form-group.full-width {
            grid-column: 1 / -1;
        }
        .modal label {
            display: block;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--pos-text-muted, #64748b);
            margin-bottom: 8px;
        }
        .modal label span.req {
            color: #ef4444;
        }
        .modal input, .modal textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid var(--pos-border, #cbd5e1);
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            color: var(--pos-text, #0f172a);
            background: #ffffff;
            transition: all 0.2s;
            outline: none;
            box-sizing: border-box;
            font-family: inherit;
        }
        .modal input:focus, .modal textarea:focus {
            border-color: var(--pos-primary, #06b6d4);
            box-shadow: 0 0 0 4px rgba(6, 182, 212, 0.15);
        }
        .modal textarea {
            resize: vertical;
            min-height: 75px;
        }
        .modal-toggle-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            background: var(--pos-primary-light, rgba(6,182,212,0.05));
            border: 1px solid rgba(6,182,212,0.2);
            border-radius: 14px;
            margin-bottom: 20px;
            cursor: pointer;
        }
        .modal-toggle-card input[type="checkbox"] {
            width: 20px;
            height: 20px;
            accent-color: var(--pos-primary, #06b6d4);
            cursor: pointer;
        }
        .modal-toggle-card label {
            margin: 0;
            font-weight: 700;
            font-size: 13px;
            color: var(--pos-text, #0f172a);
            text-transform: none;
            letter-spacing: 0;
            cursor: pointer;
        }
        .modal .btn-row {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 8px;
            padding-top: 16px;
            border-top: 1px solid var(--pos-border, #f1f5f9);
        }
    </style>
</head>
<body class="pos-app">
    <?php include __DIR__ . '/partials/navbar.php'; ?>

    <div class="fade-in" style="padding: 8px 0;">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
            <div class="pos-title">
                <h1><?php echo __('manage_stores'); ?></h1>
                <p><?php echo __('manage_stores_desc'); ?> 
                    <strong style="color:var(--pos-primary);">
                        <?php echo count($stores); ?>/<?php echo Tenant::getStoreLimit() == 0 ? '∞' : Tenant::getStoreLimit(); ?>
                    </strong> <?php echo __('stores_used'); ?>
                </p>
            </div>
            <?php $canAddStore = Tenant::getStoreLimit() == 0 || count($stores) < Tenant::getStoreLimit(); ?>
            <?php if ($canAddStore): ?>
            <button class="btn btn-primary" onclick="openAddModal()">
                <i class="fas fa-plus-circle"></i> <?php echo __('add_store'); ?>
            </button>
            <?php else: ?>
            <span class="btn" style="background:rgba(245,158,11,0.1);color:#f59e0b;cursor:default;">
                <i class="fas fa-triangle-exclamation"></i> <?php echo __('store_limit_reached'); ?>
            </span>
            <?php endif; ?>
        </div>

        <?php if (!empty($_SESSION['store_error'])): ?>
            <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.25); color: #ef4444; padding: 14px 20px; border-radius: 16px; margin-bottom: 24px; font-weight: 700; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <i class="fas fa-exclamation-circle" style="font-size: 18px;"></i>
                    <span><?php echo htmlspecialchars($_SESSION['store_error']); ?></span>
                </div>
                <a href="<?php echo mc_base_path(); ?>/pricing.php" class="btn btn-sm btn-primary" style="background: #ef4444; border-color: #ef4444; text-decoration: none;">
                    <i class="fas fa-arrow-up"></i> Upgrade Plan
                </a>
            </div>
            <?php unset($_SESSION['store_error']); ?>
        <?php endif; ?>

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
            <div class="modal-header-bar">
                <div class="modal-title-group">
                    <div class="modal-title-icon">
                        <i class="fas fa-store"></i>
                    </div>
                    <div>
                        <h2 id="modalTitle"><?php echo __('add_new_store'); ?></h2>
                        <div class="sub"><?php echo __('configure_store_details', ['default' => 'Fill in branch & store information']); ?></div>
                    </div>
                </div>
                <button type="button" class="modal-close-btn" onclick="closeModal()">&times;</button>
            </div>

            <form method="POST" action="<?php echo htmlspecialchars($posUrl('stores/create')); ?>" id="storeForm">
                <input type="hidden" name="store_id" id="editStoreId">
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="form-grid">
                    <div class="form-group">
                        <label><?php echo __('store_name'); ?> <span class="req">*</span></label>
                        <input type="text" name="name" id="storeName" required placeholder="<?php echo __('store_name_placeholder'); ?>">
                    </div>
                    <div class="form-group">
                        <label><?php echo __('store_code'); ?></label>
                        <input type="text" name="code" id="storeCode" placeholder="e.g. MAIN, TKG" maxlength="10">
                    </div>
                    <div class="form-group">
                        <label><?php echo __('phone_number'); ?></label>
                        <input type="text" name="phone" id="storePhone" placeholder="+855 XX XXX XXX">
                    </div>
                    <div class="form-group">
                        <label><?php echo __('email_address'); ?></label>
                        <input type="email" name="email" id="storeEmail" placeholder="store@example.com">
                    </div>
                    <div class="form-group full-width">
                        <label><?php echo __('address'); ?></label>
                        <textarea name="address" id="storeAddress" placeholder="<?php echo __('store_address_placeholder'); ?>"></textarea>
                    </div>
                </div>

                <div class="modal-toggle-card">
                    <input type="checkbox" name="is_default" id="storeIsDefault" value="1">
                    <label for="storeIsDefault"><?php echo __('set_as_default_store'); ?></label>
                </div>

                <div class="btn-row">
                    <button type="button" class="btn btn-outline" onclick="closeModal()"><?php echo __('cancel'); ?></button>
                    <button type="submit" class="btn btn-primary" style="padding: 10px 24px;">
                        <i class="fas fa-save" style="margin-right: 6px;"></i> <?php echo __('save_store'); ?>
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

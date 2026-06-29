<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('customers'); ?> - <?php echo htmlspecialchars($tenantName ?? 'POS'); ?></title>
    <link href="<?php echo mc_base_path(); ?>/public/css/pos_template.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Outfit:wght@300;400;500;600;700;800;900&family=Battambang:wght@100;300;400;700;900&display=swap" rel="stylesheet">
    <style>
<<<<<<< HEAD
        .customer-meta { display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; border-radius: 999px; background: rgba(14, 165, 233, 0.08); color: #0369a1; font-size: 11px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; }
=======
        .search-container { position: relative; margin-bottom: 24px; }
        .search-container i { position: absolute; left: 20px; top: 16px; color: var(--pos-primary); font-size: 18px; }
        .search-container input { width: 100%; padding: 14px 20px 14px 54px; border-radius: var(--pos-radius); border: 1.5px solid var(--pos-border); background: #ffffff; color: var(--pos-text); font-size: 15px; font-weight: 600; outline: none; transition: all 0.3s; }
        .search-container input:focus { border-color: var(--pos-primary); background: #ffffff; box-shadow: 0 0 0 4px rgba(var(--pos-primary-rgb), 0.15); }
        
        .avatar-circle { width: 44px; height: 44px; border-radius: var(--pos-radius); background: var(--pos-primary-light); color: var(--pos-primary); display: grid; place-items: center; font-weight: 900; font-size: 16px; border: 1px solid rgba(var(--pos-primary-rgb), 0.2); }
>>>>>>> 062e3cc8d9b9f40dc40c6d6c6835e28f6f8a0d77
    </style>
</head>
<body class="pos-app">
    <?php $activeNav = 'customers'; include __DIR__ . '/partials/navbar.php'; ?>

    <div class="fade-in">
<<<<<<< HEAD
        <div class="pos-page-hero">
            <div style="position: relative; z-index: 2;">
                <div class="pos-kicker"><i class="fas fa-users"></i> <?php echo __('customers'); ?></div>
                <h1 class="pos-page-hero__title" style="margin-top: 14px;"><?php echo __('customer_relations'); ?></h1>
                <p class="pos-page-hero__text"><?php echo __('customer_management_msg'); ?></p>
                <div class="pos-page-hero__actions">
                    <a href="<?php echo htmlspecialchars($posUrl('customers/create')); ?>" class="btn btn-primary" style="padding: 14px 22px; border-radius: 18px;">
                        <i class="fas fa-user-plus"></i> <?php echo __('add_customer'); ?>
                    </a>
                </div>
            </div>
=======
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
            <div class="pos-title">
                <h1><?php echo __('customers'); ?></h1>
                <p><?php echo __('customer_management_msg'); ?></p>
            </div>
            <a href="<?php echo htmlspecialchars($posUrl('customers/create')); ?>" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> <?php echo __('add_customer'); ?>
            </a>
>>>>>>> 062e3cc8d9b9f40dc40c6d6c6835e28f6f8a0d77
        </div>

        <div class="pos-grid cols-4" style="margin-bottom: 32px;">
            <div class="pos-stat">
                <span class="k"><?php echo __('total_clients'); ?></span>
                <p class="v"><?php echo count($customers); ?></p>
                <div class="chip" style="background: rgba(99, 102, 241, 0.1); color: var(--pos-primary);"><i class="fas fa-users"></i></div>
            </div>
            <div class="pos-stat">
                <span class="k"><?php echo __('active_this_month'); ?></span>
                <p class="v"><?php echo count($customers); ?></p>
                <div class="chip" style="background: rgba(16, 185, 129, 0.1); color: var(--pos-success);"><i class="fas fa-user-check"></i></div>
            </div>
        </div>

<<<<<<< HEAD
        <div class="pos-input-shell" style="margin-bottom: 24px; max-width: 560px;">
=======
        <div class="search-container">
>>>>>>> 062e3cc8d9b9f40dc40c6d6c6835e28f6f8a0d77
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="<?php echo __('search_customers_placeholder'); ?>" onkeyup="searchCustomers()">
        </div>

<<<<<<< HEAD
        <div class="pos-panel">
=======
        <div class="pos-table-container">
>>>>>>> 062e3cc8d9b9f40dc40c6d6c6835e28f6f8a0d77
            <table class="pos-table" id="customersTable">
                <thead>
                    <tr>
                        <th style="width: 60px;"><?php echo __('profile'); ?></th>
                        <th><?php echo __('display_name'); ?></th>
                        <th><?php echo __('contact_info'); ?></th>
                        <th><?php echo __('location_address'); ?></th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($customers)): ?>
                        <tr>
<<<<<<< HEAD
                            <td colspan="5" class="pos-empty-state">
                                <div class="pos-empty-state__icon">
                                    <i class="fas fa-users" style="font-size: 30px;"></i>
                                </div>
                                <h3 style="color: var(--pos-text); font-weight: 900; margin: 0;"><?php echo __('no_customers_yet'); ?></h3>
                                <p style="color: var(--pos-text-muted); margin-top: 8px; font-weight: 600;"><?php echo __('client_database_msg'); ?></p>
=======
                            <td colspan="5" style="padding: 100px; text-align: center;">
                                <div style="width: 80px; height: 80px; background: rgba(255,255,255,0.03); border: 1px solid var(--pos-border); border-radius: 50%; display: grid; place-items: center; margin: 0 auto 20px;">
                                    <i class="fas fa-users" style="font-size: 32px; color: var(--pos-text-dim);"></i>
                                </div>
                                <h3 style="color: var(--pos-text); font-weight: 800; margin: 0;"><?php echo __('no_customers_yet'); ?></h3>
                                <p style="color: var(--pos-text-muted); margin-top: 8px;"><?php echo __('client_database_msg'); ?></p>
>>>>>>> 062e3cc8d9b9f40dc40c6d6c6835e28f6f8a0d77
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($customers as $c): ?>
                            <tr class="customer-row">
                                <td>
<<<<<<< HEAD
                                    <div class="pos-avatar-circle">
=======
                                    <div class="avatar-circle">
>>>>>>> 062e3cc8d9b9f40dc40c6d6c6835e28f6f8a0d77
                                        <?php echo strtoupper(substr($c['name'], 0, 1)); ?>
                                    </div>
                                </td>
                                <td>
<<<<<<< HEAD
                                    <div style="font-weight: 900; font-size: 15px; color: var(--pos-text);"><?php echo htmlspecialchars($c['name']); ?></div>
                                    <div class="customer-meta" style="margin-top: 8px;">ID: #100<?php echo $c['id']; ?></div>
=======
                                    <div style="font-weight: 800; font-size: 15px; color: var(--pos-text);"><?php echo htmlspecialchars($c['name']); ?></div>
                                    <div style="font-size: 12px; font-weight: 600; color: var(--pos-text-muted); margin-top: 2px;">ID: #100<?php echo $c['id']; ?></div>
>>>>>>> 062e3cc8d9b9f40dc40c6d6c6835e28f6f8a0d77
                                </td>
                                <td>
                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                        <?php if (!empty($c['email'])): ?>
                                            <div style="font-size: 13px; font-weight: 600; color: var(--pos-text); display: flex; align-items: center; gap: 6px;">
                                                <i class="far fa-envelope" style="color: var(--pos-text-muted); font-size: 11px;"></i> <?php echo htmlspecialchars($c['email']); ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($c['phone'])): ?>
                                            <div style="font-size: 13px; font-weight: 600; color: var(--pos-text); display: flex; align-items: center; gap: 6px;">
                                                <i class="fas fa-phone-alt" style="color: var(--pos-text-muted); font-size: 11px;"></i> <?php echo htmlspecialchars($c['phone']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-size: 13px; font-weight: 600; color: var(--pos-text-muted);">
                                        <i class="fas fa-map-marker-alt" style="margin-right: 6px; font-size: 11px;"></i>
                                        <?php echo !empty($c['address']) ? htmlspecialchars($c['address']) : __('not_provided'); ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; justify-content: flex-end; gap: 10px;">
                                        <a href="<?php echo htmlspecialchars($posUrl('customers/' . $c['id'] . '/edit')); ?>" class="pos-icon-btn" title="Edit">
                                            <i class="fas fa-pencil-alt" style="font-size: 14px;"></i>
                                        </a>
                                        <a href="<?php echo htmlspecialchars($posUrl('customers/' . $c['id'] . '/delete')); ?>" class="pos-icon-btn" style="color: var(--pos-danger);" data-pos-confirm="<?php echo __('confirm_delete_customer'); ?>" title="Delete">
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
        function searchCustomers() {
            const filter = document.getElementById('searchInput').value.toUpperCase();
            const rows = document.querySelectorAll('.customer-row');
            rows.forEach(row => {
                const text = row.innerText.toUpperCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        }
    </script>
    
    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('customers'); ?> - <?php echo htmlspecialchars($tenantName ?? 'POS'); ?></title>
    <link href="<?php echo $urlPrefix; ?>/public/css/pos_template.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Outfit:wght@300;400;500;600;700;800;900&family=Battambang:wght@100;300;400;700;900&display=swap" rel="stylesheet">
    <style>
        .search-container { position: relative; margin-bottom: 24px; }
        .search-container i { position: absolute; left: 20px; top: 16px; color: var(--pos-primary); font-size: 18px; }
        .search-container input { width: 100%; padding: 14px 20px 14px 54px; border-radius: 16px; border: 1.5px solid var(--pos-border); background: white; font-size: 15px; font-weight: 600; outline: none; transition: all 0.3s; }
        .search-container input:focus { border-color: var(--pos-primary); box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); }
        
        .avatar-circle { width: 44px; height: 44px; border-radius: 12px; background: #eef2ff; color: var(--pos-primary); display: grid; place-items: center; font-weight: 900; font-size: 16px; border: 1px solid rgba(99, 102, 241, 0.1); }
    </style>
</head>
<body class="pos-app">
    <?php $activeNav = 'customers'; include __DIR__ . '/partials/navbar.php'; ?>

    <div class="fade-in">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
            <div class="pos-title">
                <h1><?php echo __('customer_relations'); ?></h1>
                <p><?php echo __('customer_management_msg'); ?></p>
            </div>
            <a href="<?php echo htmlspecialchars($posUrl('customers/create')); ?>" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> <?php echo __('add_customer'); ?>
            </a>
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

        <div class="search-container">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="<?php echo __('search_customers_placeholder'); ?>" onkeyup="searchCustomers()">
        </div>

        <div class="pos-table-container">
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
                            <td colspan="5" style="padding: 100px; text-align: center;">
                                <div style="width: 80px; height: 80px; background: #f1f5f9; border-radius: 50%; display: grid; place-items: center; margin: 0 auto 20px;">
                                    <i class="fas fa-users" style="font-size: 32px; color: #cbd5e1;"></i>
                                </div>
                                <h3 style="color: var(--pos-text); font-weight: 800; margin: 0;"><?php echo __('no_customers_yet'); ?></h3>
                                <p style="color: var(--pos-text-muted); margin-top: 8px;"><?php echo __('client_database_msg'); ?></p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($customers as $c): ?>
                            <tr class="customer-row">
                                <td>
                                    <div class="avatar-circle">
                                        <?php echo strtoupper(substr($c['name'], 0, 1)); ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 800; font-size: 15px; color: var(--pos-text);"><?php echo htmlspecialchars($c['name']); ?></div>
                                    <div style="font-size: 12px; font-weight: 600; color: var(--pos-text-muted); margin-top: 2px;">ID: #100<?php echo $c['id']; ?></div>
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

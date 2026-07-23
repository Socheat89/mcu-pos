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
        body, h1, h2, h3, h4, h5, h6, p, span, a, button, input, select, textarea {
            font-family: 'Battambang', 'Outfit', 'Inter', sans-serif !important;
        }
        .search-container { position: relative; margin-bottom: 24px; }
        .search-container i { position: absolute; left: 20px; top: 16px; color: var(--pos-primary); font-size: 18px; }
        .search-container input { width: 100%; padding: 14px 20px 14px 54px; border-radius: var(--pos-radius); border: 1.5px solid var(--pos-border); background: #ffffff; color: var(--pos-text); font-size: 15px; font-weight: 600; outline: none; transition: all 0.3s; }
        .search-container input:focus { border-color: var(--pos-primary); background: #ffffff; box-shadow: 0 0 0 4px rgba(var(--pos-primary-rgb), 0.15); }
        
        .avatar-circle { width: 44px; height: 44px; border-radius: 14px; background: var(--pos-primary-light); color: var(--pos-primary); display: grid; place-items: center; font-weight: 900; font-size: 16px; border: 1px solid rgba(var(--pos-primary-rgb), 0.2); }

        /* Customer Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(8px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-overlay.active { display: flex; }
        .modal {
            background: var(--pos-card, #ffffff);
            border-radius: 24px;
            padding: 32px;
            max-width: 540px;
            width: 100%;
            border: 1px solid var(--pos-border, #e2e8f0);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
            animation: modalFadeScale 0.25s ease-out;
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
        .modal-title-group { display: flex; align-items: center; gap: 14px; }
        .modal-title-icon {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            display: grid;
            place-items: center;
            font-size: 20px;
        }
        .modal h2 { font-size: 20px; font-weight: 800; color: var(--pos-text); margin: 0; }
        .modal-close-btn { width: 34px; height: 34px; border-radius: 50%; border: 1px solid var(--pos-border); background: transparent; color: var(--pos-text-muted); font-size: 18px; cursor: pointer; display: grid; place-items: center; }
        .modal-close-btn:hover { background: #fee2e2; color: #ef4444; }
        .modal .form-group { margin-bottom: 16px; }
        .modal label { display: block; font-weight: 700; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--pos-text-muted); margin-bottom: 6px; }
        .modal input, .modal textarea { width: 100%; padding: 12px 16px; border: 1.5px solid var(--pos-border); border-radius: 12px; font-size: 14px; font-weight: 600; color: var(--pos-text); background: #ffffff; outline: none; box-sizing: border-box; font-family: inherit; }
        .modal input:focus, .modal textarea:focus { border-color: var(--pos-primary); box-shadow: 0 0 0 4px rgba(var(--pos-primary-rgb), 0.15); }
    </style>
</head>
<body class="pos-app">
    <?php $activeNav = 'customers'; include __DIR__ . '/partials/navbar.php'; ?>

    <div class="fade-in">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px; flex-wrap: wrap; gap: 16px;">
            <div class="pos-title">
                <h1><?php echo __('customers'); ?></h1>
                <p><?php echo __('customer_management_msg'); ?></p>
            </div>
            <div style="display: flex; gap: 12px;">
                <button type="button" class="btn btn-primary" onclick="openAddCustomerModal()">
                    <i class="fas fa-user-plus"></i> <?php echo __('add_customer'); ?>
                </button>
            </div>
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
                            <td colspan="5" style="padding: 80px; text-align: center;">
                                <div style="width: 80px; height: 80px; background: rgba(255,255,255,0.03); border: 1px solid var(--pos-border); border-radius: 50%; display: grid; place-items: center; margin: 0 auto 20px;">
                                    <i class="fas fa-users" style="font-size: 32px; color: var(--pos-text-dim);"></i>
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
                                        <button type="button" class="pos-icon-btn" title="Edit" onclick='openEditCustomerModal(<?php echo json_encode($c); ?>)'>
                                            <i class="fas fa-pencil-alt" style="font-size: 14px;"></i>
                                        </button>
                                        <a href="<?php echo htmlspecialchars($posUrl('customers/' . $c['id'] . '/delete')); ?>" class="pos-icon-btn" style="color: var(--pos-danger);" onclick="return confirm('<?php echo __('confirm_delete_customer'); ?>')" title="Delete">
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

    <!-- Quick Add/Edit Customer Modal -->
    <div class="modal-overlay" id="customerModal">
        <div class="modal">
            <div class="modal-header-bar">
                <div class="modal-title-group">
                    <div class="modal-title-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div>
                        <h2 id="custModalTitle"><?php echo __('add_customer'); ?></h2>
                        <div style="font-size:12px;color:var(--pos-text-muted);margin-top:2px;"><?php echo __('client_relations'); ?></div>
                    </div>
                </div>
                <button type="button" class="modal-close-btn" onclick="closeCustomerModal()">&times;</button>
            </div>

            <form method="POST" action="<?php echo htmlspecialchars($posUrl('customers/create')); ?>" id="customerForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label><?php echo __('full_display_name'); ?> <span style="color:red;">*</span></label>
                        <input type="text" name="name" id="custName" required placeholder="<?php echo __('enter_name_placeholder', ['default' => 'e.g. Johnathan Doe']); ?>">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                        <div class="form-group">
                            <label><?php echo __('primary_phone'); ?></label>
                            <input type="tel" name="phone" id="custPhone" placeholder="+855 XX XXX XXX">
                        </div>
                        <div class="form-group">
                            <label><?php echo __('email_address'); ?></label>
                            <input type="email" name="email" id="custEmail" placeholder="client@company.com">
                        </div>
                    </div>

                    <div class="form-group">
                        <label><?php echo __('full_physical_address'); ?></label>
                        <textarea name="address" id="custAddress" rows="3" style="resize:none;" placeholder="<?php echo __('address_placeholder', ['default' => 'Building, Street, City...']); ?>"></textarea>
                    </div>
                </div>

                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--pos-border);">
                    <button type="button" class="btn btn-outline" onclick="closeCustomerModal()"><?php echo __('cancel'); ?></button>
                    <button type="submit" class="btn btn-primary" style="padding: 10px 24px;">
                        <i class="fas fa-check-circle" style="margin-right: 6px;"></i> <?php echo __('save'); ?>
                    </button>
                </div>
            </form>
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

        function openAddCustomerModal() {
            document.getElementById('custModalTitle').textContent = '<?php echo __('add_customer'); ?>';
            document.getElementById('customerForm').action = '<?php echo htmlspecialchars($posUrl('customers/create')); ?>';
            document.getElementById('custName').value = '';
            document.getElementById('custPhone').value = '';
            document.getElementById('custEmail').value = '';
            document.getElementById('custAddress').value = '';
            document.getElementById('customerModal').classList.add('active');
        }

        function openEditCustomerModal(customer) {
            document.getElementById('custModalTitle').textContent = '<?php echo __('profile_update'); ?>';
            document.getElementById('customerForm').action = '<?php echo htmlspecialchars($posUrl('customers/')); ?>' + customer.id + '/edit';
            document.getElementById('custName').value = customer.name || '';
            document.getElementById('custPhone').value = customer.phone || '';
            document.getElementById('custEmail').value = customer.email || '';
            document.getElementById('custAddress').value = customer.address || '';
            document.getElementById('customerModal').classList.add('active');
        }

        function closeCustomerModal() {
            document.getElementById('customerModal').classList.remove('active');
        }

        document.getElementById('customerModal').addEventListener('click', function(e) {
            if (e.target === this) closeCustomerModal();
        });
    </script>
    
    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>


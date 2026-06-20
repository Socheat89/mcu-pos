<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('orders'); ?> - <?php echo htmlspecialchars($tenantName ?? 'POS'); ?></title>
     <link href="<?php echo mc_base_path(); ?>/public/css/pos_template.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Outfit:wght@300;400;500;600;700;800;900&family=Battambang:wght@100;300;400;700;900&display=swap" rel="stylesheet">
    <style>
        .search-container { position: relative; margin-bottom: 24px; }
        .search-container i { position: absolute; left: 20px; top: 16px; color: var(--pos-primary); font-size: 18px; }
        .search-container input { width: 100%; padding: 14px 20px 14px 54px; border-radius: var(--pos-radius); border: 1.5px solid var(--pos-border); background: #ffffff; color: var(--pos-text); font-size: 15px; font-weight: 600; outline: none; transition: all 0.3s; }
        .search-container input:focus { border-color: var(--pos-primary); background: #ffffff; box-shadow: 0 0 0 4px rgba(var(--pos-primary-rgb), 0.15); }
        
        .avatar-box { width: 36px; height: 36px; border-radius: var(--pos-radius); background: var(--pos-primary-light); display: grid; place-items: center; font-size: 14px; font-weight: 900; color: var(--pos-primary); border: 1px solid rgba(var(--pos-primary-rgb), 0.2); }
    </style>
</head>
<body class="pos-app">
    <?php $activeNav = 'orders'; include __DIR__ . '/partials/navbar.php'; ?>

    <div class="fade-in">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
            <div class="pos-title">
                <h1><?php echo __('transaction_history'); ?></h1>
                <p><?php echo __('orders_management_msg'); ?></p>
            </div>
            <a href="<?php echo htmlspecialchars($posUrl('pos')); ?>" class="btn btn-primary">
                <i class="fas fa-desktop"></i> <?php echo __('open_terminal'); ?>
            </a>
        </div>

        <div class="pos-grid cols-4" style="margin-bottom: 32px;">
            <div class="pos-stat">
                <span class="k"><?php echo __('total_orders'); ?></span>
                <p class="v"><?php echo count($orders); ?></p>
                <div class="chip" style="background: rgba(99, 102, 241, 0.1); color: var(--pos-primary);"><i class="fas fa-receipt"></i></div>
            </div>
            <div class="pos-stat">
                <span class="k"><?php echo __('success_rate'); ?></span>
                <?php 
                $completed = count(array_filter($orders, fn($o) => $o['status'] === 'completed'));
                $total = count($orders) ?: 1;
                $rate = round(($completed / $total) * 100);
                ?>
                <p class="v"><?php echo $rate; ?>%</p>
                <div class="chip" style="background: rgba(16, 185, 129, 0.1); color: var(--pos-success);"><i class="fas fa-check-double"></i></div>
            </div>
        </div>

        <div class="search-container">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="<?php echo __('search_orders_placeholder'); ?>" onkeyup="searchOrders()">
        </div>

        <div class="pos-table-container">
            <table class="pos-table" id="ordersTable">
                <thead>
                    <tr>
                        <th style="width: 100px;"><?php echo __('reference'); ?></th>
                        <th><?php echo __('customer'); ?></th>
                        <th><?php echo __('date_time'); ?></th>
                        <th><?php echo __('amount'); ?></th>
                        <th><?php echo __('status'); ?></th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="6" style="padding: 100px; text-align: center;">
                                <div style="width: 80px; height: 80px; background: rgba(255,255,255,0.03); border: 1px solid var(--pos-border); border-radius: 50%; display: grid; place-items: center; margin: 0 auto 20px;">
                                    <i class="fas fa-history" style="font-size: 32px; color: var(--pos-text-dim);"></i>
                                </div>
                                <h3 style="color: var(--pos-text); font-weight: 800; margin: 0;"><?php echo __('no_transactions_found'); ?></h3>
                                <p style="color: var(--pos-text-muted); margin-top: 8px;"><?php echo __('sales_history_msg'); ?></p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $o): 
                            $status = $o['status'] ?? 'pending';
                            $badge = ($status === 'completed') ? 'badge-success' : (($status === 'cancelled') ? 'badge-danger' : 'badge-warning');
                        ?>
                            <tr class="order-row">
                                <td style="font-weight: 800; color: var(--pos-primary);">#<?php echo $o['id']; ?></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div class="avatar-box">
                                            <?php echo strtoupper(substr($o['customer_name'] ?? 'W', 0, 1)); ?>
                                        </div>
                                        <div style="font-weight: 700; color: var(--pos-text);"><?php echo htmlspecialchars($o['customer_name'] ?? 'Walk-in Customer'); ?></div>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size: 14px; font-weight: 600; color: var(--pos-text);"><?php echo date('M d, Y', strtotime($o['created_at'])); ?></div>
                                    <div style="font-size: 12px; color: var(--pos-text-muted); font-weight: 600; margin-top: 2px;"><?php echo date('h:i A', strtotime($o['created_at'])); ?></div>
                                </td>
                                <td>
                                    <div style="font-weight: 900; color: var(--pos-text); font-size: 16px;">$<?php echo number_format($o['total'], 2); ?></div>
                                </td>
                                <td>
                                    <span class="badge <?php echo $badge; ?>">
                                        <?php echo ucfirst($status); ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; justify-content: flex-end; gap: 10px;">
                                        <a href="<?php echo htmlspecialchars($posUrl('orders/' . $o['id'])); ?>" class="pos-icon-btn" title="View Details">
                                            <i class="fas fa-eye" style="font-size: 14px;"></i>
                                        </a>
                                        <a href="<?php echo htmlspecialchars($posUrl('orders/' . $o['id'] . '/receipt')); ?>" target="_blank" class="pos-icon-btn" title="Print">
                                            <i class="fas fa-print" style="font-size: 14px;"></i>
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
        function searchOrders() {
            const filter = document.getElementById('searchInput').value.toUpperCase();
            const rows = document.querySelectorAll('.order-row');
            rows.forEach(row => {
                const text = row.innerText.toUpperCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        }
    </script>
    
    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

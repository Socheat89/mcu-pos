<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('transaction_details'); ?> - <?php echo htmlspecialchars($tenantName ?? 'POS'); ?></title>
    <link href="<?php echo mc_base_path(); ?>/public/css/pos_template.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Outfit:wght@300;400;500;600;700;800;900&family=Battambang:wght@100;300;400;700;900&display=swap" rel="stylesheet">
    <style>
        body, h1, h2, h3, h4, h5, h6, p, span, a, button, input, select, textarea {
            font-family: 'Battambang', 'Outfit', 'Inter', sans-serif !important;
        }
        .detail-card { background: white; border-radius: 24px; padding: 32px; border: 1.5px solid var(--pos-border); }
        .detail-group { margin-bottom: 24px; }
        .detail-label { font-size: 11px; font-weight: 800; color: var(--pos-text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; display: block; }
        .detail-value { font-size: 15px; font-weight: 700; color: var(--pos-text); }
        
        .summary-box { background: var(--pos-gradient-primary); color: white; border-radius: 24px; padding: 32px; display: flex; flex-direction: column; justify-content: space-between; position: relative; overflow: hidden; }
        .summary-box::after { content: '\f51e'; font-family: 'Font Awesome 6 Free'; font-weight: 900; position: absolute; right: -20px; bottom: -20px; font-size: 120px; opacity: 0.1; }
    </style>
</head>
<body class="pos-app">
    <?php $activeNav = 'orders'; include __DIR__ . '/partials/navbar.php'; ?>

    <div class="fade-in">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
            <div class="pos-title">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                    <a href="<?php echo htmlspecialchars($posUrl('orders')); ?>" class="pos-icon-btn" style="width: 36px; height: 36px;"><i class="fas fa-arrow-left"></i></a>
                    <span class="badge badge-primary"><?php echo __('invoice_information'); ?></span>
                </div>
                <h1><?php echo __('order_ref'); ?><?php echo $order['id']; ?></h1>
                <p><?php echo __('order_detailed_view_msg'); ?></p>
            </div>
            <div style="display: flex; gap: 12px;">
                <?php 
                    $stat = $order['status'] ?? 'pending';
                    $badge = ($stat === 'completed') ? 'badge-success' : (($stat === 'cancelled') ? 'badge-danger' : 'badge-warning');
                ?>
                <span class="badge <?php echo $badge; ?>" style="font-size: 14px; padding: 10px 20px;">
                    <i class="fas <?php echo $stat === 'completed' ? 'fa-check-circle' : ($stat === 'pending' ? 'fa-clock' : 'fa-times-circle'); ?>"></i>
                    <?php echo __($stat); ?>
                </span>
            </div>
        </div>

        <div class="pos-grid cols-3" style="margin-bottom: 32px; align-items: stretch;">
            <!-- Customer Card -->
            <div class="detail-card">
                <span class="detail-label"><?php echo __('billing_to'); ?></span>
                <div style="display: flex; align-items: center; gap: 16px; margin-top: 12px;">
                    <div style="width: 48px; height: 48px; border-radius: 12px; background: var(--pos-primary-light); color: var(--pos-primary); display: grid; place-items: center; font-size: 18px; font-weight: 900;">
                        <?php echo strtoupper(substr($order['customer_name'] ?? 'W', 0, 1)); ?>
                    </div>
                    <div>
                        <div class="detail-value" style="font-size: 16px;"><?php echo htmlspecialchars($order['customer_name'] ?? __('walk_in_customer')); ?></div>
                        <div style="font-size: 13px; color: var(--pos-text-muted); font-weight: 600; margin-top: 2px;"><?php echo htmlspecialchars($order['phone'] ?? __('no_contact_record')); ?></div>
                    </div>
                </div>
            </div>

            <!-- Meta Card -->
            <div class="detail-card">
                <span class="detail-label"><?php echo __('transaction_context'); ?></span>
                <div style="margin-top: 16px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <span class="detail-label" style="font-size: 10px; opacity: 0.7;"><?php echo __('date_placed'); ?></span>
                        <div class="detail-value"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></div>
                    </div>
                    <div>
                        <span class="detail-label" style="font-size: 10px; opacity: 0.7;"><?php echo __('payment_mode'); ?></span>
                        <div class="detail-value"><?php echo strtoupper($order['payment_method'] ?? 'CASH'); ?></div>
                    </div>
                </div>
            </div>

            <!-- Totals Card -->
            <div class="summary-box pos-shadow-xl">
                <div>
                    <span class="detail-label" style="color: rgba(255,255,255,0.7);"><?php echo __('net_payable'); ?></span>
                    <div style="font-size: 36px; font-weight: 900; margin-top: 8px;">$<?php echo number_format($order['total'], 2); ?></div>
                </div>
                <div style="font-size: 13px; font-weight: 600; opacity: 0.9;"><?php echo __('total_value_tax_msg'); ?></div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="pos-table-container" style="margin-bottom: 32px;">
            <div style="padding: 24px; border-bottom: 1.5px solid var(--pos-border); display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-weight: 900; margin: 0; font-size: 16px;"><?php echo __('product_ledger'); ?></h3>
                <span style="font-size: 12px; font-weight: 800; color: var(--pos-text-muted);"><?php echo __('unique_items', ['count' => count($order['items'])]); ?></span>
            </div>
            <table class="pos-table">
                <thead>
                    <tr>
                        <th><?php echo __('line_description'); ?></th>
                        <th style="text-align: center;"><?php echo __('qty'); ?></th>
                        <th style="text-align: right;"><?php echo __('unit_price'); ?></th>
                        <th style="text-align: right;"><?php echo __('subtotal'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order['items'] as $item): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 40px; height: 40px; border-radius: 10px; background: #f8fafc; border: 1px solid var(--pos-border); display: grid; place-items: center;">
                                        <i class="fas fa-barcode" style="color: var(--pos-text-muted); font-size: 14px;"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight: 800;"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                        <div style="font-size: 11px; color: var(--pos-text-muted); font-weight: 600;">REF: <?php echo $item['product_id']; ?></div>
                                    </div>
                                </div>
                            </td>
                            <td style="text-align: center; font-weight: 700;">&times; <?php echo $item['quantity']; ?></td>
                            <td style="text-align: right; font-weight: 700;">$<?php echo number_format($item['unit_price'], 2); ?></td>
                            <td style="text-align: right; font-weight: 900; color: var(--pos-primary);">$<?php echo number_format($item['total'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div style="padding: 40px; background: #f8fafc; display: flex; justify-content: flex-end; border-top: 1.5px solid var(--pos-border);">
                <div style="width: 320px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                        <span style="font-weight: 700; color: var(--pos-text-muted);"><?php echo __('line_subtotal'); ?></span>
                        <span style="font-weight: 800;">$<?php echo number_format($order['total'], 2); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1.5px dashed var(--pos-border);">
                        <span style="font-weight: 700; color: var(--pos-text-muted);"><?php echo __('surcharge_tax'); ?></span>
                        <span style="font-weight: 800;">$0.00</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-weight: 900; font-size: 18px;"><?php echo __('grand_total'); ?></span>
                        <span style="font-weight: 900; font-size: 28px; color: var(--pos-primary);">$<?php echo number_format($order['total'], 2); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div style="display: flex; justify-content: center; gap: 16px; margin-bottom: 40px;">
            <?php if ($order['status'] === 'pending'): ?>
                <a href="<?php echo htmlspecialchars($posUrl('orders/' . $order['id'] . '/complete')); ?>" class="btn btn-primary" style="padding: 16px 32px;" data-pos-confirm="<?php echo __('apply_payment_confirm'); ?>">
                    <i class="fas fa-check-circle"></i> <?php echo __('finalize_transaction'); ?>
                </a>
            <?php endif; ?>
            <a href="<?php echo htmlspecialchars($posUrl('orders/' . $order['id'] . '/receipt')); ?>" class="btn btn-outline" style="padding: 16px 32px;" target="_blank">
                <i class="fas fa-print"></i> <?php echo __('generate_receipt'); ?>
            </a>
            <a href="<?php echo htmlspecialchars($posUrl('orders')); ?>" class="btn" style="padding: 16px 32px; background: white; color: var(--pos-text-muted); border: 1.5px solid var(--pos-border);">
                <i class="fas fa-history"></i> <?php echo __('return_to_logs'); ?>
            </a>
        </div>
    </div>


    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

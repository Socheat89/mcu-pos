<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<<<<<<< HEAD
    <title><?php echo __('held_orders'); ?> - <?php echo htmlspecialchars($tenantName ?? 'POS'); ?></title>
=======
    <title><?php echo __('on_hold'); ?> - <?php echo htmlspecialchars($tenantName ?? 'POS'); ?></title>
>>>>>>> 062e3cc8d9b9f40dc40c6d6c6835e28f6f8a0d77
    <link href="<?php echo mc_base_path(); ?>/public/css/pos_template.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Outfit:wght@300;400;500;600;700;800;900&family=Battambang:wght@100;300;400;700;900&display=swap" rel="stylesheet">
    <style>
        body, h1, h2, h3, h4, h5, h6, p, span, a, button, input, select, textarea {
            font-family: 'Battambang', 'Outfit', 'Inter', sans-serif !important;
        }
<<<<<<< HEAD
        .hold-card { background: white; border-radius: 20px; border: 1.5px solid var(--pos-border); padding: 24px; display: flex; align-items: center; justify-content: space-between; transition: all 0.2s; margin-bottom: 16px; position: relative; overflow: hidden; }
        .hold-card::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 6px; background: var(--pos-warning); opacity: 0.5; }
        .hold-card:hover { transform: translateY(-4px); border-color: var(--pos-warning); box-shadow: var(--pos-shadow-lg); }
        .hold-meta-tag { display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 800; color: var(--pos-text-muted); text-transform: uppercase; letter-spacing: 0.5px; background: #f8fafc; padding: 4px 10px; border-radius: 8px; border: 1px solid var(--pos-border); }
=======
        .hold-card { background: var(--pos-card); backdrop-filter: blur(12px); border-radius: 20px; border: 1px solid var(--pos-border); padding: 24px; display: flex; align-items: center; justify-content: space-between; transition: all 0.2s; margin-bottom: 16px; position: relative; overflow: hidden; }
        .hold-card::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 6px; background: var(--pos-warning); opacity: 0.5; }
        .hold-card:hover { transform: translateY(-4px); border-color: var(--pos-primary); box-shadow: var(--pos-shadow-lg), var(--pos-shadow-glow); }
        .hold-meta-tag { display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 800; color: var(--pos-text-muted); text-transform: uppercase; letter-spacing: 0.5px; background: rgba(255, 255, 255, 0.03); padding: 4px 10px; border-radius: 8px; border: 1px solid var(--pos-border); }
>>>>>>> 062e3cc8d9b9f40dc40c6d6c6835e28f6f8a0d77
    </style>
</head>
<body class="pos-app">
    <?php $activeNav = 'holds'; include __DIR__ . '/partials/navbar.php'; ?>

    <div class="fade-in">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 40px;">
            <div class="pos-title">
<<<<<<< HEAD
                <div style="display: inline-flex; align-items: center; gap: 8px; margin-bottom: 12px; background: #fffbeb; padding: 8px 16px; border-radius: 12px; color: #d97706; font-weight: 800; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">
                    <i class="fas fa-pause-circle"></i> <?php echo __('transaction_queue'); ?>
                </div>
                <h1><?php echo __('held_orders_history'); ?></h1>
=======
                <div style="display: inline-flex; align-items: center; gap: 8px; margin-bottom: 12px; background: rgba(245, 158, 11, 0.12); padding: 8px 16px; border-radius: 12px; color: var(--pos-warning); border: 1px solid rgba(245, 158, 11, 0.2); font-weight: 800; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">
                    <i class="fas fa-pause-circle"></i> <?php echo __('transaction_queue'); ?>
                </div>
                <h1><?php echo __('on_hold'); ?></h1>
>>>>>>> 062e3cc8d9b9f40dc40c6d6c6835e28f6f8a0d77
                <p><?php echo __('held_orders_msg'); ?></p>
            </div>
            <a href="<?php echo htmlspecialchars($posUrl('pos')); ?>" class="btn btn-primary" style="padding: 14px 28px;">
                <i class="fas fa-plus-circle"></i> <?php echo __('start_new_checkout'); ?>
            </a>
        </div>

        <?php if (!count($heldOrders ?? [])): ?>
            <div class="pos-card" style="padding: 100px 40px; text-align: center; border: 2px dashed var(--pos-border); background: transparent;">
<<<<<<< HEAD
                <div style="width: 100px; height: 100px; background: white; border-radius: 50%; display: grid; place-items: center; margin: 0 auto 24px; box-shadow: var(--pos-shadow-sm);">
=======
                <div style="width: 100px; height: 100px; background: rgba(255,255,255,0.03); border: 1px solid var(--pos-border); border-radius: 50%; display: grid; place-items: center; margin: 0 auto 24px; box-shadow: var(--pos-shadow-sm);">
>>>>>>> 062e3cc8d9b9f40dc40c6d6c6835e28f6f8a0d77
                    <i class="fas fa-file-invoice" style="font-size: 40px; color: var(--pos-border);"></i>
                </div>
                <h3 style="font-weight: 900; color: var(--pos-text);"><?php echo __('no_orders_standby'); ?></h3>
                <p style="color: var(--pos-text-muted); font-size: 16px; margin-top: 8px;"><?php echo __('all_transactions_finalized_msg'); ?></p>
            </div>
        <?php else: ?>
            <div style="max-width: 1000px; margin: 0 auto;">
                <?php foreach ($heldOrders as $o): ?>
                    <?php
                        $id = (int)($o['id'] ?? 0);
                        $total = (float)($o['total'] ?? 0);
                        $cust = $o['customer_name'] ?? __('walk_in_customer');
                        $date = date('M d, Y • H:i', strtotime($o['created_at']));
                    ?>
                    <div class="hold-card">
                        <div style="display: flex; align-items: center; gap: 20px;">
<<<<<<< HEAD
                            <div style="width: 56px; height: 56px; border-radius: 14px; background: #fffcf0; border: 1px solid #fef3c7; color: #f59e0b; display: grid; place-items: center; font-size: 20px;">
=======
                            <div style="width: 56px; height: 56px; border-radius: 14px; background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.2); color: var(--pos-warning); display: grid; place-items: center; font-size: 20px;">
>>>>>>> 062e3cc8d9b9f40dc40c6d6c6835e28f6f8a0d77
                                <i class="fas fa-clock-rotate-left"></i>
                            </div>
                            <div>
                                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                                    <span style="font-weight: 900; color: var(--pos-text); font-size: 18px;">ORD-<?php echo $id; ?></span>
                                    <span class="badge badge-warning" style="font-size: 10px;"><?php echo __('pending_completion'); ?></span>
                                </div>
                                <div style="font-weight: 700; color: var(--pos-text); font-size: 15px; margin-bottom: 8px;">
                                    <?php echo htmlspecialchars($cust); ?>
                                    <?php if (!empty($o['notes'])): ?>
                                        <div style="margin-top: 4px; color: var(--pos-primary); font-size: 13px;">
                                            <i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($o['notes']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div style="display: flex; gap: 8px;">
                                    <span class="hold-meta-tag"><i class="far fa-calendar-alt"></i> <?php echo $date; ?></span>
                                    <span class="hold-meta-tag"><i class="fas fa-tags"></i> <?php echo __('skus_count', ['count' => (int)$o['item_lines']]); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div style="display: flex; align-items: center; gap: 40px;">
                            <div style="text-align: right;">
                                <div style="font-size: 11px; font-weight: 800; color: var(--pos-text-muted); text-transform: uppercase; margin-bottom: 4px;"><?php echo __('value'); ?></div>
                                <div style="font-weight: 900; font-size: 24px; color: var(--pos-text);">$<?php echo number_format($total, 2); ?></div>
                            </div>
                            <div style="display: flex; gap: 10px;">
                                <a href="<?php echo htmlspecialchars($posUrl('pos?resume=' . $id)); ?>" class="btn btn-primary" style="padding: 12px 24px; font-weight: 900;">
                                    <i class="fas fa-play" style="font-size: 12px; margin-right: 8px;"></i> <?php echo __('resume'); ?>
                                </a>
                                <a href="<?php echo htmlspecialchars($posUrl('orders/' . $id)); ?>" class="pos-icon-btn" style="width: 48px; height: 48px; border-radius: 14px;" title="View Data">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

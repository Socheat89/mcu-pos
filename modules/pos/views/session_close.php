<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('close_session'); ?> - <?php echo htmlspecialchars($tenantName ?? 'POS'); ?></title>
    <link href="<?php echo mc_base_path(); ?>/public/css/pos_template.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Outfit:wght@300;400;500;600;700;800;900&family=Battambang:wght@100;300;400;700;900&display=swap" rel="stylesheet">
    <style>
        body, h1, h2, h3, h4, h5, h6, p, span, a, button, input, select, textarea {
            font-family: 'Battambang', 'Outfit', 'Inter', sans-serif !important;
        }
        .form-card { background: var(--pos-card); border-radius: 24px; padding: 40px; border: 1.5px solid var(--pos-border); max-width: 700px; margin: 0 auto; backdrop-filter: blur(12px); }
        .summary-row { display: flex; justify-content: space-between; padding: 14px 20px; border-radius: 12px; background: rgba(255, 255, 255, 0.02); border: 1px solid var(--pos-border); margin-bottom: 12px; font-weight: 700; }
        .summary-label { color: var(--pos-text-muted); }
        .summary-value { color: var(--pos-text); }
        .highlight-value { color: var(--pos-primary); font-size: 18px; font-weight: 900; }
    </style>
</head>
<body class="pos-app">
    <?php $activeNav = 'sessions'; include __DIR__ . '/partials/navbar.php'; ?>

    <div class="fade-in">
        <div style="text-align: center; margin-bottom: 40px;">
            <div style="display: inline-flex; align-items: center; gap: 8px; margin-bottom: 12px; background: rgba(244, 63, 94, 0.1); color: #fda4af; padding: 8px 16px; border-radius: 12px; font-weight: 800; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">
                <i class="fas fa-power-off"></i> <?php echo __('session'); ?>
            </div>
            <h1 style="font-size: 36px; font-weight: 900; color: var(--pos-text); margin: 0;"><?php echo __('close_session'); ?></h1>
            <p style="color: var(--pos-text-muted); margin-top: 8px; font-size: 16px;"><?php echo __('enter_closing_balance'); ?></p>
        </div>

        <div class="form-card pos-shadow-xl">
            <form method="POST" id="closeSessionForm">
                <section style="margin-bottom: 32px;">
                    <h3 style="font-size: 14px; font-weight: 900; color: var(--pos-primary); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
                        <span style="width: 24px; height: 1.5px; background: var(--pos-primary);"></span>
                        <?php echo __('session_summary'); ?>
                    </h3>
                    
                    <div class="summary-row">
                        <span class="summary-label"><?php echo __('opening_balance'); ?></span>
                        <span class="summary-value">$<?php echo number_format($activeSession['opening_balance'], 2); ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span class="summary-label"><?php echo __('cash') . ' ' . __('revenue'); ?></span>
                        <span class="summary-value">$<?php echo number_format($cashSales, 2); ?></span>
                    </div>

                    <?php foreach ($paymentSummary as $method => $amount): 
                        if ($method === 'cash') continue;
                    ?>
                        <div class="summary-row">
                            <span class="summary-label"><?php echo strtoupper($method) . ' ' . __('revenue'); ?></span>
                            <span class="summary-value">$<?php echo number_format($amount, 2); ?></span>
                        </div>
                    <?php endforeach; ?>

                    <div class="summary-row" style="background: rgba(6, 182, 212, 0.05); border-color: rgba(6, 182, 212, 0.2);">
                        <span class="summary-label" style="color: var(--pos-primary);"><?php echo __('expected_balance'); ?> (Cash in Drawer)</span>
                        <span class="highlight-value">$<?php echo number_format($expectedCash, 2); ?></span>
                    </div>
                </section>

                <section style="margin-bottom: 24px;">
                    <h3 style="font-size: 14px; font-weight: 900; color: var(--pos-primary); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
                        <span style="width: 24px; height: 1.5px; background: var(--pos-primary);"></span>
                        <?php echo __('cash_control'); ?>
                    </h3>
                    <div class="pos-form-group">
                        <label class="pos-form-label"><?php echo __('closing_balance'); ?> (Actual Cash in Drawer) <span style="color:red;">*</span></label>
                        <input type="number" step="0.01" min="0" name="closing_balance" class="pos-form-control" value="<?php echo number_format($expectedCash, 2, '.', ''); ?>" required style="font-size: 20px; font-weight: 800; text-align: center; color: var(--pos-success);">
                    </div>
                </section>

                <div style="display: flex; justify-content: flex-end; gap: 16px; margin-top: 32px; border-top: 1.5px solid var(--pos-border); padding-top: 24px;">
                    <a href="<?php echo htmlspecialchars($posUrl('sessions')); ?>" class="btn btn-outline" style="min-width: 140px; text-decoration: none;">
                        <?php echo __('cancel'); ?>
                    </a>
                    <button type="button" id="closeSessionBtn" class="btn" style="min-width: 200px; background: var(--pos-danger); color: white; border: none; box-shadow: 0 10px 25px rgba(244, 63, 94, 0.3);">
                        <i class="fas fa-power-off" style="margin-right: 8px;"></i> <?php echo __('close_session'); ?>
                    </button>
                </div>
            </form>

            <script>
                document.getElementById('closeSessionBtn').addEventListener('click', function () {
                    if (window.POSUI && window.POSUI.confirm) {
                        POSUI.confirm({
                            type: 'danger',
                            title: '<?php echo __('close_session'); ?>',
                            subtitle: '<?php echo __('close_session_confirm'); ?>',
                            message: '<?php echo __('cash_control'); ?>: <strong>$<?php echo number_format($expectedCash, 2); ?></strong>',
                            okText: '<?php echo __('close_session'); ?>',
                            cancelText: '<?php echo __('cancel'); ?>',
                            onOk: function () {
                                document.getElementById('closeSessionForm').submit();
                            }
                        });
                    } else {
                        // Fallback if POSUI not loaded yet
                        document.getElementById('closeSessionForm').submit();
                    }
                });
            </script>
        </div>
    </div>

    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

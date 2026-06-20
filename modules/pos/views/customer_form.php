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
        .form-card { background: var(--pos-card); border-radius: var(--pos-radius-lg); padding: 40px; border: 1px solid var(--pos-border); max-width: 800px; margin: 0 auto; }
    </style>
</head>
<body class="pos-app">
    <?php $activeNav = 'customers'; include __DIR__ . '/partials/navbar.php'; ?>

    <div class="fade-in">
        <div style="text-align: center; margin-bottom: 40px;">
            <div style="display: inline-flex; align-items: center; gap: 8px; margin-bottom: 12px; background: #ecfdf5; color: var(--pos-success); padding: 6px 12px; border-radius: var(--pos-radius); font-weight: 800; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; border: 1px solid #bbf7d0;">
                <i class="fas fa-users-cog"></i> <?php echo __('client_relations'); ?>
            </div>
            <h1 style="font-size: 36px; font-weight: 900; color: var(--pos-text); margin: 0;"><?php echo isset($customer) ? __('profile_update') : __('new_client_registration'); ?></h1>
            <p style="color: var(--pos-text-muted); margin-top: 8px; font-size: 16px;"><?php echo __('customer_records_msg'); ?></p>
        </div>

        <div class="form-card pos-shadow-xl">
            <form method="POST">
                
                <section style="margin-bottom: 32px;">
                    <h3 style="font-size: 14px; font-weight: 900; color: var(--pos-primary); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
                        <span style="width: 24px; height: 1.5px; background: var(--pos-primary);"></span>
                        <?php echo __('personal_identity'); ?>
                    </h3>
                    <div class="pos-form-group">
                        <label class="pos-form-label"><?php echo __('full_display_name'); ?> <span style="color:red;">*</span></label>
                        <input type="text" name="name" class="pos-form-control" value="<?php echo htmlspecialchars($customer['name'] ?? ''); ?>" required placeholder="<?php echo __('enter_name_placeholder', ['default' => 'e.g. Johnathan Doe']); ?>">
                    </div>
                </section>

                <section style="margin-bottom: 32px;">
                    <h3 style="font-size: 14px; font-weight: 900; color: var(--pos-primary); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
                        <span style="width: 24px; height: 1.5px; background: var(--pos-primary);"></span>
                        <?php echo __('communication'); ?>
                    </h3>
                    <div class="pos-grid cols-2">
                        <div class="pos-form-group">
                            <label class="pos-form-label"><?php echo __('email_address'); ?></label>
                            <input type="email" name="email" class="pos-form-control" value="<?php echo htmlspecialchars($customer['email'] ?? ''); ?>" placeholder="client@company.com">
                        </div>
                        <div class="pos-form-group">
                            <label class="pos-form-label"><?php echo __('primary_phone'); ?></label>
                            <input type="tel" name="phone" class="pos-form-control" value="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>" placeholder="+00 000 000 000">
                        </div>
                    </div>
                </section>

                <section>
                    <h3 style="font-size: 14px; font-weight: 900; color: var(--pos-primary); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
                        <span style="width: 24px; height: 1.5px; background: var(--pos-primary);"></span>
                        <?php echo __('delivery_location'); ?>
                    </h3>
                    <div class="pos-form-group">
                        <label class="pos-form-label"><?php echo __('full_physical_address'); ?></label>
                        <textarea name="address" class="pos-form-control" rows="4" style="resize: none;" placeholder="<?php echo __('address_placeholder', ['default' => 'Building, Street, City, State...']); ?>"><?php echo htmlspecialchars($customer['address'] ?? ''); ?></textarea>
                    </div>
                </section>

                <div style="display: flex; justify-content: flex-end; gap: 16px; margin-top: 48px; border-top: 1.5px solid var(--pos-border); padding-top: 32px;">
                    <a href="<?php echo htmlspecialchars($posUrl('customers')); ?>" class="btn btn-outline" style="min-width: 140px;">
                        <?php echo __('discard'); ?>
                    </a>
                    <button type="submit" class="btn btn-primary" style="min-width: 200px;">
                        <i class="fas fa-check-circle" style="margin-right: 8px;"></i> <?php echo isset($customer) ? __('update_profile') : __('register_client'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('open_session'); ?> - <?php echo htmlspecialchars($tenantName ?? 'POS'); ?></title>
    <link href="<?php echo mc_base_path(); ?>/public/css/pos_template.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Outfit:wght@300;400;500;600;700;800;900&family=Battambang:wght@100;300;400;700;900&display=swap" rel="stylesheet">
    <style>
        body, h1, h2, h3, h4, h5, h6, p, span, a, button, input, select, textarea {
            font-family: 'Battambang', 'Outfit', 'Inter', sans-serif !important;
        }
        .form-card { background: var(--pos-card); border-radius: 24px; padding: 40px; border: 1.5px solid var(--pos-border); max-width: 600px; margin: 0 auto; backdrop-filter: blur(12px); }
    </style>
</head>
<body class="pos-app">
    <?php $activeNav = 'sessions'; include __DIR__ . '/partials/navbar.php'; ?>

    <div class="fade-in">
        <div style="text-align: center; margin-bottom: 40px;">
            <div style="display: inline-flex; align-items: center; gap: 8px; margin-bottom: 12px; background: rgba(6, 182, 212, 0.1); color: var(--pos-primary); padding: 8px 16px; border-radius: 12px; font-weight: 800; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">
                <i class="fas fa-play"></i> <?php echo __('session'); ?>
            </div>
            <h1 style="font-size: 36px; font-weight: 900; color: var(--pos-text); margin: 0;"><?php echo __('open_session'); ?></h1>
            <p style="color: var(--pos-text-muted); margin-top: 8px; font-size: 16px;"><?php echo __('enter_opening_balance'); ?></p>
        </div>

        <div class="form-card pos-shadow-xl">
            <form method="POST">
                <?php
                // Auto-detect current store
                $currentStore = null;
                if (class_exists('Store')) {
                    $currentStore = Store::getCurrent();
                }
                if (!$currentStore) {
                    $userStoreId = Auth::user()['current_store_id'] ?? null;
                    if ($userStoreId && class_exists('Store')) {
                        $currentStore = Store::getById($userStoreId);
                    }
                }
                // Fallback: get default store
                if (!$currentStore && class_exists('Store')) {
                    $currentStore = Store::getDefault();
                }
                ?>
                <input type="hidden" name="store_id" value="<?php echo $currentStore ? $currentStore['id'] : ''; ?>">

                <section style="margin-bottom: 24px;">
                    <h3 style="font-size: 14px; font-weight: 900; color: var(--pos-primary); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
                        <span style="width: 24px; height: 1.5px; background: var(--pos-primary);"></span>
                        <?php echo __('cash_control'); ?>
                    </h3>

                    <?php if (!empty($allStores) && count($allStores) > 1): ?>
                    <!-- Multiple stores: show dropdown to override -->
                    <div class="pos-form-group">
                        <label class="pos-form-label"><i class="fas fa-store-alt" style="margin-right:6px; color:var(--pos-primary);"></i><?php echo __('store'); ?></label>
                        <select name="store_id" class="pos-form-control" onchange="document.querySelector('input[name=store_id]').value=this.value">
                            <?php foreach ($allStores as $st): ?>
                                <option value="<?php echo $st['id']; ?>" <?php echo ($currentStore && $currentStore['id'] == $st['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($st['code'] ? '[' . $st['code'] . '] ' : '') . htmlspecialchars($st['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php elseif ($currentStore): ?>
                    <!-- Single store: show it as a badge -->
                    <div class="pos-form-group">
                        <label class="pos-form-label"><i class="fas fa-store-alt" style="margin-right:6px; color:var(--pos-primary);"></i><?php echo __('store'); ?></label>
                        <div style="padding:14px 18px;background:rgba(var(--pos-primary-rgb),0.05);border:1.5px solid rgba(var(--pos-primary-rgb),0.15);border-radius:14px;display:flex;align-items:center;gap:10px;font-weight:700;font-size:14px;color:var(--pos-text);">
                            <span style="background:var(--pos-primary-light);color:var(--pos-primary);padding:4px 10px;border-radius:8px;font-size:11px;font-weight:800;"><?php echo htmlspecialchars($currentStore['code'] ?: 'ST'); ?></span>
                            <?php echo htmlspecialchars($currentStore['name']); ?>
                            <span style="margin-left:auto;font-size:11px;color:var(--pos-text-muted);"><i class="fas fa-check-circle" style="color:var(--pos-success);"></i> <?php echo __('auto_detected'); ?></span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="pos-form-group">
                        <label class="pos-form-label"><?php echo __('opening_balance'); ?> (USD) <span style="color:red;">*</span></label>
                        <input type="number" step="0.01" min="0" name="opening_balance" class="pos-form-control" value="0.00" required style="font-size: 20px; font-weight: 800; text-align: center; color: var(--pos-primary);">
                    </div>
                </section>

                <div style="display: flex; justify-content: flex-end; gap: 16px; margin-top: 32px; border-top: 1.5px solid var(--pos-border); padding-top: 24px;">
                    <a href="<?php echo htmlspecialchars($posUrl('sessions')); ?>" class="btn btn-outline" style="min-width: 140px; text-decoration: none;">
                        <?php echo __('cancel'); ?>
                    </a>
                    <button type="submit" class="btn btn-primary" style="min-width: 200px; box-shadow: 0 10px 25px rgba(6, 182, 212, 0.3);">
                        <i class="fas fa-play-circle" style="margin-right: 8px;"></i> <?php echo __('open_session'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

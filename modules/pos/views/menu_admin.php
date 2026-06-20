<?php
$tenant = class_exists('Tenant') ? (Tenant::getCurrent() ?? []) : [];
$tenantName = is_array($tenant) && !empty($tenant['name']) ? $tenant['name'] : 'Tenant';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('digital_menu'); ?> - <?php echo htmlspecialchars($tenantName); ?></title>
    <link href="<?php echo mc_base_path(); ?>/public/css/pos_template.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Outfit:wght@300;400;500;600;700;800;900&family=Battambang:wght@100;300;400;700;900&display=swap" rel="stylesheet">
    <style>
        body, h1, h2, h3, h4, h5, h6, p, span, a, button, input, select, textarea {
            font-family: 'Battambang', 'Outfit', 'Inter', sans-serif !important;
        }
        .menu-hero {
            background: var(--pos-gradient-dark);
            border-radius: 32px;
            padding: 40px;
            color: white;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 30px 60px rgba(15, 23, 42, 0.25);
            border: 1px solid var(--pos-border);
        }
        .qr-card {
            background: var(--pos-card);
            backdrop-filter: blur(12px);
            border-radius: 24px;
            padding: 32px;
            border: 1px solid var(--pos-border);
            text-align: center;
        }
        .qr-placeholder {
            background: white;
            border-radius: 20px;
            padding: 20px;
            display: inline-block;
            border: 1px solid var(--pos-border);
            margin-bottom: 24px;
        }
        .link-card {
            background: var(--pos-card);
            backdrop-filter: blur(12px);
            border-radius: 24px;
            padding: 32px;
            border: 1px solid var(--pos-border);
        }
        .copy-input {
            background: #ffffff;
            border: 1.5px solid var(--pos-border);
            border-radius: var(--pos-radius);
            padding: 12px 16px;
            width: 100%;
            font-family: 'Outfit', sans-serif;
            font-weight: 600;
            color: var(--pos-text);
            margin-bottom: 16px;
            outline: none;
            transition: all 0.2s;
        }
        .copy-input:focus {
            border-color: var(--pos-primary);
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(var(--pos-primary-rgb), 0.2);
        }
    </style>
</head>
<body class="pos-app">
    <?php $activeNav = 'digital_menu'; include __DIR__ . '/partials/navbar.php'; ?>

    <div class="fade-in">
        <div class="menu-hero">
            <h1 style="font-size: 36px; font-weight: 900; margin: 0;"><?php echo explode(' ', __('digital_menu'))[0]; ?> <span style="color: var(--pos-primary);"><?php echo explode(' ', __('digital_menu'))[1] ?? ''; ?></span></h1>
            <p style="color: rgba(255,255,255,0.6); margin-top: 8px;"><?php echo __('connect_customers_msg'); ?></p>
        </div>

        <div class="pos-grid cols-2">
            <div class="qr-card">
                <h3 class="pos-card-title" style="margin-bottom: 24px;"><?php echo __('scan_qr_code'); ?></h3>
                <div class="qr-placeholder">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo urlencode($menuUrl); ?>" alt="QR Code" style="width: 200px; height: 200px;">
                </div>
                <div style="display: flex; gap: 12px; justify-content: center;">
                    <button class="btn btn-primary" onclick="window.print()">
                        <i class="fas fa-print"></i> <?php echo __('print_qr'); ?>
                    </button>
                    <a href="https://api.qrserver.com/v1/create-qr-code/?size=500x500&data=<?php echo urlencode($menuUrl); ?>" download="menu-qr.png" target="_blank" class="btn btn-outline">
                        <i class="fas fa-download"></i> <?php echo __('download'); ?>
                    </a>
                </div>
            </div>

            <div class="link-card">
                <h3 class="pos-card-title" style="margin-bottom: 24px;"><?php echo __('shareable_link'); ?></h3>
                <p style="color: var(--pos-text-muted); font-size: 14px; margin-bottom: 16px;"><?php echo __('copy_link_msg'); ?></p>
                <input type="text" class="copy-input" value="<?php echo htmlspecialchars($menuUrl); ?>" readonly id="menuLink">
                <button class="btn btn-primary w-100" onclick="copyLink()">
                    <i class="fas fa-copy"></i> <?php echo __('copy_link'); ?>
                </button>
                
                <div style="margin-top: 32px; padding-top: 32px; border-top: 1.5px solid var(--pos-border);">
                    <h4 style="font-weight: 800; font-size: 16px; margin-bottom: 16px;"><?php echo __('direct_preview'); ?></h4>
                    <a href="<?php echo htmlspecialchars($menuUrl); ?>" target="_blank" class="btn btn-outline w-100">
                        <i class="fas fa-external-link-alt"></i> <?php echo __('open_menu_new_tab'); ?>
                    </a>
                </div>
            </div>
        </div>

        <div class="pos-card pad" style="margin-top: 40px;">
            <div style="display: flex; align-items: center; gap: 20px;">
                <div style="width: 60px; height: 60px; background: rgba(99, 102, 241, 0.1); border-radius: 16px; display: grid; place-items: center; color: var(--pos-primary); font-size: 24px;">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 18px; font-weight: 800;"><?php echo __('realtime_updates'); ?></h3>
                    <p style="margin: 4px 0 0; color: var(--pos-text-muted); font-size: 14px;"><?php echo __('realtime_updates_msg'); ?></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyLink() {
            var copyText = document.getElementById("menuLink");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(copyText.value);
            
            const btn = event.currentTarget;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i> <?php echo __('copied'); ?>';
            btn.classList.replace('btn-primary', 'btn-success');
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.classList.replace('btn-success', 'btn-primary');
            }, 2000);
        }
    </script>

    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>

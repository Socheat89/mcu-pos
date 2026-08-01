<?php
require_once __DIR__ . '/../../../core/helpers/url.php';
$urlPrefix = mc_base_path();
$subdomain = Tenant::getCurrent()['subdomain'] ?? '';
$tenantName = Tenant::getCurrent()['name'] ?? '';

// Determine dashboard URL matching index.php routing
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
$devPosPrefix = $urlPrefix . '/pos/';
$isDevPos = (strpos($requestPath, $devPosPrefix) === 0);

$posBase = $urlPrefix;
if ($isDevPos) {
    $posBase .= '/pos';
} elseif ($subdomain) {
    $posBase .= '/' . $subdomain . '/pos';
} else {
    $posBase .= '/pos';
}
$dashboardUrl = $posBase . '/dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title><?php echo __('pos'); ?> - <?php echo htmlspecialchars($tenantName ?? 'POS'); ?></title>

    <!-- PWA Manifest -->
    <link rel="manifest" href="<?php echo mc_base_path(); ?>/public/manifest.json">
    <meta name="theme-color" content="#06b6d4">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Mekong POS">
    <link rel="apple-touch-icon" href="<?php echo mc_base_path(); ?>/public/images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="<?php echo mc_base_path(); ?>/public/images/logo-192.png">

    <!-- React App Built Assets -->
    <link href="<?php echo mc_base_path(); ?>/public/dist/assets/index.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Battambang:wght@300;400;700;900&display=swap" rel="stylesheet">
    
    <!-- Inline values to pass down to React -->
    <script>
        window.BASE_PATH = "<?php echo mc_base_path(); ?>";
        window.SUBDOMAIN = "<?php echo $subdomain; ?>";
        window.DASHBOARD_URL = "<?php echo htmlspecialchars($dashboardUrl); ?>";
        window.PRODUCTS = <?php echo json_encode(array_map(function($p) {

            $image = !empty($p['image'])
                ? mc_url('uploads/products/' . $p['image'])
                : mc_url('public/images/no-image.svg');
            
            $sizes = [];
            if (!empty($p['sizes'])) {
                foreach ($p['sizes'] as $sz) {
                    $sizes[] = [
                        'id' => (int)$sz['id'],
                        'size_name' => $sz['size_name'],
                        'price' => (float)$sz['price']
                    ];
                }
            }

            $effectiveStock = isset($p['effective_stock']) ? (int)$p['effective_stock'] : ((!isset($p['can_sell']) || $p['can_sell']) ? 9999 : 0);

            return [
                'id' => (int)$p['id'],
                'name' => $p['name'],
                'sku' => $p['sku'] ?? '',
                'barcode' => $p['barcode'] ?? '',
                'price' => (float)$p['price'],
                'stock' => $effectiveStock,
                'stock_quantity' => $effectiveStock,
                'category' => $p['category_name'] ?? 'No Category',
                'image' => $image,
                'sizes' => $sizes,
                'can_sell' => isset($p['can_sell']) ? (bool)$p['can_sell'] : true,
                'stock_error' => $p['stock_error'] ?? ''
            ];
        }, $products)); ?>;
        
        window.CUSTOMERS = <?php echo json_encode(array_map(function($c) {
            return [
                'id' => (int)$c['id'],
                'name' => $c['name'],
                'phone' => $c['phone'] ?? ''
            ];
        }, $customers)); ?>;

        window.PENDING_ORDERS = <?php echo json_encode(array_map(function($mo) {
            return [
                'id' => (int)$mo['id'],
                'total' => (float)$mo['total'],
                'notes' => $mo['notes'] ?? '',
                'item_lines' => (int)$mo['item_lines'],
                'created_at' => $mo['created_at']
            ];
        }, $pendingMenuOrders)); ?>;

        window.SETTINGS = <?php echo json_encode([
            'bank_account' => $settings['bank_account'] ?? '',
            'merchant_name' => $settings['merchant_name'] ?? '',
            'merchant_city' => $settings['merchant_city'] ?? '',
            'phone_number' => $settings['phone_number'] ?? '',
            'store_label' => $settings['store_label'] ?? '',
            'pos_method_cash_enabled' => $settings['pos_method_cash_enabled'] ?? '1',
            'pos_method_khqr_enabled' => $settings['pos_method_khqr_enabled'] ?? '1',
            'pos_method_card_enabled' => $settings['pos_method_card_enabled'] ?? '1',
            'exchange_rate_usd_khr' => $settings['exchange_rate_usd_khr'] ?? '4100',
            'payment_qr_path' => $settings['payment_qr_path'] ?? ($settings['pos_method_khqr_image'] ?? ''),
            'price_decimal_places' => (int)($settings['price_decimal_places'] ?? 2),
        ]); ?>;

        window.CURRENT_LANG = "<?php echo Language::getCurrentLang(); ?>";
        window.ACTIVE_SESSION_ID = <?php echo (int)($activeSession['id'] ?? 0); ?>;
        window.CLOSE_SESSION_URL = "<?php echo $posBase . '/sessions/close'; ?>";

        window.RESUME = <?php

            $resumePayload = null;
            if (isset($resumeOrder) && $resumeOrder) {
                $resumePayload = [
                    'id' => (int)$resumeOrder['id'],
                    'customer_id' => $resumeOrder['customer_id'] !== null ? (int)$resumeOrder['customer_id'] : null,
                    'items' => array_map(function($it) {
                        return [
                            'product_id' => (int)($it['product_id'] ?? 0),
                            'quantity' => (int)($it['quantity'] ?? 0),
                        ];
                    }, $resumeOrder['items'] ?? [])
                ];
            }
            echo json_encode($resumePayload);
        ?>;
    </script>
</head>
<body class="bg-slate-50 text-slate-900 antialiased">
    <div id="root"></div>
    <script type="module" src="<?php echo mc_base_path(); ?>/public/dist/assets/index.js?v=<?php echo time(); ?>"></script>

    <!-- PWA Install Prompt -->
    <div id="pwa-install-banner" style="display:none; position:fixed; bottom:0; left:0; right:0; z-index:9999;
        background:linear-gradient(135deg,#0f766e,#06b6d4); color:white; padding:12px 16px;
        flex-direction:row; align-items:center; justify-content:space-between; gap:12px;
        box-shadow:0 -4px 20px rgba(0,0,0,0.3); font-family:'Sora',sans-serif;">
        <div style="display:flex;align-items:center;gap:10px;min-width:0;">
            <img src="<?php echo mc_base_path(); ?>/public/images/logo-192.png" alt="icon"
                 style="width:40px;height:40px;border-radius:10px;flex-shrink:0;">
            <div style="min-width:0;">
                <div style="font-weight:800;font-size:13px;line-height:1.2;">ដំឡើង Mekong POS</div>
                <div style="font-size:11px;opacity:0.85;">Install app on your phone</div>
            </div>
        </div>
        <div style="display:flex;gap:8px;flex-shrink:0;">
            <button id="pwa-install-btn" style="background:white;color:#0f766e;border:none;
                padding:8px 16px;border-radius:20px;font-weight:800;font-size:12px;cursor:pointer;">
                ដំឡើង
            </button>
            <button id="pwa-dismiss-btn" style="background:rgba(255,255,255,0.2);color:white;border:none;
                padding:8px 12px;border-radius:20px;font-size:12px;cursor:pointer;">✕</button>
        </div>
    </div>

    <script>
        // Register Service Worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('<?php echo mc_base_path(); ?>/public/service-worker.js')
                .then(reg => console.log('[SW] Registered:', reg.scope))
                .catch(err => console.warn('[SW] Error:', err));
        }

        // PWA Install Prompt
        let deferredPrompt = null;
        const banner = document.getElementById('pwa-install-banner');
        const installBtn = document.getElementById('pwa-install-btn');
        const dismissBtn = document.getElementById('pwa-dismiss-btn');

        // Check if already installed
        const isInstalled = window.matchMedia('(display-mode: standalone)').matches
            || window.navigator.standalone === true;

        if (!isInstalled) {
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                deferredPrompt = e;
                // Show banner after 3 seconds
                setTimeout(() => {
                    if (!sessionStorage.getItem('pwa-dismissed')) {
                        banner.style.display = 'flex';
                    }
                }, 3000);
            });

            installBtn.addEventListener('click', async () => {
                if (!deferredPrompt) return;
                banner.style.display = 'none';
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                deferredPrompt = null;
                console.log('[PWA] Install outcome:', outcome);
            });

            dismissBtn.addEventListener('click', () => {
                banner.style.display = 'none';
                sessionStorage.setItem('pwa-dismissed', '1');
            });
        }
    </script>

    <!-- Pre-sale Recipe & Stock Validation Interceptor for POS Terminal Screen -->
    <script>
    (function() {
        // Create custom alert modal
        function showPosAlert(message) {
            const existing = document.getElementById('pos-block-modal');
            if (existing) existing.remove();

            const modal = document.createElement('div');
            modal.id = 'pos-block-modal';
            modal.style.cssText = 'position:fixed; inset:0; background:rgba(0,0,0,0.6); backdrop-filter:blur(4px); z-index:99999; display:flex; align-items:center; justify-content:center; animation:fadeIn 0.2s ease-out; font-family:"Battambang","Sora",sans-serif;';
            modal.innerHTML = `
                <div style="background:#fff; border-radius:20px; width:90%; max-width:440px; box-shadow:0 25px 50px rgba(0,0,0,0.3); overflow:hidden; border:2px solid #fecaca; text-align:center; padding:28px 24px;">
                    <div style="width:60px; height:60px; background:#fee2e2; color:#ef4444; border-radius:50%; display:grid; place-items:center; margin:0 auto 16px; font-size:28px; font-weight:bold;">⚠️</div>
                    <h3 style="margin:0 0 10px; font-size:18px; font-weight:800; color:#991b1b;">មិនអាចបន្ថែមបានឡើយ! / Blocked Sale</h3>
                    <p style="margin:0 0 24px; font-size:14px; color:#4b5563; font-weight:600; line-height:1.6;">${message}</p>
                    <button onclick="document.getElementById('pos-block-modal').remove()" style="background:#ef4444; color:#fff; border:none; padding:12px 28px; border-radius:12px; font-weight:800; font-size:14px; cursor:pointer; box-shadow:0 4px 12px rgba(239,68,68,0.3); width:100%;">
                        យល់ព្រម / OK
                    </button>
                </div>
            `;
            document.body.appendChild(modal);
        }

        // Helper to find unsellable product from text
        function getUnsellableProductFromElement(el) {
            if (!window.PRODUCTS || !Array.isArray(window.PRODUCTS)) return null;
            
            // Traverse up to find card container
            let curr = el;
            let depth = 0;
            while (curr && curr !== document.body && depth < 8) {
                const text = (curr.innerText || '').trim();
                if (text) {
                    for (const p of window.PRODUCTS) {
                        if (p.can_sell === false && p.name) {
                            if (text.includes(p.name)) {
                                return p;
                            }
                        }
                    }
                }
                curr = curr.parentElement;
                depth++;
            }
            return null;
        }

        // Clean click listener for unsellable items
        document.addEventListener('click', function(e) {
            const unsellable = getUnsellableProductFromElement(e.target);
            if (unsellable) {
                e.stopImmediatePropagation();
                e.preventDefault();
                showPosAlert(unsellable.stock_error || ('ផលិតផល "' + unsellable.name + '" មិនទាន់មាន Recipe/គ្រឿងផ្សំ ឬខ្វះគ្រឿងផ្សំក្នុង Store!'));
                return false;
            }
        }, true);
    })();
    </script>

    <?php include __DIR__ . '/gps_tracker.php'; ?>

</body>
</html>

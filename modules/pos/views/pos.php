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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('pos'); ?> - <?php echo htmlspecialchars($tenantName ?? 'POS'); ?></title>
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
            return [
                'id' => (int)$p['id'],
                'name' => $p['name'],
                'sku' => $p['sku'] ?? '',
                'barcode' => $p['barcode'] ?? '',
                'price' => (float)$p['price'],
                'stock' => (int)$p['stock_quantity'],
                'category' => $p['category_name'] ?? 'No Category',
                'image' => $image
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
            'pos_method_card_enabled' => $settings['pos_method_card_enabled'] ?? '1'
        ]); ?>;

        window.CURRENT_LANG = "<?php echo Language::getCurrentLang(); ?>";
        window.ACTIVE_SESSION_ID = <?php echo (int)($activeSession['id'] ?? 0); ?>;

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
</body>
</html>

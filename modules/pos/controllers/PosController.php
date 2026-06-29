<?php
// modules/pos/controllers/PosController.php
require_once __DIR__ . '/../../../core/classes/Database.php';
require_once __DIR__ . '/../../../core/classes/Tenant.php';
require_once __DIR__ . '/../../../core/classes/Settings.php';
require_once __DIR__ . '/../../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../../middleware/TenantMiddleware.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Order.php';
require_once dirname(__DIR__, 3) . '/core/helpers/url.php';

class PosController {
    public function index() {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if (!Tenant::hasModule('pos')) {
            die('POS system not subscribed for your plan');
        }

        // Viewing the POS terminal implies ability to create sales
        if (!Auth::hasPermission('pos', 'write')) {
            die('No permission to access POS Terminal');
        }

        $tenantId = Tenant::getId();
        $products = Product::getAll();
        $customers = $this->getCustomers();
        $pendingMenuOrders = Order::getPending($tenantId);
        
        $settings = Settings::getAll($tenantId);
        
        // Load config from file to ensure we use the latest values
        $bakongConfig = require __DIR__ . '/../../../config/bakong.php';
        
        // Force config values to take precedence over DB settings for consistency
        $settings['bank_account'] = $bakongConfig['bank_account'];
        $settings['merchant_name'] = $bakongConfig['merchant_name'];
        $settings['merchant_city'] = $bakongConfig['merchant_city'];
        $settings['phone_number'] = $bakongConfig['phone_number'];
        $settings['store_label'] = $bakongConfig['store_label'];

        // Ensure defaults for payment methods if not in DB
        $defaults = [
            'pos_method_cash_enabled' => '1',
            'pos_method_khqr_enabled' => '1',
            'pos_method_khqr_image' => mc_url('public/images/khqr_preview.png'),
            'pos_method_card_enabled' => '1',
            'pos_method_transfer_enabled' => '1'
        ];
        foreach ($defaults as $key => $default) {
            if (!isset($settings[$key])) $settings[$key] = $default;
        }

        $resumeOrder = null;
        if (isset($_GET['resume'])) {
            $resumeId = (int)$_GET['resume'];
            if ($resumeId > 0) {
                $resumeOrder = Order::getPendingById($resumeId);
                if (!$resumeOrder) {
                    die('Held order not found');
                }
            }
        }

        include __DIR__ . '/../views/pos.php';
    }

    private function getCustomers() {
        $db = Database::getInstance();
        $tenantId = Tenant::getId();
        return $db->fetchAll("SELECT * FROM customers WHERE tenant_id = ? ORDER BY name", [$tenantId]);
    }
}
?>

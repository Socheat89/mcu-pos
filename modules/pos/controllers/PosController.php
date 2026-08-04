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
        $db = Database::getInstance();
        $activeSession = $db->fetchOne("SELECT * FROM pos_sessions WHERE tenant_id = ? AND status = 'open'", [$tenantId]);
        if (!$activeSession) {
            $prefix = mc_base_path();
            header("Location: " . $prefix . "/" . Tenant::getCurrent()['subdomain'] . "/pos/sessions/open");
            exit;
        }

        require_once __DIR__ . '/../../../core/classes/Store.php';
        if (!empty($activeSession['store_id'])) {
            $switched = Store::setCurrent((int)$activeSession['store_id'], $tenantId);
            // If user is locked to a different store, close the mismatched session
            if (!$switched && Store::isUserLocked()) {
                // Active session is for a different store — close it and redirect to open new one
                $db->update('pos_sessions', ['status' => 'closed', 'closed_at' => date('Y-m-d H:i:s')],
                    'id = ? AND tenant_id = ?', [$activeSession['id'], $tenantId]);
                $prefix = mc_base_path();
                header("Location: " . $prefix . "/" . Tenant::getCurrent()['subdomain'] . "/pos/sessions/open");
                exit;
            }
        }

        $businessType = Settings::get('business_type', $tenantId, 'coffee');
        $currentStore = Store::getCurrent($tenantId);
        $currentStoreId = $currentStore ? (int)$currentStore['id'] : null;

        // Auto-ensure ingredient_store_stock table exists
        try {
            $db->query("
                CREATE TABLE IF NOT EXISTS ingredient_store_stock (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    tenant_id INT NOT NULL,
                    store_id INT NOT NULL,
                    ingredient_id INT NOT NULL,
                    quantity DECIMAL(10,3) NOT NULL DEFAULT 0,
                    unit VARCHAR(50) DEFAULT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY uq_store_ing (store_id, ingredient_id),
                    INDEX idx_tenant_store (tenant_id, store_id),
                    INDEX idx_ingredient (ingredient_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        } catch (\Throwable $e) {}

        $allStores = Store::getAll($tenantId);
        $mainStoreId = null;
        if (!empty($allStores)) {
            $sortedStores = $allStores;
            usort($sortedStores, function($a, $b) {
                $aDef = !empty($a['is_default']) ? 1 : 0;
                $bDef = !empty($b['is_default']) ? 1 : 0;
                if ($aDef !== $bDef) return $bDef - $aDef;
                return (int)$a['id'] - (int)$b['id'];
            });
            $mainStoreId = (int)$sortedStores[0]['id'];
        }

        $products = Product::getAll();

        // Helper to check ingredient store stock (safely falls back to global stock for main store)
        $getIngQty = function(int $storeId, int $ingId) use ($db, $tenantId, $mainStoreId): float {
            if ($storeId > 0) {
                try {
                    $r = $db->fetchOne("SELECT quantity FROM ingredient_store_stock WHERE store_id = ? AND ingredient_id = ? AND tenant_id = ?", [$storeId, $ingId, $tenantId]);
                    if ($r !== false && $r !== null) return (float)$r['quantity'];
                } catch (\Throwable $e) {}
            }
            if ($mainStoreId === null || $storeId === $mainStoreId) {
                try {
                    $ingRow = $db->fetchOne("SELECT stock_quantity FROM ingredients WHERE id = ? AND tenant_id = ?", [$ingId, $tenantId]);
                    return $ingRow ? (float)$ingRow['stock_quantity'] : 0.0;
                } catch (\Throwable $e) {}
            }
            return 0.0;
        };

        // Attach sizes & ingredient validation to each product
        foreach ($products as &$p) {
            $p['sizes'] = Product::getSizes($p['id'], $tenantId);

            if ($businessType === 'coffee') {
                $recipes = $db->fetchAll(
                    "SELECT pr.*, i.name as ingredient_name, i.unit
                     FROM product_recipes pr
                     JOIN ingredients i ON pr.ingredient_id = i.id
                     WHERE pr.product_id = ? AND pr.tenant_id = ?",
                    [$p['id'], $tenantId]
                );

                if (empty($recipes)) {
                    $p['can_sell'] = false;
                    $p['effective_stock'] = 0;
                    $p['stock_error'] = 'ផលិតផល "' . $p['name'] . '" មិនទាន់មាន Recipe/គ្រឿងផ្សំ ទេ មិនអាចលក់បានឡើយ (Please set up a recipe)';
                } else {
                    $canSell = true;
                    $err = '';
                    $maxPossibleQty = 999999;
                    foreach ($recipes as $r) {
                        $needed = (float)$r['quantity'];
                        $avail  = $currentStoreId ? $getIngQty($currentStoreId, (int)$r['ingredient_id']) : 0.0;
                        if ($needed > 0) {
                            $possible = (int)floor($avail / $needed);
                            if ($possible < $maxPossibleQty) {
                                $maxPossibleQty = $possible;
                            }
                        }
                        if ($needed > $avail) {
                            $canSell = false;
                            $unitStr = !empty($r['unit']) ? ' ' . $r['unit'] : '';
                            $err = 'ខ្វះគ្រឿងផ្សំ "' . $r['ingredient_name'] . '" ក្នុង Store (ត្រូវការ ' . $needed . $unitStr . ' ប៉ុន្តែមាន ' . $avail . $unitStr . ')';
                            break;
                        }
                    }
                    if ($canSell && $maxPossibleQty > 0) {
                        $p['can_sell'] = true;
                        $p['effective_stock'] = $maxPossibleQty;
                        $p['stock_error'] = '';
                    } else {
                        $p['can_sell'] = false;
                        $p['effective_stock'] = 0;
                        $p['stock_error'] = $err ?: ('គ្រឿងផ្សំមិនគ្រាន់គ្រាន់សម្រាប់លក់ "' . $p['name'] . '" ឡើយ');
                    }
                }
            } else {
                $storeRow = null;
                if ($currentStoreId) {
                    try {
                        $storeRow = $db->fetchOne("SELECT quantity FROM store_stock WHERE store_id = ? AND product_id = ?", [$currentStoreId, $p['id']]);
                    } catch (\Throwable $e) {}
                }
                $availStock = $storeRow ? (int)$storeRow['quantity'] : (int)($p['stock_quantity'] ?? 0);
                if ($availStock <= 0) {
                    $p['can_sell'] = false;
                    $p['effective_stock'] = 0;
                    $p['stock_error'] = 'ផលិតផល "' . $p['name'] . '" អស់ស្តុកហើយ';
                } else {
                    $p['can_sell'] = true;
                    $p['effective_stock'] = $availStock;
                    $p['stock_error'] = '';
                }
            }
        }
        unset($p);
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

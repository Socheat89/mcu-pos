<?php
// modules/pos/controllers/IngredientController.php
require_once __DIR__ . '/../../../core/classes/Database.php';
require_once __DIR__ . '/../../../core/classes/Tenant.php';
require_once __DIR__ . '/../../../core/classes/Settings.php';
require_once __DIR__ . '/../../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../../middleware/TenantMiddleware.php';
require_once __DIR__ . '/../models/Ingredient.php';
require_once __DIR__ . '/../models/Product.php';
require_once dirname(__DIR__, 3) . '/core/helpers/url.php';

class IngredientController {
    public function index() {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if (!Tenant::hasModule('pos')) {
            die('POS system not subscribed for your plan');
        }

        if (Tenant::getPosLevel() < 1) {
            die('Upgrade to POS Starter or higher to manage inventory.');
        }

        if (!Auth::isTenantAdmin()) {
            die('No permission to view ingredients');
        }

        require_once __DIR__ . '/../../../core/classes/Store.php';
        $db = Database::getInstance();
        $tenantId = Tenant::getId();
        $allStores = Store::getAll($tenantId);

        $selectedStoreId = isset($_GET['store_id']) ? (int)$_GET['store_id'] : 0;

        $hasIngStoreStock = false;
        try {
            $db->fetchAll("SELECT 1 FROM ingredient_store_stock LIMIT 1");
            $hasIngStoreStock = true;
        } catch (\Throwable $e) {}

        $hasLogStoreId = false;
        try {
            $db->fetchOne("SELECT store_id FROM ingredient_stock_logs LIMIT 1");
            $hasLogStoreId = true;
        } catch (\Throwable $e) {}

        if ($selectedStoreId > 0 && $hasIngStoreStock) {
            $ingredients = $db->fetchAll(
                "SELECT i.*, COALESCE(iss.quantity, 0) AS stock_quantity
                 FROM ingredients i
                 LEFT JOIN ingredient_store_stock iss 
                       ON iss.ingredient_id = i.id AND iss.store_id = ? AND iss.tenant_id = i.tenant_id
                 WHERE i.tenant_id = ?
                 ORDER BY i.name ASC",
                [$selectedStoreId, $tenantId]
            );
        } else {
            $ingredients = Ingredient::getAll($tenantId);
        }

        if ($hasLogStoreId) {
            if ($selectedStoreId > 0) {
                $logs = $db->fetchAll(
                    "SELECT isl.*, i.name as ingredient_name, i.unit, o.id as order_number, s.name as store_name
                     FROM ingredient_stock_logs isl 
                     JOIN ingredients i ON isl.ingredient_id = i.id 
                     LEFT JOIN orders o ON isl.order_id = o.id 
                     LEFT JOIN stores s ON isl.store_id = s.id
                     WHERE isl.tenant_id = ? AND isl.store_id = ?
                     ORDER BY isl.created_at DESC LIMIT 100",
                    [$tenantId, $selectedStoreId]
                );
            } else {
                $logs = $db->fetchAll(
                    "SELECT isl.*, i.name as ingredient_name, i.unit, o.id as order_number, s.name as store_name
                     FROM ingredient_stock_logs isl 
                     JOIN ingredients i ON isl.ingredient_id = i.id 
                     LEFT JOIN orders o ON isl.order_id = o.id 
                     LEFT JOIN stores s ON isl.store_id = s.id
                     WHERE isl.tenant_id = ? 
                     ORDER BY isl.created_at DESC LIMIT 100",
                    [$tenantId]
                );
            }
        } else {
            $logs = $db->fetchAll(
                "SELECT isl.*, i.name as ingredient_name, i.unit, o.id as order_number, '' as store_name
                 FROM ingredient_stock_logs isl 
                 JOIN ingredients i ON isl.ingredient_id = i.id 
                 LEFT JOIN orders o ON isl.order_id = o.id 
                 WHERE isl.tenant_id = ? 
                 ORDER BY isl.created_at DESC LIMIT 100",
                [$tenantId]
            );
        }

        include __DIR__ . '/../views/ingredients.php';
    }

    public function create() {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => trim($_POST['name']),
                'stock_quantity' => (float)$_POST['stock_quantity'],
                'unit' => trim($_POST['unit']),
                'min_stock_alert' => (float)($_POST['min_stock_alert'] ?? 0.00)
            ];

            if ($data['name'] !== '' && $data['unit'] !== '') {
                $ingredientId = Ingredient::create($data);
                $tenantId = Tenant::getId();

                require_once __DIR__ . '/../../../core/classes/Store.php';
                $defaultStore = Store::getDefault($tenantId) ?? Store::getCurrent($tenantId);
                $storeId = $defaultStore ? (int)$defaultStore['id'] : null;

                // Add initial stock log & seed default store only
                if ($data['stock_quantity'] > 0 && $storeId) {
                    $db = Database::getInstance();
                    try {
                        $db->query(
                            "INSERT INTO ingredient_store_stock (tenant_id, store_id, ingredient_id, quantity)
                             VALUES (?, ?, ?, ?)
                             ON DUPLICATE KEY UPDATE quantity = VALUES(quantity)",
                            [$tenantId, $storeId, $ingredientId, $data['stock_quantity']]
                        );
                        $db->insert('ingredient_stock_logs', [
                            'tenant_id'       => $tenantId,
                            'store_id'        => $storeId,
                            'ingredient_id'   => $ingredientId,
                            'change_quantity' => $data['stock_quantity'],
                            'reason'          => 'adjust'
                        ]);
                    } catch (\Throwable $e) {}
                }
            }
        }

        $prefix = mc_base_path();
        header('Location: ' . $prefix . '/' . Tenant::getCurrent()['subdomain'] . '/pos/ingredients');
        exit;
    }

    public function update($id) {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => trim($_POST['name']),
                'unit' => trim($_POST['unit']),
                'min_stock_alert' => (float)($_POST['min_stock_alert'] ?? 0.00)
            ];

            Ingredient::update((int)$id, $data);
        }

        $prefix = mc_base_path();
        header('Location: ' . $prefix . '/' . Tenant::getCurrent()['subdomain'] . '/pos/ingredients');
        exit;
    }

    public function topup($id) {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $qty = (float)($_POST['quantity'] ?? 0);
            $storeId = !empty($_POST['store_id']) ? (int)$_POST['store_id'] : null;
            if ($qty > 0) {
                Ingredient::logTopup((int)$id, $qty, $storeId);
            }
        }

        $prefix = mc_base_path();
        header('Location: ' . $prefix . '/' . Tenant::getCurrent()['subdomain'] . '/pos/ingredients');
        exit;
    }

    public function delete($id) {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if (!Auth::isTenantAdmin()) {
            die('No permission');
        }

        Ingredient::delete((int)$id);

        $prefix = mc_base_path();
        header('Location: ' . $prefix . '/' . Tenant::getCurrent()['subdomain'] . '/pos/ingredients');
        exit;
    }
}

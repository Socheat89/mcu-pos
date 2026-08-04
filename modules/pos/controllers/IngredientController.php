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

        if (!Auth::isTenantAdmin() && !Auth::hasPermission('pos', 'read')) {
            die('No permission to view ingredients');
        }
        $isAdmin = Auth::isTenantAdmin();

        require_once __DIR__ . '/../../../core/classes/Store.php';
        $db = Database::getInstance();
        $tenantId = Tenant::getId();
        $allStores = Store::getAll($tenantId);

        // Identify Main Store (default store or lowest ID store)
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

        // Auto-detect active store from session / user store if not explicitly provided in URL
        $activeSession = $db->fetchOne("SELECT store_id FROM pos_sessions WHERE tenant_id = ? AND status = 'open'", [$tenantId]);
        if ($activeSession && !empty($activeSession['store_id'])) {
            Store::setCurrent((int)$activeSession['store_id'], $tenantId);
        }
        $currentStore = Store::getCurrent($tenantId);

        // Enforce store lock: if user is locked, ignore any store_id override
        if (Store::isUserLocked()) {
            $selectedStoreId = $currentStore ? (int)$currentStore['id'] : 0;
        } elseif (isset($_GET['store_id'])) {
            $selectedStoreId = (int)$_GET['store_id'];
        } else {
            $selectedStoreId = $currentStore ? (int)$currentStore['id'] : 0;
        }

        $hasIngStoreStock = false;
        try {
            $db->fetchAll("SELECT 1 FROM ingredient_store_stock LIMIT 1");
            $hasIngStoreStock = true;
        } catch (\Throwable $e) {}

        // Enforce per-store stock isolation:
        // All secondary stores (store_id != mainStoreId) without transfer_in or topup logs MUST BE 0 stock!
        if ($hasIngStoreStock && $mainStoreId) {
            try {
                $db->query(
                    "UPDATE ingredient_store_stock iss
                     SET iss.quantity = 0
                     WHERE iss.tenant_id = ?
                       AND iss.store_id != ?
                       AND NOT EXISTS (
                           SELECT 1 FROM ingredient_stock_logs isl 
                           WHERE isl.store_id = iss.store_id 
                             AND isl.ingredient_id = iss.ingredient_id
                             AND isl.reason IN ('transfer_in', 'topup')
                       )",
                    [$tenantId, $mainStoreId]
                );
            } catch (\Throwable $e) {}
        }

        $hasLogStoreId = false;
        try {
            $db->fetchOne("SELECT store_id FROM ingredient_stock_logs LIMIT 1");
            $hasLogStoreId = true;
        } catch (\Throwable $e) {}

        if ($selectedStoreId > 0 && $hasIngStoreStock) {
            if ($selectedStoreId === $mainStoreId) {
                $ingredients = $db->fetchAll(
                    "SELECT i.*, COALESCE(iss.quantity, i.stock_quantity, 0) AS stock_quantity
                     FROM ingredients i
                     LEFT JOIN ingredient_store_stock iss 
                           ON iss.ingredient_id = i.id AND iss.store_id = ? AND iss.tenant_id = i.tenant_id
                     WHERE i.tenant_id = ?
                     ORDER BY i.name ASC",
                    [$selectedStoreId, $tenantId]
                );
            } else {
                $ingredients = $db->fetchAll(
                    "SELECT i.*, COALESCE(iss.quantity, 0) AS stock_quantity
                     FROM ingredients i
                     LEFT JOIN ingredient_store_stock iss 
                           ON iss.ingredient_id = i.id AND iss.store_id = ? AND iss.tenant_id = i.tenant_id
                     WHERE i.tenant_id = ?
                     ORDER BY i.name ASC",
                    [$selectedStoreId, $tenantId]
                );
            }
        } else {
            // Default view (All Stores or store_id = 0): show Main Store stock
            if ($mainStoreId && $hasIngStoreStock) {
                $ingredients = $db->fetchAll(
                    "SELECT i.*, COALESCE(iss.quantity, i.stock_quantity, 0) AS stock_quantity
                     FROM ingredients i
                     LEFT JOIN ingredient_store_stock iss 
                           ON iss.ingredient_id = i.id AND iss.store_id = ? AND iss.tenant_id = i.tenant_id
                     WHERE i.tenant_id = ?
                     ORDER BY i.name ASC",
                    [$mainStoreId, $tenantId]
                );
            } else {
                $ingredients = Ingredient::getAll($tenantId);
            }
        }

        if ($hasLogStoreId) {
            if ($selectedStoreId > 0) {
                $logs = $db->fetchAll(
                    "SELECT isl.*, i.name as ingredient_name, i.unit, o.id as order_number, s.name as store_name,
                            s2.name AS counterpart_store_name
                     FROM ingredient_stock_logs isl 
                     JOIN ingredients i ON isl.ingredient_id = i.id 
                     LEFT JOIN orders o ON isl.order_id = o.id 
                     LEFT JOIN stores s ON isl.store_id = s.id
                     LEFT JOIN ingredient_stock_logs isl2 ON isl2.ingredient_id = isl.ingredient_id
                         AND isl2.note = isl.note
                         AND isl2.reason = CASE WHEN isl.reason = 'transfer_out' THEN 'transfer_in'
                                                WHEN isl.reason = 'transfer_in' THEN 'transfer_out'
                                                ELSE '' END
                         AND isl2.id != isl.id
                     LEFT JOIN stores s2 ON s2.id = isl2.store_id
                     WHERE isl.tenant_id = ? AND isl.store_id = ?
                     ORDER BY isl.created_at DESC LIMIT 100",
                    [$tenantId, $selectedStoreId]
                );
            } else {
                $logs = $db->fetchAll(
                    "SELECT isl.*, i.name as ingredient_name, i.unit, o.id as order_number, s.name as store_name,
                            s2.name AS counterpart_store_name
                     FROM ingredient_stock_logs isl 
                     JOIN ingredients i ON isl.ingredient_id = i.id 
                     LEFT JOIN orders o ON isl.order_id = o.id 
                     LEFT JOIN stores s ON isl.store_id = s.id
                     LEFT JOIN ingredient_stock_logs isl2 ON isl2.ingredient_id = isl.ingredient_id
                         AND isl2.note = isl.note
                         AND isl2.reason = CASE WHEN isl.reason = 'transfer_out' THEN 'transfer_in'
                                                WHEN isl.reason = 'transfer_in' THEN 'transfer_out'
                                                ELSE '' END
                         AND isl2.id != isl.id
                     LEFT JOIN stores s2 ON s2.id = isl2.store_id
                     WHERE isl.tenant_id = ? 
                     ORDER BY isl.created_at DESC LIMIT 100",
                    [$tenantId]
                );
            }
        } else {
            $logs = $db->fetchAll(
                "SELECT isl.*, i.name as ingredient_name, i.unit, o.id as order_number, '' as store_name,
                        '' AS counterpart_store_name
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

        if (!Auth::isTenantAdmin()) {
            die('No permission to modify ingredients');
        }

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
                $allStores = Store::getAll($tenantId);
                $db = Database::getInstance();

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

                if ($mainStoreId) {
                    // Seed Main Store with initial quantity
                    try {
                        $db->query(
                            "INSERT INTO ingredient_store_stock (tenant_id, store_id, ingredient_id, quantity)
                             VALUES (?, ?, ?, ?)
                             ON DUPLICATE KEY UPDATE quantity = ?",
                            [$tenantId, $mainStoreId, $ingredientId, $data['stock_quantity'], $data['stock_quantity']]
                        );
                    } catch (\Throwable $e) {}

                    // Seed ALL Secondary Stores with ZERO stock explicitly
                    foreach ($allStores as $st) {
                        $stId = (int)$st['id'];
                        if ($stId !== $mainStoreId) {
                            try {
                                $db->query(
                                    "INSERT INTO ingredient_store_stock (tenant_id, store_id, ingredient_id, quantity)
                                     VALUES (?, ?, ?, 0)
                                     ON DUPLICATE KEY UPDATE quantity = 0",
                                    [$tenantId, $stId, $ingredientId]
                                );
                            } catch (\Throwable $e) {}
                        }
                    }

                    // Add initial stock log for Main Store
                    if ($data['stock_quantity'] > 0) {
                        try {
                            $db->insert('ingredient_stock_logs', [
                                'tenant_id'       => $tenantId,
                                'store_id'        => $mainStoreId,
                                'ingredient_id'   => $ingredientId,
                                'change_quantity' => $data['stock_quantity'],
                                'reason'          => 'adjust'
                            ]);
                        } catch (\Throwable $e) {}
                    }
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

        if (!Auth::isTenantAdmin()) {
            die('No permission to modify ingredients');
        }

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

        if (!Auth::isTenantAdmin()) {
            die('No permission to modify ingredients');
        }

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

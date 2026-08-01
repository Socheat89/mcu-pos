<?php
// modules/pos/controllers/StockTransferController.php
require_once __DIR__ . '/../../../core/classes/Database.php';
require_once __DIR__ . '/../../../core/classes/Tenant.php';
require_once __DIR__ . '/../../../core/classes/Auth.php';
require_once __DIR__ . '/../../../core/classes/Store.php';
require_once __DIR__ . '/../../../core/classes/Settings.php';
require_once __DIR__ . '/../../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../../middleware/TenantMiddleware.php';
require_once __DIR__ . '/../../../core/helpers/url.php';

class StockTransferController
{
    public function index()
    {
        require_once __DIR__ . '/../../../core/bootstrap_session.php';
        require_once __DIR__ . '/../../../middleware/AuthMiddleware.php';
        require_once __DIR__ . '/../../../middleware/TenantMiddleware.php';
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if (!Auth::isTenantAdmin()) {
            die('No permission');
        }

        $tenantId     = Tenant::getId();
        $db           = Database::getInstance();
        $tenantName   = Tenant::getCurrent()['name'] ?? 'POS';
        $allStores    = Store::getAll($tenantId);
        $currentStore = Store::getCurrent($tenantId);
        $currentStoreId = $currentStore ? (int)$currentStore['id'] : null;

        // Business type: 'coffee' (ingredient-based) or 'mart' (product-based)
        $businessType = Settings::get('business_type', $tenantId, 'coffee');
        $isCoffee     = ($businessType === 'coffee');

        // Check tables exist
        $hasIngStoreStock = false;
        try { $db->fetchAll("SELECT 1 FROM ingredient_store_stock LIMIT 1"); $hasIngStoreStock = true; } catch (\Throwable $e) {}

        $hasStoreStock = false;
        try { $db->fetchAll("SELECT 1 FROM store_stock LIMIT 1"); $hasStoreStock = true; } catch (\Throwable $e) {}

        // ── AJAX: Search items (ingredients for coffee / products for mart) ──
        if (isset($_GET['ajax_items']) && isset($_GET['store_id'])) {
            header('Content-Type: application/json');
            $storeId = (int)$_GET['store_id'];
            $search  = trim($_GET['q'] ?? '');
            $like    = '%' . $search . '%';

            if ($isCoffee) {
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
                $isFromMainStore = ($storeId === $mainStoreId);

                // Search Ingredients with per-store qty
                if ($hasIngStoreStock) {
                    if ($isFromMainStore) {
                        $rows = $db->fetchAll(
                            "SELECT i.id, i.name, i.unit, i.min_stock_alert,
                                    COALESCE(iss.quantity, i.stock_quantity, 0) AS available
                             FROM ingredients i
                             LEFT JOIN ingredient_store_stock iss
                                   ON iss.ingredient_id = i.id AND iss.store_id = ? AND iss.tenant_id = ?
                             WHERE i.tenant_id = ?
                               AND i.name LIKE ?
                             ORDER BY i.name
                             LIMIT 80",
                            [$storeId, $tenantId, $tenantId, $like]
                        );
                    } else {
                        $rows = $db->fetchAll(
                            "SELECT i.id, i.name, i.unit, i.min_stock_alert,
                                    COALESCE(iss.quantity, 0) AS available
                             FROM ingredients i
                             LEFT JOIN ingredient_store_stock iss
                                   ON iss.ingredient_id = i.id AND iss.store_id = ? AND iss.tenant_id = ?
                             WHERE i.tenant_id = ?
                               AND i.name LIKE ?
                             ORDER BY i.name
                             LIMIT 80",
                            [$storeId, $tenantId, $tenantId, $like]
                        );
                    }
                } else {
                    $rows = $db->fetchAll(
                        "SELECT i.id, i.name, i.unit, i.min_stock_alert,
                                COALESCE(i.stock_quantity, 0) AS available
                         FROM ingredients i
                         WHERE i.tenant_id = ? AND i.name LIKE ?
                         ORDER BY i.name LIMIT 80",
                        [$tenantId, $like]
                    );
                }
            } else {
                // Search Products with per-store qty
                if ($hasStoreStock) {
                    $rows = $db->fetchAll(
                        "SELECT p.id, p.name, p.sku, p.image, c.name AS category_name,
                                COALESCE(ss.quantity, 0) AS available
                         FROM products p
                         LEFT JOIN categories c ON c.id = p.category_id
                         LEFT JOIN store_stock ss ON ss.product_id = p.id AND ss.store_id = ? AND ss.tenant_id = ?
                         WHERE p.tenant_id = ? AND (p.name LIKE ? OR p.sku LIKE ?)
                         ORDER BY p.name LIMIT 80",
                        [$storeId, $tenantId, $tenantId, $like, $like]
                    );
                } else {
                    $rows = $db->fetchAll(
                        "SELECT p.id, p.name, p.sku, p.image, c.name AS category_name,
                                COALESCE(p.stock_quantity,0) AS available
                         FROM products p
                         LEFT JOIN categories c ON c.id = p.category_id
                         WHERE p.tenant_id = ? AND (p.name LIKE ? OR p.sku LIKE ?)
                         ORDER BY p.name LIMIT 80",
                        [$tenantId, $like, $like]
                    );
                }
            }
            echo json_encode(array_values($rows));
            exit;
        }

        // ── AJAX: Validate Transfer ──────────────────────────────────────────
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_validate_transfer'])) {
            header('Content-Type: application/json');

            $fromStoreId = (int)($_POST['from_store_id'] ?? 0);
            $toStoreId   = (int)($_POST['to_store_id']   ?? 0);
            $reference   = trim($_POST['reference'] ?? '');
            $note        = trim($_POST['note'] ?? '');
            $lines       = $_POST['lines'] ?? [];

            if ($fromStoreId <= 0 || $toStoreId <= 0) {
                echo json_encode(['success' => false, 'error' => 'Select source and destination stores.']);
                exit;
            }
            if ($fromStoreId === $toStoreId) {
                echo json_encode(['success' => false, 'error' => 'Source and destination must be different.']);
                exit;
            }
            if (empty($lines) || !is_array($lines)) {
                echo json_encode(['success' => false, 'error' => 'Add at least one line.']);
                exit;
            }

            if (empty($reference)) {
                $reference = 'TRF/' . date('Ymd') . '/' . strtoupper(substr(uniqid(), -4));
            }
            $logNote = $reference . ($note ? ' — ' . $note : '');

            // Validate lines + check stock
            $validLines = [];
            foreach ($lines as $line) {
                $itemId = (int)($line['item_id'] ?? 0);
                $qty    = $isCoffee ? (float)($line['qty'] ?? 0) : (int)($line['qty'] ?? 0);
                if ($itemId <= 0 || $qty <= 0) continue;

                if ($isCoffee) {
                    $avail = $this->getIngStoreQty($db, $fromStoreId, $itemId, $tenantId, $hasIngStoreStock);
                    if ($qty > $avail) {
                        $ing = $db->fetchOne("SELECT name, unit FROM ingredients WHERE id = ? AND tenant_id = ?", [$itemId, $tenantId]);
                        echo json_encode(['success' => false,
                            'error' => 'Insufficient stock for "' . ($ing['name'] ?? "#$itemId") . '". Available: ' . $avail . ' ' . ($ing['unit'] ?? '')]);
                        exit;
                    }
                } else {
                    $avail = $this->getProductStoreQty($db, $fromStoreId, $itemId, $tenantId, $hasStoreStock);
                    if ($qty > $avail) {
                        $prd = $db->fetchOne("SELECT name FROM products WHERE id = ? AND tenant_id = ?", [$itemId, $tenantId]);
                        echo json_encode(['success' => false,
                            'error' => 'Insufficient stock for "' . ($prd['name'] ?? "#$itemId") . '". Available: ' . $avail]);
                        exit;
                    }
                }
                $validLines[] = ['item_id' => $itemId, 'qty' => $qty, 'avail' => $avail];
            }

            if (empty($validLines)) {
                echo json_encode(['success' => false, 'error' => 'No valid lines found.']);
                exit;
            }

            try {
                $conn = $db->getConnection();
                $conn->beginTransaction();

                foreach ($validLines as $line) {
                    $itemId = $line['item_id'];
                    $qty    = $line['qty'];

                    if ($isCoffee) {
                        $this->transferIngredient($db, $tenantId, $fromStoreId, $toStoreId, $itemId, $qty, $logNote, $hasIngStoreStock);
                    } else {
                        $this->transferProduct($db, $tenantId, $fromStoreId, $toStoreId, $itemId, $qty, $logNote, $hasStoreStock);
                    }
                }

                $conn->commit();
                echo json_encode([
                    'success'   => true,
                    'reference' => $reference,
                    'lines'     => count($validLines),
                ]);
            } catch (\Throwable $e) {
                $conn->rollBack();
                error_log('Transfer error: ' . $e->getMessage());
                echo json_encode(['success' => false, 'error' => 'Transfer failed: ' . $e->getMessage()]);
            }
            exit;
        }

        // ── Load transfer history ────────────────────────────────────────────
        $transferHistory = [];
        try {
            if ($isCoffee) {
                $transferHistory = $db->fetchAll(
                    "SELECT isl.change_quantity, isl.reason, isl.note, isl.created_at,
                            i.name AS item_name, i.unit,
                            s.name AS store_name
                     FROM ingredient_stock_logs isl
                     LEFT JOIN ingredients i ON i.id = isl.ingredient_id
                     LEFT JOIN stores s ON s.id = isl.store_id
                     WHERE isl.tenant_id = ?
                       AND isl.reason IN ('transfer_in','transfer_out')
                     ORDER BY isl.created_at DESC
                     LIMIT 100",
                    [$tenantId]
                );
            } else {
                $transferHistory = $db->fetchAll(
                    "SELECT sl.change_quantity, sl.reason, sl.note, sl.created_at,
                            p.name AS item_name, '' AS unit,
                            s.name AS store_name
                     FROM stock_logs sl
                     LEFT JOIN products p ON p.id = sl.product_id
                     LEFT JOIN stores s ON s.id = sl.store_id
                     WHERE sl.tenant_id = ?
                       AND sl.reason IN ('transfer_in','transfer_out')
                     ORDER BY sl.created_at DESC
                     LIMIT 100",
                    [$tenantId]
                );
            }
        } catch (\Throwable $e) {}

        $subdomain = Tenant::getCurrent()['subdomain'] ?? '';
        $posBase   = mc_base_path() . '/' . $subdomain . '/pos';
        $posUrl    = function (string $path) use ($posBase): string {
            return $posBase . '/' . ltrim($path, '/');
        };

        require __DIR__ . '/../views/stock_transfer.php';
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    private function getIngStoreQty(Database $db, int $storeId, int $ingId, int $tenantId, bool $hasTable): float {
        if ($hasTable && $storeId > 0) {
            $row = $db->fetchOne(
                "SELECT quantity FROM ingredient_store_stock WHERE store_id = ? AND ingredient_id = ? AND tenant_id = ?",
                [$storeId, $ingId, $tenantId]
            );
            if ($row !== false && $row !== null) return (float)$row['quantity'];
        }

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

        if ($mainStoreId === null || $storeId === $mainStoreId || !$hasTable) {
            $row = $db->fetchOne("SELECT stock_quantity FROM ingredients WHERE id = ? AND tenant_id = ?", [$ingId, $tenantId]);
            return $row ? (float)$row['stock_quantity'] : 0.0;
        }

        return 0.0;
    }

    private function getProductStoreQty(Database $db, int $storeId, int $productId, int $tenantId, bool $hasTable): int {
        if ($hasTable) {
            $row = $db->fetchOne(
                "SELECT quantity FROM store_stock WHERE store_id = ? AND product_id = ? AND tenant_id = ?",
                [$storeId, $productId, $tenantId]
            );
            return $row ? (int)$row['quantity'] : 0;
        }
        $row = $db->fetchOne("SELECT stock_quantity FROM products WHERE id = ? AND tenant_id = ?", [$productId, $tenantId]);
        return $row ? (int)$row['stock_quantity'] : 0;
    }

    private function transferIngredient(
        Database $db, int $tenantId, int $fromId, int $toId,
        int $ingId, float $qty, string $note, bool $hasTable
    ): void {
        // Deduct from source global
        $db->query("UPDATE ingredients SET stock_quantity = GREATEST(0, stock_quantity - ?) WHERE id = ? AND tenant_id = ?",
            [$qty, $ingId, $tenantId]);

        // Add to destination global
        $db->query("UPDATE ingredients SET stock_quantity = stock_quantity + ? WHERE id = ? AND tenant_id = ?",
            [$qty, $ingId, $tenantId]);
        // Note: global stays the same since we're just redistributing between stores.
        // Actually for global: leave unchanged (global = sum of all stores, redistribution doesn't change total)

        if ($hasTable) {
            // Deduct from source store
            $db->query(
                "INSERT INTO ingredient_store_stock (tenant_id, store_id, ingredient_id, quantity)
                 VALUES (?, ?, ?, GREATEST(0, ? - ?))
                 ON DUPLICATE KEY UPDATE quantity = GREATEST(0, quantity - ?)",
                [$tenantId, $fromId, $ingId,
                 $this->getIngStoreQty($db, $fromId, $ingId, $tenantId, true), $qty, $qty]
            );
            // Add to destination store
            $db->query(
                "INSERT INTO ingredient_store_stock (tenant_id, store_id, ingredient_id, quantity)
                 VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE quantity = quantity + ?",
                [$tenantId, $toId, $ingId, $qty, $qty]
            );
        }

        // Log transfer_out
        try {
            $db->query(
                "INSERT INTO ingredient_stock_logs (tenant_id, store_id, ingredient_id, change_quantity, reason, note, created_at)
                 VALUES (?, ?, ?, ?, 'transfer_out', ?, NOW())",
                [$tenantId, $fromId, $ingId, -$qty, $note]
            );
            $db->query(
                "INSERT INTO ingredient_stock_logs (tenant_id, store_id, ingredient_id, change_quantity, reason, note, created_at)
                 VALUES (?, ?, ?, ?, 'transfer_in', ?, NOW())",
                [$tenantId, $toId, $ingId, $qty, $note]
            );
        } catch (\Throwable $e) {
            error_log('Ingredient transfer log error: ' . $e->getMessage());
        }
    }

    private function transferProduct(
        Database $db, int $tenantId, int $fromId, int $toId,
        int $productId, int $qty, string $note, bool $hasTable
    ): void {
        if ($hasTable) {
            $db->query(
                "INSERT INTO store_stock (tenant_id, store_id, product_id, quantity)
                 VALUES (?, ?, ?, GREATEST(0, ? - ?))
                 ON DUPLICATE KEY UPDATE quantity = GREATEST(0, quantity - ?)",
                [$tenantId, $fromId, $productId,
                 $this->getProductStoreQty($db, $fromId, $productId, $tenantId, true), $qty, $qty]
            );
            $db->query(
                "INSERT INTO store_stock (tenant_id, store_id, product_id, quantity)
                 VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE quantity = quantity + ?",
                [$tenantId, $toId, $productId, $qty, $qty]
            );
        }
        try {
            $db->query(
                "INSERT INTO stock_logs (tenant_id, store_id, product_id, change_quantity, reason, note, created_at)
                 VALUES (?, ?, ?, ?, 'transfer_out', ?, NOW())",
                [$tenantId, $fromId, $productId, -$qty, $note]
            );
            $db->query(
                "INSERT INTO stock_logs (tenant_id, store_id, product_id, change_quantity, reason, note, created_at)
                 VALUES (?, ?, ?, ?, 'transfer_in', ?, NOW())",
                [$tenantId, $toId, $productId, $qty, $note]
            );
        } catch (\Throwable $e) {
            error_log('Product transfer log error: ' . $e->getMessage());
        }
    }
}

<?php
// modules/pos/controllers/StockReportController.php
require_once __DIR__ . '/../../../core/classes/Database.php';
require_once __DIR__ . '/../../../core/classes/Tenant.php';
require_once __DIR__ . '/../../../core/classes/Auth.php';
require_once __DIR__ . '/../../../core/classes/Store.php';
require_once __DIR__ . '/../../../core/classes/Settings.php';
require_once __DIR__ . '/../../../core/helpers/url.php';
require_once __DIR__ . '/../models/Product.php';

class StockReportController {

    public function index() {
        require_once __DIR__ . '/../../../core/bootstrap_session.php';
        require_once __DIR__ . '/../../../middleware/AuthMiddleware.php';
        require_once __DIR__ . '/../../../middleware/TenantMiddleware.php';
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if (!Auth::isTenantAdmin()) {
            die('No permission');
        }

        $tenantId   = Tenant::getId();
        $db         = Database::getInstance();
        $tenantName = Tenant::getCurrent()['name'] ?? 'POS';

        // Get current active store
        $currentStore   = Store::getCurrent($tenantId);
        $currentStoreId = $currentStore ? (int)$currentStore['id'] : null;

        // Get all stores for the transfer modal
        $allStores = Store::getAll($tenantId);

        // ── Check if store_stock table exists ────────────────────────────────
        $hasStoreStock = false;
        try {
            $db->fetchAll("SELECT 1 FROM store_stock LIMIT 1");
            $hasStoreStock = true;
        } catch (\Throwable $e) {
            $hasStoreStock = false;
        }

        // ── Handle AJAX: Stock Adjustment (existing) ─────────────────────────
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_stock_adjust'])) {
            header('Content-Type: application/json');
            $productId = (int)($_POST['product_id'] ?? 0);
            $qty       = (int)($_POST['quantity'] ?? 0);
            $type      = $_POST['movement_type'] ?? 'in'; // 'in' or 'out'
            $note      = trim($_POST['note'] ?? '');
            $storeId   = (int)($_POST['store_id'] ?? $currentStoreId ?? 0);

            if ($productId <= 0 || $qty <= 0) {
                echo json_encode(['success' => false, 'error' => 'Invalid input']);
                exit;
            }

            $changeQty = ($type === 'out') ? -$qty : $qty;

            // Update product stock (global fallback)
            $product = $db->fetchOne(
                "SELECT id, stock_quantity FROM products WHERE id = ? AND tenant_id = ?",
                [$productId, $tenantId]
            );

            if (!$product) {
                echo json_encode(['success' => false, 'error' => 'Product not found']);
                exit;
            }

            $newGlobalQty = max(0, (int)$product['stock_quantity'] + $changeQty);
            $db->query(
                "UPDATE products SET stock_quantity = ? WHERE id = ? AND tenant_id = ?",
                [$newGlobalQty, $productId, $tenantId]
            );

            $newStoreQty = $newGlobalQty; // default

            // Also update store_stock if available
            if ($hasStoreStock && $storeId > 0) {
                $storeRow = $db->fetchOne(
                    "SELECT id, quantity FROM store_stock WHERE store_id = ? AND product_id = ?",
                    [$storeId, $productId]
                );
                if ($storeRow) {
                    $newStoreQty = max(0, (int)$storeRow['quantity'] + $changeQty);
                    $db->query(
                        "UPDATE store_stock SET quantity = ? WHERE store_id = ? AND product_id = ?",
                        [$newStoreQty, $storeId, $productId]
                    );
                } else {
                    $newStoreQty = max(0, $changeQty);
                    $db->query(
                        "INSERT INTO store_stock (tenant_id, store_id, product_id, quantity) VALUES (?, ?, ?, ?)
                         ON DUPLICATE KEY UPDATE quantity = VALUES(quantity)",
                        [$tenantId, $storeId, $productId, $newStoreQty]
                    );
                }
            }

            // Log the stock movement
            try {
                $reason = ($type === 'in') ? 'purchase' : 'adjustment';
                $db->query(
                    "INSERT INTO stock_logs (tenant_id, store_id, product_id, change_quantity, reason, note, created_at)
                     VALUES (?, ?, ?, ?, ?, ?, NOW())",
                    [$tenantId, $storeId ?: null, $productId, $changeQty, $reason, $note ?: null]
                );
            } catch (\Throwable $e) {
                error_log('Stock log insert error: ' . $e->getMessage());
            }

            echo json_encode(['success' => true, 'new_stock' => $newStoreQty]);
            exit;
        }

        // ── Handle AJAX: Stock Transfer ──────────────────────────────────────
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_stock_transfer'])) {
            header('Content-Type: application/json');

            if (!$hasStoreStock) {
                echo json_encode(['success' => false, 'error' => 'store_stock table not found. Please run the migration first.']);
                exit;
            }

            $productId   = (int)($_POST['product_id'] ?? 0);
            $qty         = (int)($_POST['quantity'] ?? 0);
            $fromStoreId = (int)($_POST['from_store_id'] ?? 0);
            $toStoreId   = (int)($_POST['to_store_id'] ?? 0);
            $note        = trim($_POST['note'] ?? '');

            if ($productId <= 0 || $qty <= 0 || $fromStoreId <= 0 || $toStoreId <= 0) {
                echo json_encode(['success' => false, 'error' => 'Missing required fields']);
                exit;
            }
            if ($fromStoreId === $toStoreId) {
                echo json_encode(['success' => false, 'error' => 'Source and destination store must be different']);
                exit;
            }

            // Check source store stock
            $fromRow = $db->fetchOne(
                "SELECT quantity FROM store_stock WHERE store_id = ? AND product_id = ? AND tenant_id = ?",
                [$fromStoreId, $productId, $tenantId]
            );
            $fromQty = $fromRow ? (int)$fromRow['quantity'] : 0;

            if ($qty > $fromQty) {
                echo json_encode(['success' => false, 'error' => "Insufficient stock. Available: {$fromQty}"]);
                exit;
            }

            try {
                $conn = $db->getConnection();
                $conn->beginTransaction();

                // Deduct from source store
                $db->query(
                    "INSERT INTO store_stock (tenant_id, store_id, product_id, quantity)
                     VALUES (?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE quantity = GREATEST(0, quantity - ?)",
                    [$tenantId, $fromStoreId, $productId, max(0, $fromQty - $qty), $qty]
                );

                // Add to destination store
                $db->query(
                    "INSERT INTO store_stock (tenant_id, store_id, product_id, quantity)
                     VALUES (?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE quantity = quantity + ?",
                    [$tenantId, $toStoreId, $productId, $qty, $qty]
                );

                // Log transfer OUT from source
                $db->query(
                    "INSERT INTO stock_logs (tenant_id, store_id, product_id, change_quantity, reason, note, created_at)
                     VALUES (?, ?, ?, ?, 'transfer_out', ?, NOW())",
                    [$tenantId, $fromStoreId, $productId, -$qty, $note ?: null]
                );

                // Log transfer IN to destination
                $db->query(
                    "INSERT INTO stock_logs (tenant_id, store_id, product_id, change_quantity, reason, note, created_at)
                     VALUES (?, ?, ?, ?, 'transfer_in', ?, NOW())",
                    [$tenantId, $toStoreId, $productId, $qty, $note ?: null]
                );

                $conn->commit();

                // Recalculate from store new qty
                $newFromRow = $db->fetchOne(
                    "SELECT quantity FROM store_stock WHERE store_id = ? AND product_id = ?",
                    [$fromStoreId, $productId]
                );

                echo json_encode([
                    'success'   => true,
                    'new_from'  => $newFromRow ? (int)$newFromRow['quantity'] : 0,
                    'from_store_id' => $fromStoreId,
                    'to_store_id'   => $toStoreId,
                    'qty'       => $qty
                ]);
            } catch (\Throwable $e) {
                $conn->rollBack();
                error_log('Stock transfer error: ' . $e->getMessage());
                echo json_encode(['success' => false, 'error' => 'Transfer failed: ' . $e->getMessage()]);
            }
            exit;
        }

        // ── Load Products with per-store stock ───────────────────────────────
        if ($hasStoreStock && $currentStoreId) {
            // Show stock per active store
            $products = $db->fetchAll(
                "SELECT p.id, p.name, p.sku, p.stock_quantity, p.image, p.price, p.cost_price,
                        c.name AS category_name,
                        COALESCE(ss.quantity, 0) AS store_stock_qty,
                        COALESCE(s.qty_sold, 0) AS qty_sold
                 FROM products p
                 LEFT JOIN categories c ON c.id = p.category_id
                 LEFT JOIN store_stock ss ON ss.product_id = p.id AND ss.store_id = ?
                 LEFT JOIN (
                     SELECT oi.product_id, SUM(oi.quantity) as qty_sold
                     FROM order_items oi
                     JOIN orders o ON oi.order_id = o.id
                     WHERE o.tenant_id = ? AND o.status = 'completed'
                     GROUP BY oi.product_id
                 ) s ON p.id = s.product_id
                 WHERE p.tenant_id = ?
                 ORDER BY p.name ASC",
                [$currentStoreId, $tenantId, $tenantId]
            );
            // Use store_stock_qty as the displayed quantity
            foreach ($products as &$p) {
                $p['display_stock'] = (int)$p['store_stock_qty'];
            }
            unset($p);
        } else {
            // Fallback: no store_stock table yet
            $products = $db->fetchAll(
                "SELECT p.id, p.name, p.sku, p.stock_quantity, p.image, p.price, p.cost_price,
                        c.name AS category_name,
                        COALESCE(s.qty_sold, 0) AS qty_sold
                 FROM products p
                 LEFT JOIN categories c ON c.id = p.category_id
                 LEFT JOIN (
                     SELECT oi.product_id, SUM(oi.quantity) as qty_sold
                     FROM order_items oi
                     JOIN orders o ON oi.order_id = o.id
                     WHERE o.tenant_id = ? AND o.status = 'completed'
                     GROUP BY oi.product_id
                 ) s ON p.id = s.product_id
                 WHERE p.tenant_id = ?
                 ORDER BY p.name ASC",
                [$tenantId, $tenantId]
            );
            foreach ($products as &$p) {
                $p['display_stock'] = (int)($p['stock_quantity'] ?? 0);
                $p['store_stock_qty'] = $p['display_stock'];
            }
            unset($p);
        }

        // ── Load recent stock log entries (last 80) ──────────────────────────
        $stockLogs = [];
        try {
            $stockLogs = $db->fetchAll(
                "SELECT sl.*, p.name AS product_name,
                        st.name AS store_name
                 FROM stock_logs sl
                 LEFT JOIN products p ON p.id = sl.product_id
                 LEFT JOIN stores st ON st.id = sl.store_id
                 WHERE sl.tenant_id = ?
                 ORDER BY sl.created_at DESC
                 LIMIT 80",
                [$tenantId]
            );
        } catch (\Throwable $e) {
            // stock_logs may not exist yet — silently ignore
        }

        // Build $posUrl helper for view
        $subdomain = Tenant::getCurrent()['subdomain'] ?? '';
        $posBase   = mc_base_path() . '/' . $subdomain . '/pos';
        $posUrl    = function(string $path) use ($posBase): string {
            return $posBase . '/' . ltrim($path, '/');
        };

        require __DIR__ . '/../views/stock_report.php';
    }
}

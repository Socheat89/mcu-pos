<?php
// modules/pos/controllers/StockTransferController.php
require_once __DIR__ . '/../../../core/classes/Database.php';
require_once __DIR__ . '/../../../core/classes/Tenant.php';
require_once __DIR__ . '/../../../core/classes/Auth.php';
require_once __DIR__ . '/../../../core/classes/Store.php';
require_once __DIR__ . '/../../../core/classes/Settings.php';
require_once __DIR__ . '/../../../core/helpers/url.php';

class StockTransferController
{
    /** GET/POST: Transfer form + AJAX handlers */
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

        $tenantId   = Tenant::getId();
        $db         = Database::getInstance();
        $tenantName = Tenant::getCurrent()['name'] ?? 'POS';
        $allStores  = Store::getAll($tenantId);
        $currentStore = Store::getCurrent($tenantId);
        $currentStoreId = $currentStore ? (int)$currentStore['id'] : null;

        // ── Check store_stock table ──────────────────────────────────────────
        $hasStoreStock = false;
        try {
            $db->fetchAll("SELECT 1 FROM store_stock LIMIT 1");
            $hasStoreStock = true;
        } catch (\Throwable $e) {}

        // ── AJAX: Search products ────────────────────────────────────────────
        if (isset($_GET['ajax_products']) && isset($_GET['store_id'])) {
            header('Content-Type: application/json');
            $storeId = (int)$_GET['store_id'];
            $search  = trim($_GET['q'] ?? '');
            $like    = '%' . $search . '%';

            if ($hasStoreStock) {
                $rows = $db->fetchAll(
                    "SELECT p.id, p.name, p.sku, p.price, p.image,
                            c.name AS category_name,
                            COALESCE(ss.quantity, 0) AS available
                     FROM products p
                     LEFT JOIN categories c ON c.id = p.category_id
                     LEFT JOIN store_stock ss ON ss.product_id = p.id AND ss.store_id = ?
                     WHERE p.tenant_id = ?
                       AND (p.name LIKE ? OR p.sku LIKE ?)
                     ORDER BY p.name
                     LIMIT 50",
                    [$storeId, $tenantId, $like, $like]
                );
            } else {
                $rows = $db->fetchAll(
                    "SELECT p.id, p.name, p.sku, p.price, p.image,
                            c.name AS category_name,
                            COALESCE(p.stock_quantity, 0) AS available
                     FROM products p
                     LEFT JOIN categories c ON c.id = p.category_id
                     WHERE p.tenant_id = ?
                       AND (p.name LIKE ? OR p.sku LIKE ?)
                     ORDER BY p.name
                     LIMIT 50",
                    [$tenantId, $like, $like]
                );
            }
            echo json_encode($rows);
            exit;
        }

        // ── AJAX: Validate Transfer ──────────────────────────────────────────
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_validate_transfer'])) {
            header('Content-Type: application/json');

            if (!$hasStoreStock) {
                echo json_encode(['success' => false, 'error' => 'Please run the store_stock migration first.']);
                exit;
            }

            $fromStoreId = (int)($_POST['from_store_id'] ?? 0);
            $toStoreId   = (int)($_POST['to_store_id'] ?? 0);
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
                echo json_encode(['success' => false, 'error' => 'Add at least one product line.']);
                exit;
            }

            // Validate lines & check available stock
            $validLines = [];
            foreach ($lines as $line) {
                $productId = (int)($line['product_id'] ?? 0);
                $qty       = (int)($line['qty'] ?? 0);
                if ($productId <= 0 || $qty <= 0) continue;

                $storeRow = $db->fetchOne(
                    "SELECT quantity FROM store_stock WHERE store_id = ? AND product_id = ? AND tenant_id = ?",
                    [$fromStoreId, $productId, $tenantId]
                );
                $available = $storeRow ? (int)$storeRow['quantity'] : 0;

                if ($qty > $available) {
                    $product = $db->fetchOne("SELECT name FROM products WHERE id = ?", [$productId]);
                    echo json_encode([
                        'success' => false,
                        'error'   => 'Insufficient stock for "' . ($product['name'] ?? "Product #{$productId}") . '". Available: ' . $available
                    ]);
                    exit;
                }
                $validLines[] = ['product_id' => $productId, 'qty' => $qty, 'available' => $available];
            }

            if (empty($validLines)) {
                echo json_encode(['success' => false, 'error' => 'No valid lines found.']);
                exit;
            }

            // Auto-generate reference if empty
            if (empty($reference)) {
                $reference = 'TRF/' . date('Ymd') . '/' . strtoupper(substr(uniqid(), -4));
            }

            try {
                $conn = $db->getConnection();
                $conn->beginTransaction();

                foreach ($validLines as $line) {
                    $productId = $line['product_id'];
                    $qty       = $line['qty'];

                    // Deduct from source
                    $db->query(
                        "INSERT INTO store_stock (tenant_id, store_id, product_id, quantity)
                         VALUES (?, ?, ?, GREATEST(0, ? - ?))
                         ON DUPLICATE KEY UPDATE quantity = GREATEST(0, quantity - ?)",
                        [$tenantId, $fromStoreId, $productId, $line['available'], $qty, $qty]
                    );

                    // Add to destination
                    $db->query(
                        "INSERT INTO store_stock (tenant_id, store_id, product_id, quantity)
                         VALUES (?, ?, ?, ?)
                         ON DUPLICATE KEY UPDATE quantity = quantity + ?",
                        [$tenantId, $toStoreId, $productId, $qty, $qty]
                    );

                    // Log transfer_out
                    try {
                        $db->query(
                            "INSERT INTO stock_logs (tenant_id, store_id, product_id, change_quantity, reason, note, created_at)
                             VALUES (?, ?, ?, ?, 'transfer_out', ?, NOW())",
                            [$tenantId, $fromStoreId, $productId, -$qty, $reference . ($note ? ' — ' . $note : '')]
                        );
                        // Log transfer_in
                        $db->query(
                            "INSERT INTO stock_logs (tenant_id, store_id, product_id, change_quantity, reason, note, created_at)
                             VALUES (?, ?, ?, ?, 'transfer_in', ?, NOW())",
                            [$tenantId, $toStoreId, $productId, $qty, $reference . ($note ? ' — ' . $note : '')]
                        );
                    } catch (\Throwable $e) {
                        error_log('Stock log error: ' . $e->getMessage());
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
            $transferHistory = $db->fetchAll(
                "SELECT sl.*, p.name AS product_name, st.name AS store_name
                 FROM stock_logs sl
                 LEFT JOIN products p ON p.id = sl.product_id
                 LEFT JOIN stores st ON st.id = sl.store_id
                 WHERE sl.tenant_id = ?
                   AND sl.reason IN ('transfer_in', 'transfer_out')
                 ORDER BY sl.created_at DESC
                 LIMIT 100",
                [$tenantId]
            );
        } catch (\Throwable $e) {}

        $subdomain = Tenant::getCurrent()['subdomain'] ?? '';
        $posBase   = mc_base_path() . '/' . $subdomain . '/pos';
        $posUrl    = function (string $path) use ($posBase): string {
            return $posBase . '/' . ltrim($path, '/');
        };

        require __DIR__ . '/../views/stock_transfer.php';
    }
}

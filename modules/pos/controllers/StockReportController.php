<?php
// modules/pos/controllers/StockReportController.php
require_once __DIR__ . '/../../../core/classes/Database.php';
require_once __DIR__ . '/../../../core/classes/Tenant.php';
require_once __DIR__ . '/../../../core/classes/Auth.php';
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

        // Handle AJAX stock adjustment POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_stock_adjust'])) {
            header('Content-Type: application/json');
            $productId = (int)($_POST['product_id'] ?? 0);
            $qty       = (int)($_POST['quantity'] ?? 0);
            $type      = $_POST['movement_type'] ?? 'in'; // 'in' or 'out'
            $note      = trim($_POST['note'] ?? '');

            if ($productId <= 0 || $qty <= 0) {
                echo json_encode(['success' => false, 'error' => 'Invalid input']);
                exit;
            }

            $changeQty = ($type === 'out') ? -$qty : $qty;

            // Update product stock
            $product = $db->fetchOne(
                "SELECT id, stock_quantity FROM products WHERE id = ? AND tenant_id = ?",
                [$productId, $tenantId]
            );

            if (!$product) {
                echo json_encode(['success' => false, 'error' => 'Product not found']);
                exit;
            }

            $newQty = max(0, (int)$product['stock_quantity'] + $changeQty);

            $db->query(
                "UPDATE products SET stock_quantity = ? WHERE id = ? AND tenant_id = ?",
                [$newQty, $productId, $tenantId]
            );

            // Log the stock movement
            try {
                $reason = ($type === 'in') ? 'purchase' : 'adjustment';
                $db->query(
                    "INSERT INTO stock_logs (tenant_id, product_id, change_quantity, reason, created_at) VALUES (?, ?, ?, ?, NOW())",
                    [$tenantId, $productId, $changeQty, $reason]
                );
            } catch (\Throwable $e) {
                // stock_logs may not have note column — just log silently
                error_log('Stock log insert error: ' . $e->getMessage());
            }

            echo json_encode(['success' => true, 'new_stock' => $newQty]);
            exit;
        }

        // Load all products with current stock and total qty sold
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

        // Load recent stock log entries (last 50)
        $stockLogs = [];
        try {
            $stockLogs = $db->fetchAll(
                "SELECT sl.*, p.name AS product_name
                 FROM stock_logs sl
                 LEFT JOIN products p ON p.id = sl.product_id
                 WHERE sl.tenant_id = ?
                 ORDER BY sl.created_at DESC
                 LIMIT 50",
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

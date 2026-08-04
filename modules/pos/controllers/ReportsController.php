<?php
// modules/pos/controllers/ReportsController.php
require_once __DIR__ . '/../../../core/classes/Database.php';
require_once __DIR__ . '/../../../core/classes/Tenant.php';
require_once __DIR__ . '/../../../core/classes/Store.php';
require_once __DIR__ . '/../../../core/classes/Settings.php';
require_once __DIR__ . '/../../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../../middleware/TenantMiddleware.php';
require_once __DIR__ . '/../../../core/classes/Auth.php';

class ReportsController {
    public function index() {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if (!Tenant::hasModule('pos')) {
            die('POS system not subscribed for your plan');
        }

        if (!Tenant::hasFeature('pos', 'reports')) {
             die('Upgrade to POS Premium to view advanced reports.');
        }

        if (!Auth::isTenantAdmin()) {
            die('No permission to access POS Reports');
        }

        $db = Database::getInstance();
        $tenantId = Tenant::getId();

        // Load all stores for dropdown
        $allStores = class_exists('Store') ? Store::getAll($tenantId) : [];

        // Selected store filter (0 = all stores)
        $storeId = isset($_GET['store_id']) ? (int)$_GET['store_id'] : 0;

        // Enforce store lock: if user is locked, only show their store
        if (class_exists('Store') && Store::isUserLocked()) {
            $lockedStoreId = Store::getUserLockedStoreId();
            $storeId = $lockedStoreId ?: 0;
        }

        try {
            $storeFilter = '';
            $storeParams = [$tenantId];

            if ($storeId > 0) {
                $storeFilter = ' AND o.store_id = ?';
                $storeParams[] = $storeId;
            }

            // ── Sales Summary ──────────────────────────────────
            $salesSummary = $db->fetchOne("
                SELECT
                    COUNT(*) as total_orders,
                    COALESCE(SUM(o.total), 0) as total_sales,
                    COALESCE(AVG(o.total), 0) as avg_order_value,
                    COUNT(DISTINCT o.customer_id) as unique_customers
                FROM orders o
                WHERE o.tenant_id = ? AND o.status = 'completed'" . $storeFilter,
                $storeParams
            );

            // ── Daily sales (last 7 days) ─────────────────────
            $dailySales = $db->fetchAll("
                SELECT
                    DATE(o.created_at) as date,
                    COUNT(*) as orders_count,
                    COALESCE(SUM(o.total), 0) as daily_total
                FROM orders o
                WHERE o.tenant_id = ? AND o.status = 'completed'
                    AND o.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)" . $storeFilter . "
                GROUP BY DATE(o.created_at)
                ORDER BY date DESC",
                $storeParams
            );

            // ── Top Selling Products ──────────────────────────
            $topProducts = $db->fetchAll("
                SELECT
                    p.name,
                    COALESCE(s.name, '—') as store_name,
                    SUM(oi.quantity) as total_quantity,
                    SUM(oi.quantity * oi.unit_price) as total_revenue
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                JOIN orders o ON oi.order_id = o.id
                LEFT JOIN stores s ON o.store_id = s.id
                WHERE o.tenant_id = ? AND o.status = 'completed'" . $storeFilter . "
                GROUP BY p.id, p.name, s.name
                ORDER BY total_quantity DESC
                LIMIT 10",
                $storeParams
            );

            // ── Monthly sales (last 6 months) ─────────────────
            $monthlySales = $db->fetchAll("
                SELECT
                    DATE_FORMAT(o.created_at, '%Y-%m') as month,
                    COUNT(*) as orders_count,
                    COALESCE(SUM(o.total), 0) as monthly_total
                FROM orders o
                WHERE o.tenant_id = ? AND o.status = 'completed'
                    AND o.created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)" . $storeFilter . "
                GROUP BY DATE_FORMAT(o.created_at, '%Y-%m')
                ORDER BY month DESC",
                $storeParams
            );

            // ── 🔥 Sales by Store Breakdown ──────────────────
            $salesByStore = $db->fetchAll("
                SELECT
                    COALESCE(s.id, 0) as store_id,
                    COALESCE(s.name, 'No Store') as store_name,
                    COALESCE(s.code, '—') as store_code,
                    COUNT(o.id) as total_orders,
                    COALESCE(SUM(o.total), 0) as total_sales,
                    COALESCE(AVG(o.total), 0) as avg_order,
                    COUNT(DISTINCT o.customer_id) as unique_customers
                FROM orders o
                LEFT JOIN stores s ON o.store_id = s.id
                WHERE o.tenant_id = ? AND o.status = 'completed'
                GROUP BY s.id, s.name, s.code
                ORDER BY total_sales DESC",
                [$tenantId]
            );

            // ── 🛒 Top Products by Store ─────────────────────
            $productsByStore = $db->fetchAll("
                SELECT
                    COALESCE(s.name, 'No Store') as store_name,
                    COALESCE(s.code, '—') as store_code,
                    p.name as product_name,
                    SUM(oi.quantity) as qty_sold,
                    SUM(oi.quantity * oi.unit_price) as revenue
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                JOIN orders o ON oi.order_id = o.id
                LEFT JOIN stores s ON o.store_id = s.id
                WHERE o.tenant_id = ? AND o.status = 'completed'
                GROUP BY s.id, s.name, s.code, p.id, p.name
                ORDER BY s.name, qty_sold DESC
                LIMIT 50",
                [$tenantId]
            );

        } catch (Exception $e) {
            // Fallback: store_id columns don't exist yet
            // Run basic queries without store filtering
            $salesSummary = $db->fetchOne("
                SELECT COUNT(*) as total_orders, COALESCE(SUM(total),0) as total_sales,
                       COALESCE(AVG(total),0) as avg_order_value,
                       COUNT(DISTINCT customer_id) as unique_customers
                FROM orders WHERE tenant_id = ? AND status = 'completed'",
                [$tenantId]
            );

            $dailySales = $db->fetchAll("
                SELECT DATE(created_at) as date, COUNT(*) as orders_count,
                       COALESCE(SUM(total),0) as daily_total
                FROM orders WHERE tenant_id = ? AND status = 'completed'
                    AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                GROUP BY DATE(created_at) ORDER BY date DESC",
                [$tenantId]
            );

            $topProducts = $db->fetchAll("
                SELECT p.name, '' as store_name, SUM(oi.quantity) as total_quantity,
                       SUM(oi.quantity * oi.unit_price) as total_revenue
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                JOIN orders o ON oi.order_id = o.id
                WHERE o.tenant_id = ? AND o.status = 'completed'
                GROUP BY p.id, p.name ORDER BY total_quantity DESC LIMIT 10",
                [$tenantId]
            );

            $monthlySales = $db->fetchAll("
                SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as orders_count,
                       COALESCE(SUM(total),0) as monthly_total
                FROM orders WHERE tenant_id = ? AND status = 'completed'
                    AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY month DESC",
                [$tenantId]
            );

            $salesByStore = [];
            $productsByStore = [];
        }

        require_once __DIR__ . '/../views/reports.php';
    }
}
?>
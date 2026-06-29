<?php
// modules/pos/controllers/ReportsController.php
require_once __DIR__ . '/../../../core/classes/Database.php';
require_once __DIR__ . '/../../../core/classes/Tenant.php';
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

        if (!Auth::hasPermission('pos', 'read')) {
            die('No permission to access POS Reports');
        }

        // Get report data
        $db = Database::getInstance();

        // Sales summary
        $salesSummary = $db->fetchOne("
            SELECT
                COUNT(*) as total_orders,
                SUM(total) as total_sales,
                AVG(total) as avg_order_value,
                COUNT(DISTINCT customer_id) as unique_customers
            FROM orders
            WHERE tenant_id = ? AND status = 'completed'
        ", [Tenant::getId()]);

        // Daily sales for the last 7 days
        $dailySales = $db->fetchAll("
            SELECT
                DATE(created_at) as date,
                COUNT(*) as orders_count,
                SUM(total) as daily_total
            FROM orders
            WHERE tenant_id = ? AND status = 'completed'
                AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date DESC
        ", [Tenant::getId()]);

        // Top selling products
        $topProducts = $db->fetchAll("
            SELECT
                p.name,
                SUM(oi.quantity) as total_quantity,
                SUM(oi.quantity * oi.unit_price) as total_revenue
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            JOIN orders o ON oi.order_id = o.id
            WHERE o.tenant_id = ? AND o.status = 'completed'
            GROUP BY p.id, p.name
            ORDER BY total_quantity DESC
            LIMIT 10
        ", [Tenant::getId()]);

        // Monthly sales for the last 6 months
        $monthlySales = $db->fetchAll("
            SELECT
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as orders_count,
                SUM(total) as monthly_total
            FROM orders
            WHERE tenant_id = ? AND status = 'completed'
                AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month DESC
        ", [Tenant::getId()]);

        require_once __DIR__ . '/../views/reports.php';
    }
}
?>
<?php
// modules/pos/models/Order.php
require_once __DIR__ . '/../../../core/classes/Database.php';
require_once __DIR__ . '/../../../core/classes/Tenant.php';

class Order {
    private static $db;

    public static function init() {
        self::$db = Database::getInstance();
    }

    private static function getDb() {
        if (!self::$db) {
            self::$db = Database::getInstance();
        }
        return self::$db;
    }

    public static function getAll($tenantId = null, $limit = null, $offset = 0) {
        if (!$tenantId) $tenantId = Tenant::getId();
        $sql = "SELECT o.*, c.name as customer_name FROM orders o 
                LEFT JOIN customers c ON o.customer_id = c.id 
                WHERE o.tenant_id = ? ORDER BY o.created_at DESC";
        if ($limit) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        return self::getDb()->fetchAll($sql, [$tenantId]);
    }

    public static function getPending($tenantId = null, $limit = 100) {
        if (!$tenantId) $tenantId = Tenant::getId();
        $limit = (int)$limit;
        if ($limit <= 0) $limit = 100;

        return self::getDb()->fetchAll(
            "SELECT 
                o.id, o.tenant_id, o.customer_id, o.total, o.tax, o.discount, o.status, o.notes, o.created_at, o.updated_at,
                c.name as customer_name,
                (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as item_lines,
                (SELECT COALESCE(SUM(oi.quantity), 0) FROM order_items oi WHERE oi.order_id = o.id) as total_qty
             FROM orders o
             LEFT JOIN customers c ON o.customer_id = c.id
             WHERE o.tenant_id = ? AND o.status = 'pending'
             ORDER BY o.updated_at DESC, o.created_at DESC
             LIMIT {$limit}",
            [$tenantId]
        );
    }

    public static function getPendingById($id, $tenantId = null) {
        if (!$tenantId) $tenantId = Tenant::getId();
        $order = self::getDb()->fetchOne(
            "SELECT o.*, c.name as customer_name, c.email, c.phone FROM orders o
             LEFT JOIN customers c ON o.customer_id = c.id
             WHERE o.id = ? AND o.tenant_id = ? AND o.status = 'pending'",
            [$id, $tenantId]
        );
        if ($order) {
            $order['items'] = self::getOrderItems($id);
        }
        return $order;
    }

    public static function getById($id, $tenantId = null) {
        if (!$tenantId) $tenantId = Tenant::getId();
        $order = self::getDb()->fetchOne(
            "SELECT o.*, c.name as customer_name, c.email, c.phone FROM orders o 
             LEFT JOIN customers c ON o.customer_id = c.id 
             WHERE o.id = ? AND o.tenant_id = ?",
            [$id, $tenantId]
        );
        if ($order) {
            $order['items'] = self::getOrderItems($id);
            $order['payments'] = self::getOrderPayments($id);
        }
        return $order;
    }

    public static function getOrderItems($orderId) {
        return self::getDb()->fetchAll(
            "SELECT oi.*, p.name as product_name FROM order_items oi 
             JOIN products p ON oi.product_id = p.id 
             WHERE oi.order_id = ?",
            [$orderId]
        );
    }

    public static function getOrderPayments($orderId) {
        return self::getDb()->fetchAll("SELECT * FROM payments WHERE order_id = ?", [$orderId]);
    }

    public static function create($data, $items, $tenantId = null) {
        if (!$tenantId) $tenantId = Tenant::getId();
        $data['tenant_id'] = $tenantId;
        $orderId = self::getDb()->insert('orders', $data);
        foreach ($items as $item) {
            $item['order_id'] = $orderId;
            self::getDb()->insert('order_items', $item);
        }
        return $orderId;
    }

    public static function updateStatus($id, $status, $tenantId = null) {
        if (!$tenantId) $tenantId = Tenant::getId();
        return self::getDb()->update('orders', ['status' => $status], 'id = ? AND tenant_id = ?', [$id, $tenantId]);
    }

    public static function getTotalSales($tenantId = null, $dateFrom = null, $dateTo = null) {
        if (!$tenantId) $tenantId = Tenant::getId();
        $sql = "SELECT SUM(total) as total_sales, COUNT(*) as order_count FROM orders WHERE tenant_id = ? AND status = 'completed'";
        $params = [$tenantId];
        if ($dateFrom && $dateTo) {
            $sql .= " AND created_at BETWEEN ? AND ?";
            $params[] = $dateFrom;
            $params[] = $dateTo;
        }
        return self::getDb()->fetchOne($sql, $params);
    }
}

Order::init();
?>
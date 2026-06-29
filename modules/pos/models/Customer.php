<?php
// modules/pos/models/Customer.php
require_once __DIR__ . '/../../../core/classes/Database.php';
require_once __DIR__ . '/../../../core/classes/Tenant.php';

class Customer {
    private static $db;

    public static function init() {
        self::$db = Database::getInstance();
    }

    public static function getAll($tenantId = null) {
        if (!$tenantId) $tenantId = Tenant::getId();
        return self::$db->fetchAll("SELECT * FROM customers WHERE tenant_id = ? ORDER BY name", [$tenantId]);
    }

    public static function getById($id, $tenantId = null) {
        if (!$tenantId) $tenantId = Tenant::getId();
        return self::$db->fetchOne("SELECT * FROM customers WHERE id = ? AND tenant_id = ?", [$id, $tenantId]);
    }

    public static function create($data, $tenantId = null) {
        if (!$tenantId) $tenantId = Tenant::getId();
        $data['tenant_id'] = $tenantId;
        return self::$db->insert('customers', $data);
    }

    public static function update($id, $data, $tenantId = null) {
        if (!$tenantId) $tenantId = Tenant::getId();
        return self::$db->update('customers', $data, 'id = ? AND tenant_id = ?', [$id, $tenantId]);
    }

    public static function delete($id, $tenantId = null) {
        if (!$tenantId) $tenantId = Tenant::getId();
        return self::$db->delete('customers', 'id = ? AND tenant_id = ?', [$id, $tenantId]);
    }

    public static function search($query, $tenantId = null) {
        if (!$tenantId) $tenantId = Tenant::getId();
        $searchTerm = "%{$query}%";
        return self::$db->fetchAll(
            "SELECT * FROM customers WHERE tenant_id = ? AND (name LIKE ? OR email LIKE ? OR phone LIKE ?) ORDER BY name",
            [$tenantId, $searchTerm, $searchTerm, $searchTerm]
        );
    }
}

Customer::init();
?>
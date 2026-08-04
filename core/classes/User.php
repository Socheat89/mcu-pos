<?php
// core/classes/User.php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Tenant.php';
require_once __DIR__ . '/Settings.php';

class User {
    private static $db;

    private static function getDb() {
        if (!self::$db) {
            self::$db = Database::getInstance();
        }
        return self::$db;
    }

    public static function create($data, $tenantId = null) {
        if (!$tenantId) $tenantId = Tenant::getId();

        // Check user creation limit (skip if CashierController already checked via getCashierLimit)
        // Only enforce this limit if the caller hasn't already done a plan-based check.
        // We keep this as a safety net but treat 0 as unlimited.
        $limit = self::getUserLimit($tenantId);
        if ($limit > 0) {
            $currentUsers = self::countUsers($tenantId);
            if ($currentUsers >= $limit) {
                throw new Exception('User creation limit reached. Please upgrade your plan.');
            }
        }

        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        $userData = [
            'tenant_id' => $tenantId,
            'username' => $data['username'],
            'email' => $data['email'],
            'password_hash' => $passwordHash,
            'role_id' => $data['role_id'],
            'status' => $data['status'] ?? 'active'
        ];

        // Optional: store assignment
        if (isset($data['current_store_id'])) {
            $userData['current_store_id'] = $data['current_store_id'] ?: null;
        }
        if (isset($data['locked_store_id'])) {
            $userData['locked_store_id'] = $data['locked_store_id'] ?: null;
        }

        return self::getDb()->insert('users', $userData);
    }

    public static function getUserLimit($tenantId = null) {
        if (!$tenantId) $tenantId = Tenant::getId();
        // Try plan-based limit first (safe — catches missing columns)
        try {
            if (method_exists('Tenant', 'getCashierLimit')) {
                $planLimit = Tenant::getCashierLimit();
                if ($planLimit > 0) return $planLimit;
            }
        } catch (\Exception $e) {
            // Column may not exist yet — fall through to settings
        }
        $settingsLimit = (int) Settings::get('max_free_users', $tenantId, 0);
        return $settingsLimit; // 0 = unlimited
    }

    public static function canCreateUser($tenantId = null) {
        if (!$tenantId) $tenantId = Tenant::getId();
        $limit = self::getUserLimit($tenantId);
        if ($limit <= 0) return true; // 0 = unlimited
        $currentUsers = self::countUsers($tenantId);
        return $currentUsers < $limit;
    }

    public static function countUsers($tenantId = null) {
        if (!$tenantId) $tenantId = Tenant::getId();

        $count = self::getDb()->fetchOne(
            "SELECT COUNT(*) as count FROM users WHERE tenant_id = ? AND status = 'active'",
            [$tenantId]
        );
        return (int) $count['count'];
    }

    public static function getAll($tenantId = null, $limit = null, $offset = 0) {
        if (!$tenantId) $tenantId = Tenant::getId();
        $sql = "SELECT u.*, r.name as role_name FROM users u 
                JOIN roles r ON u.role_id = r.id 
                WHERE u.tenant_id = ? ORDER BY u.created_at DESC";
        if ($limit) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        return self::getDb()->fetchAll($sql, [$tenantId]);
    }
}
?>
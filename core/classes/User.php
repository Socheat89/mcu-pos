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

        // Check user creation limit
        if (!self::canCreateUser($tenantId)) {
            throw new Exception('User creation limit reached. Please upgrade your plan.');
        }

        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        return self::getDb()->insert('users', [
            'tenant_id' => $tenantId,
            'username' => $data['username'],
            'email' => $data['email'],
            'password_hash' => $passwordHash,
            'role_id' => $data['role_id'],
            'status' => $data['status'] ?? 'active'
        ]);
    }

    public static function canCreateUser($tenantId = null) {
        if (!$tenantId) $tenantId = Tenant::getId();

        $maxFreeUsers = (int) Settings::get('max_free_users', $tenantId, 5);
        $currentUsers = self::countUsers($tenantId);

        return $currentUsers < $maxFreeUsers;
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
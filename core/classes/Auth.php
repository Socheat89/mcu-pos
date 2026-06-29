<?php
// core/classes/Auth.php
class Auth {
    public static function login($username, $password, $tenantId) {
        $db = Database::getInstance();
        $user = $db->fetchOne(
            "SELECT u.*, r.name as role_name, r.level as role_level 
             FROM users u 
             JOIN roles r ON u.role_id = r.id 
             WHERE u.username = ? AND u.tenant_id = ? AND u.status = 'active'",
            [$username, $tenantId]
        );

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['tenant_id'] = $user['tenant_id'];
            $_SESSION['role_level'] = $user['role_level'];
            return $user;
        }
        return false;
    }

    public static function logout() {
        session_destroy();
    }

    public static function check() {
        return isset($_SESSION['user_id']);
    }

    public static function user() {
        if (!self::check()) return null;
        $db = Database::getInstance();
        return $db->fetchOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
    }

    public static function hasPermission($module, $action) {
        if (!self::check()) return false;

        // Super Admin has all permissions globally
        if (self::isSuperAdmin()) return true;

        // Tenant Admin has all permissions for any module their tenant has subscribed to
        if (self::isTenantAdmin()) {
            require_once __DIR__ . '/Tenant.php';
            if (Tenant::hasModule($module)) {
                return true;
            }
        }

        $db = Database::getInstance();
        $count = $db->fetchOne(
            "SELECT COUNT(*) as count FROM role_permissions rp 
             JOIN permissions p ON rp.permission_id = p.id 
             WHERE rp.role_id = (SELECT role_id FROM users WHERE id = ?) 
             AND p.module = ? AND p.action = ?",
            [$_SESSION['user_id'], $module, $action]
        );
        return $count['count'] > 0;
    }

    public static function isSuperAdmin() {
        return isset($_SESSION['role_level']) && $_SESSION['role_level'] == 3;
    }

    public static function isTenantAdmin() {
        return isset($_SESSION['role_level']) && $_SESSION['role_level'] >= 2;
    }
}
?>
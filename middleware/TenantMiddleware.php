<?php
// middleware/TenantMiddleware.php
require_once __DIR__ . '/../core/classes/Database.php';
require_once __DIR__ . '/../core/classes/Tenant.php';

class TenantMiddleware {
    public static function handle() {
        if (!Tenant::getCurrent()) {
            // Try to detect tenant from URL
            try {
                Tenant::detect();
            } catch (Exception $e) {
                // Fallback: check session for tenant_id
                if (isset($_SESSION['tenant_id'])) {
                    $db = Database::getInstance();
                    $tenant = $db->fetchOne("SELECT * FROM tenants WHERE id = ? AND status = 'active'", [$_SESSION['tenant_id']]);
                    if ($tenant) {
                        Tenant::setCurrent($tenant);
                        return;
                    }
                }
                die('Tenant not found');
            }
        }
    }
}
?>
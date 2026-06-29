<?php
// middleware/AuthMiddleware.php
require_once __DIR__ . '/../core/helpers/url.php';

class AuthMiddleware {
    public static function handle($requiredLevel = 1) {
        $urlPrefix = mc_base_path();


        if (!Auth::check()) {
            header("Location: $urlPrefix/public/login.php");
            exit;
        }

        // Subscription Check (Skip for Super Admin)
        if ($_SESSION['role_level'] < 3) {
            require_once __DIR__ . '/../core/classes/Database.php';
            $db = Database::getInstance();
            $tenantId = $_SESSION['tenant_id'];
            
            $activeSystems = $db->fetchOne(
                "SELECT COUNT(*) as count FROM tenant_systems 
                 WHERE tenant_id = ? AND status = 'active' 
                 AND (expires_at IS NULL OR expires_at > NOW())",
                [$tenantId]
            );
            
            if ($activeSystems['count'] == 0) {
                // Check if already on the expired page to prevent loop
                if (basename($_SERVER['PHP_SELF']) !== 'subscription_expired.php') {
                    header("Location: $urlPrefix/public/subscription_expired.php");
                    exit;
                }
            }
        }

        if ($_SESSION['role_level'] < $requiredLevel) {
            header("Location: $urlPrefix/public/unauthorized.php");
            exit;
        }
    }
}
?>
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

<<<<<<< HEAD
=======
        // Password Age Policy: Force change every 90 days (3 months)
        if (basename($_SERVER['PHP_SELF']) !== 'change_expired_password.php' && 
            basename($_SERVER['PHP_SELF']) !== 'logout.php') {
            
            require_once __DIR__ . '/../core/classes/Database.php';
            $db = Database::getInstance();
            $user = $db->fetchOne("SELECT password_changed_at FROM users WHERE id = ?", [$_SESSION['user_id']]);
            if ($user && !empty($user['password_changed_at'])) {
                $changedAt = new DateTime($user['password_changed_at']);
                $now = new DateTime();
                $diff = $now->diff($changedAt);
                if ($diff->days >= 90) {
                    header("Location: $urlPrefix/public/change_expired_password.php");
                    exit;
                }
            }
        }

>>>>>>> 062e3cc8d9b9f40dc40c6d6c6835e28f6f8a0d77
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
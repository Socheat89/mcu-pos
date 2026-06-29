<?php
// middleware/SuperAdminMiddleware.php
require_once __DIR__ . '/../core/classes/Auth.php';
require_once __DIR__ . '/../core/helpers/url.php';

class SuperAdminMiddleware {
    public static function handle() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $urlPrefix = mc_base_path();


        if (!Auth::check() || !Auth::isSuperAdmin()) {
            // Store the attempted URL to redirect back after login
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header("Location: $urlPrefix/admin/login.php?error=" . urlencode('Master authentication required.'));
            exit;
        }
    }
}
?>

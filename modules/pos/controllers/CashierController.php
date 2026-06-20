<?php
require_once __DIR__ . '/../../../core/classes/Database.php';
require_once __DIR__ . '/../../../core/classes/Tenant.php';
require_once __DIR__ . '/../../../core/classes/Auth.php';
require_once __DIR__ . '/../../../core/classes/User.php';
require_once __DIR__ . '/../../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../../middleware/TenantMiddleware.php';
require_once __DIR__ . '/../../../core/helpers/url.php';

class CashierController {

    public function index() {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if (!Tenant::hasModule('pos')) die('POS not subscribed.');
        if (!Auth::isTenantAdmin()) {
            header('Location: ' . mc_url(Tenant::getCurrent()['subdomain'] . '/pos/dashboard'));
            exit;
        }

        $db       = Database::getInstance();
        $tenantId = Tenant::getId();

        $message  = '';
        $error    = '';

        // ── Handle POST actions ──────────────────────────────
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['_action'] ?? '';

            // Create cashier
            if ($action === 'create') {
                $username = trim($_POST['username'] ?? '');
                $email    = trim($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';

                if (!$username || !$email || !$password) {
                    $error = 'Please fill in all required fields.';
                } elseif (strlen($password) < 6) {
                    $error = 'Password must be at least 6 characters.';
                } else {
                    // Get cashier role id (lowest level, typically level=1)
                    $cashierRole = $db->fetchOne(
                        "SELECT id FROM roles WHERE level = 1 ORDER BY id LIMIT 1"
                    );
                    if (!$cashierRole) {
                        $cashierRole = $db->fetchOne("SELECT id FROM roles ORDER BY level ASC LIMIT 1");
                    }

                    // Check duplicate username in tenant
                    $existing = $db->fetchOne(
                        "SELECT id FROM users WHERE username = ? AND tenant_id = ?",
                        [$username, $tenantId]
                    );

                    if ($existing) {
                        $error = 'Username already exists. Please choose another.';
                    } elseif (!User::canCreateUser($tenantId)) {
                        $error = 'User limit reached. Upgrade your plan to add more users.';
                    } else {
                        try {
                            User::create([
                                'username' => $username,
                                'email'    => $email,
                                'password' => $password,
                                'role_id'  => $cashierRole['id'],
                                'status'   => 'active',
                            ], $tenantId);
                            $message = "Cashier \"$username\" created successfully!";
                        } catch (Exception $e) {
                            $error = $e->getMessage();
                        }
                    }
                }
            }

            // Toggle status
            if ($action === 'toggle_status') {
                $userId    = (int)($_POST['user_id'] ?? 0);
                $newStatus = $_POST['new_status'] ?? 'active';
                $newStatus = in_array($newStatus, ['active', 'inactive']) ? $newStatus : 'active';
                if ($userId) {
                    $db->update('users', ['status' => $newStatus],
                        'id = ? AND tenant_id = ?', [$userId, $tenantId]);
                    $message = 'User status updated.';
                }
            }

            // Reset password
            if ($action === 'reset_password') {
                $userId      = (int)($_POST['user_id'] ?? 0);
                $newPassword = $_POST['new_password'] ?? '';
                if ($userId && strlen($newPassword) >= 6) {
                    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                    $db->update('users', ['password_hash' => $hash],
                        'id = ? AND tenant_id = ?', [$userId, $tenantId]);
                    $message = 'Password has been reset.';
                } else {
                    $error = 'Password must be at least 6 characters.';
                }
            }
        }

        // ── Load cashier users (role level = 1) ─────────────
        $cashiers = $db->fetchAll(
            "SELECT u.*, r.name as role_name, r.level as role_level
             FROM users u
             JOIN roles r ON u.role_id = r.id
             WHERE u.tenant_id = ? AND r.level = 1
             ORDER BY u.created_at DESC",
            [$tenantId]
        );

        // Count usage
        $totalUsers    = User::countUsers($tenantId);
        $maxUsers      = (int)\Settings::get('max_free_users', $tenantId, 5);
        $canCreate     = User::canCreateUser($tenantId);
        $tenantName    = Tenant::getCurrent()['name'] ?? '';

        include __DIR__ . '/../views/cashiers.php';
    }
}
?>

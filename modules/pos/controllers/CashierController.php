<?php
require_once __DIR__ . '/../../../core/classes/Database.php';
require_once __DIR__ . '/../../../core/classes/Tenant.php';
require_once __DIR__ . '/../../../core/classes/Auth.php';
require_once __DIR__ . '/../../../core/classes/User.php';
require_once __DIR__ . '/../../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../../middleware/TenantMiddleware.php';
require_once __DIR__ . '/../../../core/helpers/url.php';

class CashierController {

    // ── Helper: assign permissions to a role ────────────────────
    private function assignPermissionsToRole(int $roleId, array $permissionIds, $db): void {
        try {
            $db->getConnection()->prepare("DELETE FROM role_permissions WHERE role_id = ?")->execute([$roleId]);
        } catch (Exception $e) { /* ignore */ }

        foreach ($permissionIds as $permId) {
            $permId = (int)$permId;
            if (!$permId) continue;
            // INSERT IGNORE to avoid duplicate key errors
            try {
                $db->getConnection()->prepare(
                    "INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)"
                )->execute([$roleId, $permId]);
            } catch (Exception $e) { /* already exists */ }
        }
    }

    // ── Helper: get permissions assigned to a role ───────────────
    private function getRolePermissions(int $roleId, $db): array {
        $rows = $db->fetchAll(
            "SELECT p.id, p.module, p.action, p.name
             FROM role_permissions rp
             JOIN permissions p ON rp.permission_id = p.id
             WHERE rp.role_id = ?",
            [$roleId]
        );
        return $rows ?: [];
    }

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

        // ── Seed default permissions if they don't exist ──
        $defaultPerms = [
            ['name' => 'pos_read',        'module' => 'pos',       'action' => 'read'],
            ['name' => 'pos_write',       'module' => 'pos',       'action' => 'write'],
            ['name' => 'pos_delete',      'module' => 'pos',       'action' => 'delete'],
            ['name' => 'inventory_read',  'module' => 'inventory', 'action' => 'read'],
            ['name' => 'inventory_write', 'module' => 'inventory', 'action' => 'write'],
            ['name' => 'hr_read',         'module' => 'hr',        'action' => 'read'],
            ['name' => 'hr_write',        'module' => 'hr',        'action' => 'write'],
        ];
        foreach ($defaultPerms as $perm) {
            $exists = $db->fetchOne(
                "SELECT id FROM permissions WHERE module = ? AND action = ?",
                [$perm['module'], $perm['action']]
            );
            if (!$exists) {
                try {
                    $db->getConnection()->prepare(
                        "INSERT INTO permissions (name, module, action) VALUES (?, ?, ?)"
                    )->execute([$perm['name'], $perm['module'], $perm['action']]);
                } catch (Exception $e) { /* ignore */ }
            }
        }
        $allPermissions = $db->fetchAll("SELECT * FROM permissions ORDER BY module, action") ?: [];

        // ── Get cashier role (level=1) ────────────────────────────
        $cashierRole = $db->fetchOne(
            "SELECT id FROM roles WHERE level = 1 ORDER BY id LIMIT 1"
        );
        if (!$cashierRole) {
            try {
                $db->getConnection()->prepare(
                    "INSERT INTO roles (name, description, level) VALUES ('staff', 'Staff / Cashier', 1)"
                )->execute();
                $cashierRole = $db->fetchOne(
                    "SELECT id FROM roles WHERE level = 1 ORDER BY id LIMIT 1"
                );
            } catch (Exception $e) { /* ignore */ }
        }
        if (!$cashierRole) {
            $cashierRole = $db->fetchOne("SELECT id FROM roles ORDER BY level ASC LIMIT 1");
        }
        $cashierRoleId = $cashierRole ? (int)$cashierRole['id'] : 0;

        // ── Calculate cashier limits BEFORE POST handling ──
        $cashierCount = $db->fetchOne(
            "SELECT COUNT(*) as cnt FROM users u
             JOIN roles r ON u.role_id = r.id
             WHERE u.tenant_id = ? AND r.level = 1",
            [$tenantId]
        );
        $totalUsers = (int)($cashierCount['cnt'] ?? 0);
        $maxUsers   = Tenant::getCashierLimit();
        $canCreate  = ($maxUsers === 0) || ($totalUsers < $maxUsers);

        // ── Handle POST actions ──────────────────────────────────
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['_action'] ?? '';

            // Create cashier
            if ($action === 'create') {
                $username   = trim($_POST['username'] ?? '');
                $email      = trim($_POST['email'] ?? '');
                $password   = $_POST['password'] ?? '';
                $permIds    = $_POST['permissions'] ?? [];
                $storeId    = !empty($_POST['store_id']) ? (int)$_POST['store_id'] : null;

                if (!$username || !$email || !$password) {
                    $error = 'Please fill in all required fields.';
                } elseif (strlen($password) < 6) {
                    $error = 'Password must be at least 6 characters.';
                } else {
                    // Check duplicate username in tenant
                    $existing = $db->fetchOne(
                        "SELECT id FROM users WHERE username = ? AND tenant_id = ?",
                        [$username, $tenantId]
                    );

                    if ($existing) {
                        $error = 'Username already exists. Please choose another.';
                    } elseif (!$canCreate) {
                        $limitText = $maxUsers === 0 ? '∞' : $maxUsers;
                        $error = __('cashier_limit_reached') . " ({$totalUsers}/{$limitText})";
                    } else {
                        try {
                            User::create([
                                'username'          => $username,
                                'email'             => $email,
                                'password'          => $password,
                                'role_id'           => $cashierRoleId,
                                'status'            => 'active',
                                'current_store_id'  => $storeId,
                            ], $tenantId);

                            // Auto-assign selected permissions (or default pos read+write)
                            if (empty($permIds)) {
                                // Default: pos read + write
                                $defaultPerms = $db->fetchAll(
                                    "SELECT id FROM permissions WHERE module = 'pos' AND action IN ('read','write')"
                                );
                                $permIds = array_column($defaultPerms ?: [], 'id');
                            }
                            $this->assignPermissionsToRole($cashierRoleId, $permIds, $db);

                            $message = "Cashier \"$username\" created successfully!";
                        } catch (Exception $e) {
                            error_log('Cashier creation failed for tenant ' . $tenantId . ': ' . $e->getMessage());
                            $error = 'Unable to create the cashier. Please review the details and try again.';
                            // Show actual error for tenant admins (help debug)
                            if (Auth::isTenantAdmin()) {
                                $error .= ' [Debug: ' . htmlspecialchars($e->getMessage()) . ']';
                            }
                        }
                    }
                }
            }

            // Fix permissions for all cashiers
            if ($action === 'fix_permissions') {
                $permIds = $_POST['fix_perm_ids'] ?? [];
                if (empty($permIds)) {
                    // Default: pos read + write
                    $defaultPerms = $db->fetchAll(
                        "SELECT id FROM permissions WHERE module = 'pos' AND action IN ('read','write')"
                    );
                    $permIds = array_column($defaultPerms ?: [], 'id');
                }
                $this->assignPermissionsToRole($cashierRoleId, $permIds, $db);
                $message = 'Permissions updated for cashier role successfully!';
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
                    $db->update('users', [
                        'password_hash' => $hash,
                        'password_changed_at' => date('Y-m-d H:i:s')
                    ], 'id = ? AND tenant_id = ?', [$userId, $tenantId]);
                    $message = 'Password has been reset.';
                } else {
                    $error = 'Password must be at least 6 characters.';
                }
            }

            // Edit cashier
            if ($action === 'edit') {
                $userId   = (int)($_POST['user_id'] ?? 0);
                $username = trim($_POST['username'] ?? '');
                $email    = trim($_POST['email'] ?? '');
                $storeId  = !empty($_POST['store_id']) ? (int)$_POST['store_id'] : null;
                $status   = $_POST['status'] ?? 'active';
                $status   = in_array($status, ['active', 'inactive']) ? $status : 'active';

                if (!$userId || !$username || !$email) {
                    $error = 'Please fill in all required fields.';
                } else {
                    // Check duplicate username (exclude current user)
                    $existing = $db->fetchOne(
                        "SELECT id FROM users WHERE username = ? AND tenant_id = ? AND id != ?",
                        [$username, $tenantId, $userId]
                    );
                    if ($existing) {
                        $error = 'Username already taken by another user.';
                    } else {
                        $db->update('users', [
                            'username'         => $username,
                            'email'            => $email,
                            'current_store_id' => $storeId,
                            'status'           => $status,
                        ], 'id = ? AND tenant_id = ?', [$userId, $tenantId]);
                        $message = "Cashier \"$username\" updated successfully!";
                    }
                }
            }
        }

        // ── Load cashier users (role level = 1) ──────────────────
        $cashiers = $db->fetchAll(
            "SELECT u.*, r.name as role_name, r.level as role_level,
                    s.name as store_name, s.code as store_code
             FROM users u
             JOIN roles r ON u.role_id = r.id
             LEFT JOIN stores s ON u.current_store_id = s.id
             WHERE u.tenant_id = ? AND r.level = 1
             ORDER BY u.created_at DESC",
            [$tenantId]
        );

        // ── Load all stores for the dropdown ─────────────────────
        require_once __DIR__ . '/../../../core/classes/Store.php';
        $allStores = Store::getAll($tenantId);

        // ── Load current cashier role permissions ─────────────────
        $cashierRolePerms = [];
        if ($cashierRoleId) {
            $cashierRolePerms = $this->getRolePermissions($cashierRoleId, $db);
        }
        $cashierPermIds = array_column($cashierRolePerms, 'id');

        // Count usage (cashiers only, not all users)
        $totalUsers    = count($cashiers);
        $maxUsers      = Tenant::getCashierLimit();
        $canCreate     = ($maxUsers === 0) || ($totalUsers < $maxUsers);
        $tenantName    = Tenant::getCurrent()['name'] ?? '';

        include __DIR__ . '/../views/cashiers.php';
    }
}
?>

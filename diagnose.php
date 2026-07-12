<?php
// diagnose.php - Database Roles & Permissions Diagnostic Tool
require_once __DIR__ . '/core/classes/Database.php';
$db = Database::getInstance();

$message = '';
$error = '';

if (isset($_POST['action']) && $_POST['action'] === 'fix') {
    try {
        // Run database migrations first to ensure columns/tables exist
        ob_start();
        include __DIR__ . '/run_migrations.php';
        $migration_output = ob_get_clean();

        $db->getConnection()->beginTransaction();

        // 1. Ensure basic roles exist
        $requiredRoles = [
            ['name' => 'super_admin', 'description' => 'Super Administrator - SaaS Owner', 'level' => 3],
            ['name' => 'tenant_admin', 'description' => 'Tenant Administrator', 'level' => 2],
            ['name' => 'staff', 'description' => 'Staff / Cashier', 'level' => 1]
        ];

        foreach ($requiredRoles as $r) {
            $existing = $db->fetchOne("SELECT id FROM roles WHERE name = ? OR level = ?", [$r['name'], $r['level']]);
            if (!$existing) {
                $db->getConnection()->prepare(
                    "INSERT INTO roles (name, description, level) VALUES (?, ?, ?)"
                )->execute([$r['name'], $r['description'], $r['level']]);
            }
        }

        // Get the cashier/staff role IDs (level = 1)
        $cashierRoles = $db->fetchAll("SELECT id FROM roles WHERE level = 1");
        $cashierRoleIds = array_column($cashierRoles ?: [], 'id');

        // 2. Ensure default permissions exist
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
            $existing = $db->fetchOne(
                "SELECT id FROM permissions WHERE module = ? AND action = ?",
                [$perm['module'], $perm['action']]
            );
            if (!$existing) {
                $db->getConnection()->prepare(
                    "INSERT INTO permissions (name, module, action) VALUES (?, ?, ?)"
                )->execute([$perm['name'], $perm['module'], $perm['action']]);
            }
        }

        // Reload permissions to get their fresh IDs
        $allPerms = $db->fetchAll("SELECT * FROM permissions");
        $permMap = [];
        foreach ($allPerms as $p) {
            $permMap[$p['module'] . ':' . $p['action']] = (int)$p['id'];
        }

        // 3. Assign pos_read and pos_write permissions to all level = 1 roles
        if (!empty($cashierRoleIds)) {
            $posReadId = $permMap['pos:read'] ?? null;
            $posWriteId = $permMap['pos:write'] ?? null;

            foreach ($cashierRoleIds as $roleId) {
                if ($posReadId) {
                    $db->getConnection()->prepare(
                        "INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)"
                    )->execute([$roleId, $posReadId]);
                }
                if ($posWriteId) {
                    $db->getConnection()->prepare(
                        "INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)"
                    )->execute([$roleId, $posWriteId]);
                }
            }
        }

        $db->getConnection()->commit();
        $message = "Database roles, permissions, and cashier mappings have been successfully repaired!<br><br><strong>Migration Output:</strong><br>" . $migration_output;
    } catch (Exception $e) {
        $db->getConnection()->rollBack();
        $error = "Failed to run repair: " . $e->getMessage();
    }
}

// Fetch all roles
$roles = $db->fetchAll("SELECT * FROM roles ORDER BY level DESC") ?: [];

// Fetch all permissions
$permissions = $db->fetchAll("SELECT * FROM permissions ORDER BY module, action") ?: [];

// Fetch all users with their roles
$users = $db->fetchAll(
    "SELECT u.id, u.username, u.email, u.tenant_id, u.status, r.name as role_name, r.level as role_level, u.role_id 
     FROM users u 
     LEFT JOIN roles r ON u.role_id = r.id 
     ORDER BY u.tenant_id, u.username"
) ?: [];

// Fetch role permissions mapping
$rolePermsRaw = $db->fetchAll(
    "SELECT rp.role_id, p.module, p.action, p.name as perm_name 
     FROM role_permissions rp 
     JOIN permissions p ON rp.permission_id = p.id"
) ?: [];

$rolePerms = [];
foreach ($rolePermsRaw as $rp) {
    $rolePerms[$rp['role_id']][] = $rp['module'] . ':' . $rp['action'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Permissions Diagnostician</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: #0f172a; color: #f8fafc; padding: 40px 20px; line-height: 1.5; }
        .container { max-width: 1000px; margin: 0 auto; }
        h1, h2, h3 { color: #f1f5f9; }
        .card { background: #1e293b; border-radius: 12px; border: 1px solid #334155; padding: 24px; margin-bottom: 24px; }
        .alert-success { background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #34d399; padding: 16px; border-radius: 8px; margin-bottom: 20px; }
        .alert-error { background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #f87171; padding: 16px; border-radius: 8px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #334155; }
        th { background: #0f172a; color: #94a3b8; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: bold; }
        .badge-active { background: rgba(16, 185, 129, 0.2); color: #34d399; }
        .badge-inactive { background: rgba(239, 68, 68, 0.2); color: #f87171; }
        .badge-level-3 { background: rgba(168, 85, 247, 0.2); color: #c084fc; }
        .badge-level-2 { background: rgba(59, 130, 246, 0.2); color: #60a5fa; }
        .badge-level-1 { background: rgba(245, 158, 11, 0.2); color: #fbbf24; }
        .perm-badge { background: #334155; color: #cbd5e1; border: 1px solid #475569; margin: 2px; }
        .btn { display: inline-block; background: #3b82f6; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 14px; text-decoration: none; }
        .btn:hover { background: #2563eb; }
        .btn-danger { background: #ef4444; }
        .btn-danger:hover { background: #dc2626; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Database Permissions Diagnostician</h1>
        <p style="color: #94a3b8; margin-top: -10px; margin-bottom: 30px;">Helper utility to diagnose and resolve permission errors for POS cashier logins.</p>

        <?php if ($message): ?>
            <div class="alert-success">✓ <?php echo $message; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert-error">✗ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="card">
            <h2>🛠️ Quick Repair Action</h2>
            <p>If cashiers are unable to access the POS Dashboard, click the button below to restore default roles, seed missing permissions, and assign POS read/write permissions to all Cashier/Staff roles automatically.</p>
            <form method="POST">
                <input type="hidden" name="action" value="fix">
                <button type="submit" class="btn btn-danger">🚀 Run Auto-Fix Database Permissions</button>
            </form>
        </div>

        <div class="card">
            <h2>👥 Registered Users</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tenant ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role Name</th>
                        <th>Role Level</th>
                        <th>Assigned Permissions</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="8" style="text-align: center; color: #94a3b8;">No users found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?php echo (int)$u['id']; ?></td>
                                <td><?php echo (int)$u['tenant_id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($u['username']); ?></strong></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($u['role_name'] ?: 'NONE'); ?>
                                </td>
                                <td>
                                    <span class="badge badge-level-<?php echo (int)$u['role_level']; ?>">
                                        Level <?php echo (int)$u['role_level']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $pList = $rolePerms[$u['role_id']] ?? [];
                                    if ($u['role_level'] >= 2) {
                                        echo '<span class="badge badge-level-2">Admin (All subscribed modules)</span>';
                                    } elseif (empty($pList)) {
                                        echo '<span style="color: #ef4444; font-weight: bold;">NO PERMISSIONS ASSIGNED</span>';
                                    } else {
                                        foreach ($pList as $p) {
                                            echo '<span class="badge perm-badge">' . htmlspecialchars($p) . '</span>';
                                        }
                                    }
                                    ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $u['status'] === 'active' ? 'active' : 'inactive'; ?>">
                                        <?php echo htmlspecialchars($u['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="card">
            <h2>🔑 Defined Roles</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Role Name</th>
                        <th>Description</th>
                        <th>Level</th>
                        <th>Permissions Associated (Global)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($roles as $r): ?>
                        <tr>
                            <td><?php echo (int)$r['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($r['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($r['description']); ?></td>
                            <td>Level <?php echo (int)$r['level']; ?></td>
                            <td>
                                <?php
                                $pList = $rolePerms[$r['id']] ?? [];
                                if (empty($pList)) {
                                    echo '<span style="color: #94a3b8; font-style: italic;">None</span>';
                                } else {
                                    foreach ($pList as $p) {
                                        echo '<span class="badge perm-badge">' . htmlspecialchars($p) . '</span>';
                                    }
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="card">
            <h2>📜 Available Permissions in Table</h2>
            <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                <?php if (empty($permissions)): ?>
                    <p style="color: #ef4444; font-weight: bold;">Permissions table is empty!</p>
                <?php else: ?>
                    <?php foreach ($permissions as $p): ?>
                        <span class="badge perm-badge" style="background: #1e293b; padding: 6px 12px;">
                            <strong><?php echo htmlspecialchars($p['module']); ?></strong> &rarr; <?php echo htmlspecialchars($p['action']); ?> (<?php echo htmlspecialchars($p['name']); ?>)
                        </span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

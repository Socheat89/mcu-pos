<?php
// tenant/users.php

require_once __DIR__ . '/../core/classes/Database.php';
require_once __DIR__ . '/../core/classes/Tenant.php';
require_once __DIR__ . '/../core/classes/Auth.php';
require_once __DIR__ . '/../core/classes/User.php';
require_once __DIR__ . '/../core/classes/Settings.php';
require_once __DIR__ . '/../middleware/TenantMiddleware.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../core/helpers/url.php';

TenantMiddleware::handle();
AuthMiddleware::handle();

// Check if user has permission to manage users
if (!Auth::isTenantAdmin()) {
    header('Location: ' . mc_url(Tenant::getCurrent()['subdomain'] . '/dashboard?error=' . urlencode('Access denied')));
    exit;
}

$urlPrefix = mc_base_path();

$db = Database::getInstance();
$tenantId = Tenant::getId();

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_user'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $roleId = (int) $_POST['role_id'];

        try {
            User::create([
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'role_id' => $roleId
            ], $tenantId);
            $message = 'User created successfully!';
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// Get users
$users = User::getAll($tenantId);

// Get roles for dropdown
$roles = $db->fetchAll("SELECT * FROM roles WHERE level <= 2 ORDER BY level DESC");

// Get current settings
$maxFreeUsers = Settings::get('max_free_users', $tenantId, 5);
$currentUserCount = User::countUsers($tenantId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - <?php echo htmlspecialchars(Tenant::getCurrent()['name']); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #6a5cff;
            --primary-dark: #5648d4;
            --secondary: #8a3ffc;
            --accent: #2dd4ff;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --bg: #f6f7fb;
            --card-bg: #ffffff;
            --text: #1e293b;
            --text-muted: #64748b;
            --border: rgba(30, 41, 59, 0.08);
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            --shadow-hover: 0 15px 40px rgba(0, 0, 0, 0.12);
        }

        * { 
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
            background: 
                radial-gradient(900px 600px at 15% -10%, rgba(106, 92, 255, 0.15), transparent 60%),
                radial-gradient(900px 600px at 110% 10%, rgba(138, 63, 252, 0.12), transparent 60%),
                var(--bg);
            color: var(--text);
            min-height: 100vh;
            line-height: 1.6;
        }

        /* Navbar */
        .navbar {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        
        .navbar-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 72px;
        }
        
        .nav-brand {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .nav-brand i {
            font-size: 1.75rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .nav-links {
            display: flex;
            list-style: none;
            gap: 8px;
            align-items: center;
        }
        
        .nav-links a {
            color: var(--text);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            padding: 10px 18px;
            border-radius: 10px;
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .nav-links a:hover {
            background: rgba(106, 92, 255, 0.08);
            color: var(--primary);
        }
        
        .nav-links a.active {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
        }
        
        .nav-links .logout-btn {
            background: rgba(239, 68, 68, 0.08);
            color: var(--danger);
        }
        
        .nav-links .logout-btn:hover {
            background: var(--danger);
            color: white;
        }

        /* Container & Layout */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 32px;
        }

        .welcome-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 48px;
            border-radius: 20px;
            margin-bottom: 32px;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .welcome-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .welcome-content { z-index: 1; }
        
        .welcome-header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 8px;
        }
        
        .welcome-header p {
            font-size: 1.1rem;
            opacity: 0.95;
        }

        .main-grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 24px;
        }

        .card {
            background: var(--card-bg);
            border-radius: 16px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            overflow: hidden;
        }
        
        .card-half { grid-column: span 6; }
        
        @media (max-width: 1024px) {
            .card-half { grid-column: span 12; }
        }

        .card-header {
            padding: 24px 28px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .card-header h3 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card-header i { color: var(--primary); }
        .card-body { padding: 28px; }

        /* Forms & Inputs */
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--text); font-size: 0.9rem; }
        input, select { 
            width: 100%; 
            padding: 12px 16px; 
            border: 2px solid var(--border); 
            border-radius: 10px; 
            font-size: 0.95rem;
            background: var(--bg);
            transition: all 0.2s;
            color: var(--text);
        }
        input:focus, select:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 4px rgba(106, 92, 255, 0.1);
            background: white;
        }

        .btn {
            padding: 14px 24px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.95rem;
            text-align: center;
            transition: all 0.25s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(106, 92, 255, 0.3);
        }
        .btn:disabled { opacity: 0.7; cursor: not-allowed; transform: none; box-shadow: none; }

        /* Tables */
        table { width: 100%; border-collapse: separate; border-spacing: 0; }
        th, td { padding: 16px 20px; text-align: left; border-bottom: 1px solid var(--border); }
        th { background: rgba(0,0,0,0.02); font-weight: 700; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: rgba(0,0,0,0.01); }

        /* Alerts & Info */
        .message { padding: 16px; margin-bottom: 24px; border-radius: 12px; display: flex; align-items: center; gap: 12px; }
        .success { background: rgba(16, 185, 129, 0.1); color: var(--success); border: 1px solid rgba(16, 185, 129, 0.2); }
        .error { background: rgba(239, 68, 68, 0.1); color: var(--danger); border: 1px solid rgba(239, 68, 68, 0.2); }
        
        .limit-info {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(245, 158, 11, 0.05));
            color: #d97706;
            padding: 16px 24px;
            border-radius: 16px;
            margin-bottom: 24px;
            border: 1px solid rgba(245, 158, 11, 0.2);
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <div class="nav-brand">
                <i class="fas fa-cube"></i> <?php echo htmlspecialchars(Tenant::getCurrent()['name']); ?> Admin
            </div>
            <ul class="nav-links">
                <li><a href="<?php echo $urlPrefix; ?>/<?php echo Tenant::getCurrent()['subdomain']; ?>/dashboard"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                <li><a href="<?php echo $urlPrefix; ?>/<?php echo Tenant::getCurrent()['subdomain']; ?>/users" class="active"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="<?php echo $urlPrefix; ?>/<?php echo Tenant::getCurrent()['subdomain']; ?>/settings"><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="<?php echo $urlPrefix; ?>/<?php echo Tenant::getCurrent()['subdomain']; ?>/logout" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-header">
            <div class="welcome-content">
                <h1>User Management</h1>
                <p>Manage access levels and permissions for your team members</p>
            </div>
            <a href="<?php echo $urlPrefix; ?>/<?php echo Tenant::getCurrent()['subdomain']; ?>/dashboard" class="btn" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3);">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <?php if ($message): ?>
            <div class="message success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="message error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="limit-info">
            <i class="fas fa-info-circle"></i>
            <span>
                <strong>Plan Usage:</strong> <?php echo $currentUserCount; ?>/<?php echo $maxFreeUsers; ?> users active
                <?php if ($currentUserCount >= $maxFreeUsers): ?>
                    <span style="color: var(--danger); font-weight: 700; margin-left: 8px;">(Limit reached - Upgrade to add more)</span>
                <?php endif; ?>
            </span>
        </div>

        <div class="main-grid">
            <!-- Create User Form -->
            <div class="card card-half">
                <div class="card-header">
                    <h3><i class="fas fa-user-plus"></i> Create New User</h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-group">
                            <label><i class="fas fa-user-tag"></i> Username</label>
                            <input type="text" name="username" required placeholder="e.g. john_doe">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-envelope"></i> Email Address</label>
                            <input type="email" name="email" required placeholder="e.g. john@company.com">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-lock"></i> Password</label>
                            <input type="password" name="password" required placeholder="••••••••">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-shield-alt"></i> Role</label>
                            <select name="role_id" required>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?php echo $role['id']; ?>"><?php echo htmlspecialchars($role['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" name="create_user" class="btn btn-primary" style="width: 100%;" <?php echo (!User::canCreateUser($tenantId) ? 'disabled' : ''); ?>>
                            <?php if(!User::canCreateUser($tenantId)): ?>
                                <i class="fas fa-lock"></i> Limit Reached
                            <?php else: ?>
                                <i class="fas fa-plus-circle"></i> Create User
                            <?php endif; ?>
                        </button>
                    </form>
                </div>
            </div>

            <!-- User List -->
            <div class="card card-half">
                <div class="card-header">
                    <h3><i class="fas fa-users-cog"></i> Existing Users</h3>
                </div>
                <div class="card-body" style="padding: 0; overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text);">
                                            <?php echo htmlspecialchars($user['username']); ?>
                                        </div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted);">
                                            Added <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span style="background: rgba(106, 92, 255, 0.1); color: var(--primary); padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 700;">
                                            <?php echo htmlspecialchars($user['role_name']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if($user['status'] == 'active'): ?>
                                            <span style="color: var(--success); font-weight: 600; display: flex; align-items: center; gap: 6px;">
                                                <i class="fas fa-check-circle" style="font-size: 0.9em;"></i> Active
                                            </span>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted); display: flex; align-items: center; gap: 6px;">
                                                <i class="fas fa-ban" style="font-size: 0.9em;"></i> <?php echo ucfirst($user['status']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
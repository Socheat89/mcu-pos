<?php
// admin/header.php
require_once __DIR__ . '/../middleware/SuperAdminMiddleware.php';
SuperAdminMiddleware::handle();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Mekong CyberUnit</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="logo">
                <i class="ph-bold ph-cube" style="font-size: 1.5rem; color: var(--primary);"></i>
                <span>Admin Panel</span>
            </div>
            <nav>
                <ul class="nav-links">
                    <li class="nav-item">
                        <a href="index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                            <i class="ph-bold ph-house"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="plans.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'plans.php' ? 'active' : ''; ?>">
                            <i class="ph-bold ph-package"></i>
                            Pricing Plans
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="modules.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'modules.php' ? 'active' : ''; ?>">
                            <i class="ph-bold ph-squares-four"></i>
                            Plan Modules
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="tenants.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'tenants.php' ? 'active' : ''; ?>">
                            <i class="ph-bold ph-users-three"></i>
                            Subscriptions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="security.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'security.php' ? 'active' : ''; ?>">
                            <i class="ph-bold ph-lock"></i>
                            Security
                        </a>
                    </li>
                    <li class="nav-item" style="margin-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem;">
                        <a href="../public/index.php" class="nav-link">
                            <i class="ph-bold ph-arrow-left"></i>
                            Back to Site
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="nav-link" style="color: #ef4444;">
                            <i class="ph-bold ph-sign-out"></i>
                            Logout
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">

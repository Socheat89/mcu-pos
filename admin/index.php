<?php
require_once __DIR__ . '/../middleware/SuperAdminMiddleware.php';
SuperAdminMiddleware::handle();
require_once __DIR__ . '/../core/classes/Database.php';
include 'header.php';

$db = Database::getInstance();
$plansCount = $db->fetchOne("SELECT COUNT(*) as count FROM systems");
$tenantsCount = $db->fetchOne("SELECT COUNT(*) as count FROM tenants");
?>

<div class="header">
    <h1 class="page-title">Dashboard Overview</h1>
    <div class="user-info">
        <span class="btn btn-outline"><i class="ph-bold ph-user"></i> Super Admin</span>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <div class="card" style="display: flex; align-items: center; gap: 1.5rem;">
        <div style="background: rgba(37, 99, 235, 0.1); color: var(--primary); width: 60px; height: 60px; border-radius: 1rem; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
            <i class="ph-bold ph-package"></i>
        </div>
        <div>
            <div style="color: var(--text-muted); font-size: 0.875rem; font-weight: 500;">Active Plans</div>
            <div style="font-size: 1.5rem; font-weight: 700;"><?php echo $plansCount['count']; ?></div>
        </div>
    </div>
    <div class="card" style="display: flex; align-items: center; gap: 1.5rem;">
        <div style="background: rgba(16, 185, 129, 0.1); color: var(--success); width: 60px; height: 60px; border-radius: 1rem; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
            <i class="ph-bold ph-users"></i>
        </div>
        <div>
            <div style="color: var(--text-muted); font-size: 0.875rem; font-weight: 500;">Total Tenants</div>
            <div style="font-size: 1.5rem; font-weight: 700;"><?php echo $tenantsCount['count']; ?></div>
        </div>
    </div>
</div>

<div class="card">
    <h3 style="margin-bottom: 1.5rem;">Welcome to Mekong CyberUnit Admin</h3>
    <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Use the sidebar to manage your SAAS platform. You can configure pricing plans and assign modules to them.</p>
    <div class="actions">
        <a href="plans.php" class="btn btn-primary">Manage Plans</a>
        <a href="modules.php" class="btn btn-outline">Configure Modules</a>
    </div>
</div>

<?php include 'footer.php'; ?>

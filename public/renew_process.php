<?php
// public/renew_process.php
require_once __DIR__ . '/../core/classes/Database.php';
require_once __DIR__ . '/../core/classes/Auth.php';
require_once __DIR__ . '/../core/helpers/url.php';

session_start();

$urlPrefix = mc_base_path();

if (!Auth::check()) {
    header("Location: $urlPrefix/public/login.php");
    exit;
}

$ref = $_GET['ref'] ?? '';
$months = (int)($_GET['months'] ?? 1);
$planId = (int)($_GET['plan_id'] ?? 0);
$tenantId = $_SESSION['tenant_id'];

if (!$ref || !$planId) {
    die("Invalid request parameters.");
}

// 1. Verify approval status one last time
require_once __DIR__ . '/api/TransactionLogger.php';
$tx = TransactionLogger::get($ref);

if (!$tx || ($tx['status'] !== 'APPROVED' && $tx['status'] !== 'SUCCESS')) {
    header("Location: renew.php?error=" . urlencode("Payment verification failed. Please contact support."));
    exit;
}

try {
    $db = Database::getInstance();
    
    // 2. Check if tenant already has this system
    $existing = $db->fetchOne("SELECT expires_at FROM tenant_systems WHERE tenant_id = ? AND system_id = ?", [$tenantId, $planId]);
    
    // Calculate new expiry
    $baseDate = time();
    if ($existing && $existing['expires_at'] && strtotime($existing['expires_at']) > time()) {
        // If not yet expired, extend from the current expiry date
        $baseDate = strtotime($existing['expires_at']);
    }
    
    $newExpiry = date('Y-m-d H:i:s', strtotime("+$months months", $baseDate));

    if ($existing) {
        // Update existing
        $db->update('tenant_systems', 
            ['expires_at' => $newExpiry, 'status' => 'active', 'subscribed_at' => date('Y-m-d H:i:s')], 
            'tenant_id = ? AND system_id = ?', 
            [$tenantId, $planId]
        );
    } else {
        // First time subscripting to this plan (or switching)
        $db->insert('tenant_systems', [
            'tenant_id' => $tenantId,
            'system_id' => $planId,
            'status' => 'active',
            'expires_at' => $newExpiry
        ]);
    }

    // 3. Mark transaction as processed in logger to prevent reuse
    TransactionLogger::save($ref, ['status' => 'PROCESSED', 'processed_at' => time()]);

    // Redirect to success or dashboard
    header("Location: $urlPrefix/public/index.php?success=" . urlencode("Subscription renewed until $newExpiry!"));

} catch (Exception $e) {
    error_log("Renewal Error: " . $e->getMessage());
    header("Location: renew.php?error=" . urlencode("Database error during renewal."));
}
?>

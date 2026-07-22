<?php
// public/api/check_approval.php
// VERSION: V5_ROBUST_PATH
error_reporting(0); 

require_once __DIR__ . '/../../core/helpers/api.php';
mc_api_preflight('GET, OPTIONS');

// Accept both 'md5' and 'ref' parameters for compatibility
$md5 = $_GET['md5'] ?? $_GET['ref'] ?? '';

if (empty($md5)) {
    mc_json(['success' => false, 'status' => 'INVALID'], 400);
}

// HARDENED PATH LOGIC V7 (Using Class)
require_once __DIR__ . '/TransactionLogger.php';
require_once __DIR__ . '/../../core/classes/Database.php';

$txJson = TransactionLogger::get($md5);
$statusJson = $txJson ? strtoupper($txJson['status']) : 'NOT_FOUND';

$statusDb = 'NOT_FOUND';
try {
    $db = Database::getInstance();
    $dbTx = $db->fetchOne("SELECT status FROM payment_approvals WHERE reference_id = ?", [$md5]);
    if ($dbTx) {
        $statusDb = strtoupper($dbTx['status']);
    }
} catch (Exception $e) {
    error_log('Approval status DB error: ' . $e->getMessage());
    $statusDb = 'DB_ERROR';
}

$finalStatus = ($statusJson !== 'NOT_FOUND') ? $statusJson : $statusDb;

if ($finalStatus !== 'NOT_FOUND') {
    if ($finalStatus === 'APPROVED' || $finalStatus === 'SUCCESS') {
        mc_json(['success' => true, 'status' => 'SUCCESS']);
    } elseif ($finalStatus === 'REJECTED') {
        mc_json(['success' => true, 'status' => 'REJECTED']);
    } else {
        // Return PENDING status as is
        mc_json(['success' => true, 'status' => $finalStatus]);
    }
} else {
    mc_json([
        'success' => false, 
        'status' => 'NOT_FOUND'
    ], 404);
}
?>

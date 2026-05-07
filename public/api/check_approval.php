<?php
// public/api/check_approval.php
// VERSION: V5_ROBUST_PATH
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
error_reporting(0); 

// Accept both 'md5' and 'ref' parameters for compatibility
$md5 = $_GET['md5'] ?? $_GET['ref'] ?? '';

if (empty($md5)) {
    echo json_encode(['success' => false, 'status' => 'INVALID']);
    exit;
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
    $statusDb = 'DB_ERROR: ' . $e->getMessage();
}

$finalStatus = ($statusJson !== 'NOT_FOUND') ? $statusJson : $statusDb;

if ($finalStatus !== 'NOT_FOUND') {
    if ($finalStatus === 'APPROVED' || $finalStatus === 'SUCCESS') {
        echo json_encode(['success' => true, 'status' => 'SUCCESS', 'db' => $statusDb, 'json' => $statusJson]);
    } elseif ($finalStatus === 'REJECTED') {
        echo json_encode(['success' => true, 'status' => 'REJECTED', 'db' => $statusDb, 'json' => $statusJson]); 
    } else {
        // Return PENDING status as is
        echo json_encode(['success' => true, 'status' => $finalStatus, 'db' => $statusDb, 'json' => $statusJson]);
    }
} else {
    echo json_encode([
        'success' => false, 
        'status' => 'NOT_FOUND', 
        'debug' => [
            'path' => TransactionLogger::getPath(),
            'db_status' => $statusDb,
            'json_status' => $statusJson,
            'reference_id' => $md5
        ]
    ]);
}
?>

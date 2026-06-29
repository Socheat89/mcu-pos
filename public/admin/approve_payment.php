<?php
// public/admin/approve_payment.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../core/classes/Database.php';

$ref = $_GET['ref'] ?? '';
$action = $_GET['action'] ?? 'approve'; // Default to approve for backward compatibility

if (empty($ref)) {
    die("Error: No reference ID provided.");
}

// Adjust path logic for robust finding match check_approval
$logCandidates = [
    $_SERVER['DOCUMENT_ROOT'] . '/Mekong_CyberUnit/logs/transactions.json',
    $_SERVER['DOCUMENT_ROOT'] . '/logs/transactions.json',
    __DIR__ . '/../../logs/transactions.json' 
];

$logFile = null;
foreach ($logCandidates as $path) {
    if (file_exists($path)) {
        $logFile = $path;
        break;
    }
}
// If not found, try to use default to create it
if (!$logFile) {
     $logFile = __DIR__ . '/../../logs/transactions.json';
}


if (!$logFile || !file_exists($logFile)) {
     // Try to create if dir exists
    if ($logFile && file_exists(dirname($logFile))) {
         // It's okay, we will handle empty array
    } else {
        die("Error: Transaction log not found. Searched: " . implode(", ", $logCandidates));
    }
}

$transactions = json_decode(file_get_contents($logFile), true) ?? [];

if (!isset($transactions[$ref])) {
    die("Error: Transaction not found.");
}

// Update Status based on Action
$status = ($action === 'reject') ? 'REJECTED' : 'APPROVED';
$transactions[$ref]['status'] = $status;
$transactions[$ref]['processed_at'] = time();

if (file_put_contents($logFile, json_encode($transactions, JSON_PRETTY_PRINT))) {
    try {
        $db = Database::getInstance();
        $db->update('payment_approvals', ['status' => strtolower($status)], 'reference_id = ?', [$ref]);
    } catch (Exception $e) {
        // Silently ignore DB sync issues to keep manual approvals responsive
    }
    // Show success page
    $color = ($action === 'reject') ? '#dc2626' : '#16a34a';
    $bg = ($action === 'reject') ? '#fef2f2' : '#f0fdf4';
    $title = ($action === 'reject') ? 'Payment Rejected' : 'Payment Approved';
    $icon = ($action === 'reject') ? '❌' : '✅';
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $title; ?></title>
        <style>
            body { font-family: 'Segoe UI', sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; background: <?php echo $bg; ?>; margin: 0; }
            .card { background: white; padding: 2.5rem; border-radius: 1.5rem; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); text-align: center; max-width: 400px; width: 90%; }
            .icon { font-size: 4rem; margin-bottom: 1rem; }
            h1 { color: <?php echo $color; ?>; margin: 0 0 0.5rem 0; font-size: 1.5rem; }
            p { color: #6b7280; line-height: 1.5; }
            .meta { background: #f3f4f6; padding: 0.75rem; border-radius: 0.5rem; margin-top: 1.5rem; font-family: monospace; font-size: 0.9rem; }
        </style>
    </head>
    <body>
        <div class="card">
            <div class="icon"><?php echo $icon; ?></div>
            <h1><?php echo $title; ?></h1>
            <p>Transaction has been processed.</p>
            <div class="meta">REF: <?php echo htmlspecialchars($ref); ?></div>
            <p style="font-size: 0.875rem; margin-top: 1rem;">You can close this window.</p>
        </div>
    </body>
    </html>
    <?php
} else {
    echo "Error: Failed to save status.";
}
?>

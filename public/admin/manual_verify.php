<?php
// public/admin/manual_verify.php
// Localhost Helper to Simulate Telegram Approval
header('Content-Type: text/html');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../core/classes/Database.php';
require_once __DIR__ . '/../api/TransactionLogger.php';

// Handle Action
if (isset($_GET['action']) && isset($_GET['ref'])) {
    $ref = $_GET['ref'];
    $act = $_GET['action'];
    $newStatus = ($act === 'approve') ? 'APPROVED' : 'REJECTED';
    
    $processed = false;

    // 1. Update JSON
    if (TransactionLogger::get($ref)) {
        if (TransactionLogger::save($ref, ['status' => $newStatus])) {
            $processed = true;
        }
    }

    // 2. Update Database
    try {
        $db = Database::getInstance();
        $count = $db->update('payment_approvals', ['status' => strtolower($newStatus)], 'reference_id = ?', [$ref]);
        if ($count > 0) {
            $processed = true;
        }
    } catch (Exception $e) {}

    if ($processed) {
        echo "<div style='padding:15px; background:#dcfce7; color:#166534; border:1px solid #bbf7d0; border-radius:8px; margin-bottom:20px; font-family: sans-serif;'>
                 ✅ Transaction <strong>$ref</strong> marked as <strong>$newStatus</strong>!
              </div>";
    }
}

// Display
echo "<div style='font-family: sans-serif; max-width: 800px; margin: 0 auto; padding: 20px;'>";
echo "<div style='display:flex; justify-content:space-between; align-items:center;'>";
echo "<h2>🛠️ Localhost Approval Tool</h2>";
echo "<a href='../register.php' style='text-decoration:none; padding:8px 16px; background:#64748b; color:white; border-radius:6px; font-size:14px;'>⬅️ Back to Register</a>";
echo "</div>";
echo "<p style='color:#64748b;'>Since Telegram Webhooks don't work on Localhost, use this tool to manually approve or reject pending payments during testing.</p>";

$allTransactions = [];

// Get from JSON
$json = TransactionLogger::get();
if ($json) {
    foreach ($json as $ref => $data) {
        $allTransactions[$ref] = [
            'amount' => $data['amount'] ?? '0.00',
            'status' => strtoupper($data['status'] ?? 'PENDING'),
            'source' => 'JSON'
        ];
    }
}

// Get from DB
try {
    $db = Database::getInstance();
    $dbTxs = $db->fetchAll("SELECT * FROM payment_approvals ORDER BY id DESC LIMIT 20");
    foreach ($dbTxs as $tx) {
        $ref = $tx['reference_id'];
        if (!isset($allTransactions[$ref])) {
            $allTransactions[$ref] = [
                'amount' => $tx['amount'],
                'status' => strtoupper($tx['status']),
                'source' => 'DB'
            ];
        } else {
             $allTransactions[$ref]['source'] .= ' + DB';
        }
    }
} catch (Exception $e) {}

if (!empty($allTransactions)) {
    echo "<table border='0' style='width:100%; border-collapse:collapse; box-shadow:0 1px 3px rgba(0,0,0,0.1); border-radius:8px; overflow:hidden;'>";
    echo "<tr style='background:#f8fafc; border-bottom:1px solid #e2e8f0; text-align:left;'>
            <th style='padding:12px;'>Reference</th>
            <th style='padding:12px;'>Amount</th>
            <th style='padding:12px;'>Status</th>
            <th style='padding:12px;'>Action</th>
          </tr>";
    
    foreach ($allTransactions as $ref => $data) {
        $status = $data['status'];
        $bg = ($status === 'APPROVED' || $status === 'SUCCESS') ? '#f0fdf4' : (($status === 'REJECTED') ? '#fef2f2' : '#ffffff');
        $color = ($status === 'APPROVED' || $status === 'SUCCESS') ? '#166534' : (($status === 'REJECTED') ? '#991b1b' : '#0f172a');
        
        echo "<tr style='background:$bg; border-bottom:1px solid #f1f5f9; color:$color;'>";
        echo "<td style='padding:12px;'><code style='background:#f1f5f9; padding:2px 4px; border-radius:4px;'>$ref</code><br><small style='color:#94a3b8;'>Source: {$data['source']}</small></td>";
        echo "<td style='padding:12px;'>\${$data['amount']}</td>";
        echo "<td style='padding:12px;'><b>$status</b></td>";
        echo "<td style='padding:12px;'>";
        if ($status === 'PENDING') {
            echo "<a href='?action=approve&ref=$ref' style='text-decoration:none; padding:6px 12px; background:#16a34a; color:white; border-radius:4px; font-size:13px; font-weight:bold; margin-right:5px;'>✅ Approve</a>";
            echo "<a href='?action=reject&ref=$ref' style='text-decoration:none; padding:6px 12px; background:#dc2626; color:white; border-radius:4px; font-size:13px; font-weight:bold;'>❌ Reject</a>";
        } else {
            echo "<span style='color:#94a3b8; font-style:italic;'>Processed</span>";
        }
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div style='padding:40px; text-align:center; background:#f8fafc; border:2px dashed #e2e8f0; border-radius:12px; color:#64748b;'>
            No transactions found in logs or database.
          </div>";
}
echo "</div>";
?>

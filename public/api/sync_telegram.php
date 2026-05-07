<?php
// public/api/sync_telegram.php
// Manually fetch updates from Telegram Bot (polling) - for Localhost testing
header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../core/classes/Database.php';
require_once __DIR__ . '/TransactionLogger.php';

$config = require __DIR__ . '/../../config/telegram.php';
$token  = $config['bot_token'];

echo "<!DOCTYPE html>
<html lang='en'>
<head>
<meta charset='utf-8'>
<meta name='viewport' content='width=device-width,initial-scale=1'>
<title>🔄 Telegram Local Sync</title>
<style>
  body { font-family: Arial, sans-serif; background:#111; color:#eee; padding:20px; }
  h2   { color:#0cf; }
  .card { background:#1e1e1e; border:1px solid #333; border-radius:8px; padding:15px; margin:10px 0; }
  .ok   { color:#0f0; } .err { color:#f44; } .warn { color:#fa0; }
  button { padding:10px 18px; border:none; border-radius:6px; cursor:pointer; font-size:14px; margin:5px 0; }
  .btn-red  { background:#c00; color:#fff; }
  .btn-blue { background:#007bff; color:#fff; }
  .btn-grn  { background:#28a745; color:#fff; }
  a   { color:#6af; }
  pre { background:#000; padding:10px; border-radius:5px; font-size:12px; overflow:auto; }
</style>
</head>
<body>
<h2>🔄 Telegram Local Sync (Polling Mode)</h2>";

if (empty($token) || $token === 'YOUR_BOT_TOKEN_HERE') {
    echo "<p class='err'>❌ Bot token not configured in config/telegram.php</p></body></html>";
    exit;
}

// ─── Handle DELETE WEBHOOK ────────────────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'delete_webhook') {
    $delUrl  = "https://api.telegram.org/bot$token/deleteWebhook?drop_pending_updates=false";
    $delResp = @file_get_contents($delUrl);
    $delJson = $delResp ? json_decode($delResp, true) : null;
    if ($delJson['ok'] ?? false) {
        echo "<p class='ok'>✅ Webhook removed! Reloading...</p>
              <script>setTimeout(() => location.href = location.pathname, 1500);</script>";
    } else {
        echo "<p class='err'>❌ Failed to remove webhook: " . htmlspecialchars($delResp ?? 'No response') . "</p>";
    }
    echo "</body></html>";
    exit;
}

// ─── Check Current Webhook Status ────────────────────────────────────────────
$whInfo = @file_get_contents("https://api.telegram.org/bot$token/getWebhookInfo");
$whData = $whInfo ? json_decode($whInfo, true) : null;
$activeWebhook = $whData['result']['url'] ?? '';

if (!empty($activeWebhook)) {
    echo "<div class='card warn'>
        <b>⚠️ Active Webhook Detected:</b><br>
        <code>" . htmlspecialchars($activeWebhook) . "</code><br><br>
        <i>Polling (getUpdates) cannot be used while a webhook is active.<br>
        Click below to temporarily disable it for local testing.</i><br><br>
        <form method='POST'>
            <input type='hidden' name='action' value='delete_webhook'>
            <button type='submit' class='btn-red'>🗑️ Disable Webhook (Local Testing)</button>
        </form>
    </div>";
    echo "</body></html>";
    exit;
}

echo "<div class='card ok'>✅ No active webhook — polling mode active!</div>";

// ─── Fetch Last 20 Updates ────────────────────────────────────────────────────
$url      = "https://api.telegram.org/bot$token/getUpdates?limit=20&allowed_updates[]=callback_query";
$response = @file_get_contents($url);

if ($response === false) {
    $err = error_get_last();
    echo "<div class='card err'>❌ Cannot connect to Telegram API:<br>" . htmlspecialchars($err['message'] ?? 'Unknown error') . "</div>";
    echo "</body></html>";
    exit;
}

$updates = json_decode($response, true);

if (!($updates['ok'] ?? false)) {
    echo "<div class='card err'>❌ Telegram API Error: " . htmlspecialchars($updates['description'] ?? 'Unknown') . "</div>";
    echo "</body></html>";
    exit;
}

$results = $updates['result'] ?? [];
echo "<p>📦 Found <b>" . count($results) . "</b> update(s) from Telegram.</p>";

$processed = 0;
$skipped   = 0;

if (!empty($results)) {
    echo "<ul>";
    foreach ($results as $upd) {
        $callback = $upd['callback_query'] ?? null;
        if (!$callback) { $skipped++; continue; }

        $data    = $callback['data'] ?? '';
        $ref     = '';
        $action  = '';

        // Detect separator (::, :, _)
        $sep = null;
        foreach (['::', ':', '_'] as $s) {
            if (strpos($data, $s) !== false) { $sep = $s; break; }
        }
        if (!$sep) { $skipped++; continue; }

        [$action, $ref] = explode($sep, $data, 2);
        $action = strtolower(trim($action));
        $ref    = trim($ref);

        if (!in_array($action, ['approve', 'reject'])) { $skipped++; continue; }

        $newStatus = ($action === 'approve') ? 'APPROVED' : 'REJECTED';

        // Update JSON log
        TransactionLogger::save($ref, ['status' => $newStatus, 'processed_at' => time()]);

        // Update DB
        try {
            $db = Database::getInstance();
            $db->update('payment_approvals', ['status' => strtolower($newStatus)], 'reference_id = ?', [$ref]);
        } catch (Exception $e) {}

        $icon = ($newStatus === 'APPROVED') ? '✅' : '❌';
        echo "<li>$icon Processed: <b>" . htmlspecialchars($ref) . "</b> → <b class='ok'>$newStatus</b></li>";
        $processed++;
    }
    echo "</ul>";
}

if ($skipped > 0) {
    echo "<p class='warn'>⚠️ Skipped $skipped non-callback update(s).</p>";
}

if ($processed === 0) {
    echo "<div class='card'>
        <p>📭 No new approval updates found.</p>
        <p><small>Press <b>Approve/Reject</b> in Telegram first, then click Refresh below.</small></p>
    </div>";
} else {
    echo "<p class='ok' style='font-weight:bold;'>🎉 Successfully synced $processed transaction(s)!</p>";
}

echo "
<hr style='border-color:#333;'>
<button class='btn-blue' onclick='location.reload()'>🔄 Refresh / Check Again</button>
<a href='../register.php' style='margin-left:10px;'>⬅️ Back to Register</a>
<a href='debug_hits.txt' target='_blank' style='margin-left:10px;'>📋 View Debug Log</a>
</body></html>";

<?php
/**
 * GPS Tracking Migration for Production
 * Visit: https://pos.mekongcyberunit.app/public/api/migrate_gps.php?key=mcu-deploy-2026
 * Or: https://mcu-pos.me/public/api/migrate_gps.php?key=mcu-deploy-2026
 * 
 * This creates the GPS tracking tables on your production database.
 * Safe to run multiple times — skips existing tables.
 */

// Security key
$secretKey = $_GET['key'] ?? '';
if ($secretKey !== 'mcu-deploy-2026') {
    http_response_code(403);
    die('<h1>403 - Access Denied</h1><p>Add ?key=mcu-deploy-2026 to the URL.</p>');
}

require_once __DIR__ . '/../../core/classes/Database.php';

header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html><head><title>GPS Migration</title>';
echo '<style>body{font-family:monospace;background:#0f172a;color:#e2e8f0;padding:30px;max-width:800px;margin:0 auto}';
echo 'h1{color:#06b6d4}.ok{color:#10b981}.skip{color:#f59e0b}.err{color:#ef4444}';
echo 'pre{background:#1e293b;padding:16px;border-radius:8px;overflow-x:auto}</style>';
echo '</head><body>';
echo '<h1>🛰️ GPS Tracking Migration</h1>';

try {
    $db = Database::getInstance();
    echo '<p class="ok">✅ Connected to database</p>';

    $sql = file_get_contents(__DIR__ . '/../../database/migrations/004_add_gps_tracking.sql');
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    echo '<pre>';
    foreach ($statements as $stmt) {
        if (empty($stmt)) continue;
        if (stripos($stmt, 'CREATE TABLE') === false && stripos($stmt, 'ALTER TABLE') === false) continue;

        try {
            $db->query($stmt);
            if (stripos($stmt, 'CREATE TABLE') !== false) {
                preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $stmt, $m);
                echo '<span class="ok">✅ Created:</span> ' . ($m[1] ?? 'table') . "\n";
            } else {
                preg_match('/ALTER TABLE\s+`?(\w+)`?\s+ADD COLUMN\s+`?(\w+)`?/i', $stmt, $m);
                echo '<span class="ok">✅ Added column:</span> ' . ($m[2] ?? 'column') . ' → ' . ($m[1] ?? 'table') . "\n";
            }
        } catch (Exception $e) {
            $msg = $e->getMessage();
            if (stripos($msg, 'already exists') !== false || stripos($msg, 'Duplicate') !== false || stripos($msg, 'duplicate') !== false) {
                preg_match('/ADD COLUMN\s+`?(\w+)`?/i', $stmt, $m);
                echo '<span class="skip">⏭️  Skip:</span> ' . ($m[1] ?? 'column') . " (already exists)\n";
            } else {
                echo '<span class="err">❌ Error:</span> ' . htmlspecialchars($msg) . "\n";
            }
        }
    }
    echo '</pre>';

    // Verify tables exist
    echo '<h3>📋 Verification:</h3><pre>';
    $tables = ['gps_tracking_sessions', 'gps_locations', 'tenant_telegram_config'];
    foreach ($tables as $t) {
        try {
            $check = $db->fetchAll("SHOW TABLES LIKE '$t'");
            if (!empty($check)) {
                echo "<span class=\"ok\">✅</span> $t — exists\n";
            } else {
                echo "<span class=\"err\">❌</span> $t — NOT FOUND\n";
            }
        } catch (Exception $e) {
            echo "<span class=\"err\">❌</span> $t — ERROR: " . htmlspecialchars($e->getMessage()) . "\n";
        }
    }
    echo '</pre>';

    echo '<h2 class="ok">✅ Migration Complete!</h2>';
    echo '<p>GPS Tracking is now ready. Go to your POS → GPS Tracking in the sidebar.</p>';
    echo '<p><a href="../.." style="color:#06b6d4;">← Back to Dashboard</a></p>';

} catch (Exception $e) {
    echo '<h2 class="err">❌ Connection Failed</h2>';
    echo '<pre class="err">' . htmlspecialchars($e->getMessage()) . '</pre>';
}

echo '</body></html>';

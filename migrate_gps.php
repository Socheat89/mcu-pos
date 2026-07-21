<?php
// Quick migration runner for GPS tracking tables
require_once __DIR__ . '/core/classes/Database.php';

$db = Database::getInstance();

$sql = file_get_contents(__DIR__ . '/database/migrations/004_add_gps_tracking.sql');
$statements = array_filter(array_map('trim', explode(';', $sql)));

echo "Running GPS tracking migration...\n\n";

foreach ($statements as $stmt) {
    if (empty($stmt)) continue;
    if (stripos($stmt, 'CREATE TABLE') === false) continue;
    
    try {
        $db->query($stmt);
        // Extract table name
        preg_match('/CREATE TABLE.*?(\w+)/i', $stmt, $m);
        echo "✅ OK: " . ($m[1] ?? 'table') . "\n";
    } catch (Exception $e) {
        $msg = $e->getMessage();
        if (stripos($msg, 'already exists') !== false || stripos($msg, 'Duplicate') !== false) {
            echo "⏭️  SKIP (already exists): " . (isset($m[1]) ? $m[1] : 'table') . "\n";
        } else {
            echo "❌ ERROR: " . $msg . "\n";
        }
    }
}

echo "\n✅ Migration complete.\n";

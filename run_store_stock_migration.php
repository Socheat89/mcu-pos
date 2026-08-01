<?php
// ─── Store Stock Migration Runner ───
// Run this once via browser to create the store_stock table.
// Delete this file after running!

require_once __DIR__ . '/core/bootstrap_session.php';
require_once __DIR__ . '/core/classes/Database.php';
require_once __DIR__ . '/core/classes/Tenant.php';
require_once __DIR__ . '/core/classes/Auth.php';

// Security: only super admin can run migrations
if (!Auth::check() || !Auth::isSuperAdmin()) {
    http_response_code(403);
    die('<h2>403 Forbidden — Super Admin only</h2>');
}

header('Content-Type: text/plain; charset=utf-8');

try {
    $pdo = Database::getInstance()->getConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = file_get_contents(__DIR__ . '/database/migrations/2026_08_01_store_stock.sql');

    // Split into statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($statements as $s) {
        if (empty($s)) continue;
        $clean = preg_replace('/--[^\n]*\n?/', '', $s);
        $clean = trim($clean);
        if (empty($clean)) continue;
        try {
            $pdo->exec($s);
            echo "✅ OK: " . substr(trim($s), 0, 100) . "\n";
        } catch (PDOException $e) {
            echo "⚠️  WARN: " . $e->getMessage() . "\n";
        }
    }
    echo "\n✅ Migration complete! Please delete this file now.\n";
} catch (Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

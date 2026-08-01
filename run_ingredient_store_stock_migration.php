<?php
/**
 * run_ingredient_store_stock_migration.php
 * Run this ONCE via browser to set up ingredient_store_stock table.
 * DELETE this file after running!
 */

// Basic auth guard — only super admin can run this
session_start();
$allowed = false;
if (!empty($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'super_admin') {
    $allowed = true;
}
if (!$allowed && php_sapi_name() !== 'cli') {
    die('<h2 style="color:red;">Access Denied — Must be Super Admin</h2>');
}

require_once __DIR__ . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4",
        $db_user, $db_pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}

header('Content-Type: text/plain; charset=utf-8');
echo "=== Ingredient Store Stock Migration ===\n\n";

$sql = file_get_contents(__DIR__ . '/database/migrations/2026_08_01_ingredient_store_stock.sql');

// Strip all comments before splitting on semicolons
$sql = preg_replace('/--[^\n]*/', '', $sql);
$sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

$statements = array_filter(array_map('trim', explode(';', $sql)));
foreach ($statements as $s) {
    if (empty($s)) continue;
    try {
        $pdo->exec($s);
        echo "✅ OK: " . substr($s, 0, 120) . "\n";
    } catch (PDOException $e) {
        echo "⚠️  WARN: " . $e->getMessage() . "\n";
    }
}
echo "\n✅ Migration complete! Please delete this file now.\n";

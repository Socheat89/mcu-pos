<?php
/**
 * test_db_connect.php
 * Upload to: public_html/test_db_connect.php
 * Then visit: https://pos.mekongcyberunit.app/test_db_connect.php
 * DELETE THIS FILE AFTER TESTING!
 */

echo "<h2>Database Connection Test</h2>";
echo "<pre>";

// Step 1: Check if database.local.php exists
$localConfigPath = __DIR__ . '/config/database.local.php';
echo "1. database.local.php exists: " . (file_exists($localConfigPath) ? 'YES ✅' : 'NO ❌') . "\n";
if (file_exists($localConfigPath)) {
    echo "   Size: " . filesize($localConfigPath) . " bytes\n";
    echo "   Readable: " . (is_readable($localConfigPath) ? 'YES ✅' : 'NO ❌') . "\n";
    $config = require $localConfigPath;
    echo "   Config loaded: " . (is_array($config) ? 'YES ✅' : 'NO ❌') . "\n";
    if (is_array($config)) {
        echo "   Keys: " . implode(', ', array_keys($config)) . "\n";
        echo "   Host: " . $config['host'] . "\n";
        echo "   Database: " . $config['database'] . "\n";
        echo "   Username: " . $config['username'] . "\n";
    }
}

// Step 2: Check PDO extension
echo "\n2. PDO extension: " . (extension_loaded('pdo') ? 'YES ✅' : 'NO ❌') . "\n";
echo "   PDO MySQL driver: " . (extension_loaded('pdo_mysql') ? 'YES ✅' : 'NO ❌') . "\n";

// Step 3: Try connection
echo "\n3. Trying MySQL connection...\n";
try {
    $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
    echo "   DSN: $dsn\n";
    
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5
    ]);
    echo "   CONNECTION SUCCESS ✅\n";
    
    // Test query
    $stmt = $pdo->query("SELECT 1 AS test");
    echo "   Query test: " . ($stmt->fetchColumn() == 1 ? 'OK ✅' : 'FAIL ❌') . "\n";
    
    // Show MySQL version
    $version = $pdo->query("SELECT VERSION()")->fetchColumn();
    echo "   MySQL version: $version\n";
    
} catch (PDOException $e) {
    echo "   CONNECTION FAILED ❌\n";
    echo "   Error: " . $e->getMessage() . "\n";
    echo "   Code: " . $e->getCode() . "\n";
    
    // Specific diagnosis
    $msg = $e->getMessage();
    if (strpos($msg, 'Access denied') !== false) {
        echo "\n   => DIAGNOSIS: Wrong username or password!\n";
        echo "   => Check cPanel > MySQL Databases > check user privileges\n";
    } elseif (strpos($msg, 'Unknown database') !== false) {
        echo "\n   => DIAGNOSIS: Database does not exist!\n";
        echo "   => Check cPanel > MySQL Databases\n";
    } elseif (strpos($msg, 'getaddrinfo') !== false || strpos($msg, 'No such host') !== false) {
        echo "\n   => DIAGNOSIS: Cannot resolve hostname!\n";
        echo "   => Try changing 'host' to '127.0.0.1' in database.local.php\n";
    } elseif (strpos($msg, 'Connection refused') !== false || strpos($msg, 'Connection timed out') !== false) {
        echo "\n   => DIAGNOSIS: MySQL server not reachable!\n";
        echo "   => Try changing 'host' to '127.0.0.1' in database.local.php\n";
    } elseif (strpos($msg, 'could not find driver') !== false) {
        echo "\n   => DIAGNOSIS: PDO MySQL driver not installed!\n";
        echo "   => Contact hosting provider\n";
    }
}

echo "\n</pre>";
echo "<p style='color:red;font-weight:bold;'>⚠️ DELETE THIS FILE AFTER TESTING!</p>";

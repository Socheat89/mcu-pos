<?php
// admin/debug_telegram.php
// TEMPORARY debug script — remove after troubleshooting
require_once __DIR__ . '/../middleware/SuperAdminMiddleware.php';
SuperAdminMiddleware::handle();

header('Content-Type: text/plain; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$root = dirname(__DIR__);
$issues = [];
$info   = [];

echo "=== MCU POS Telegram Debug ===\n\n";

// 1. Check local config file
$localFile = $root . '/config/telegram.local.php';
if (is_file($localFile)) {
    $tok = require $localFile;
    $info[] = "telegram.local.php : EXISTS  (token length=" . strlen((string)$tok) . ")";
} else {
    $issues[] = "telegram.local.php : NOT FOUND — save Bot Token via Admin → Settings first";
    $info[]   = "telegram.local.php : NOT FOUND";
}

// 2. Check env var
$envToken = getenv('MC_TELEGRAM_BOT_TOKEN') ?: ($_ENV['MC_TELEGRAM_BOT_TOKEN'] ?? '');
$info[] = "MC_TELEGRAM_BOT_TOKEN env : " . (empty($envToken) ? "NOT SET" : "SET (len=" . strlen($envToken) . ")");

// 3. Load config/telegram.php and check bot_token
try {
    $cfg = require $root . '/config/telegram.php';
    $botLen = strlen($cfg['bot_token'] ?? '');
    $info[] = "config/telegram.php bot_token length : " . $botLen;
    if ($botLen === 0) {
        $issues[] = "Bot token is EMPTY — neither telegram.local.php nor MC_TELEGRAM_BOT_TOKEN is set";
    }
} catch (Throwable $e) {
    $issues[] = "EXCEPTION loading config/telegram.php: " . $e->getMessage() . " in " . $e->getFile() . " line " . $e->getLine();
}

// 4. Check CookieCrypt
try {
    require_once $root . '/core/classes/CookieCrypt.php';
    $enc = CookieCrypt::encrypt('test');
    $dec = CookieCrypt::decrypt($enc);
    $info[] = "CookieCrypt encrypt/decrypt : OK (roundtrip=" . ($dec === 'test' ? 'PASS' : 'FAIL') . ")";
} catch (Throwable $e) {
    $issues[] = "CookieCrypt ERROR: " . $e->getMessage() . " in " . $e->getFile() . " line " . $e->getLine();
}

// 5. Check config/telegram.local.php writability
$configDir = $root . '/config';
$info[] = "config/ dir writable : " . (is_writable($configDir) ? 'YES' : 'NO');

// 6. Check DB connection
try {
    require_once $root . '/core/classes/Database.php';
    $db = Database::getInstance();
    $info[] = "Database connection : OK";
} catch (Throwable $e) {
    $issues[] = "Database ERROR: " . $e->getMessage();
}

// Print results
echo "--- Info ---\n";
foreach ($info as $line) echo "  $line\n";

echo "\n--- Issues ---\n";
if (empty($issues)) {
    echo "  No issues found!\n";
} else {
    foreach ($issues as $issue) echo "  ⚠ $issue\n";
}

echo "\n--- PHP Error Log (last 20 lines) ---\n";
$logFile = ini_get('error_log');
if ($logFile && is_readable($logFile)) {
    $lines = array_slice(file($logFile), -20);
    echo implode('', $lines);
} else {
    echo "  Cannot read PHP error log ($logFile)\n";
}

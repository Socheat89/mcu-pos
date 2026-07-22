<?php
/**
 * Deploy Script — Pull latest code from GitHub
 * Visit: https://pos.mekongcyberunit.app/deploy.php
 * 
 * Disabled by default. Enable only temporarily with MC_ENABLE_WEB_DEPLOY=1
 * and MC_DEPLOY_KEY set in the server environment.
 */

require_once __DIR__ . '/config/env.php';

$requiredKey = mc_env('MC_DEPLOY_KEY', '');
if (!mc_bool_env('MC_ENABLE_WEB_DEPLOY', false) || $requiredKey === '') {
    http_response_code(404);
    die('<h1>404 - Not Found</h1>');
}

$secretKey = $_GET['key'] ?? '';

if (!hash_equals((string) $requiredKey, (string) $secretKey)) {
    http_response_code(403);
    die('<h1>403 - Access Denied</h1>');
}

echo '<pre style="font-family:monospace;padding:20px;">';
echo "🚀 Starting deploy...\n\n";

// Run git pull with hard reset to avoid local conflicts
$output = [];
$returnCode = 0;
chdir(__DIR__);
exec('git fetch origin 2>&1', $output, $returnCode);
exec('git pull --ff-only origin main 2>&1', $output2, $returnCode2);
$output = array_merge($output, $output2);
$returnCode = $returnCode !== 0 ? $returnCode : $returnCode2;

foreach ($output as $line) {
    echo htmlspecialchars($line) . "\n";
}

if (function_exists('opcache_reset')) {
    @opcache_reset();
    echo "⚡ OPcache reset successfully!\n";
}
clearstatcache(true);

echo "\n" . ($returnCode === 0 ? '✅ Deploy successful!' : '❌ Deploy failed!') . "\n";

// Show last 3 commits
echo "\n📋 Recent commits:\n";
exec('git log --oneline -3 2>&1', $logOutput);
foreach ($logOutput as $line) {
    echo '  ' . htmlspecialchars($line) . "\n";
}

echo '</pre>';

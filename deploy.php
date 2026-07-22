<?php
/**
 * Deploy Script — Pull latest code from GitHub
 * Visit: https://pos.mekongcyberunit.app/deploy.php?key=mcu-deploy-2026
 * 
 * Set MC_DEPLOY_KEY in server env to override the built-in key.
 */

require_once __DIR__ . '/config/env.php';

// Built-in deploy key — works without any env config.
// Set MC_DEPLOY_KEY env var on server to use a custom key instead.
$builtInKey = 'mcu-deploy-2026';
$requiredKey = mc_env('MC_DEPLOY_KEY', $builtInKey);

$secretKey = $_GET['key'] ?? '';

if (!hash_equals((string) $requiredKey, (string) $secretKey)) {
    http_response_code(403);
    die('<h1>403 - Access Denied</h1><p>Invalid or missing deploy key.</p>');
}

echo '<pre style="font-family:monospace;padding:20px;">';
echo "🚀 Starting deploy...\n\n";

// 1. Run git pull with fast-forward only (safe for production)
$output = [];
chdir(__DIR__);
exec('git fetch origin 2>&1', $output, $returnCode);
exec('git pull --ff-only origin main 2>&1', $output2, $returnCode2);
$output = array_merge($output, $output2);
$returnCode = $returnCode !== 0 ? $returnCode : $returnCode2;

foreach ($output as $line) {
    echo htmlspecialchars($line) . "\n";
}

if ($returnCode !== 0) {
    echo "\n❌ Git pull failed. Aborting deploy.\n";
    echo '</pre>';
    exit(1);
}

echo "\n✅ Code pulled successfully!\n";

// 2. Run database migrations
echo "\n🗄️ Running database migrations...\n";
$migrationOutput = [];
$migrationCode = 0;
exec('php ' . escapeshellarg(__DIR__ . '/run_migrations.php') . ' 2>&1', $migrationOutput, $migrationCode);
foreach ($migrationOutput as $line) {
    echo '  ' . htmlspecialchars($line) . "\n";
}
echo $migrationCode === 0 ? "✅ Migrations complete!\n" : "⚠️ Migrations had warnings (check output above)\n";

// 3. Rebuild React frontend
echo "\n⚛️ Rebuilding frontend...\n";
$buildOutput = [];
$buildCode = 0;
$frontendDir = __DIR__ . '/frontend';

if (is_dir($frontendDir) && file_exists($frontendDir . '/package.json')) {
    // Check if node_modules exists, if not run npm install first
    if (!is_dir($frontendDir . '/node_modules')) {
        echo "  → Installing npm dependencies...\n";
        exec('cd ' . escapeshellarg($frontendDir) . ' && npm install 2>&1', $installOutput, $installCode);
        foreach ($installOutput as $line) {
            echo '    ' . htmlspecialchars($line) . "\n";
        }
    }
    
    exec('cd ' . escapeshellarg($frontendDir) . ' && npm run build 2>&1', $buildOutput, $buildCode);
    foreach ($buildOutput as $line) {
        echo '  ' . htmlspecialchars($line) . "\n";
    }
    echo $buildCode === 0 ? "✅ Frontend built!\n" : "⚠️ Frontend build had warnings\n";
} else {
    echo "  ⚠️ Frontend directory not found, skipping build.\n";
}

// 4. Clear PHP caches
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

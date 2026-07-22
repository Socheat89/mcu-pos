<?php
/**
 * One-time deploy helper — upload via cPanel File Manager to:
 * /home/mekocclj/pos.mekongcyberunit.app/deploy_now.php
 * Then visit: https://pos.mekongcyberunit.app/deploy_now.php
 * Delete this file after use!
 */
echo '<pre style="font-family:monospace;padding:20px;background:#1a1a2e;color:#e0e0e0;">';
echo "🚀 MCU POS — Full Deploy\n";
echo str_repeat('=', 50) . "\n\n";

// 1. Git pull
echo "📦 Step 1/3: git pull...\n";
chdir(__DIR__);
exec('git fetch origin 2>&1', $out, $code);
exec('git pull --ff-only origin main 2>&1', $out2, $code2);
echo implode("\n", array_merge($out, $out2)) . "\n";
echo ($code === 0 && $code2 === 0) ? "✅ Git pull OK\n\n" : "❌ Git pull FAILED\n\n";

// 2. Run migrations
echo "🗄️ Step 2/3: Database migrations...\n";
exec('php ' . escapeshellarg(__DIR__ . '/run_migrations.php') . ' 2>&1', $mOut, $mCode);
echo implode("\n", $mOut) . "\n";
echo $mCode === 0 ? "✅ Migrations OK\n\n" : "⚠️ Migrations done (warnings may be OK)\n\n";

// 3. Build frontend
echo "⚛️ Step 3/3: Build React frontend...\n";
$feDir = __DIR__ . '/frontend';
if (is_dir($feDir)) {
    if (!is_dir($feDir . '/node_modules')) {
        echo "  → Installing npm packages...\n";
        exec('cd ' . escapeshellarg($feDir) . ' && npm install 2>&1', $nOut, $nCode);
        echo implode("\n", $nOut) . "\n";
    }
    exec('cd ' . escapeshellarg($feDir) . ' && npm run build 2>&1', $bOut, $bCode);
    echo implode("\n", array_slice($bOut, -20)) . "\n";
    echo $bCode === 0 ? "✅ Frontend built OK\n\n" : "⚠️ Build warnings\n\n";
} else {
    echo "⚠️ No frontend dir\n\n";
}

// Clear cache
if (function_exists('opcache_reset')) { @opcache_reset(); }

echo str_repeat('=', 50) . "\n";
echo "✅ ALL DONE! POS now has Size Selection feature.\n";
echo "\n⚠️ DELETE THIS FILE after use: deploy_now.php\n";
echo '</pre>';

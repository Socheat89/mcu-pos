<?php
/**
 * Deploy Script — Pull latest code from GitHub
 * Visit: https://pos.mekongcyberunit.app/deploy.php
 * 
 * Security: This script only runs git pull.
 * Delete after use or protect with a secret key.
 */

// Optional: set a secret key to prevent abuse
$secretKey = $_GET['key'] ?? '';
$requiredKey = 'mcu-deploy-2026'; // Change this to your own secret!

if ($secretKey !== $requiredKey) {
    http_response_code(403);
    die('<h1>403 - Access Denied</h1><p>Add ?key=YOUR_SECRET to the URL.</p>');
}

echo '<pre style="font-family:monospace;padding:20px;">';
echo "🚀 Starting deploy...\n\n";

// Run git pull with hard reset to avoid local conflicts
$output = [];
$returnCode = 0;
chdir(__DIR__);
exec('git fetch origin 2>&1', $output, $returnCode);
exec('git reset --hard origin/main 2>&1', $output2, $returnCode2);
$output = array_merge($output, $output2);
$returnCode = $returnCode !== 0 ? $returnCode : $returnCode2;

foreach ($output as $line) {
    echo htmlspecialchars($line) . "\n";
}

echo "\n" . ($returnCode === 0 ? '✅ Deploy successful!' : '❌ Deploy failed!') . "\n";

// Show last 3 commits
echo "\n📋 Recent commits:\n";
exec('git log --oneline -3 2>&1', $logOutput);
foreach ($logOutput as $line) {
    echo '  ' . htmlspecialchars($line) . "\n";
}

echo '</pre>';

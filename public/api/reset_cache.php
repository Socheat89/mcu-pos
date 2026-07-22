<?php
// public/api/reset_cache.php
// Clears PHP OPcache and stat cache on production server

require_once __DIR__ . '/../../config/env.php';

$requiredKey = mc_env('MC_CACHE_RESET_KEY', '');
if (!mc_bool_env('MC_ENABLE_WEB_CACHE_RESET', false) || $requiredKey === '') {
    http_response_code(404);
    die('<h1>404 - Not Found</h1>');
}

$providedKey = $_GET['key'] ?? '';
if (!hash_equals((string) $requiredKey, (string) $providedKey)) {
    http_response_code(403);
    die('<h1>403 - Access Denied</h1>');
}

if (function_exists('opcache_reset')) {
    @opcache_reset();
    $status = "✅ PHP OPcache reset successfully!";
} else {
    $status = "ℹ️ OPcache is not enabled or function opcache_reset() is disabled.";
}

clearstatcache(true);

header('Content-Type: text/html; charset=utf-8');
echo "<!DOCTYPE html><html><head><title>Cache Cleared</title></head><body style='font-family:sans-serif;padding:30px;background:#0f172a;color:#10b981;'>";
echo "<h1>⚡ Server Cache Reset</h1>";
echo "<p style='font-size:1.2rem;'>$status</p>";
echo "<p style='color:#94a3b8;'>Stat cache cleared. Please refresh your browser (Ctrl + F5 or Cmd + Shift + R).</p>";
echo "<p><a href='../../' style='color:#06b6d4;'>← Return to POS</a></p>";
echo "</body></html>";

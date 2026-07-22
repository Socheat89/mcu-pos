<?php
// public/api/reset_cache.php
// Clears PHP OPcache and stat cache on production server

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

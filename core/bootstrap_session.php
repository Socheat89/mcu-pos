<?php
// core/bootstrap_session.php
// Custom session initialization to prevent shared host /tmp auto-purges

$baseDir = dirname(__DIR__);
$sessionSavePath = $baseDir . '/sessions';

if (!is_dir($sessionSavePath)) {
    @mkdir($sessionSavePath, 0700, true);
    @file_put_contents($sessionSavePath . '/.htaccess', "Deny from all\n");
}

if (is_dir($sessionSavePath) && is_writable($sessionSavePath)) {
    session_save_path($sessionSavePath);
}

// Harden Session Security & Extend Session Lifetime to 30 Days (1 Month)
ini_set('session.cookie_lifetime', 2592000); // 30 days
ini_set('session.gc_maxlifetime', 2592000);   // 30 days
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Lax');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

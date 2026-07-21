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

// Detect HTTPS
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

// Harden Session Security & Extend Session Lifetime to 90 Days
ini_set('session.gc_maxlifetime', 7776000);   // 90 days in seconds
ini_set('session.cookie_lifetime', 7776000);  // 90 days in seconds
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Lax');

if ($isHttps) {
    ini_set('session.cookie_secure', 1); // Only over HTTPS in production
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── Remember Me Auto-Login ───────────────────────────────────────────────
// If user is not logged in but has a remember_me cookie, try to restore session
if (empty($_SESSION['user_id']) && !empty($_COOKIE['remember_me'])) {
    _remember_me_restore($_COOKIE['remember_me']);
}

function _remember_me_restore(string $sealedCookie): void {
    try {
        require_once __DIR__ . '/classes/CookieCrypt.php';
        require_once dirname(__DIR__) . '/core/classes/Database.php';

        // ── Step 1: Decrypt + verify cookie ──────────────────────────────────
        $crypt = CookieCrypt::fromConfig();
        $plainToken = $crypt->open($sealedCookie);

        if ($plainToken === null) {
            // Tampered, invalid, or encrypted with a different key → reject
            setcookie('remember_me', '', ['expires' => time() - 3600, 'path' => '/']);
            return;
        }

        // ── Step 2: Hash token and look up in DB ──────────────────────────────
        $db   = Database::getInstance();
        $hash = hash('sha256', $plainToken);

        $row = $db->fetchOne(
            "SELECT rt.user_id, u.tenant_id, t.subdomain as tenant_subdomain, u.status, r.level as role_level
               FROM remember_tokens rt
               JOIN users u   ON u.id = rt.user_id
               JOIN tenants t ON t.id = u.tenant_id
               JOIN roles r   ON r.id = u.role_id
              WHERE rt.token_hash = ?
                AND rt.expires_at > NOW()
                AND u.status = 'active'
                AND t.status = 'active'
              LIMIT 1",
            [$hash]
        );

        if ($row) {
            // ── Step 3: Restore session ───────────────────────────────────────
            session_regenerate_id(true);
            $_SESSION['user_id']          = $row['user_id'];
            $_SESSION['tenant_id']        = $row['tenant_id'];
            $_SESSION['tenant_subdomain'] = $row['tenant_subdomain'];
            $_SESSION['role_level']       = $row['role_level'];

            // Rolling expiry — extend token lifetime on each visit
            $newExpiry = date('Y-m-d H:i:s', strtotime('+90 days'));
            $db->execute(
                "UPDATE remember_tokens SET expires_at = ? WHERE token_hash = ?",
                [$newExpiry, $hash]
            );

            // Re-issue encrypted cookie (new seal, same plaintext token)
            $newSealed = $crypt->seal($plainToken);
            $isHttps   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
                || (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on')
                || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
            setcookie('remember_me', $newSealed, [
                'expires'  => strtotime('+90 days'),
                'path'     => '/',
                'secure'   => $isHttps,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        } else {
            // DB token expired or revoked → clear cookie
            setcookie('remember_me', '', ['expires' => time() - 3600, 'path' => '/']);
        }
    } catch (Throwable $e) {
        // Silently fail — never break the page load
    }
}
?>

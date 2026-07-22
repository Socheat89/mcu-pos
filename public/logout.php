<?php
// public/logout.php
require_once __DIR__ . '/../core/bootstrap_session.php';
require_once __DIR__ . '/../core/helpers/url.php';

// Get tenant subdomain before destroying session
$tenantSubdomain = $_SESSION['tenant_subdomain'] ?? null;

// ── Clear "Remember Me" token ─────────────────────────────────────────────
if (!empty($_COOKIE['remember_me'])) {
    try {
        require_once __DIR__ . '/../core/classes/Database.php';
        require_once __DIR__ . '/../core/classes/CookieCrypt.php';
        $db   = Database::getInstance();
        $plainToken = CookieCrypt::fromConfig()->open($_COOKIE['remember_me']);
        if ($plainToken !== null) {
            $hash = hash('sha256', $plainToken);
            $db->execute("DELETE FROM remember_tokens WHERE token_hash = ?", [$hash]);
        }
    } catch (Throwable $e) {
        error_log("Logout remember_me cleanup error: " . $e->getMessage());
    }
    // Expire the cookie
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
        || (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on')
        || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
    setcookie('remember_me', '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

session_destroy();

// Always redirect to public login page
$redirectUrl = mc_url('public/login.php?success=' . urlencode('Logged out successfully'));
if ($tenantSubdomain) {
    $redirectUrl .= '&tenant=' . urlencode($tenantSubdomain);
}

header('Location: ' . $redirectUrl);
exit;

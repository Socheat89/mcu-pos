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
        $db   = Database::getInstance();
        $hash = hash('sha256', $_COOKIE['remember_me']);
        $db->execute("DELETE FROM remember_tokens WHERE token_hash = ?", [$hash]);
    } catch (Exception $e) {
        error_log("Logout remember_me cleanup error: " . $e->getMessage());
    }
    // Expire the cookie
    setcookie('remember_me', '', [
        'expires'  => time() - 3600,
        'path'     => '/',
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

<?php
// public/logout.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/helpers/url.php';

// Get tenant subdomain before destroying session
$tenantSubdomain = $_SESSION['tenant_subdomain'] ?? null;

session_destroy();

// Always redirect to public login page
// If tenant subdomain is available, pass it as parameter for context
$redirectUrl = mc_url('public/login.php?success=' . urlencode('Logged out successfully'));
if ($tenantSubdomain) {
    $redirectUrl .= '&tenant=' . urlencode($tenantSubdomain);
}

header('Location: ' . $redirectUrl);
exit;

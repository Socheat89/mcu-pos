<?php
// public/forgot_password_process.php
session_start();
require_once __DIR__ . '/../core/classes/PasswordReset.php';
require_once __DIR__ . '/../core/helpers/url.php';

$urlPrefix = mc_base_path();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: $urlPrefix/public/forgot_password.php");
    exit;
}

$identity = trim($_POST['identity'] ?? '');
if ($identity === '') {
    header("Location: $urlPrefix/public/forgot_password.php?error=" . urlencode('Please enter your username or email.'));
    exit;
}

try {
    $result = PasswordReset::requestReset($identity);

    $_SESSION['password_reset_success'] = 'If an active account matches, a password reset link has been prepared.';
    if ($result && PasswordReset::shouldExposeResetLink()) {
        $_SESSION['password_reset_debug_link'] = $result['reset_url'];
    }

    header("Location: $urlPrefix/public/forgot_password.php?sent=1");
    exit;
} catch (Exception $e) {
    error_log('Forgot password error: ' . $e->getMessage());
    header("Location: $urlPrefix/public/forgot_password.php?error=" . urlencode('Unable to prepare a reset link. Please try again.'));
    exit;
}
?>

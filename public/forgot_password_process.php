<?php
// public/forgot_password_process.php
session_start();
require_once __DIR__ . '/../core/classes/PasswordReset.php';
require_once __DIR__ . '/../core/helpers/url.php';

$urlPrefix = mc_base_path();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: $urlPrefix/forgot_password.php");
    exit;
}

$identity = trim($_POST['identity'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if ($identity === '') {
    header("Location: $urlPrefix/forgot_password.php?error=" . urlencode('Please enter your username or email.'));
    exit;
}

if (strlen($password) < 8) {
    header("Location: $urlPrefix/forgot_password.php?error=" . urlencode('Password must be at least 8 characters.'));
    exit;
}

if ($password !== $confirmPassword) {
    header("Location: $urlPrefix/forgot_password.php?error=" . urlencode('Passwords do not match.'));
    exit;
}

try {
    if (PasswordReset::resetPasswordDirectly($identity, $password)) {
        header("Location: $urlPrefix/login.php?success=" . urlencode('Password reset successfully. Please sign in with your new password.'));
        exit;
    }

    header("Location: $urlPrefix/forgot_password.php?error=" . urlencode('Account not found or inactive.'));
    exit;
} catch (Exception $e) {
    error_log('Direct password reset error: ' . $e->getMessage());
    header("Location: $urlPrefix/forgot_password.php?error=" . urlencode('Unable to reset password. Please try again.'));
    exit;
}
?>

<?php
// public/reset_password_process.php
session_start();
require_once __DIR__ . '/../core/classes/PasswordReset.php';
require_once __DIR__ . '/../core/helpers/url.php';

$urlPrefix = mc_base_path();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: $urlPrefix/public/forgot_password.php");
    exit;
}

$token = $_POST['token'] ?? '';
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';
$resetUrl = "$urlPrefix/public/reset_password.php?token=" . urlencode($token);

if (strlen($password) < 8) {
    header("Location: $resetUrl&error=" . urlencode('Password must be at least 8 characters.'));
    exit;
}

if ($password !== $confirmPassword) {
    header("Location: $resetUrl&error=" . urlencode('Passwords do not match.'));
    exit;
}

try {
    if (PasswordReset::resetPassword($token, $password)) {
        header("Location: $urlPrefix/public/login.php?success=" . urlencode('Password reset successfully. Please sign in with your new password.'));
        exit;
    }

    header("Location: $urlPrefix/public/forgot_password.php?error=" . urlencode('Reset link is invalid or expired. Please request a new link.'));
    exit;
} catch (Exception $e) {
    error_log('Reset password error: ' . $e->getMessage());
    header("Location: $urlPrefix/public/forgot_password.php?error=" . urlencode('Unable to reset password. Please try again.'));
    exit;
}
?>

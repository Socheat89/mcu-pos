<?php
// public/change_expired_password.php
session_start();
require_once __DIR__ . '/../core/classes/Database.php';
require_once __DIR__ . '/../core/helpers/url.php';

$urlPrefix = mc_base_path();

// 1. Force authentication check
if (!isset($_SESSION['user_id'])) {
    header("Location: $urlPrefix/public/login.php");
    exit;
}

$error = '';
$success = '';

$db = Database::getInstance();
$userId = $_SESSION['user_id'];

// Get user profile details
$user = $db->fetchOne("SELECT username, email, password_hash FROM users WHERE id = ?", [$userId]);
if (!$user) {
    session_destroy();
    header("Location: $urlPrefix/public/login.php");
    exit;
}

// 2. Process password change POST action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPass = $_POST['current_password'] ?? '';
    $newPass = $_POST['new_password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';

    if (empty($currentPass) || empty($newPass) || empty($confirmPass)) {
        $error = 'All fields are required.';
    } elseif (strlen($newPass) < 6) {
        $error = 'New password must be at least 6 characters.';
    } elseif ($newPass !== $confirmPass) {
        $error = 'New passwords do not match.';
    } elseif (!password_verify($currentPass, $user['password_hash'])) {
        $error = 'Current password is incorrect.';
    } elseif ($currentPass === $newPass) {
        $error = 'New password cannot be the same as your current password.';
    } else {
        try {
            $newHash = password_hash($newPass, PASSWORD_DEFAULT);
            $db->update(
                'users', 
                [
                    'password_hash' => $newHash,
                    'password_changed_at' => date('Y-m-d H:i:s')
                ], 
                'id = ?', 
                [$userId]
            );

            // Successfully updated password, clear session and force login again
            session_destroy();
            header("Location: $urlPrefix/public/login.php?success=" . urlencode('Your password has been changed successfully. Please log in with your new password.'));
            exit;
        } catch (Exception $e) {
            $error = 'Failed to update password: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>Change Expired Password - Mekong CyberUnit</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@400;500;600;700&family=Sora:wght@300;400;500;600;700&family=Battambang:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo mc_asset('css/landing.css'); ?>?v=2.3">
    <link rel="icon" href="<?php echo mc_asset('images/logo.png'); ?>" type="image/png">
    <link rel="shortcut icon" href="<?php echo mc_asset('images/logo.png'); ?>" type="image/png">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body class="auth-page">
    <main class="auth-shell">
        <div class="auth-card auth-card--compact">
            <div class="auth-header">
                <div class="auth-logo">
                    <div class="logo-icon" style="background: rgba(239, 68, 68, 0.1); color: #f87171;">
                        <i class="ph-bold ph-keyholder"></i>
                    </div>
                    <span>Mekong CyberUnit</span>
                </div>
                <h2>Password Expired</h2>
                <p>For your security, passwords must be changed every 3 months (90 days). Please update your password below to continue.</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="ph-bold ph-warning-circle" style="vertical-align: text-bottom; margin-right: 6px;"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required placeholder="••••••••" autocomplete="current-password">
                </div>

                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required minlength="6" placeholder="••••••••" autocomplete="new-password">
                    <p class="form-helper">Password must be at least 6 characters long.</p>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6" placeholder="••••••••" autocomplete="new-password">
                </div>

                <button type="submit" class="btn btn-primary full-width">
                    Update Password & Sign In
                </button>
            </form>

            <div class="auth-footer">
                <a href="<?php echo mc_url('public/logout.php'); ?>" class="link-strong"><i class="ph-bold ph-sign-out" style="vertical-align: text-bottom;"></i> Sign out</a>
            </div>
        </div>
    </main>
</body>
</html>

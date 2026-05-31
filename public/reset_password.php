<?php
session_start();
require_once __DIR__ . '/../core/classes/PasswordReset.php';
require_once __DIR__ . '/../core/helpers/url.php';

$token = $_GET['token'] ?? '';
$error = $_GET['error'] ?? '';
$reset = PasswordReset::findValidToken($token);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>Reset Password - Mekong CyberUnit</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@400;500;600;700&family=Sora:wght@300;400;500;600;700&family=Battambang:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/landing.css">
    <link rel="icon" href="images/logo.png" type="image/png">
    <link rel="shortcut icon" href="images/logo.png" type="image/png">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body class="auth-page">
    <div class="page-loader" id="pageLoader">
        <div class="loader-card">
            <div class="loader-logo">
                <i class="ph-bold ph-cube"></i>
            </div>
            <p class="loader-title">Mekong CyberUnit</p>
            <p class="loader-caption">Opening secure reset form</p>
            <div class="loader-spinner"></div>
            <div class="loader-progress"><span></span></div>
        </div>
    </div>

    <main class="auth-shell">
        <div class="auth-card auth-card--compact">
            <div class="auth-header">
                <a href="index.php" class="auth-logo">
                    <div class="logo-icon">
                        <i class="ph-bold ph-cube"></i>
                    </div>
                    <span>Mekong CyberUnit</span>
                </a>
                <h2>Create new password</h2>
                <p>Choose a new password for your account.</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="ph-bold ph-warning-circle" style="vertical-align: text-bottom;"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!$reset): ?>
                <div class="alert alert-error">
                    <i class="ph-bold ph-warning-circle" style="vertical-align: text-bottom;"></i>
                    This reset link is invalid or expired.
                </div>
                <a href="forgot_password.php" class="btn btn-primary full-width" style="text-align: center;">Request New Link</a>
            <?php else: ?>
                <form method="POST" action="reset_password_process.php">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                    <div class="form-group">
                        <label>Account</label>
                        <input type="text" value="<?php echo htmlspecialchars($reset['username'] . ' (' . $reset['email'] . ')'); ?>" disabled>
                    </div>

                    <div class="form-group">
                        <label for="password">New password</label>
                        <input type="password" id="password" name="password" required minlength="8" placeholder="At least 8 characters" autocomplete="new-password">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="8" placeholder="Repeat new password" autocomplete="new-password">
                    </div>

                    <button type="submit" class="btn btn-primary full-width">Reset Password</button>
                </form>
            <?php endif; ?>

            <div class="auth-footer">
                <a href="login.php" class="link-strong">Back to sign in</a>
            </div>
        </div>
    </main>
    <script src="js/loader.js"></script>
</body>
</html>

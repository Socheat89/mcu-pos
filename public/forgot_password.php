<?php
session_start();
require_once __DIR__ . '/../core/helpers/url.php';

$success = $_SESSION['password_reset_success'] ?? '';
$debugLink = $_SESSION['password_reset_debug_link'] ?? '';
$error = $_GET['error'] ?? '';
unset($_SESSION['password_reset_success'], $_SESSION['password_reset_debug_link']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>Forgot Password - Mekong CyberUnit</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@400;500;600;700&family=Sora:wght@300;400;500;600;700&family=Battambang:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo mc_asset('css/landing.css'); ?>?v=2.3">
    <link rel="icon" href="<?php echo mc_asset('images/logo.png'); ?>" type="image/png">
    <link rel="shortcut icon" href="<?php echo mc_asset('images/logo.png'); ?>" type="image/png">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body class="auth-page">
    <div class="page-loader" id="pageLoader">
        <div class="loader-card">
            <div class="loader-logo">
                <i class="ph-bold ph-cube"></i>
            </div>
            <p class="loader-title">Mekong CyberUnit</p>
            <p class="loader-caption">Preparing password recovery</p>
            <div class="loader-spinner"></div>
            <div class="loader-progress"><span></span></div>
        </div>
    </div>

    <main class="auth-shell">
        <div class="auth-card auth-card--compact">
            <div class="auth-header">
                <a href="<?php echo mc_url('index.php'); ?>" class="auth-logo">
                    <div class="logo-icon">
                        <i class="ph-bold ph-cube"></i>
                    </div>
                    <span>Mekong CyberUnit</span>
                </a>
                <h2>Reset password</h2>
                <p>Enter your username or email to receive a secure reset link.</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="ph-bold ph-warning-circle" style="vertical-align: text-bottom;"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="ph-bold ph-check-circle" style="vertical-align: text-bottom;"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <?php if ($debugLink): ?>
                <div class="alert alert-success">
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <strong>Local reset link</strong>
                        <span style="font-size: 0.82rem;">Email is not required in local/dev mode. Use this link now:</span>
                        <a href="<?php echo htmlspecialchars($debugLink); ?>" class="link-strong" style="word-break: break-all;">
                            <?php echo htmlspecialchars($debugLink); ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST" action="forgot_password_process.php">
                <div class="form-group">
                    <label for="identity">Username or email</label>
                    <input type="text" id="identity" name="identity" required placeholder="username or email@example.com" autocomplete="username">
                    <p class="form-helper">If the account exists, the reset link expires in 1 hour.</p>
                </div>

                <button type="submit" class="btn btn-primary full-width">
                    Send Reset Link
                </button>
            </form>

            <div class="auth-footer">
                Remembered it? <a href="<?php echo mc_url('login.php'); ?>" class="link-strong">Back to sign in</a>
            </div>
        </div>
    </main>
    <script src="<?php echo mc_asset('js/loader.js'); ?>"></script>
</body>
</html>

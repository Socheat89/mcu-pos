<?php 
require_once __DIR__ . '/../core/bootstrap_session.php';
require_once __DIR__ . '/../core/classes/Database.php';
require_once __DIR__ . '/../core/classes/Auth.php';
require_once __DIR__ . '/../core/helpers/url.php';

if (Auth::check()) {
    $urlPrefix = mc_base_path();
    $subdomain = $_SESSION['tenant_subdomain'] ?? '';
    if (Auth::isSuperAdmin()) {
        header("Location: $urlPrefix/admin/index.php");
        exit;
    } elseif (!empty($subdomain)) {
        header("Location: $urlPrefix/$subdomain/pos/pos");
        exit;
    }
}

// ── Server-side lockout check (by IP) — cannot be bypassed by URL editing ──
define('_LOGIN_MAX_ATTEMPTS', 5);
define('_LOGIN_LOCKOUT_MINUTES', 30);

$serverLocked       = false;
$serverRemainingSec = 0;

try {
    $db       = Database::getInstance();
    $clientIp = trim(explode(',', ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0')))[0];

    // Ensure table exists
    $db->getConnection()->exec("CREATE TABLE IF NOT EXISTS `login_attempts` (
        `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `username`     VARCHAR(255) NOT NULL,
        `ip_address`   VARCHAR(45)  NOT NULL DEFAULT '',
        `attempted_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_user_ip`      (`username`, `ip_address`),
        KEY `idx_ip`           (`ip_address`),
        KEY `idx_attempted_at` (`attempted_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Check IP-based lockout
    $rowIp = $db->fetchOne(
        "SELECT COUNT(*) as cnt, MAX(attempted_at) as last_attempt
         FROM login_attempts
         WHERE ip_address = ? AND attempted_at >= DATE_SUB(NOW(), INTERVAL ? MINUTE)",
        [$clientIp, _LOGIN_LOCKOUT_MINUTES]
    );

    if ((int)($rowIp['cnt'] ?? 0) >= _LOGIN_MAX_ATTEMPTS) {
        $serverLocked       = true;
        $lastAttempt        = strtotime($rowIp['last_attempt']);
        $serverRemainingSec = max(0, ($lastAttempt + (_LOGIN_LOCKOUT_MINUTES * 60)) - time());
    }
} catch (Throwable $e) {
    error_log("Login page lockout check error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - Mekong CyberUnit</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Battambang:wght@400;700&display=swap" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" href="<?php echo mc_asset('images/my-logo.jpg'); ?>" type="image/jpeg">
    <link rel="shortcut icon" href="<?php echo mc_asset('images/my-logo.jpg'); ?>" type="image/jpeg">

    <!-- Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <!-- Google reCAPTCHA v3 -->
    <script src="https://www.google.com/recaptcha/api.js?render=6LdjN3gtAAAAAKK5FjRu40mupbu-5sZnO4byqgUA"></script>

    <style>
        :root {
            --primary: #0284c7;
            --primary-hover: #0369a1;
            --bg: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body, input, button {
            font-family: 'Sora', 'Battambang', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body.auth-page {
            background-color: var(--bg);
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
            color: var(--text-main);
        }

        .auth-shell {
            width: 100%;
            max-width: 410px;
            margin: 0 auto;
        }

        .auth-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            border-radius: 20px;
            padding: 2.25rem 2rem;
            width: 100%;
        }

        /* Header */
        .brand-header {
            text-align: center;
            margin-bottom: 1.75rem;
        }

        .auth-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
            margin-bottom: 1.25rem;
        }

        .logo-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #cbd5e1;
            flex-shrink: 0;
            background: #ffffff;
        }

        .logo-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .brand-title {
            font-weight: 800;
            font-size: 1.2rem;
            color: #0f172a;
            letter-spacing: -0.02em;
        }

        .welcome-title {
            font-size: 1.4rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
            margin-bottom: 0.25rem;
        }

        .welcome-subtitle {
            font-size: 0.875rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        /* Form Controls */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.4rem;
        }

        .form-label {
            font-size: 0.78rem;
            font-weight: 700;
            color: #334155;
        }

        .forgot-link {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--primary);
            text-decoration: none;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .input-wrapper {
            position: relative;
            width: 100%;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            font-size: 18px;
            color: #94a3b8;
            pointer-events: none;
            z-index: 2;
        }

        .form-input {
            width: 100%;
            height: 46px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 0 40px 0 42px;
            color: #0f172a;
            font-size: 0.92rem;
            font-weight: 600;
            outline: none;
            transition: all 0.15s ease;
        }

        .form-input::placeholder {
            color: #94a3b8;
            font-weight: 400;
        }

        .form-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
        }

        .password-toggle-btn {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            color: #94a3b8;
            font-size: 18px;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
        }

        .password-toggle-btn:hover {
            color: var(--primary);
        }

        /* Submit Button */
        .btn-submit {
            width: 100%;
            height: 48px;
            background: var(--primary);
            border: none;
            border-radius: 12px;
            color: #ffffff;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background 0.15s ease;
            margin-top: 1.5rem;
        }

        .btn-submit:hover {
            background: var(--primary-hover);
        }

        /* Alert Boxes */
        .alert {
            padding: 10px 14px;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
        }

        .alert-warning {
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
        }

        .alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #16a34a;
        }

        .lockout-countdown {
            font-size: 1.1rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            display: inline-block;
            margin-left: 4px;
            color: #b91c1c;
        }

        .btn-submit:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        .form-input:disabled {
            background: #f1f5f9;
            color: #94a3b8;
            cursor: not-allowed;
            border-color: #e2e8f0;
        }

        .lockout-overlay {
            position: relative;
        }
        .lockout-overlay::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 12px;
            background: rgba(241,245,249,0.5);
            pointer-events: none;
        }

        /* Footer */
        .auth-footer {
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid #f1f5f9;
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .auth-footer a {
            color: var(--primary);
            font-weight: 700;
            text-decoration: none;
            margin-left: 4px;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        .recaptcha-wrapper {
            display: flex;
            justify-content: center;
            margin: 1.25rem 0 0.5rem;
            transform-origin: center;
        }
        @media (max-width: 380px) {
            .recaptcha-wrapper { transform: scale(0.88); }
        }

        @media (max-width: 480px) {
            body.auth-page {
                padding: 1rem;
            }
            .auth-card {
                padding: 1.75rem 1.25rem;
                border-radius: 16px;
            }
        }
    </style>
</head>
<body class="auth-page">

    <main class="auth-shell">
        <div class="auth-card">
            <!-- Header & Logo -->
            <div class="brand-header">
                <a href="<?php echo mc_url('index.php'); ?>" class="auth-logo">
                    <div class="logo-icon">
                        <img src="<?php echo mc_asset('images/my-logo.jpg'); ?>" alt="MCU Logo">
                    </div>
                    <div class="brand-title">Mekong CyberUnit</div>
                </a>
                <h1 class="welcome-title">Welcome Back</h1>
                <p class="welcome-subtitle">Sign in to your account</p>
            </div>

            <!-- Error / Lockout Notification -->
            <?php
                // Determine lockout: prefer server DB check, fallback to URL param
                $isLocked     = $serverLocked || !empty($_GET['locked']);
                $remainingSec = $serverLocked ? $serverRemainingSec
                                             : (isset($_GET['remaining']) ? (int)$_GET['remaining'] : 0);
            ?>

            <?php if ($isLocked && $remainingSec > 0): ?>
                <div class="alert alert-warning" id="loginAlert">
                    <i class="ph-bold ph-lock" style="font-size: 18px; flex-shrink: 0; margin-top: 1px;"></i>
                    <div>🔒 គណនីត្រូវបានចាក់សោបណ្ដោះអាសន្ន។ <br>សូមព្យាយាមម្ដងទៀតក្នុងរយៈពេល <span class="lockout-countdown" id="lockoutTimer"></span></div>
                </div>
                <script>
                    (function() {
                        var remaining = <?php echo $remainingSec; ?>;
                        var timerEl   = document.getElementById('lockoutTimer');
                        var submitBtn = document.querySelector('.btn-submit');
                        var usernameInput = document.getElementById('username');
                        var passwordInput = document.getElementById('password');
                        var loginForm     = document.querySelector('form[action="login_process.php"]');

                        function lockForm() {
                            if (submitBtn)    { submitBtn.disabled = true; }
                            if (usernameInput){ usernameInput.disabled = true; }
                            if (passwordInput){ passwordInput.disabled = true; }
                        }
                        function unlockForm() {
                            if (submitBtn)    { submitBtn.disabled = false; }
                            if (usernameInput){ usernameInput.disabled = false; usernameInput.focus(); }
                            if (passwordInput){ passwordInput.disabled = false; }
                        }
                        if (loginForm) {
                            loginForm.addEventListener('submit', function(e) {
                                if (remaining > 0) { e.preventDefault(); }
                            });
                        }
                        lockForm();
                        function formatTime(s) {
                            var m = Math.floor(s / 60);
                            var sec = s % 60;
                            return m + 'm ' + (sec < 10 ? '0' : '') + sec + 's';
                        }
                        if (timerEl) timerEl.textContent = formatTime(remaining);
                        var interval = setInterval(function() {
                            remaining--;
                            if (remaining <= 0) {
                                clearInterval(interval);
                                if (timerEl) timerEl.textContent = '0m 00s';
                                unlockForm();
                                window.location.href = window.location.pathname;
                            } else {
                                if (timerEl) timerEl.textContent = formatTime(remaining);
                            }
                        }, 1000);
                    })();
                </script>
            <?php elseif (isset($_GET['error']) && !$isLocked): ?>
                <div class="alert alert-error" id="loginAlert">
                    <i class="ph-bold ph-warning-circle" style="font-size: 18px; flex-shrink: 0; margin-top: 1px;"></i>
                    <span><?php echo htmlspecialchars($_GET['error']); ?></span>
                </div>
            <?php endif; ?>

            <!-- Success Notification -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="ph-bold ph-check-circle" style="font-size: 18px; flex-shrink: 0;"></i>
                    <span><?php echo htmlspecialchars($_GET['success']); ?></span>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" action="login_process.php">
                <!-- Username Input -->
                <div class="form-group">
                    <div class="form-label-row">
                        <label for="username" class="form-label">Username</label>
                    </div>
                    <div class="input-wrapper" id="usernameWrapper">
                        <i class="ph-bold ph-user input-icon"></i>
                        <input type="text" id="username" name="username" class="form-input" required 
                               placeholder="Enter your username" autocomplete="username" autofocus
                               <?php if ($isLocked): ?>disabled<?php endif; ?>>
                    </div>
                </div>

                <!-- Password Input -->
                <div class="form-group">
                    <div class="form-label-row">
                        <label for="password" class="form-label">Password</label>
                        <a href="<?php echo mc_url('forgot_password.php'); ?>" class="forgot-link">Forgot password?</a>
                    </div>
                    <div class="input-wrapper" id="passwordWrapper">
                        <i class="ph-bold ph-lock-key input-icon"></i>
                        <input type="password" id="password" name="password" class="form-input" required 
                               placeholder="Enter your password" autocomplete="current-password"
                               <?php if ($isLocked): ?>disabled<?php endif; ?>>
                        <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility()" aria-label="Toggle password visibility"
                                <?php if ($isLocked): ?>disabled<?php endif; ?>>
                            <i class="ph-bold ph-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-submit" id="loginSubmitBtn" <?php if ($isLocked): ?>disabled<?php endif; ?>>
                    <i class="ph-bold ph-sign-in" style="font-size: 20px;"></i>
                    <span>Sign In</span>
                </button>
                <input type="hidden" name="g-recaptcha-response" id="loginRecaptchaToken">
            </form>

            <!-- Footer -->
            <div class="auth-footer">
                Don't have an account? <a href="<?php echo mc_url('register.php'); ?>">Sign up</a>
            </div>
        </div>
    </main>

    <script>
        function togglePasswordVisibility() {
            var input = document.getElementById("password");
            var icon = document.getElementById("toggleIcon");
            if (input.type === "password") {
                input.type = "text";
                icon.className = "ph-bold ph-eye-slash";
            } else {
                input.type = "password";
                icon.className = "ph-bold ph-eye";
            }
        }

        // reCAPTCHA v3 — execute on form submit
        var loginForm = document.querySelector('form[action="login_process.php"]');
        if (loginForm) {
            loginForm.addEventListener('submit', function(e) {
                var tokenInput = document.getElementById('loginRecaptchaToken');
                if (tokenInput && tokenInput.value) return; // already has token
                e.preventDefault();
                var btn = document.getElementById('loginSubmitBtn');
                if (btn) { btn.disabled = true; btn.querySelector('span').textContent = 'Verifying...'; }
                grecaptcha.ready(function() {
                    grecaptcha.execute('6LdjN3gtAAAAAKK5FjRu40mupbu-5sZnO4byqgUA', {action: 'login'})
                        .then(function(token) {
                            if (tokenInput) tokenInput.value = token;
                            loginForm.submit();
                        });
                });
            });
        }
    </script>
</body>
</html>

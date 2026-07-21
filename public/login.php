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
            align-items: center;
            gap: 8px;
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
        }

        .alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #16a34a;
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

            <!-- Error Notification -->
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error">
                    <i class="ph-bold ph-warning-circle" style="font-size: 18px; flex-shrink: 0;"></i>
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
                    <div class="input-wrapper">
                        <i class="ph-bold ph-user input-icon"></i>
                        <input type="text" id="username" name="username" class="form-input" required 
                               placeholder="Enter your username" autocomplete="username" autofocus>
                    </div>
                </div>

                <!-- Password Input -->
                <div class="form-group">
                    <div class="form-label-row">
                        <label for="password" class="form-label">Password</label>
                        <a href="<?php echo mc_url('forgot_password.php'); ?>" class="forgot-link">Forgot password?</a>
                    </div>
                    <div class="input-wrapper">
                        <i class="ph-bold ph-lock-key input-icon"></i>
                        <input type="password" id="password" name="password" class="form-input" required 
                               placeholder="Enter your password" autocomplete="current-password">
                        <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility()" aria-label="Toggle password visibility">
                            <i class="ph-bold ph-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-submit">
                    <i class="ph-bold ph-sign-in" style="font-size: 20px;"></i>
                    <span>Sign In</span>
                </button>
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
    </script>
</body>
</html>

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
    <title>Login - Mekong CyberUnit POS</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Sora:wght@400;600;700;800&family=Battambang:wght@400;700;900&display=swap" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" href="<?php echo mc_asset('images/my-logo.jpg'); ?>" type="image/jpeg">
    <link rel="shortcut icon" href="<?php echo mc_asset('images/my-logo.jpg'); ?>" type="image/jpeg">

    <!-- Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <style>
        :root {
            --bg-dark: #090d16;
            --card-bg: rgba(17, 24, 39, 0.75);
            --brand-cyan: #06b6d4;
            --brand-teal: #0d9488;
            --brand-gradient: linear-gradient(135deg, #06b6d4 0%, #0891b2 50%, #0f766e 100%);
            --border-glow: rgba(6, 182, 212, 0.25);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body, input, button {
            font-family: 'Sora', 'Battambang', sans-serif;
        }

        body.auth-page {
            background-color: var(--bg-dark);
            background-image: 
                radial-gradient(circle at 15% 15%, rgba(6, 182, 212, 0.15) 0%, transparent 40%),
                radial-gradient(circle at 85% 85%, rgba(13, 148, 136, 0.12) 0%, transparent 45%),
                radial-gradient(circle at 50% 50%, rgba(15, 23, 42, 0.9) 0%, #090d16 100%);
            background-attachment: fixed;
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
            color: var(--text-main);
            overflow-x: hidden;
            position: relative;
        }

        /* Ambient Glow Orbs */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(90px);
            pointer-events: none;
            z-index: 0;
            opacity: 0.5;
        }
        .orb-1 {
            width: 350px;
            height: 350px;
            background: #06b6d4;
            top: -100px;
            left: -100px;
        }
        .orb-2 {
            width: 400px;
            height: 400px;
            background: #0d9488;
            bottom: -150px;
            right: -150px;
        }

        /* Container Shell */
        .auth-shell {
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 10;
            margin: 0 auto;
        }

        /* Glassmorphism Card */
        .auth-card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border-glow);
            box-shadow: 
                0 25px 50px -12px rgba(0, 0, 0, 0.6),
                0 0 30px rgba(6, 182, 212, 0.1),
                inset 0 1px 1px rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 2.25rem 2rem;
            width: 100%;
        }

        /* Brand Logo Block */
        .brand-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .auth-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            text-decoration: none;
            margin-bottom: 1rem;
        }

        .logo-icon {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(6, 182, 212, 0.4);
            border: 1.5px solid rgba(6, 182, 212, 0.4);
            flex-shrink: 0;
            background: #0f172a;
        }

        .logo-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .brand-title {
            font-family: 'Space Grotesk', 'Sora', sans-serif;
            font-weight: 700;
            font-size: 1.25rem;
            color: #ffffff;
            letter-spacing: -0.02em;
        }

        .brand-title span {
            color: var(--brand-cyan);
        }

        .welcome-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.03em;
            margin-bottom: 0.35rem;
        }

        .welcome-subtitle {
            font-size: 0.875rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        /* Form Inputs */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .form-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #cbd5e1;
        }

        .forgot-link {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--brand-cyan);
            text-decoration: none;
            transition: color 0.2s;
        }

        .forgot-link:hover {
            color: #38bdf8;
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
            left: 16px;
            font-size: 20px;
            color: #64748b;
            transition: color 0.2s;
            pointer-events: none;
            z-index: 2;
        }

        .form-input {
            width: 100%;
            height: 50px;
            background: rgba(15, 23, 42, 0.7);
            border: 1.5px solid rgba(51, 65, 85, 0.7);
            border-radius: 14px;
            padding: 0 44px 0 48px;
            color: #ffffff;
            font-size: 0.95rem;
            font-weight: 600;
            outline: none;
            transition: all 0.2s ease;
        }

        .form-input::placeholder {
            color: #475569;
            font-weight: 400;
        }

        .form-input:focus {
            border-color: var(--brand-cyan);
            background: rgba(15, 23, 42, 0.95);
            box-shadow: 0 0 0 4px rgba(6, 182, 212, 0.18);
        }

        .form-input:focus ~ .input-icon {
            color: var(--brand-cyan);
        }

        .password-toggle-btn {
            position: absolute;
            right: 14px;
            background: none;
            border: none;
            color: #64748b;
            font-size: 20px;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
            z-index: 2;
        }

        .password-toggle-btn:hover {
            color: var(--brand-cyan);
        }

        /* Primary Submit Button */
        .btn-submit {
            width: 100%;
            height: 52px;
            background: var(--brand-gradient);
            border: none;
            border-radius: 14px;
            color: #ffffff;
            font-size: 1rem;
            font-weight: 800;
            letter-spacing: 0.02em;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 10px 25px -5px rgba(6, 182, 212, 0.4);
            transition: all 0.25s ease;
            margin-top: 1.75rem;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px -5px rgba(6, 182, 212, 0.55);
            filter: brightness(1.08);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* Alert Boxes */
        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 10px;
            line-height: 1.4;
        }

        .alert-error {
            background: rgba(225, 29, 72, 0.15);
            border: 1px solid rgba(225, 29, 72, 0.3);
            color: #fecdd3;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #a7f3d0;
        }

        /* Footer */
        .auth-footer {
            margin-top: 1.75rem;
            padding-top: 1.25rem;
            border-top: 1px solid rgba(51, 65, 85, 0.5);
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .auth-footer a {
            color: var(--brand-cyan);
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
                border-radius: 20px;
            }
            .welcome-title {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body class="auth-page">

    <!-- Ambient Glow Orbs -->
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <main class="auth-shell">
        <div class="auth-card">
            <!-- Header & Logo -->
            <div class="brand-header">
                <a href="<?php echo mc_url('index.php'); ?>" class="auth-logo">
                    <div class="logo-icon">
                        <img src="<?php echo mc_asset('images/my-logo.jpg'); ?>" alt="MCU Logo">
                    </div>
                    <div class="brand-title">Mekong <span>CyberUnit</span></div>
                </a>
                <h1 class="welcome-title">Welcome Back</h1>
                <p class="welcome-subtitle">Sign in to your Mekong POS account</p>
            </div>

            <!-- Error Notification -->
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error">
                    <i class="ph-bold ph-warning-circle" style="font-size: 20px; flex-shrink: 0; color: #f43f5e;"></i>
                    <span><?php echo htmlspecialchars($_GET['error']); ?></span>
                </div>
            <?php endif; ?>

            <!-- Success Notification -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="ph-bold ph-check-circle" style="font-size: 20px; flex-shrink: 0; color: #10b981;"></i>
                    <span><?php echo htmlspecialchars($_GET['success']); ?></span>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" action="login_process.php">
                <!-- Username Input -->
                <div class="form-group">
                    <div class="form-label-row">
                        <label for="username" class="form-label">Username / ឈ្មោះប្រកាស</label>
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
                        <label for="password" class="form-label">Password / ពាក្យសម្ងាត់</label>
                        <a href="<?php echo mc_url('forgot_password.php'); ?>" class="forgot-link">Forgot?</a>
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
                    <i class="ph-bold ph-sign-in" style="font-size: 22px;"></i>
                    <span>Sign In / ចូលប្រព័ន្ធ</span>
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

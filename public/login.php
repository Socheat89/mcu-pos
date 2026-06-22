<?php require_once __DIR__ . '/../core/helpers/url.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Mekong CyberUnit</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@400;500;600;700&family=Sora:wght@300;400;500;600;700&family=Battambang:wght@300;400;700&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link rel="stylesheet" href="<?php echo mc_asset('css/landing.css'); ?>?v=2.3">
    
    <!-- Favicon -->
    <link rel="icon" href="<?php echo mc_asset('images/logo.png'); ?>" type="image/png">
    <link rel="shortcut icon" href="<?php echo mc_asset('images/logo.png'); ?>" type="image/png">
    
    <!-- Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <style>
        body, h1, h2, h3, h4, h5, h6, p, span, a, label, input, button {
            font-family: 'Sora', 'Battambang', sans-serif !important;
        }

        /* ── Floating Blobs ── */
        .blob-container {
            position: fixed;
            inset: 0;
            overflow: hidden;
            z-index: -2;
            pointer-events: none;
        }
        .blob {
            position: absolute;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.16;
            animation: float 22s infinite alternate ease-in-out;
        }
        .blob-1 {
            background: var(--brand);
            left: -150px;
            top: -150px;
            animation-delay: 0s;
        }
        .blob-2 {
            background: #f4a261;
            right: -150px;
            bottom: -150px;
            animation-duration: 26s;
            animation-delay: -6s;
        }
        .blob-3 {
            background: #2563eb;
            left: 45%;
            top: 40%;
            width: 600px;
            height: 600px;
            animation-duration: 32s;
            animation-delay: -12s;
        }
        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(80px, 60px) scale(1.15); }
            100% { transform: translate(-40px, 90px) scale(0.9); }
        }

        /* ── Premium Glassmorphism Card ── */
        body.auth-page {
            background: linear-gradient(180deg, #fefbf6 0%, #f6eee0 100%) !important;
            position: relative;
        }
        .auth-shell {
            position: relative;
            z-index: 10;
        }
        .auth-card {
            background: rgba(255, 255, 255, 0.45) !important;
            backdrop-filter: blur(24px) !important;
            -webkit-backdrop-filter: blur(24px) !important;
            border: 1.5px solid rgba(255, 255, 255, 0.65) !important;
            box-shadow: 0 28px 75px rgba(15, 118, 110, 0.08), 0 10px 30px rgba(0,0,0,0.02) !important;
            border-radius: 28px !important;
            padding: 3.5rem 3rem !important;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .auth-card:hover {
            box-shadow: 0 35px 85px rgba(15, 118, 110, 0.12), 0 15px 35px rgba(0,0,0,0.03) !important;
        }
        .auth-header h2 {
            font-size: 1.85rem;
            font-weight: 850;
            color: #0f172a;
            letter-spacing: -0.04em;
            margin-top: 15px;
            margin-bottom: 6px;
        }
        .auth-header p {
            color: #64748b;
            font-weight: 500;
            font-size: 0.95rem;
        }
        
        .logo-icon {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--brand), var(--brand-strong), #2563eb);
            box-shadow: 0 10px 24px rgba(15, 118, 110, 0.35);
            transition: transform 0.4s var(--ease-out);
        }
        .auth-logo:hover .logo-icon {
            transform: rotate(15deg) scale(1.08);
        }
        .auth-logo span {
            font-family: 'Unbounded', sans-serif !important;
            font-weight: 800;
            color: #0f172a;
            font-size: 1.12rem;
            letter-spacing: -0.03em;
        }

        /* ── Input Wrapper & Style Refresh ── */
        .form-group {
            margin-bottom: 22px !important;
        }
        .form-group label {
            font-weight: 800 !important;
            font-size: 0.76rem !important;
            text-transform: uppercase !important;
            letter-spacing: 0.8px !important;
            color: #475569 !important;
            margin-bottom: 8px !important;
        }
        .input-wrapper {
            position: relative;
            display: block;
            width: 100%;
        }
        .input-wrapper i.prefix-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 20px;
            color: #94a3b8;
            transition: color 0.25s ease;
            pointer-events: none;
            z-index: 10;
        }
        .input-wrapper input {
            width: 100%;
            height: 54px;
            padding: 10px 48px 10px 50px !important;
            border-radius: 16px !important;
            border: 1.5px solid rgba(15, 118, 110, 0.16) !important;
            background: rgba(255, 255, 255, 0.72) !important;
            color: #0f172a !important;
            font-weight: 600 !important;
            font-size: 0.98rem !important;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
            outline: none;
        }
        .input-wrapper input:focus {
            border-color: var(--brand) !important;
            background: #ffffff !important;
            box-shadow: 0 0 0 4.5px rgba(15, 118, 110, 0.14) !important;
        }
        .input-wrapper input:focus ~ i.prefix-icon {
            color: var(--brand);
        }

        /* Password toggle button */
        .btn-toggle-password {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            font-size: 20px;
            padding: 0;
            display: grid;
            place-items: center;
            transition: color 0.2s ease;
            z-index: 15;
            outline: none;
        }
        .btn-toggle-password:hover {
            color: var(--brand);
        }

        /* ── Submit Button ── */
        .btn-submit {
            height: 54px;
            border-radius: 16px !important;
            background: linear-gradient(135deg, var(--brand), #0d9488, #2563eb) !important;
            background-size: 200% auto !important;
            color: #ffffff !important;
            font-weight: 800 !important;
            font-size: 0.98rem !important;
            letter-spacing: 0.5px;
            box-shadow: 0 10px 24px rgba(15, 118, 110, 0.28) !important;
            border: none !important;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 26px;
        }
        .btn-submit:hover {
            background-position: right center !important;
            transform: translateY(-2px);
            box-shadow: 0 14px 32px rgba(15, 118, 110, 0.38) !important;
        }
        .btn-submit:active {
            transform: translateY(0);
        }

        /* ── Alerts ── */
        .alert {
            padding: 14px 18px !important;
            border-radius: 16px !important;
            font-size: 0.88rem !important;
            font-weight: 600 !important;
            margin-bottom: 22px !important;
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid transparent;
        }
        .alert-error {
            background: rgba(244, 63, 94, 0.08) !important;
            color: #e11d48 !important;
            border-color: rgba(244, 63, 94, 0.2) !important;
        }
        .alert-success {
            background: rgba(16, 185, 129, 0.08) !important;
            color: #059669 !important;
            border-color: rgba(16, 185, 129, 0.2) !important;
        }

        .auth-footer {
            margin-top: 26px !important;
            border-top: 1.5px solid rgba(15, 118, 110, 0.08);
            padding-top: 20px;
            font-size: 0.9rem !important;
            font-weight: 600;
            color: #64748b;
        }
    </style>
</head>
<body class="auth-page">
    
    <!-- Animated Blurred Blobs -->
    <div class="blob-container">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>

    <!-- Page Loader -->
    <div class="page-loader" id="pageLoader">
        <div class="loader-card">
            <div class="loader-logo">
                <i class="ph-bold ph-cube"></i>
            </div>
            <p class="loader-title">Mekong CyberUnit</p>
            <p class="loader-caption">Loading secure session</p>
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
                <h2>Welcome back</h2>
                <p>Sign in to your account</p>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error">
                    <i class="ph-bold ph-warning-circle" style="font-size: 18px;"></i>
                    <span><?php echo htmlspecialchars($_GET['error']); ?></span>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="ph-bold ph-check-circle" style="font-size: 18px;"></i>
                    <span><?php echo htmlspecialchars($_GET['success']); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="login_process.php">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <i class="ph ph-user prefix-icon"></i>
                        <input type="text" id="username" name="username" required placeholder="Enter your username" autocomplete="username">
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="auth-label-row" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <label for="password" style="margin-bottom: 0 !important;">Password</label>
                        <a href="<?php echo mc_url('forgot_password.php'); ?>" class="link-strong" style="font-size: 0.8rem; font-weight: 700; text-decoration: none;">Forgot password?</a>
                    </div>
                    <div class="input-wrapper">
                        <i class="ph ph-lock prefix-icon"></i>
                        <input type="password" id="password" name="password" required placeholder="Enter your password" autocomplete="current-password">
                        <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility()" aria-label="Toggle password visibility">
                            <i class="ph ph-eye" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn-submit full-width">
                    <i class="ph ph-sign-in" style="font-size: 20px;"></i>
                    <span>Sign In</span>
                </button>
            </form>
            
            <div class="auth-footer text-center">
                Don't have an account? <a href="<?php echo mc_url('register.php'); ?>" class="link-strong" style="text-decoration: none;">Sign up</a>
            </div>
        </div>
    </main>

    <script>
        function togglePasswordVisibility() {
            var passwordField = document.getElementById("password");
            var icon = document.getElementById("togglePasswordIcon");
            if (passwordField.type === "password") {
                passwordField.type = "text";
                icon.className = "ph ph-eye-slash";
            } else {
                passwordField.type = "password";
                icon.className = "ph ph-eye";
            }
        }
    </script>
    <script src="<?php echo mc_asset('js/loader.js'); ?>"></script>
</body>
</html>

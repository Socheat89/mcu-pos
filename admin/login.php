<?php
// admin/login.php
session_start();
require_once __DIR__ . '/../core/classes/Auth.php';

// If already logged in as super admin, go to dashboard
if (Auth::check() && Auth::isSuperAdmin()) {
    header('Location: index.php');
    exit;
}

$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SaaS Master Login - Mekong CyberUnit</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@400;500;600;700&family=Sora:wght@300;400;500;600;700&family=Battambang:wght@300;400;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --bg: #0f172a;
            --text: #f8fafc;
            --text-muted: #94a3b8;
            --brand: #2563eb;
        }

        body, h1, h2, h3, h4, h5, h6, p, span, a, label, input, button {
            font-family: 'Sora', 'Battambang', sans-serif !important;
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background: #0f172a; /* Sleek dark background */
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: white;
            padding: 20px;
            overflow: hidden;
            position: relative;
        }

        /* ── Floating Blobs ── */
        .blob-container {
            position: absolute;
            inset: 0;
            overflow: hidden;
            z-index: 1;
            pointer-events: none;
        }
        .blob {
            position: absolute;
            width: 450px;
            height: 450px;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.22;
            animation: float 20s infinite alternate ease-in-out;
        }
        .blob-1 {
            background: #3b82f6;
            left: -100px;
            top: -100px;
            animation-delay: 0s;
        }
        .blob-2 {
            background: #a855f7;
            right: -100px;
            bottom: -100px;
            animation-duration: 25s;
            animation-delay: -5s;
        }
        .blob-3 {
            background: #6366f1;
            left: 45%;
            top: 40%;
            width: 500px;
            height: 500px;
            animation-duration: 28s;
            animation-delay: -10s;
        }
        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(60px, 50px) scale(1.15); }
            100% { transform: translate(-30px, 80px) scale(0.95); }
        }

        /* ── Glassmorphism Login Card ── */
        .login-card {
            position: relative;
            z-index: 10;
            background: rgba(30, 41, 59, 0.45) !important;
            backdrop-filter: blur(24px) !important;
            -webkit-backdrop-filter: blur(24px) !important;
            border: 1.5px solid rgba(255, 255, 255, 0.1) !important;
            box-shadow: 0 30px 90px rgba(0, 0, 0, 0.4) !important;
            width: 100%;
            max-width: 420px;
            padding: 3rem 2.5rem;
            border-radius: 28px;
            transition: all 0.3s ease;
        }
        .login-card:hover {
            border-color: rgba(255, 255, 255, 0.15) !important;
            box-shadow: 0 35px 100px rgba(0, 0, 0, 0.45) !important;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-box {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #3b82f6, #6366f1, #a855f7);
            color: white;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin: 0 auto 1.2rem;
            box-shadow: 0 10px 24px rgba(59, 130, 246, 0.35);
            transition: transform 0.4s ease;
        }
        .login-card:hover .logo-box {
            transform: rotate(10deg) scale(1.06);
        }

        .header h1 { 
            font-family: 'Unbounded', sans-serif !important;
            font-size: 1.45rem; 
            font-weight: 800; 
            margin-bottom: 0.5rem; 
            color: #ffffff; 
            letter-spacing: -0.03em;
        }
        .header p { color: var(--text-muted); font-size: 0.88rem; font-weight: 500; }

        .form-group { margin-bottom: 20px; }
        .form-group label { 
            display: block; 
            margin-bottom: 8px; 
            font-weight: 800; 
            font-size: 0.75rem; 
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #94a3b8;
        }
        
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }
        
        .input-wrapper i.prefix-icon {
            position: absolute;
            left: 18px;
            font-size: 20px;
            color: #64748b;
            transition: color 0.25s ease;
            pointer-events: none;
            z-index: 10;
        }

        .input-wrapper input {
            width: 100%;
            height: 52px;
            padding: 10px 48px 10px 50px !important;
            border: 1.5px solid rgba(255, 255, 255, 0.08);
            background: rgba(15, 23, 42, 0.6);
            border-radius: 16px;
            font-size: 0.96rem;
            color: white;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            outline: none;
        }

        .input-wrapper input:focus {
            border-color: var(--primary);
            background: rgba(15, 23, 42, 0.8);
            box-shadow: 0 0 0 4.5px rgba(59, 130, 246, 0.15);
        }
        .input-wrapper input:focus ~ i.prefix-icon {
            color: var(--primary);
        }

        /* Password toggle button */
        .btn-toggle-password {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #64748b;
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
            color: var(--primary);
        }

        .btn-submit {
            width: 100%;
            height: 52px;
            border-radius: 16px;
            border: none;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            background: linear-gradient(135deg, var(--primary), #4f46e5);
            color: white;
            font-size: 0.98rem;
            letter-spacing: 0.5px;
            box-shadow: 0 10px 24px rgba(59, 130, 246, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 26px;
        }

        .btn-submit:hover { 
            background: linear-gradient(135deg, var(--primary-dark), #4338ca);
            transform: translateY(-2px); 
            box-shadow: 0 14px 32px rgba(59, 130, 246, 0.35);
        }
        .btn-submit:active { transform: translateY(0); }

        .alert {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
            padding: 12px 16px;
            border-radius: 16px;
            font-size: 0.88rem;
            font-weight: 600;
            margin-bottom: 20px;
            border: 1px solid rgba(239, 68, 68, 0.2);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: #34d399;
            border-color: rgba(16, 185, 129, 0.2);
        }

        .footer-note {
            margin-top: 2rem;
            text-align: center;
            font-size: 0.76rem;
            color: var(--text-muted);
            font-weight: 600;
            border-top: 1.5px solid rgba(255, 255, 255, 0.05);
            padding-top: 15px;
        }

        .form-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 0.5rem;
        }

        .form-row label { margin-bottom: 0; }

        .text-link {
            color: var(--primary);
            font-size: 0.78rem;
            font-weight: 700;
            text-decoration: none;
            transition: color 0.2s;
        }
        .text-link:hover { color: #60a5fa; }
    </style>
</head>
<body>
    
    <!-- Animated Blurred Blobs -->
    <div class="blob-container">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>

    <div class="login-card">
        <div class="header">
            <div class="logo-box">
                <i class="ph-bold ph-shield-check"></i>
            </div>
            <h1>System Master</h1>
            <p>SaaS Control Center Authentication</p>
        </div>

        <?php if ($error): ?>
            <div class="alert">
                <i class="ph-bold ph-warning-circle" style="font-size: 18px;"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="ph-bold ph-check-circle" style="font-size: 18px;"></i>
                <span><?php echo htmlspecialchars($success); ?></span>
            </div>
        <?php endif; ?>

        <form action="login_process.php" method="POST">
            <div class="form-group">
                <label>Username</label>
                <div class="input-wrapper">
                    <i class="ph ph-user prefix-icon"></i>
                    <input type="text" name="username" required placeholder="Enter master username" autocomplete="username">
                </div>
            </div>

            <div class="form-group">
                <div class="form-row" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <label style="margin-bottom: 0 !important;">Password</label>
                    <a href="../public/forgot_password.php" class="text-link">Forgot password?</a>
                </div>
                <div class="input-wrapper">
                    <i class="ph ph-lock prefix-icon"></i>
                    <input type="password" id="password" name="password" required placeholder="••••••••" autocomplete="current-password">
                    <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility()" aria-label="Toggle password visibility">
                        <i class="ph ph-eye" id="togglePasswordIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="ph ph-shield-warning" style="font-size: 20px;"></i>
                <span>Authorize Access</span>
            </button>
        </form>

        <div class="footer-note">
            &copy; <?php echo date('Y'); ?> Mekong CyberUnit Master Admin System
        </div>
    </div>

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
</body>
</html>

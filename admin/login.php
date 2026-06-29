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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --bg: #f8fafc;
            --text: #0f172a;
            --text-muted: #64748b;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        
        body {
            background: #0f172a; /* Dark professional background */
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: white;
            padding: 20px;
        }

        .login-card {
            background: white;
            color: var(--text);
            width: 100%;
            max-width: 400px;
            padding: 2.5rem;
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-box {
            width: 64px;
            height: 64px;
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary);
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 1rem;
        }

        .header h1 { font-size: 1.5rem; font-weight: 800; margin-bottom: 0.5rem; color: #1e293b; }
        .header p { color: var(--text-muted); font-size: 0.875rem; }

        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.875rem; }
        
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .input-wrapper i {
            position: absolute;
            left: 1rem;
            color: var(--text-muted);
        }

        .input-wrapper input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .input-wrapper input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .btn {
            width: 100%;
            padding: 0.875rem;
            border-radius: 0.75rem;
            border: none;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            background: var(--primary);
            color: white;
            font-size: 1rem;
        }

        .btn:hover { background: var(--primary-dark); transform: translateY(-1px); }

        .alert {
            background: #fef2f2;
            color: #b91c1c;
            padding: 0.75rem;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            border: 1px solid #fecaca;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-success {
            background: #f0fdf4;
            color: #15803d;
            border-color: #bbf7d0;
        }

        .footer-note {
            margin-top: 2rem;
            text-align: center;
            font-size: 0.75rem;
            color: var(--text-muted);
        }
    </style>
</head>
<body>
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
                <i class="ph-bold ph-warning-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="ph-bold ph-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form action="login_process.php" method="POST">
            <div class="form-group">
                <label>Username</label>
                <div class="input-wrapper">
                    <i class="ph-bold ph-user-circle"></i>
                    <input type="text" name="username" required placeholder="Enter master username">
                </div>
            </div>

            <div class="form-group">
                <label>Password</label>
                <div class="input-wrapper">
                    <i class="ph-bold ph-key"></i>
                    <input type="password" name="password" required placeholder="••••••••">
                </div>
            </div>

            <button type="submit" class="btn">Authorize Access</button>
        </form>

        <div class="footer-note">
            &copy; <?php echo date('Y'); ?> Mekong CyberUnit Master Admin System
        </div>
    </div>
</body>
</html>

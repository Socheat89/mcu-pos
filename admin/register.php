<?php
// admin/register.php
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
    <title>SaaS Master Register - Mekong CyberUnit</title>
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
            max-width: 450px;
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
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .btn-primary {
            width: 100%;
            padding: 0.875rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 0.75rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .alert-error {
            background: #fef2f2;
            color: #dc2626;
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            border: 1px solid #fee2e2;
        }

        .alert-success {
            background: #f0fdf4;
            color: #16a34a;
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            border: 1px solid #dcfce7;
        }

        .back-link {
            text-align: center;
            margin-top: 1.5rem;
        }
        
        .back-link a {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.875rem;
        }
        
        .back-link a:hover {
            color: var(--primary);
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="header">
            <div class="logo-box">
                <i class="ph-bold ph-cube"></i>
            </div>
            <h1>Admin Register</h1>
            <p>Create a new master administrator account</p>
        </div>

        <?php if ($error): ?>
            <div class="alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert-success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form action="register_process.php" method="POST">
            <div class="form-group">
                <label>Username</label>
                <div class="input-wrapper">
                    <i class="ph-bold ph-user"></i>
                    <input type="text" name="username" placeholder="admin_username" required>
                </div>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <div class="input-wrapper">
                    <i class="ph-bold ph-envelope"></i>
                    <input type="email" name="email" placeholder="admin@example.com" required>
                </div>
            </div>

            <div class="form-group">
                <label>Password</label>
                <div class="input-wrapper">
                    <i class="ph-bold ph-lock-key"></i>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <div class="input-wrapper">
                    <i class="ph-bold ph-lock-key"></i>
                    <input type="password" name="confirm_password" placeholder="••••••••" required>
                </div>
            </div>

            <div class="form-group">
                <label>Admin Secret Key</label>
                <div class="input-wrapper">
                    <i class="ph-bold ph-key"></i>
                    <input type="password" name="secret_key" placeholder="System secret for creation" required>
                </div>
                <small style="color: #64748b; font-size: 0.75rem; margin-top: 5px; display: block;">Required for security verification</small>
            </div>

            <button type="submit" class="btn-primary">
                Create Account <i class="ph-bold ph-arrow-right"></i>
            </button>
        </form>

        <div class="back-link">
            <a href="login.php">Already have an account? Sign In</a>
        </div>
    </div>

</body>
</html>

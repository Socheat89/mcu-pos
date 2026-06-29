<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Expired - Mekong CyberUnit</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/landing.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
            background: #fef2f2;
            font-family: 'Inter', sans-serif;
        }
        .error-card {
            background: white;
            padding: 3rem;
            border-radius: 1.5rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            width: 100%;
            max-width: 500px;
            text-align: center;
            border: 1px solid #fee2e2;
        }
        .icon-box {
            width: 80px;
            height: 80px;
            background: #fee2e2;
            color: #dc2626;
            border-radius: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 2rem;
        }
        h2 { color: #111827; margin-bottom: 1rem; font-weight: 800; }
        p { color: #4b5563; margin-bottom: 2rem; line-height: 1.6; }
        .btn {
            display: inline-block;
            padding: 0.875rem 1.5rem;
            background: #dc2626;
            color: white;
            text-decoration: none;
            border-radius: 0.75rem;
            font-weight: 700;
            transition: all 0.2s;
        }
        .btn:hover { background: #b91c1c; transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="icon-box">
            <i class="ph-bold ph-clock-countdown"></i>
        </div>
        <h2>Subscription Expired</h2>
        <p>Your workspace subscription has expired. Please contact our support team or renew your plan to continue using Mekong CyberUnit.</p>
        <div style="display: flex; gap: 1rem; justify-content: center;">
            <a href="login.php" class="btn" style="background: #64748b;">Return to Login</a>
            <a href="renew.php" class="btn">Renew Now</a>
        </div>
    </div>
</body>
</html>

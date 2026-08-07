<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 &mdash; Service Unavailable | MCU POS</title>
    <meta name="robots" content="noindex, nofollow">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 20%, rgba(239,68,68,0.10) 0%, transparent 60%),
                radial-gradient(ellipse 60% 80% at 80% 80%, rgba(249,115,22,0.08) 0%, transparent 60%);
            pointer-events: none;
        }

        .card {
            position: relative;
            background: rgba(30, 41, 59, 0.85);
            border: 1px solid rgba(239,68,68,0.20);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 20px;
            padding: 60px 50px;
            text-align: center;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 25px 50px rgba(0,0,0,0.4);
            animation: fadeUp 0.5s ease forwards;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .icon {
            font-size: 72px;
            line-height: 1;
            margin-bottom: 20px;
            display: block;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50%       { transform: translateY(-8px); }
        }

        .code {
            font-size: 80px;
            font-weight: 800;
            letter-spacing: -2px;
            background: linear-gradient(135deg, #f87171, #fb923c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 8px;
        }

        h1 {
            font-size: 22px;
            font-weight: 600;
            color: #f1f5f9;
            margin-bottom: 12px;
        }

        p {
            font-size: 15px;
            color: #94a3b8;
            line-height: 1.7;
            margin-bottom: 32px;
        }

        .btn {
            display: inline-block;
            padding: 12px 28px;
            background: linear-gradient(135deg, #ef4444, #f97316);
            color: #fff;
            text-decoration: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            transition: opacity 0.2s, transform 0.15s;
            box-shadow: 0 4px 15px rgba(239,68,68,0.35);
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .divider {
            border: none;
            border-top: 1px solid rgba(239,68,68,0.12);
            margin: 28px 0;
        }

        .brand {
            font-size: 12px;
            color: #475569;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="card" role="main">
        <span class="icon" aria-hidden="true">⚙️</span>
        <div class="code" aria-hidden="true">500</div>
        <h1>Something Went Wrong</h1>
        <p>
            We&rsquo;re experiencing a temporary issue. Our team has been notified and is
            working on it. Please try again in a few moments.
        </p>
        <a href="/" class="btn" id="btn-go-home">&#8592; Back to Homepage</a>
        <hr class="divider">
        <p class="brand">MCU POS &mdash; Mekong CyberUnit</p>
    </div>
</body>
</html>

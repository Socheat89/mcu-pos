<?php
// public/success.php
require_once __DIR__ . '/../core/helpers/url.php';

$subdomain = $_GET['subdomain'] ?? '';
$businessName = $_GET['name'] ?? 'Your Business';
$host = $_SERVER['HTTP_HOST'] ?? 'mekongcyberunit.app';
$host = preg_replace('/^www\./', '', $host);
$pathSegment = trim(mc_base_path(), '/');
$workspaceBase = rtrim($host . ($pathSegment ? '/' . $pathSegment : ''), '/') . '/';
$workspaceUrl = 'https://' . $workspaceBase . rawurlencode($subdomain) . '/pos/dashboard';
$workspaceDisplayUrl = $workspaceBase . $subdomain;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful - Mekong CyberUnit</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@400;500;600;700&family=Sora:wght@300;400;500;600;700&family=Battambang:wght@300;400;700&display=swap" rel="stylesheet">

    
    <!-- Styles -->
    <link rel="stylesheet" href="css/landing.css">
    
    <!-- Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <style>
        :root { --brand: #308AC6; --brand-strong: #1F6896; }

        * { box-sizing: border-box; }

        body.status-page {
            font-family: 'Sora', 'Battambang', sans-serif;
            margin: 0; min-height: 100vh;
            background: linear-gradient(180deg, #f0fdf4 0%, #dcfce7 100%);
            display: flex; align-items: center; justify-content: center;
            padding: 2rem 1rem;
        }

        .auth-shell { width: 100%; max-width: 560px; margin: 0 auto; }

        .status-card {
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.7);
            border-radius: 24px; padding: 2.5rem 2rem;
            box-shadow: 0 20px 50px rgba(0,0,0,0.06);
            text-align: center;
        }

        .status-icon.success {
            width: 72px; height: 72px; border-radius: 50%;
            background: #d1fae5; color: #059669;
            display: grid; place-items: center; margin: 0 auto 1.2rem;
            font-size: 2rem;
        }

        h1 {
            font-family: 'Unbounded', sans-serif; font-size: 1.6rem;
            font-weight: 700; color: #0f172a; margin: 0 0 0.5rem;
        }
        .subtitle { color: #64748b; font-size: 0.9rem; margin-bottom: 1.8rem; line-height: 1.5; }

        /* ── Stepper ── */
        .stepper {
            display: flex; gap: 10px; margin-bottom: 1.5rem;
            border-bottom: 1px solid #e2e8f0; padding-bottom: 1rem;
        }
        .stepper-item {
            flex: 1; display: flex; align-items: center; gap: 8px;
            opacity: 0.6; text-align: left;
        }
        .stepper-item.completed { opacity: 0.85; }
        .step-number {
            width: 26px; height: 26px; border-radius: 50%;
            display: grid; place-items: center; font-weight: 700;
            font-size: 0.7rem; flex-shrink: 0;
            background: #059669; color: #fff;
        }
        .stepper-item strong { font-size: 0.72rem; color: #0f172a; display: block; line-height: 1.2; }
        .stepper-item small { font-size: 0.62rem; color: #64748b; display: block; line-height: 1.2; }

        /* ── Workspace Info ── */
        .workspace-info {
            text-align: left; background: #f8fafc;
            border: 1px solid #e2e8f0; border-radius: 16px;
            padding: 1.5rem; margin-bottom: 1.5rem;
        }
        .info-label {
            font-size: 0.7rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.05em; color: #64748b; display: block; margin-bottom: 0.3rem;
        }
        .info-value {
            font-weight: 700; font-size: 1rem; color: #0f172a;
            word-break: break-all;
        }
        .workspace-url {
            display: flex; align-items: center; gap: 0.5rem;
            background: #fff; border: 1px solid #e2e8f0; border-radius: 10px;
            padding: 0.6rem 0.8rem; margin-top: 0.3rem;
        }
        .url-text { font-weight: 600; font-size: 0.85rem; color: #0f172a; flex: 1; word-break: break-all; }
        .copy-btn {
            background: none; border: none; cursor: pointer;
            color: #64748b; font-size: 1.1rem; padding: 4px;
            flex-shrink: 0; transition: color 0.2s;
        }
        .copy-btn:hover { color: var(--brand); }

        /* ── Buttons ── */
        .btn-group { display: flex; gap: 0.8rem; }
        .btn {
            display: inline-flex; align-items: center; justify-content: center;
            gap: 8px; font-weight: 700; border-radius: 50px;
            padding: 0.7rem 1.6rem; font-size: 0.9rem;
            transition: all 0.25s ease; cursor: pointer; border: none;
            text-decoration: none; flex: 1;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--brand), var(--brand-strong));
            color: #fff; box-shadow: 0 4px 16px rgba(48,138,198,0.25);
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(48,138,198,0.35); }
        .btn-outline {
            background: transparent; border: 2px solid rgba(48,138,198,0.2);
            color: var(--brand);
        }
        .btn-outline:hover { background: rgba(48,138,198,0.05); border-color: var(--brand); }

        /* ── Confetti ── */
        .confetti {
            position: fixed; width: 8px; height: 8px; border-radius: 2px;
            pointer-events: none; z-index: 9999;
        }

        /* ── Responsive ── */
        @media (max-width: 600px) {
            .status-card { padding: 1.8rem 1.2rem; border-radius: 18px; }
            h1 { font-size: 1.3rem; }
            .subtitle { font-size: 0.82rem; }
            .status-icon.success { width: 56px; height: 56px; font-size: 1.5rem; }
            .stepper { flex-direction: column; gap: 8px; }
            .btn-group { flex-direction: column; }
            .btn { font-size: 0.85rem; padding: 0.65rem 1.2rem; }
            .workspace-info { padding: 1rem; }
            .url-text { font-size: 0.78rem; }
        }
    </style>
    
</head>
<body class="status-page">
    <main class="auth-shell">
    <div class="status-card">
        <div class="status-icon success">

            <i class="ph-bold ph-check"></i>
        </div>
        
        <h1>Workspace is Ready!</h1>
        <p class="subtitle">Congratulations! Your business platform has been provisioned and is ready for use.</p>

        <div class="stepper">
            <div class="stepper-item completed">
                <div class="step-number">1</div>
                <div>
                    <strong>Payment Verified</strong>
                    <small>Bakong transfer confirmed</small>
                </div>
            </div>
            <div class="stepper-item completed">
                <div class="step-number">2</div>
                <div>
                    <strong>Workspace Setup</strong>
                    <small>Business profile locked</small>
                </div>
            </div>
            <div class="stepper-item completed">
                <div class="step-number">3</div>
                <div>
                    <strong>Launch</strong>
                    <small>Portal ready for login</small>
                </div>
            </div>
        </div>
        
        <div class="workspace-info">
            <span class="info-label">Business Name</span>
            <div class="info-value"><?php echo htmlspecialchars($businessName); ?></div>
            
            <span class="info-label" style="margin-top: 1.5rem;">Access URL</span>
            <div class="workspace-url">
                <i class="ph-bold ph-globe"></i>
                <span class="url-text" id="urlText"><?php echo htmlspecialchars($workspaceDisplayUrl); ?></span>
                <button class="copy-btn" onclick="copyUrl()" title="Copy URL">
                    <i class="ph-bold ph-copy"></i>
                </button>
            </div>
            <p style="font-size: 0.8rem; color: #64748b; margin-top: 0.5rem;">
                <i class="ph-bold ph-info" style="vertical-align: middle;"></i> 
                Save this URL to access your portal directly.
            </p>
        </div>
        
        <div class="btn-group">
            <a href="login.php" class="btn btn-primary">
                Go to Sign In <i class="ph-bold ph-arrow-right"></i>
            </a>
            <a href="/" class="btn btn-outline">

                Back to Home
            </a>
        </div>
        
        <div style="margin-top: 2rem; font-size: 0.85rem; color: #94a3b8;">
            A confirmation email has been sent to your administrator account.
        </div>
    </div>
    </main>


    <script>
        function copyUrl() {
            const urlText = document.getElementById('urlText').innerText;
            navigator.clipboard.writeText(urlText).then(() => {
                const btn = document.querySelector('.copy-btn');
                const icon = btn.querySelector('i');
                icon.className = 'ph-bold ph-check';
                icon.style.color = '#10b981';
                setTimeout(() => {
                    icon.className = 'ph-bold ph-copy';
                    icon.style.color = '#64748b';
                }, 2000);
            });
        }
        
        // Simple confetti effect
        function createConfetti() {
            const colors = ['#2563eb', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];
            for (let i = 0; i < 50; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.left = Math.random() * 100 + '%';
                confetti.style.top = '-10px';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.transform = 'rotate(' + Math.random() * 360 + 'deg)';
                confetti.style.opacity = '1';
                document.body.appendChild(confetti);

                const animation = confetti.animate([
                    { top: '-10px', opacity: 1 },
                    { top: '100vh', opacity: 0 }
                ], {
                    duration: Math.random() * 3000 + 2000,
                    easing: 'cubic-bezier(0, .9, .57, 1)'
                });

                animation.onfinish = () => confetti.remove();
            }
        }
        
        window.onload = createConfetti;
    </script>
</body>
</html>

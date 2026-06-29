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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link rel="stylesheet" href="css/landing.css">
    
    <!-- Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 40px 20px;
            background: #f8fafc;
        }

        .success-card {
            background: white;
            padding: 3rem;
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
            text-align: center;
            border: 1px solid #e2e8f0;
            position: relative;
            overflow: hidden;
        }

        .success-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, #10b981, #34d399);
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: #ecfdf5;
            color: #10b981;
            border-radius: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 2rem;
            animation: scaleIn 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes scaleIn {
            0% { transform: scale(0); }
            100% { transform: scale(1); }
        }

        h1 {
            font-size: 1.875rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 1rem;
            letter-spacing: -0.025em;
        }

        .subtitle {
            font-size: 1rem;
            color: #64748b;
            margin-bottom: 2.5rem;
        }

        .workspace-info {
            background: #f1f5f9;
            padding: 1.5rem;
            border-radius: 1rem;
            margin-bottom: 2.5rem;
            text-align: left;
            border: 1px solid #e2e8f0;
        }

        .info-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            font-weight: 700;
            margin-bottom: 0.5rem;
            display: block;
        }

        .info-value {
            font-size: 1.125rem;
            font-weight: 700;
            color: #0f172a;
            word-break: break-all;
        }

        .workspace-url {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: white;
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
            margin-top: 0.75rem;
        }

        .workspace-url i { color: #2563eb; }
        
        .url-text {
            flex: 1;
            font-family: monospace;
            color: #2563eb;
            font-weight: 600;
        }

        .copy-btn {
            background: none;
            border: none;
            color: #64748b;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .copy-btn:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .btn-primary {
            background: #0f172a;
            color: white;
            padding: 1rem;
            border-radius: 0.75rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary:hover {
            background: #1e293b;
            transform: translateY(-1px);
        }

        .btn-outline {
            background: white;
            color: #0f172a;
            padding: 1rem;
            border-radius: 0.75rem;
            font-weight: 700;
            text-decoration: none;
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
        }

        .btn-outline:hover {
            background: #f8fafc;
        }

        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            background-color: #f00;
            opacity: 0;
        }

        .stepper {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 0.75rem;
            margin-bottom: 2rem;
        }

        .stepper-item {
            display: flex;
            gap: 0.6rem;
            padding: 0.85rem;
            border: 1.5px dashed #e2e8f0;
            border-radius: 0.85rem;
            background: #f8fafc;
            align-items: center;
        }

        .stepper-item .step-number {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: white;
            border: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #475569;
        }

        .stepper-item.completed {
            border-color: #10b981;
            background: #ecfdf5;
        }

        .stepper-item.completed .step-number {
            border-color: #10b981;
            background: #10b981;
            color: white;
        }

        .stepper-item small {
            display: block;
            color: #64748b;
            font-size: 0.75rem;
            margin-top: 0.15rem;
        }
    </style>
</head>
<body>
    <div class="success-card">
        <div class="success-icon">
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
            <a href="login.php" class="btn-primary">
                Go to Sign In <i class="ph-bold ph-arrow-right"></i>
            </a>
            <a href="/" class="btn-outline">
                Back to Home
            </a>
        </div>
        
        <div style="margin-top: 2rem; font-size: 0.85rem; color: #94a3b8;">
            A confirmation email has been sent to your administrator account.
        </div>
    </div>

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

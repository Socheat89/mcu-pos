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

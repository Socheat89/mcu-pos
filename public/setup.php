<?php
// public/setup.php
require_once __DIR__ . '/../core/helpers/url.php';

$plan = $_GET['plan'] ?? 'starter';
$ref = $_GET['ref'] ?? '';
$paid = $_GET['paid'] ?? 'false';

if ($paid !== 'true') {
    header('Location: ' . mc_url('public/register.php?error=' . urlencode('Payment verification required to access setup.')));
    exit;
}

$displayHost = $_SERVER['HTTP_HOST'] ?? 'mekongcyberunit.app';
$displayHost = preg_replace('/^www\./', '', $displayHost);
$setupBase = trim(mc_base_path(), '/');
$workspaceBasePreview = $displayHost . ($setupBase ? '/' . $setupBase : '') . '/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Your Workspace - Mekong CyberUnit</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@400;500;600;700&family=Sora:wght@300;400;500;600;700&family=Battambang:wght@300;400;700&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link rel="stylesheet" href="css/landing.css">
    
    <!-- Favicon -->
    <link rel="icon" href="<?php echo mc_url('public/images/logo.png'); ?>" type="image/png">
    <link rel="shortcut icon" href="<?php echo mc_url('public/images/logo.png'); ?>" type="image/png">
    
    <!-- Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    
</head>
<body class="auth-page">
    <div class="page-loader" id="pageLoader">
        <div class="loader-card">
            <div class="loader-logo">
                <i class="ph-bold ph-cube"></i>
            </div>
            <p class="loader-title">Mekong CyberUnit</p>
            <p class="loader-caption">Provisioning workspace</p>
            <div class="loader-spinner"></div>
            <div class="loader-progress"><span></span></div>
        </div>
    </div>
    <main class="auth-shell">
        <div class="auth-card">
            <div class="auth-header">
                <a href="/" class="auth-logo">
                    <div class="logo-icon">
                        <i class="ph-bold ph-cube"></i>
                    </div>
                    <span>Mekong CyberUnit</span>
                </a>
                <div class="badge-success">
                    <i class="ph-bold ph-check-circle"></i> Payment Confirmed
                </div>
                <h2>Business Information</h2>
                <p>Complete your setup to activate your <span class="link-strong" style="text-transform: capitalize;">
                    <?php echo htmlspecialchars($plan); ?>
                </span> workspace</p>
            </div>

        <div class="system-preview">
            <div class="system-icon-mini">
                <i class="ph-bold ph-sketch-logo"></i>
            </div>
            <div>
                <div style="font-size: 0.85rem; font-weight: 700; color: #0f172a;">Plan Selected</div>
                <div style="font-size: 0.75rem; color: #64748b;">Cloud POS <?php echo ucfirst($plan); ?> - <?php echo $ref ? 'Ref: ' . htmlspecialchars($ref) : 'Paid'; ?></div>
            </div>
        </div>

        <form method="POST" action="<?php echo mc_url('public/register_process.php'); ?>" id="setupForm">
            <div class="stepper">
                <div class="stepper-item completed">
                    <div class="step-number">1</div>
                    <div>
                        <strong>Payment Verified</strong>
                        <small>Bakong transfer confirmed</small>
                    </div>
                </div>
                <div class="stepper-item active">
                    <div class="step-number">2</div>
                    <div>
                        <strong>Workspace Setup</strong>
                        <small>Provide business details</small>
                    </div>
                </div>
                <div class="stepper-item">
                    <div class="step-number">3</div>
                    <div>
                        <strong>Launch Portal</strong>
                        <small>Auto deploy dashboard</small>
                    </div>
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group full-width">
                    <label for="business_name"><i class="ph-bold ph-storefront" style="color: var(--primary);"></i> Business Name</label>
                    <input type="text" id="business_name" name="business_name" required placeholder="e.g. Mekong CyberUnit Co., Ltd">
                </div>

                <div class="form-group full-width">
                    <label for="subdomain"><i class="ph-bold ph-globe" style="color: var(--primary);"></i> Workspace URL</label>
                    <div style="display: flex; align-items: center; gap: 0.5rem; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 0.75rem; padding: 0 1rem;">
                        <span style="color: #64748b; font-weight: 600; font-size: 0.85rem; white-space: nowrap;"><?php echo htmlspecialchars($workspaceBasePreview); ?></span>
                        <input type="text" id="subdomain" name="subdomain" required pattern="[a-zA-Z0-9]+" title="Only letters and numbers allowed" placeholder="your-business" style="border: none; background: transparent; padding: 0.875rem 0; outline: none; box-shadow: none;">
                    </div>
                    <span class="form-helper">This will be your unique portal address.</span>
                </div>

                <div style="grid-column: span 2; margin: 1rem 0 0.5rem;">
                    <h3 style="font-size: 1rem; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em;">
                        <i class="ph-bold ph-identification-card" style="color: var(--primary);"></i> Admin Credentials
                    </h3>
                </div>

                <div class="form-group full-width">
                    <label for="admin_email">Work Email</label>
                    <div style="position: relative;">
                        <i class="ph-bold ph-envelope" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                        <input type="email" id="admin_email" name="admin_email" required placeholder="admin@business.com" style="padding-left: 2.75rem;">
                    </div>
                </div>

                <div class="form-group">
                    <label for="admin_username">Username</label>
                    <div style="position: relative;">
                        <i class="ph-bold ph-user" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                        <input type="text" id="admin_username" name="admin_username" required placeholder="admin" style="padding-left: 2.75rem;">
                    </div>
                </div>
            
                <div class="form-group">
                    <label for="admin_password">Password</label>
                    <div style="position: relative;">
                        <i class="ph-bold ph-lock" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                        <input type="password" id="admin_password" name="admin_password" required minlength="8" placeholder="••••••••" style="padding-left: 2.75rem;">
                    </div>
                </div>

                <div class="form-group full-width">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirm your secure password">
                </div>
                
                <input type="hidden" name="payment_status" value="paid">
                <input type="hidden" name="payment_ref" value="<?php echo htmlspecialchars($ref); ?>">
                <div id="hidden_systems">
                    <?php
                        // Pre-populate systems based on plan
                        $systems = [1];
                        if ($plan === 'professional') $systems = [1, 2];
                        if ($plan === 'enterprise') $systems = [1, 2, 3];
                        foreach($systems as $id) {
                            echo '<input type="hidden" name="systems[]" value="'.$id.'">';
                        }
                    ?>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary full-width" style="margin-top: 1rem;">
                Activate My Workspace <i class="ph-bold ph-arrow-right"></i>
            </button>
        </form>
        </div>
    </main>

    <!-- Creation Loading Modal -->
    <div id="creationModal" class="modal">
        <div class="modal-content modal-content--sm modal-content--center">
            <div id="creationSpinner" style="margin-bottom: 2rem;">
                <i class="ph-bold ph-spinner ph-spin" style="font-size: 4rem; color: var(--primary);"></i>
            </div>
            <div id="creationSuccess" style="display: none; margin-bottom: 2rem;">
                <i class="ph-bold ph-check-circle" style="font-size: 4rem; color: #10b981;"></i>
            </div>
            <h3 id="creationTitle" style="font-size: 1.5rem; margin-bottom: 0.5rem;">Creating Your Workspace</h3>
            <p id="creationText" style="color: #64748b;">Please wait while we set up your personalized environment...</p>
            
            <div style="margin-top: 2rem;">
                <div style="height: 6px; width: 100%; background: #f1f5f9; border-radius: 3px; overflow: hidden;">
                    <div id="creationProgress" style="height: 100%; width: 0%; background: var(--primary); transition: width 0.5s ease;"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('setupForm');
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const password = document.getElementById('admin_password').value;
            const confirm = document.getElementById('confirm_password').value;
            if (password !== confirm) {
                alert('Passwords do not match!');
                return;
            }

            const creationModal = document.getElementById('creationModal');
            const creationProgress = document.getElementById('creationProgress');
            const creationText = document.getElementById('creationText');
            const creationTitle = document.getElementById('creationTitle');
            const creationSpinner = document.getElementById('creationSpinner');
            const creationSuccess = document.getElementById('creationSuccess');
            
            creationModal.classList.add('active');
            
            const steps = [
                { p: 20, t: 'Initializing tenant workspace...' },
                { p: 40, t: 'Provisioning secure database...' },
                { p: 60, t: 'Configuring selected systems...' },
                { p: 80, t: 'Generating administrative credentials...' },
                { p: 100, t: 'Finalizing setup...' }
            ];
            
            let currentStep = 0;
            const interval = setInterval(() => {
                if (currentStep < steps.length) {
                    creationProgress.style.width = steps[currentStep].p + '%';
                    creationText.textContent = steps[currentStep].t;
                    currentStep++;
                } else {
                    clearInterval(interval);
                    creationSpinner.style.display = 'none';
                    creationSuccess.style.display = 'block';
                    creationTitle.textContent = 'Setup Complete!';
                    creationText.textContent = 'Redirecting to your new workspace...';
                    
                    setTimeout(() => {
                        form.submit();
                    }, 1000);
                }
            }, 800);
        });
    </script>
    <script src="<?php echo mc_url('public/js/loader.js'); ?>"></script>
</body>
</html>

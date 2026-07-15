<?php
// public/setup.php
require_once __DIR__ . '/../core/helpers/url.php';
require_once __DIR__ . '/../core/classes/Database.php';

$plan = $_GET['plan'] ?? 'starter';
$ref = $_GET['ref'] ?? '';
$paid = $_GET['paid'] ?? 'false';
$trial = $_GET['trial'] ?? 'false';

if ($paid !== 'true' && $trial !== 'true') {
    header('Location: ' . mc_url('public/register.php?error=' . urlencode('Payment verification required to access setup.')));
    exit;
}

// Fetch plan ID from DB by plan name/code
$db = Database::getInstance();
$planSystem = $db->fetchOne("SELECT id, name, price FROM systems WHERE (name = ? OR REPLACE(LOWER(name), ' ', '_') = ?) AND status = 'active'", [$plan, $plan]);
$planId = $planSystem ? $planSystem['id'] : 1;
$isTrial = ($trial === 'true');

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
    <link rel="stylesheet" href="css/landing.css?v=5.1">
    <style>
        :root {
            --brand: #308AC6;
            --brand-strong: #1F6896;
            --brand-light: #52A2D4;
            --primary: #308AC6;
            --mc-primary: #308AC6;
            --surface: rgba(255, 255, 255, 0.85);
            --border: rgba(48, 138, 198, 0.15);
            --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* ── Page Background & Shell ── */
        body.auth-page {
            background: linear-gradient(180deg, #f0f9ff 0%, #e0f2fe 100%) !important;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1rem;
            position: relative;
            font-family: 'Sora', 'Battambang', sans-serif;
            overflow-x: hidden;
            max-width: 100vw;
        }

        /* Ambient animated blobs */
        .blob-container {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
            max-width: 100vw;
        }
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.45;
            animation: floatBlob 16s infinite ease-in-out;
        }
        .blob-1 { width: 500px; height: 500px; background: rgba(48, 138, 198, 0.15); top: -10%; left: -5%; }
        .blob-2 { width: 400px; height: 400px; background: rgba(82, 162, 212, 0.12); bottom: -10%; right: -5%; animation-delay: -4s; }
        @keyframes floatBlob {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(40px, -40px) scale(1.08); }
        }

        .auth-shell {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 650px;
            min-width: 0;
            margin: 0 auto;
        }

        /* ── Glassmorphism Card ── */
        .auth-card {
            background: rgba(255, 255, 255, 0.8) !important;
            backdrop-filter: blur(20px) !important;
            -webkit-backdrop-filter: blur(20px) !important;
            border: 1px solid rgba(255, 255, 255, 0.7) !important;
            box-shadow: 0 20px 50px rgba(48, 138, 198, 0.08), 0 5px 15px rgba(0, 0, 0, 0.02) !important;
            border-radius: 24px !important;
            padding: 2.5rem 2.2rem !important;
            transition: all 0.3s ease;
            overflow: hidden;
            max-width: 100%;
        }
        .auth-card:hover {
            box-shadow: 0 25px 60px rgba(48, 138, 198, 0.12), 0 8px 25px rgba(0, 0, 0, 0.03) !important;
        }

        /* ── Logo & Header ── */
        .auth-header {
            text-align: center;
            margin-bottom: 1.8rem;
        }
        .auth-header-top {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }
        .auth-logo {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .logo-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(48, 138, 198, 0.15);
            display: grid;
            place-items: center;
            overflow: hidden;
            background: #fff;
        }
        .auth-logo span {
            font-family: 'Unbounded', sans-serif;
            font-weight: 700;
            color: #0f172a;
            font-size: 1.1rem;
            letter-spacing: -0.03em;
        }
        .auth-header h2 {
            font-family: 'Unbounded', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: #0f172a;
            margin-top: 1rem;
            margin-bottom: 0.5rem;
        }
        .auth-header p {
            color: #64748b;
            font-size: 0.88rem;
            font-weight: 500;
        }

        /* ── Badge & Status Strip ── */
        .badge-success {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.9rem;
            border-radius: 50px;
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .system-preview {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.85rem 1.2rem;
            border-radius: 14px;
            background: rgba(48, 138, 198, 0.06);
            border: 1px solid rgba(48, 138, 198, 0.15);
            margin-bottom: 1.5rem;
            text-align: left;
        }
        .system-icon-mini {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: var(--brand);
            color: #fff;
            display: grid;
            place-items: center;
            font-size: 1rem;
        }

        /* ── Stepper ── */
        .stepper {
            display: flex;
            gap: 10px;
            margin-bottom: 2rem;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 1.2rem;
        }
        .stepper-item {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 10px;
            opacity: 0.45;
            transition: all 0.3s ease;
            text-align: left;
        }
        .stepper-item.active {
            opacity: 1;
        }
        .stepper-item.completed {
            opacity: 0.8;
        }
        .step-number {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-weight: 700;
            font-size: 0.75rem;
            background: #f1f5f9;
            color: #94a3b8;
            border: 1.5px solid #cbd5e1;
            flex-shrink: 0;
        }
        .stepper-item.active .step-number {
            background: var(--brand);
            color: #fff;
            border-color: var(--brand);
            box-shadow: 0 4px 10px rgba(48, 138, 198, 0.25);
        }
        .stepper-item.completed .step-number {
            background: var(--brand-strong);
            color: #fff;
            border-color: var(--brand-strong);
        }
        .stepper-item strong {
            font-size: 0.78rem;
            color: #0f172a;
            display: block;
            line-height: 1.2;
        }
        .stepper-item small {
            font-size: 0.65rem;
            color: #64748b;
            display: block;
            line-height: 1.2;
        }

        /* ── Form Layout & Controls ── */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.2rem;
            min-width: 0;
        }
        .form-row-split {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.2rem;
            width: 100%;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            text-align: left;
        }
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        .form-group label {
            font-size: 0.75rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .form-group input, .form-group select {
            width: 100%;
            max-width: 100%;
            height: 46px;
            padding: 0 16px;
            border-radius: 12px;
            border: 1.5px solid #cbd5e1;
            background: rgba(255, 255, 255, 0.9);
            font-weight: 600;
            font-size: 0.9rem;
            color: #0f172a;
            transition: all 0.25s var(--ease-out);
            outline: none;
            box-sizing: border-box;
        }
        .form-group input:focus, .form-group select:focus {
            border-color: var(--brand);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(48, 138, 198, 0.12);
        }
        .form-helper {
            font-size: 0.72rem;
            color: #64748b;
            margin-top: 0.2rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--brand), var(--brand-strong)) !important;
            color: #fff !important;
            font-weight: 700;
            border: none !important;
            border-radius: 12px !important;
            height: 48px !important;
            padding: 0 1.6rem !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 8px !important;
            cursor: pointer !important;
            transition: all 0.25s var(--ease-out) !important;
            box-shadow: 0 6px 20px rgba(48, 138, 198, 0.25) !important;
            text-decoration: none !important;
            width: 100% !important;
            font-size: 0.95rem !important;
        }
        .btn-primary:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 10px 25px rgba(48, 138, 198, 0.35) !important;
        }

        /* ── Loader Modal ── */
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(8px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal.active {
            display: flex !important;
        }
        .modal-content {
            background: #fff;
            border-radius: 24px;
            padding: 2.5rem;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            text-align: center;
            border: none !important;
        }

        @media (max-width: 600px) {
            .auth-header-top {
                flex-direction: column;
                gap: 8px;
            }
            .form-grid {
                grid-template-columns: 1fr;
            }
            .form-grid > .form-group.full-width,
            .form-grid > .form-section-header {
                grid-column: 1 / -1 !important;
            }
            .form-row-split {
                grid-template-columns: 1fr;
            }
            .form-row-split .form-group {
                grid-column: auto !important;
            }
            .stepper {
                flex-direction: column;
                gap: 12px;
            }
            .auth-card {
                padding: 1.8rem 1.2rem !important;
            }
            .auth-header h2 {
                font-size: 1.25rem;
            }
            .auth-header p {
                font-size: 0.8rem;
            }
            .auth-logo span {
                font-size: 1rem;
            }
            .badge-success {
                font-size: 0.68rem;
                padding: 0.3rem 0.7rem;
            }
            .system-preview {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            .btn-primary {
                height: 44px !important;
                font-size: 0.88rem !important;
            }
            .form-group input, .form-group select {
                height: 44px;
                font-size: 0.85rem;
            }
            .form-group .workspace-url-prefix {
                font-size: 0.65rem;
                max-width: 140px;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .url-input-container {
                padding: 0 0.6rem !important;
                gap: 0.3rem !important;
            }
        }

        @media (max-width: 380px) {
            body.auth-page {
                padding: 1rem 0.5rem;
            }
            .auth-card {
                padding: 1.4rem 0.9rem !important;
                border-radius: 18px !important;
            }
            .auth-header h2 {
                font-size: 1.1rem;
            }
            .auth-header-top {
                gap: 6px;
            }
            .auth-logo span {
                font-size: 0.88rem;
            }
            .badge-success {
                font-size: 0.62rem;
                padding: 0.2rem 0.5rem;
            }
            .form-group label {
                font-size: 0.68rem;
            }
            .stepper-item strong {
                font-size: 0.65rem;
            }
        }
    </style>
    
    <!-- Favicon -->
    <link rel="icon" href="<?php echo mc_url('public/images/my-logo.jpg'); ?>" type="image/jpeg">
    <link rel="shortcut icon" href="<?php echo mc_url('public/images/my-logo.jpg'); ?>" type="image/jpeg">
    
    <!-- Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    
</head>
<body class="auth-page">
    <div class="blob-container">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
    </div>

    <div class="page-loader" id="pageLoader">
        <div class="loader-card">
            <div class="loader-logo">
                <img src="<?php echo mc_url('public/images/my-logo.jpg'); ?>" alt="MCU" style="width:100%;height:100%;object-fit:contain;">
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
                <div class="auth-header-top">
                    <a href="/" class="auth-logo">
                        <div class="logo-icon">
                            <img src="<?php echo mc_url('public/images/my-logo.jpg'); ?>" alt="MCU" style="width:100%;height:100%;object-fit:contain;border-radius:inherit;">
                        </div>
                        <span>Mekong CyberUnit</span>
                    </a>
                    <?php if ($isTrial): ?>
                    <div class="badge-success" style="background: #dbeafe; color: #1e40af; border-color: #bfdbfe;">
                        <i class="ph-bold ph-gift"></i> 7-Day Free Trial
                    </div>
                    <?php else: ?>
                    <div class="badge-success">
                        <i class="ph-bold ph-check-circle"></i> Payment Confirmed
                    </div>
                    <?php endif; ?>
                </div>
                <h2>Business Information</h2>
                <p>Complete your setup to activate your <span class="link-strong" style="text-transform: capitalize;">
                    <?php echo htmlspecialchars(str_replace('_', ' ', $plan)); ?>
                </span> workspace</p>
            </div>


        <div class="system-preview">
            <div class="system-icon-mini">
                <i class="ph-bold ph-sketch-logo"></i>
            </div>
            <div>
                <div style="font-size: 0.85rem; font-weight: 700; color: #0f172a;">Plan Selected</div>
                <div style="font-size: 0.75rem; color: #64748b;">Cloud POS <?php echo ucfirst(str_replace('_', ' ', $plan)); ?> &mdash; <?php echo $ref ? 'Ref: ' . htmlspecialchars($ref) : ($isTrial ? 'Free Trial' : 'Paid'); ?></div>
            </div>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error" style="padding:14px 18px; border-radius:16px; font-size:0.85rem; font-weight:600; margin-bottom:20px; display:flex; align-items:center; gap:8px; background:rgba(244,63,94,0.08); color:#e11d48; border:1px solid rgba(244,63,94,0.2);">
                <i class="ph-bold ph-warning-circle" style="font-size:18px;"></i>
                <span><?php echo htmlspecialchars($_GET['error']); ?></span>
            </div>
        <?php endif; ?>

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
                    <div class="url-input-container" style="display: flex; align-items: center; gap: 0.5rem; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 0.75rem; padding: 0 1rem; max-width: 100%; overflow: hidden;">
                        <span class="workspace-url-prefix" style="color: #64748b; font-weight: 600; font-size: 0.85rem; white-space: nowrap; flex-shrink: 0;"><?php echo htmlspecialchars($workspaceBasePreview); ?></span>
                        <input type="text" id="subdomain" name="subdomain" required pattern="[a-zA-Z0-9]+" title="Only letters and numbers allowed" placeholder="your-business" style="border: none; background: transparent; padding: 0.875rem 0; outline: none; box-shadow: none; min-width: 0; flex: 1;">
                    </div>
                    <span class="form-helper">This will be your unique portal address.</span>
                </div>

                <div class="form-section-header" style="grid-column: 1 / -1; margin: 1rem 0 0.5rem;">
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

                <div class="form-group full-width">
                    <label for="admin_username">Username</label>
                    <div style="position: relative;">
                        <i class="ph-bold ph-user" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                        <input type="text" id="admin_username" name="admin_username" required placeholder="admin" style="padding-left: 2.75rem;">
                    </div>
                </div>
            
                <div class="form-group full-width">
                    <div class="form-row-split">
                        <div class="form-group">
                            <label for="admin_password">Password</label>
                            <div style="position: relative;">
                                <i class="ph-bold ph-lock" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                                <input type="password" id="admin_password" name="admin_password" required minlength="8" placeholder="••••••••" style="padding-left: 2.75rem;">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <div style="position: relative;">
                                <i class="ph-bold ph-lock" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                                <input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirm secure password" style="padding-left: 2.75rem;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <input type="hidden" name="payment_status" value="<?php echo $isTrial ? 'trial' : 'paid'; ?>">
            <input type="hidden" name="payment_ref" value="<?php echo htmlspecialchars($ref); ?>">
            <input type="hidden" name="plan_code" value="<?php echo htmlspecialchars($plan); ?>">
            <div id="hidden_systems">
                <?php
                    // Pass the actual plan ID from DB
                    echo '<input type="hidden" name="systems[]" value="' . $planId . '">';
                ?>
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

            <div id="creationSpinner" style="margin-bottom: 2rem; display: flex; justify-content: center;">
                <div class="loader-spinner"></div>
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

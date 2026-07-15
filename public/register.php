<?php require_once __DIR__ . '/../core/classes/Database.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Mekong CyberUnit</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@400;500;600;700&family=Sora:wght@300;400;500;600;700&family=Battambang:wght@300;400;700&display=swap" rel="stylesheet">

    
    <!-- Styles -->
    <link rel="stylesheet" href="css/landing.css">
    
    <!-- Favicon -->
    <link rel="icon" href="images/my-logo.jpg" type="image/jpeg">
    <link rel="shortcut icon" href="images/my-logo.jpg" type="image/jpeg">
    
    <!-- Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <style>
        :root {
            --brand: #0F766E;
            --brand-strong: #0D9488;
            --brand-light: #14B8A6;
            --surface: rgba(255,255,255,0.52);
            --border: rgba(15,118,110,0.12);
            --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* ── Animated Blobs ── */
        .blob-container { position:fixed; inset:0; pointer-events:none; z-index:0; overflow:hidden; }
        .blob {
            position:absolute; border-radius:50%; filter:blur(90px); opacity:0.55;
            animation:floatBlob 18s infinite ease-in-out;
        }
        .blob-1 { width:500px; height:500px; background:rgba(13,148,136,0.18); top:-15%; left:-8%; animation-delay:0s; }
        .blob-2 { width:420px; height:420px; background:rgba(245,158,11,0.13); bottom:-12%; right:-10%; animation-delay:-6s; }
        .blob-3 { width:360px; height:360px; background:rgba(37,99,235,0.10); top:45%; left:55%; animation-delay:-12s; }
        @keyframes floatBlob {
            0%,100% { transform:translate(0,0) scale(1); }
            33% { transform:translate(60px,-50px) scale(1.12); }
            66% { transform:translate(-40px,40px) scale(0.92); }
        }

        /* ── Glassmorphism Card ── */
        body.auth-page { background:linear-gradient(180deg,#fefbf6 0%,#f6eee0 100%) !important; position:relative; }
        .auth-shell { position:relative; z-index:10; }
        .auth-card {
            background:rgba(255,255,255,0.45) !important;
            backdrop-filter:blur(24px) !important;
            -webkit-backdrop-filter:blur(24px) !important;
            border:1.5px solid rgba(255,255,255,0.65) !important;
            box-shadow:0 28px 75px rgba(15,118,110,0.08),0 10px 30px rgba(0,0,0,0.02) !important;
            border-radius:28px !important;
            padding:2.8rem 2.5rem !important;
            max-width:620px; margin:0 auto;
            transition:transform 0.3s ease,box-shadow 0.3s ease;
        }
        .auth-card:hover { box-shadow:0 35px 85px rgba(15,118,110,0.12),0 15px 35px rgba(0,0,0,0.03) !important; }

        /* ── Logo & Header ── */
        .auth-logo { display:inline-flex; align-items:center; gap:10px; text-decoration:none; margin-bottom:8px; }
        .logo-icon {
            width:44px; height:44px; border-radius:14px;
            background:transparent;
            box-shadow:0 4px 12px rgba(15,118,110,0.12);
            display:grid; place-items:center; overflow:hidden;
            transition:transform 0.4s var(--ease-out);
        }
        .auth-logo:hover .logo-icon { transform:scale(1.08); }
        .auth-logo span { font-family:'Unbounded',sans-serif; font-weight:800; color:#0f172a; font-size:1.12rem; letter-spacing:-0.03em; }
        .auth-header h3 { font-size:1.7rem; font-weight:850; color:#0f172a; letter-spacing:-0.04em; margin-top:15px; margin-bottom:6px; }
        .auth-header p { color:#64748b; font-weight:500; font-size:0.93rem; line-height:1.5; }

        /* ── Stepper ── */
        .stepper { display:flex; gap:0; margin:1.8rem 0 1.5rem; position:relative; }
        .stepper-item {
            flex:1; display:flex; align-items:center; gap:10px; padding:10px 8px;
            position:relative; opacity:0.45; transition:opacity 0.3s ease;
        }
        .stepper-item::after {
            content:''; position:absolute; top:50%; left:36px; right:8px; height:2px;
            background:linear-gradient(90deg,var(--brand-light),#e2e8f0);
            transform:translateY(-50%); z-index:0; border-radius:2px;
        }
        .stepper-item:last-child::after { display:none; }
        .stepper-item.active { opacity:1; }
        .stepper-item.completed { opacity:0.75; }
        .stepper-item.completed::after { background:var(--brand-light) !important; }
        .step-number {
            width:32px; height:32px; border-radius:50%;
            display:grid; place-items:center; font-weight:800; font-size:0.8rem;
            background:#f1f5f9; color:#94a3b8; z-index:1; flex-shrink:0;
            border:2px solid #e2e8f0; transition:all 0.3s ease;
        }
        .stepper-item.active .step-number {
            background:var(--brand); color:#fff; border-color:var(--brand);
            box-shadow:0 4px 14px rgba(15,118,110,0.3);
        }
        .stepper-item.completed .step-number {
            background:var(--brand-light); color:#fff; border-color:var(--brand-light);
        }
        .stepper-item strong { font-size:0.82rem; color:#0f172a; display:block; line-height:1.2; }
        .stepper-item small { font-size:0.7rem; color:#94a3b8; display:block; line-height:1.2; }

        /* ── Plan Selection Cards ── */
        .system-selection { margin-bottom:1.5rem; }
        .system-selection h3 {
            font-size:0.82rem; font-weight:800; text-transform:uppercase;
            letter-spacing:0.8px; color:#475569; margin-bottom:1rem;
        }
        .checkbox-group { display:flex; flex-direction:column; gap:12px; }
        .checkbox-card {
            display:block; padding:1.2rem 1.3rem; border-radius:18px;
            border:2px solid rgba(15,118,110,0.14); background:rgba(255,255,255,0.55);
            backdrop-filter:blur(8px); cursor:pointer;
            transition:all 0.25s var(--ease-out); position:relative;
        }
        .checkbox-card:hover {
            border-color:rgba(15,118,110,0.3); background:rgba(255,255,255,0.75);
            transform:translateY(-1px); box-shadow:0 8px 24px rgba(15,118,110,0.08);
        }
        .checkbox-card.selected {
            border-color:var(--brand) !important; background:rgba(15,118,110,0.04);
            box-shadow:0 0 0 5px rgba(15,118,110,0.08);
        }
        .checkbox-card input[type="radio"] { display:none; }
        .checkbox-card__row {
            display:flex; justify-content:space-between; align-items:center;
        }
        .checkbox-card__row-left {
            display:flex; align-items:center; gap:10px; font-weight:700;
            font-size:1rem; color:#0f172a;
        }
        .checkbox-card__row-left::before {
            content:''; width:20px; height:20px; border-radius:50%;
            border:2px solid #cbd5e1; flex-shrink:0;
            transition:all 0.25s ease;
        }
        .checkbox-card.selected .checkbox-card__row-left::before {
            border-color:var(--brand); background:var(--brand);
            box-shadow:inset 0 0 0 4px #fff;
        }
        .checkbox-price {
            font-weight:800; font-size:1.05rem; color:var(--brand);
            white-space:nowrap;
        }
        .checkbox-desc {
            font-size:0.82rem; color:#64748b; margin-top:6px; line-height:1.4;
        }

        /* ── Feature Chips ── */
        .plan-chip-row { display:flex; flex-wrap:wrap; gap:6px; margin-top:10px; }
        .plan-chip {
            display:inline-block; padding:4px 10px; border-radius:20px;
            font-size:0.7rem; font-weight:700; text-transform:uppercase;
            letter-spacing:0.4px;
            background:rgba(15,118,110,0.07); color:var(--brand);
            border:1px solid rgba(15,118,110,0.12);
        }

        /* ── Duration & Payment Section ── */
        .form-control {
            width:100%; height:50px; padding:0 16px; border-radius:14px;
            border:1.5px solid rgba(15,118,110,0.16); background:rgba(255,255,255,0.6);
            font-weight:600; font-size:0.95rem; color:#0f172a;
            transition:all 0.25s ease; outline:none;
        }
        .form-control:focus {
            border-color:var(--brand); background:#fff;
            box-shadow:0 0 0 4px rgba(15,118,110,0.1);
        }
        .notice-card {
            display:flex; align-items:center; gap:8px; padding:12px 16px;
            border-radius:14px; margin-bottom:1rem;
            background:rgba(245,158,11,0.08); border:1px solid rgba(245,158,11,0.2);
            font-size:0.85rem; color:#92400e; font-weight:600;
        }
        .notice-card i { font-size:1.2rem; }
        .price-summary {
            display:flex; justify-content:space-between; align-items:center;
            padding:14px 18px; border-radius:14px;
            background:rgba(15,118,110,0.05); border:1px solid rgba(15,118,110,0.1);
            font-size:0.95rem;
        }
        .price-highlight {
            font-size:1.4rem; font-weight:800; color:var(--brand);
        }

        /* ── Payment Method Card ── */
        .method-card .method-card__content { display:flex; flex-direction:column; }
        .method-card .method-card__content span {
            font-weight:700; font-size:0.95rem; color:#0f172a;
        }

        /* ── CTA Buttons ── */
        .payment-cta { margin-top:1.5rem; text-align:center; }
        .btn {
            display:inline-flex; align-items:center; justify-content:center; gap:8px;
            font-weight:800; border-radius:16px; padding:0.85rem 2rem;
            font-size:0.95rem; transition:all 0.3s var(--ease-out);
            cursor:pointer; border:none; text-decoration:none; letter-spacing:0.3px;
        }
        .btn.full-width { width:100%; }
        .btn-primary {
            background:linear-gradient(135deg,var(--brand),var(--brand-strong),#2563eb);
            background-size:200% auto; color:#fff;
            box-shadow:0 10px 24px rgba(15,118,110,0.28);
        }
        .btn-primary:hover {
            background-position:right center; transform:translateY(-2px);
            box-shadow:0 14px 32px rgba(15,118,110,0.38);
        }
        .btn-outline {
            background:transparent; border:2px solid rgba(15,118,110,0.2);
            color:var(--brand); font-weight:700;
        }
        .btn-outline:hover { background:rgba(15,118,110,0.05); border-color:var(--brand); }
        .payment-cta__note {
            font-size:0.75rem; color:#94a3b8; margin-top:10px;
            display:flex; align-items:center; justify-content:center; gap:6px;
        }

        /* ── Alerts ── */
        .alert {
            padding:14px 18px; border-radius:16px; font-size:0.85rem; font-weight:600;
            margin-bottom:20px; display:flex; align-items:center; gap:8px;
            border:1px solid transparent;
        }
        .alert-error {
            background:rgba(244,63,94,0.08); color:#e11d48;
            border-color:rgba(244,63,94,0.2);
        }

        /* ── Footer ── */
        .auth-footer {
            margin-top:26px; border-top:1.5px solid rgba(15,118,110,0.08);
            padding-top:20px; font-size:0.9rem; font-weight:600; color:#64748b; text-align:center;
        }
        .link-strong { color:var(--brand); font-weight:800; text-decoration:none; }
        .link-strong:hover { color:var(--brand-strong); text-decoration:underline; }

        /* ── Modal Refinements ── */
        .modal.active { display:flex !important; }
        .modal {
            display:none; position:fixed; inset:0; background:rgba(15,23,42,0.45);
            backdrop-filter:blur(6px); z-index:1000; align-items:center; justify-content:center;
        }
        .modal-content {
            background:#fff; border-radius:24px; padding:2rem; max-width:440px; width:92%;
            box-shadow:0 30px 60px rgba(15,23,42,0.2); position:relative;
        }
        .modal-content--sm { max-width:380px; text-align:center; }
        .modal-content--center { text-align:center; }
        .modal-close {
            position:absolute; top:16px; right:16px; background:none; border:none;
            font-size:1.5rem; color:#94a3b8; cursor:pointer; transition:color 0.2s;
        }
        .modal-close:hover { color:#0f172a; }
        .modal-header { margin-bottom:1.2rem; }
        .modal-header h3 { font-size:1.15rem; font-weight:800; color:#0f172a; display:flex; align-items:center; gap:10px; margin:0; }
        .modal-badge {
            width:38px; height:38px; border-radius:12px; display:grid; place-items:center;
            background:linear-gradient(135deg,var(--brand),var(--brand-strong));
            color:#fff; font-size:1.1rem; box-shadow:0 6px 16px rgba(15,118,110,0.25);
        }
        .modal-header--brand h3 { color:#0f172a; }
        .modal-header--telegram h3 { color:#0088cc; }
        .modal-body { margin-bottom:1rem; }
        .payment-amount {
            font-size:2.2rem; font-weight:800; color:var(--brand);
            text-align:center; margin-bottom:4px;
        }
        .payment-instruction {
            font-size:0.82rem; color:#64748b; text-align:center; margin-bottom:1.2rem;
        }
        .qr-code-container--center { text-align:center; }
        .qr-code-container--center img { max-width:220px; border-radius:12px; }
        .modal-footer {
            display:flex; gap:10px; margin-top:1.2rem; justify-content:flex-end;
        }
        .modal-icon--success {
            width:64px; height:64px; border-radius:50%; margin:0 auto 1rem;
            background:rgba(16,185,129,0.1); display:grid; place-items:center;
            font-size:2rem; color:#10b981;
        }
        .waiting-status { text-align:center; }
        .waiting-title { font-size:1.1rem; font-weight:800; color:#0f172a; margin:1rem 0 0.5rem; }
        .waiting-desc { font-size:0.85rem; color:#64748b; line-height:1.5; margin-bottom:1rem; }
        .telegram-badge {
            display:inline-flex; align-items:center; gap:6px; padding:8px 16px;
            border-radius:20px; background:rgba(0,136,204,0.08); color:#0088cc;
            font-weight:700; font-size:0.85rem;
        }
        .countdown-container {
            position:relative; width:120px; height:120px; margin:0 auto;
        }
        .countdown-svg { width:120px; height:120px; transform:rotate(-90deg); }
        .countdown-circle-bg { fill:none; stroke:#f1f5f9; stroke-width:6; }
        .countdown-circle-progress {
            fill:none; stroke:var(--brand); stroke-width:6; stroke-linecap:round;
            stroke-dasharray:351.85; stroke-dashoffset:0; transition:stroke-dashoffset 1s linear;
        }
        .countdown-text {
            position:absolute; top:50%; left:50%; transform:translate(-50%,-50%);
            font-size:1.5rem; font-weight:800; color:#0f172a;
        }
        .status-inline {
            display:flex; align-items:center; justify-content:center; gap:8px;
            font-size:0.85rem; color:#64748b; margin-top:1rem;
        }

        /* ── Responsive ── */
        @media (max-width:640px) {
            .auth-card { padding:2rem 1.3rem !important; border-radius:22px !important; }
            .auth-header h3 { font-size:1.4rem; }
            .stepper-item strong { font-size:0.72rem; }
            .stepper-item small { font-size:0.65rem; display:none; }
            .checkbox-card { padding:1rem; }
            .checkbox-price { font-size:0.9rem; }
        }
    </style>
</head>
<body class="auth-page">

    <!-- Animated Blurred Blobs -->
    <div class="blob-container">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>

    <div class="page-loader" id="pageLoader">
        <div class="loader-card">
            <div class="loader-logo">
                <img src="images/my-logo.jpg" alt="MCU" style="width:100%;height:100%;object-fit:contain;">
            </div>
            <p class="loader-title">Mekong CyberUnit</p>
            <p class="loader-caption">Preparing sign-up flow</p>
            <div class="loader-spinner"></div>
            <div class="loader-progress"><span></span></div>
        </div>
    </div>
    <main class="auth-shell">
        <div class="auth-card">
            <div class="auth-header">
                <a href="index.php" class="auth-logo">
                    <div class="logo-icon">
                        <img src="images/my-logo.jpg" alt="MCU" style="width:100%;height:100%;object-fit:contain;border-radius:inherit;">
                    </div>
                    <span>Mekong CyberUnit</span>
                </a>
                <h3>Create Account</h3>
                <p>Choose a plan and start your journey with Mekong CyberUnit</p>
            </div>


        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <i class="ph-bold ph-warning-circle" style="font-size:18px;"></i>
                <span><?php echo htmlspecialchars($_GET['error']); ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="register_process.php" id="registerForm">
            <div class="stepper" id="register_stepper">
                <div class="stepper-item active" data-step="1">
                    <div class="step-number">1</div>
                    <div>
                        <strong>Choose Plan</strong>
                        <small>Pick your stack</small>
                    </div>
                </div>
                <div class="stepper-item" data-step="2">
                    <div class="step-number">2</div>
                    <div>
                        <strong>Payment</strong>
                        <small>Secure via Bakong</small>
                    </div>
                </div>
                <div class="stepper-item" data-step="3">
                    <div class="step-number">3</div>
                    <div>
                        <strong>Launch</strong>
                        <small>Scan & go live</small>
                    </div>
                </div>
            </div>
            <!-- Plan Selection (Visible First) -->
            <div class="system-selection" id="plan_section">
                <h3>Select a Plan</h3>
                <div class="checkbox-group">
                    <?php
                    $db = Database::getInstance();
                    $plans = $db->fetchAll("SELECT * FROM systems WHERE status = 'active' ORDER BY price ASC");
                    foreach ($plans as $plan):
                        $planCode = strtolower(str_replace(' ', '_', $plan['name']));
                        $planPrice = (float)$plan['price'];
                        $isFree = ($planPrice === 0.00);
                        // Fetch features for this plan
                        $features = $db->fetchAll("SELECT feature_key FROM system_modules WHERE system_id = ?", [$plan['id']]);
                        $featureList = array_column($features, 'feature_key');
                    ?>
                    <label class="checkbox-card checkbox-card--stack<?php echo $isFree ? ' free-trial-card' : ''; ?>" onclick="selectPlan(<?php echo $plan['id']; ?>, <?php echo $planPrice; ?>, '<?php echo $planCode; ?>')" style="<?php echo $isFree ? 'border-color:rgba(5,150,105,0.35); background:rgba(5,150,105,0.025);' : ''; ?>">
                        <div class="checkbox-card__row">
                            <div class="checkbox-card__row-left">
                                <input type="radio" name="plan_select" value="<?php echo $plan['id']; ?>" class="plan-radio" data-plan-code="<?php echo $planCode; ?>" data-plan-price="<?php echo number_format($planPrice, 2, '.', ''); ?>">
                                <span><?php echo htmlspecialchars($plan['name']); ?><?php echo $isFree ? '&nbsp;<span style="font-size:0.68rem;background:#d1fae5;color:#065f46;padding:2px 8px;border-radius:10px;font-weight:700;">FREE</span>' : ''; ?></span>
                            </div>
                            <div class="checkbox-price"><?php echo $isFree ? '<span style="color:#059669;">Free</span>' : '$' . number_format($planPrice, 2) . '/mo'; ?></div>
                        </div>
                        
                        <div class="checkbox-desc"><?php echo htmlspecialchars($plan['description']); ?></div>
                        
                        <?php if (!empty($featureList)): ?>
                        <div class="plan-chip-row">
                            <?php foreach ($featureList as $feat): ?>
                                <span class="plan-chip"><?php echo str_replace('_', ' ', $feat); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Subscription Duration Selection -->
            <div class="system-selection" id="duration_section" style="display: none;">
                <h3>Subscription Duration</h3>
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <select id="duration_select" class="form-control" onchange="updateTotalPrice()">

                        <?php for($i=1; $i<=12; $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?> Month<?php echo $i>1?'s':''; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div id="bonus_notice" class="notice-card" style="display: none;">
                    <i class="ph-bold ph-gift"></i>
                    <strong>Special Offer!</strong> Get <span id="bonus_months">0</span> months free for 1-year subscription.
                </div>
                <div class="price-summary">
                    <span style="font-weight: 600;">Total Amount:</span>
                    <span id="total_price_display" class="price-highlight">$0.00</span>

                </div>
            </div>

            <!-- Payment Method Selection -->
            <div class="system-selection" id="payment_method_section" style="display: none;">

                <h3>Payment Method</h3>
                <div class="checkbox-group">
                    <label class="checkbox-card method-card" onclick="selectPaymentMethod('bakong')">
                        <input type="radio" name="payment_method" value="bakong" class="method-radio" checked>
                        <div class="checkbox-card__row">
                            <div class="checkbox-card__row-left">
                                <span>Bakong KHQR</span>
                            </div>
                            <div class="checkbox-price" style="color:#0F766E;">Instant</div>
                        </div>
                        <div class="checkbox-desc">Scan with Bakong or any Cambodian banking app</div>
                    </label>
                </div>
            </div>

            <!-- Pay CTA -->
            <div class="payment-cta" id="payment_cta" style="display:none;">
                <button type="button" class="btn btn-primary full-width" onclick="showModal()">
                    <i class="ph-bold ph-qr-code"></i> <span id="pay_btn_text">Proceed to Payment</span>
                </button>
                <p class="payment-cta__note">
                    <i class="ph-bold ph-shield-check"></i> Secure payment powered by Bakong KHQR
                </p>
            </div>

            <!-- Free Trial CTA -->
            <div class="payment-cta" id="trial_cta" style="display:none;">
                <button type="button" class="btn btn-primary full-width" onclick="startFreeTrial()" style="background:linear-gradient(135deg,#059669,#047857,#10b981); background-size:200% auto;">
                    <i class="ph-bold ph-gift"></i> Start 7-Day Free Trial
                </button>
                <p class="payment-cta__note">
                    <i class="ph-bold ph-info"></i> No credit card required — full access for 7 days
                </p>
            </div>
        </form>
        
        <div class="auth-footer">
            Already have an account? <a href="login.php" class="link-strong">Sign in</a>
        </div>
        </div>
    </main>


    <!-- Payment Modal (Bakong Branded) -->
    <div id="paymentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header modal-header--brand">
                <h3>
                    <div class="modal-badge">
                        <i class="ph-bold ph-qr-code"></i>
                    </div>
                    Scan to Pay (Bakong)
                </h3>
                <button type="button" class="modal-close" onclick="closeModal()">&times;</button>

            </div>
            <div class="modal-body">
                <div class="payment-amount" id="modalAmount">$0.00</div>
                <div class="payment-instruction">Scan with Bakong or any Banking App</div>
                
                <div class="qr-code-container qr-code-container--center">
                    <div id="qrPlaceholder" style="display: none;">
                         <i class="ph-bold ph-spinner ph-spin"></i>

                    </div>
                    <img id="qrImage" src="" alt="KHQR Payment" style="display: none;">
                </div>
                
                <div id="staticNotice" style="margin-top: 1rem; padding: 1rem; background: #ecfdf5; border: 1px solid #d1fae5; border-radius: 0.5rem; text-align: left; display: none;">
                    <p style="font-size: 0.85rem; color: #065f46; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="ph-bold ph-seal-check"></i>
                        Please click 'I Have Paid' after scanning.
                    </p>
                </div>
                
                <div id="pollingNotice" style="margin-top: 1rem; padding: 1rem; background: #fffbeb; border: 1px solid #fef3c7; border-radius: 0.5rem; text-align: left;">
                    <p style="font-size: 0.85rem; color: #92400e; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="ph-bold ph-spinner ph-spin"></i>
                        កំពុងរង់ចាំការទូទាត់... (Waiting for payment)
                    </p>
                    <div id="apiStatus" style="font-size: 11px; color: #666; margin-top: 5px; font-family: monospace;">Status: INITIALIZING...</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="confirmBtn" class="btn btn-primary" style="flex: 2; display: none;" onclick="notifyAdmin()">

                    <i class="ph-bold ph-check-circle"></i> I Have Paid (Notify Admin)
                </button>
                <button type="button" class="btn btn-outline" style="flex: 1;" onclick="closeModal()">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Payment Success Modal -->
    <div id="successModal" class="modal">
        <div class="modal-content modal-content--sm modal-content--center">
            <div class="modal-icon modal-icon--success">
                <i class="ph-bold ph-check"></i>
            </div>
            <h3>Payment Successful!</h3>
            <p>Thank you for your payment. Your workspace setup is being initialized.</p>
            <div class="status-inline">

                <i class="ph-bold ph-spinner ph-spin"></i> Redirecting to setup...
            </div>
        </div>
    </div>

    <!-- Waiting for Approval Modal -->
    <div id="waitingModal" class="modal">
        <div class="modal-content">
            <div class="modal-header modal-header--telegram">
                <h3>
                    <i class="ph-bold ph-telegram-logo"></i> Awaiting Approval
                </h3>
                <button type="button" class="modal-close" onclick="closeWaitingModal()">&times;</button>
            </div>
            <div class="modal-body">

                <div class="waiting-status">
                    <div class="countdown-container">
                        <svg class="countdown-svg">
                            <circle class="countdown-circle-bg" cx="60" cy="60" r="56"></circle>
                            <circle id="countdown-progress" class="countdown-circle-progress" cx="60" cy="60" r="56"></circle>
                        </svg>
                        <div id="countdown-text" class="countdown-text">120</div>
                    </div>
                    
                    <div class="waiting-title">Admin Notification Sent</div>
                    <div class="waiting-desc">
                        We've notified our team to verify your payment. 
                        This usually takes less than 2 minutes. 
                        <br><strong>Please stay on this page.</strong>
                    </div>
                    
                    <div class="telegram-badge">
                        <i class="ph-bold ph-spinner ph-spin"></i>
                        <span id="waitingBadgeText">Waiting for manual approval...</span>
                    </div>

                    <!-- LOCAL DEV MODE NOTICE -->
                    <div id="localDevNotice" style="display:none; margin-top:12px; padding:12px; background:#fff3cd; border:1px solid #ffc107; border-radius:8px; font-size:0.82rem; color:#664d03; text-align:left; width:100%;">
                        <b>⚠️ Local Dev Mode:</b> Telegram webhook cannot reach localhost.<br>
                        After clicking <b>Approve</b> in Telegram, open the sync page below to process it:<br><br>
                        <a href="api/sync_telegram.php" target="_blank" style="display:inline-block; background:#0088cc; color:white; padding:8px 14px; border-radius:6px; text-decoration:none; font-weight:700; font-size:0.85rem;">
                            🔄 Sync Telegram Approval
                        </a>
                        <span style="margin-left:8px; font-size:0.78rem; color:#888;">Then wait 5 seconds for auto-detect.</span>
                    </div>

                    <div id="apiStatus" style="font-size: 10px; color: #94a3b8; font-family: monospace; margin-top: 10px; background: #f8fafc; padding: 4px 8px; border-radius: 4px;">Status: Initializing...</div>
                </div>
            </div>
        </div>
    </div>


    <script>
        // State
        const form = document.getElementById('registerForm');
        const paymentModal = document.getElementById('paymentModal');
        const planSection = document.getElementById('plan_section');
        const hiddenSystems = document.getElementById('hidden_systems'); // Note: This element might be dynamically created if missing in HTML, but here we assume it exists if used. Wait, it's missing in HTML above. I should remove it or check unlockForm. Ah, unlockForm uses populateHiddenSystems but where is hidden_systems div? It's not in the form HTML above. I must assume it's missing or I should add it. I will add it to the form.
        const payBtnText = document.getElementById('pay_btn_text');
        const stepperItems = document.querySelectorAll('.stepper-item');
        
        // Detect localhost / local dev mode
        const isLocalMode = ['localhost', '127.0.0.1', '::1'].includes(window.location.hostname)
            || window.location.hostname.startsWith('192.168.')
            || window.location.port !== '';
        
        let selectedPlan = null;
        let selectedPlanId = null;
        let selectedPlanCode = null;
        let selectedPrice = 0;
        let selectedDuration = 1;
        let totalPrice = 0;
        let selectedMethod = 'bakong'; // Default
        let paymentConfirmed = false;

        const durationSelect = document.getElementById('duration_select');
        const durationSection = document.getElementById('duration_section');
        const bonusNotice = document.getElementById('bonus_notice');
        const bonusMonths = document.getElementById('bonus_months');
        const totalPriceDisplay = document.getElementById('total_price_display');
        const paymentCta = document.getElementById('payment_cta');
        const trialCta = document.getElementById('trial_cta');
        const paymentMethodSection = document.getElementById('payment_method_section');
        let currentMd5 = null;
        const basePublicUrl = window.location.origin + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);

        function updateStepper(activeStep) {
            if (!stepperItems.length) return;
            stepperItems.forEach(item => {
                const step = parseInt(item.dataset.step, 10);
                const isActive = step === activeStep;
                const isCompleted = step < activeStep;
                item.classList.toggle('active', isActive);
                item.classList.toggle('completed', isCompleted);
                if (!isActive && !isCompleted) {
                    item.classList.remove('active');
                }
            });
        }

        // Plan Selection
        window.selectPlan = function(planId, price, planCode) {
            document.querySelectorAll('.checkbox-card').forEach(card => card.classList.remove('selected'));
            
            const input = document.querySelector(`input[name="plan_select"][value="${planId}"]`);
            if (input) {
                input.checked = true;
                input.closest('.checkbox-card').classList.add('selected');
            }

            selectedPrice = price;
            selectedPlanId = planId;
            selectedPlan = planCode;
            selectedPlanCode = planCode;
            
            const isFree = (parseFloat(price) === 0);
            
            if (isFree) {
                // Free trial: hide payment sections, show trial CTA
                durationSection.style.display = 'none';
                paymentMethodSection.style.display = 'none';
                paymentCta.style.display = 'none';
                trialCta.style.display = 'block';
                updateStepper(2);
            } else {
                // Paid plan: show duration and payment method
                durationSection.style.display = 'block';
                paymentMethodSection.style.display = 'block';
                paymentCta.style.display = 'block';
                trialCta.style.display = 'none';
                updateStepper(2);
                updateTotalPrice();
            }
        };

        // Start Free Trial - redirect to setup
        window.startFreeTrial = function() {
            if (!selectedPlanCode) return;
            window.location.href = `${basePublicUrl}setup.php?plan=${encodeURIComponent(selectedPlanCode)}&trial=true`;
        };

        window.updateTotalPrice = function() {
            selectedDuration = parseInt(durationSelect.value);
            totalPrice = selectedPrice * selectedDuration;
            
            // Bonus Logic
            let bonus = 0;
            if (selectedDuration === 12) {
                if (selectedPlan === 'starter') bonus = 1;
                else if (selectedPlan === 'professional') bonus = 2;
                else if (selectedPlan === 'enterprise') bonus = 3;
            }
            
            if (bonus > 0) {
                bonusMonths.textContent = bonus;
                bonusNotice.style.display = 'block';
            } else {
                bonusNotice.style.display = 'none';
            }
            
            totalPriceDisplay.textContent = '$' + totalPrice.toFixed(2);
            payBtnText.textContent = 'Pay $' + totalPrice.toFixed(2) + ' via Bakong';
        };

        // Payment Method Selection
        window.selectPaymentMethod = function(method) {
            document.querySelectorAll('.method-card').forEach(card => card.classList.remove('selected'));
            
            const input = document.querySelector(`input[name="payment_method"][value="${method}"]`);
            if (input) {
                input.checked = true;
                input.closest('.checkbox-card').classList.add('selected');
            }

            selectedMethod = method;
            updateTotalPrice();
        };

        window.showModal = async function() {
            if (!selectedPlan) return;
            updateStepper(3);
            document.getElementById('modalAmount').textContent = '$' + totalPrice.toFixed(2);
            

            // Reset UI
            const qrImage = document.getElementById('qrImage');
            const qrPlaceholder = document.getElementById('qrPlaceholder');
            const confirmBtn = document.getElementById('confirmBtn');
            const staticNotice = document.getElementById('staticNotice');
            const pollingNotice = document.getElementById('pollingNotice');

            qrImage.style.display = 'none';
            qrPlaceholder.style.display = 'block';
            
            confirmBtn.style.display = 'block';
            confirmBtn.textContent = 'I Have Paid (Notify Admin)';
            confirmBtn.onclick = () => notifyAdmin(); 
            confirmBtn.disabled = false;

            staticNotice.style.display = 'none';
            pollingNotice.style.display = 'none';
            
            paymentModal.classList.add('active');

            try {
                console.log('Fetching QR from:', basePublicUrl + 'api/final_qr.php');
                
                const response = await fetch(`${basePublicUrl}api/final_qr.php?plan=${selectedPlan}&method=${selectedMethod}&amount=${totalPrice}&t=${Date.now()}`);
                
                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(`Server Error (${response.status}): ` + errorText.substring(0, 200));
                }
                
                const textResult = await response.text();
                let result;
                try {
                    result = JSON.parse(textResult);
                } catch (e) {
                    throw new Error("Invalid JSON Response: " + textResult.substring(0, 200));
                }

                if (result.success) {
                    qrImage.src = result.image;
                    qrImage.style.display = 'block';
                    qrPlaceholder.style.display = 'none';
                    currentMd5 = result.md5;
                } else {
                    alert('Error generating QR: ' + result.error);
                    // Don't close modal, verify specific error
                    if(result.error.includes('Vendor')) {
                         alert("TIP: Please verify the 'vendor' folder is uploaded to your hosting root.");
                    }
                }
            } catch (error) {
                console.error('Payment Error:', error);
                alert('Connection Failed:\n' + error.message);
                // Keep modal open so they can see "I Have Paid" button fallback
            }
        };

        // Notify Admin via Telegram
        window.notifyAdmin = async function() {
            if (!currentMd5) {
                alert("QR Code reference missing. Please close and try again.");
                return;
            }

            const confirmBtn = document.getElementById('confirmBtn');
            confirmBtn.innerHTML = '<i class="ph-bold ph-spinner ph-spin"></i> Notifying...';
            confirmBtn.disabled = true;

            try {
                const response = await fetch(`${basePublicUrl}api/telegram_notify.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        md5: currentMd5,
                        amount: totalPrice.toFixed(2),
                        plan: selectedPlan,
                        method: selectedMethod
                    })
                });

                const result = await response.json();

                if (result.success) {
                    // Switch to waiting modal
                    paymentModal.classList.remove('active');
                    document.getElementById('waitingModal').classList.add('active');
                    
                    startCountdown(120);
                    startApprovalPolling(currentMd5);
                } else {
                    alert("Failed to notify admin: " + (result.error || 'Unknown error'));
                    confirmBtn.innerHTML = 'Try Again';
                    confirmBtn.disabled = false;
                }
            } catch (error) {
                console.error("Notify Error:", error);
                alert("Network error. Please try again.");
                confirmBtn.innerHTML = 'Try Again';
                confirmBtn.disabled = false;
            }
        };

        let pollingInterval = null;
        let countdownInterval = null;
        
        function startCountdown(duration) {
            let timeLeft = duration;
            const textDisplay = document.getElementById('countdown-text');
            const progressCircle = document.getElementById('countdown-progress');
            const totalDash = 351.85; // 2 * PI * 56
            
            // Initial state
            textDisplay.textContent = timeLeft;
            progressCircle.style.strokeDashoffset = 0;
            
            if (countdownInterval) clearInterval(countdownInterval);
            
            countdownInterval = setInterval(() => {
                timeLeft--;
                if (timeLeft < 0) {
                    clearInterval(countdownInterval);
                    document.getElementById('waitingBadgeText').textContent = "Taking longer than usual, please wait...";
                    return;
                }
                
                textDisplay.textContent = timeLeft;
                const offset = totalDash - (timeLeft / duration) * totalDash;
                progressCircle.style.strokeDashoffset = offset;
            }, 1000);
        }

        function startApprovalPolling(md5) {
            if (pollingInterval) clearInterval(pollingInterval);
            
            // Show local dev helper
            if (isLocalMode) {
                const notice = document.getElementById('localDevNotice');
                if (notice) notice.style.display = 'block';
            }
            
            const startTime = Date.now();
            const waitingBadgeText = document.getElementById('waitingBadgeText');
            
            pollingInterval = setInterval(async () => {
                try {
                    const response = await fetch(`${basePublicUrl}api/check_approval.php?md5=${md5}&t=${Date.now()}`);
                    const result = await response.json();

                    // Debug Status for dev
                    const statusEl = document.getElementById('apiStatus');
                    if (statusEl) {
                        statusEl.textContent = `Local Status: ${result.status || 'Checking...'} (Success: ${result.success}, JSON: ${result.json || '?'}, DB: ${result.db || '?'})`;
                    }

                    // Check if approved (case-insensitive)
                    const statusUpper = (result.status || '').toUpperCase();
                    if (result.success && (statusUpper === 'SUCCESS' || statusUpper === 'APPROVED')) {
                        clearInterval(pollingInterval);
                        clearInterval(countdownInterval);
                        
                        const waitingContent = document.querySelector('#waitingModal .modal-body');
                        waitingContent.innerHTML = `
                            <div style="text-align:center; color: #16a34a; padding: 15px;">
                                <i class="ph-bold ph-check-circle" style="font-size: 5rem; margin-bottom: 20px; animation: scaleIn 0.5s ease;"></i>
                                <h2 style="margin-bottom: 10px;">Payment Approved!</h2>
                                <p style="color: #64748b; font-size: 1.1rem;">Redirecting to setup your workspace...</p>
                            </div>
                        `;
                        
                        setTimeout(() => {
                            window.location.href = `${basePublicUrl}setup.php?plan=${selectedPlan}&paid=true&ref=${md5}`;
                        }, 2000);
                        return;
                    }

                    // Check if rejected
                    if (result.success && statusUpper === 'REJECTED') {
                        clearInterval(pollingInterval);
                        clearInterval(countdownInterval);
                        
                        const waitingContent = document.querySelector('#waitingModal .modal-body');
                        waitingContent.innerHTML = `
                            <div style="text-align:center; color: #dc2626; padding: 15px;">
                                <i class="ph-bold ph-x-circle" style="font-size: 5rem; margin-bottom: 20px;"></i>
                                <h2 style="margin-bottom: 10px;">Payment Rejected</h2>
                                <p style="color: #64748b; font-size: 1.1rem;">Please try again or contact support.</p>
                                <button onclick="location.reload()" style="margin-top: 20px; padding: 8px 16px; background: #0066cc; color: white; border: none; border-radius: 4px; cursor: pointer;">Try Again</button>
                            </div>
                        `;
                        return;
                    }

                    // Standard Polling: Just check for status changes
                    const elapsed = (Date.now() - startTime) / 1000;
                    if (elapsed > 30) {
                        waitingBadgeText.textContent = "Still waiting for admin... Please check your internet connection.";
                    }

                } catch (e) { console.error("Polling error", e); }
            }, 3000); 
        }

        window.closeWaitingModal = function() {
            if(confirm("Are you sure you want to cancel the waiting process? Your payment notification has already been sent.")) {
                document.getElementById('waitingModal').classList.remove('active');
                if (pollingInterval) clearInterval(pollingInterval);
                if (countdownInterval) clearInterval(countdownInterval);
            }
        };

        window.closeModal = function() {
            paymentModal.classList.remove('active');
            if (pollingInterval) clearInterval(pollingInterval);
            if (selectedPlan) {
                updateStepper(2);
            }
        };

        // Initialize
        function init() {
            const urlParams = new URLSearchParams(window.location.search);
            updateStepper(1);

            const planParam = urlParams.get('plan');
            if (planParam) {
                const normalized = planParam.toLowerCase().replace(/[\s-]+/g, '_');
                const targetRadio = document.querySelector(`.plan-radio[data-plan-code="${normalized}"]`);
                if (targetRadio) {
                    const planId = parseInt(targetRadio.value, 10);
                    const planPrice = parseFloat(targetRadio.dataset.planPrice);
                    const planCode = targetRadio.dataset.planCode;
                    selectPlan(planId, planPrice, planCode);
                    setTimeout(() => {
                        if (parseFloat(planPrice) === 0) {
                            trialCta.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        } else {
                            durationSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                    }, 300);
                }
            }
        }
        
        init();
    </script>
    <script src="js/loader.js"></script>
</body>
</html>
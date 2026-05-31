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
    <link rel="icon" href="images/logo.png" type="image/png">
    <link rel="shortcut icon" href="images/logo.png" type="image/png">
    
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
                        <i class="ph-bold ph-cube"></i>
                    </div>
                    <span>Mekong CyberUnit</span>
                </a>
                <h3>Create Account</h3>
                <p>Complete payment via Bakong to setup your workspace</p>
            </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <i class="ph-bold ph-warning-circle" style="vertical-align: text-bottom;"></i>
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="register_process.php" id="registerForm">
            <div class="stepper" id="register_stepper">
                <div class="stepper-item active" data-step="1">
                    <div class="step-number">1</div>
                    <div>
                        <strong>Choose Plan</strong>
                        <small>Pick the stack that fits your team</small>
                    </div>
                </div>
                <div class="stepper-item" data-step="2">
                    <div class="step-number">2</div>
                    <div>
                        <strong>Duration & Payment</strong>
                        <small>Lock months and method</small>
                    </div>
                </div>
                <div class="stepper-item" data-step="3">
                    <div class="step-number">3</div>
                    <div>
                        <strong>Scan & Launch</strong>
                        <small>Pay via Bakong, auto setup</small>
                    </div>
                </div>
            </div>
            <!-- Plan Selection (Visible First) -->
            <div class="system-selection" id="plan_section">
                <h3>1. Select Plan to Pay</h3>
                <div class="checkbox-group">
                    <?php
                    $db = Database::getInstance();
                    $plans = $db->fetchAll("SELECT * FROM systems WHERE status = 'active' ORDER BY price ASC");
                    foreach ($plans as $plan):
                        $planCode = strtolower(str_replace(' ', '_', $plan['name']));
                        // Fetch features for this plan
                        $features = $db->fetchAll("SELECT feature_key FROM system_modules WHERE system_id = ?", [$plan['id']]);
                        $featureList = array_column($features, 'feature_key');
                    ?>
                    <label class="checkbox-card checkbox-card--stack" onclick="selectPlan(<?php echo $plan['id']; ?>, <?php echo $plan['price']; ?>, '<?php echo $planCode; ?>')">
                        <div class="checkbox-card__row">
                            <div class="checkbox-card__row-left">
                                <input type="radio" name="plan_select" value="<?php echo $plan['id']; ?>" class="plan-radio" data-plan-code="<?php echo $planCode; ?>" data-plan-price="<?php echo number_format($plan['price'], 2, '.', ''); ?>">
                                <span><?php echo htmlspecialchars($plan['name']); ?></span>
                            </div>
                            <div class="checkbox-price">$<?php echo number_format($plan['price'], 2); ?>/mo</div>
                        </div>
                        
                        <div class="checkbox-desc"><?php echo htmlspecialchars($plan['description']); ?></div>
                        
                        <?php if (!empty($featureList)): ?>
                        <div class="plan-chip-row">
                            <?php foreach ($featureList as $feat): ?>
                                <span class="plan-chip">
                                    <?php echo str_replace('_', ' ', $feat); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Subscription Duration Selection -->
            <div class="system-selection" id="duration_section" style="display: none;">
                <h3>2. Select Duration</h3>
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
                    <span style="font-weight: 500;">Total Amount:</span>
                    <span id="total_price_display" class="price-highlight">$0.00</span>
                </div>
            </div>

            <!-- Payment Method Selection -->
            <div class="system-selection" id="payment_method_section" style="display: none;">
                <h3>3. Select Payment Method</h3>
                <div class="checkbox-group">
                    <label class="checkbox-card method-card" onclick="selectPaymentMethod('bakong')">
                        <input type="radio" name="payment_method" value="bakong" class="method-radio" checked>
                        <div class="method-card__content">
                            <span>Bakong QR</span>
                            <div class="checkbox-desc">Scan with Bakong or any Banking App</div>
                        </div>
                        <div class="checkbox-price">Dynamic</div>
                    </label>
                </div>
            </div>

            <!-- Pay CTA moved here -->
            <div class="payment-cta" id="payment_cta" style="display:none;">
                <button type="button" class="btn btn-primary full-width" onclick="showModal()">
                    <i class="ph-bold ph-qr-code"></i> <span id="pay_btn_text">Proceed to Payment</span>
                </button>
                <p class="payment-cta__note">
                    <i class="ph-bold ph-shield-check"></i> Secure payment powered by Bakong KHQR
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
        const paymentMethodSection = document.getElementById('payment_method_section');
        const payBtnText = document.getElementById('pay_btn_text');
        const paymentCta = document.getElementById('payment_cta');
        const stepperItems = document.querySelectorAll('.stepper-item');
        
        // Detect localhost / local dev mode
        const isLocalMode = ['localhost', '127.0.0.1', '::1'].includes(window.location.hostname)
            || window.location.hostname.startsWith('192.168.')
            || window.location.port !== '';
        
        let selectedPlan = null;
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
            const cards = document.querySelectorAll('.plan-radio');
            cards.forEach(input => input.closest('.checkbox-card').style.borderColor = 'var(--border-color)');
            
            const input = document.querySelector(`input[name="plan_select"][value="${planId}"]`);
            if (input) {
                input.checked = true;
                input.closest('.checkbox-card').style.borderColor = '#E31E26';
            }

            selectedPrice = price;
            selectedPlan = planCode;
            
            // Show duration and payment method
            durationSection.style.display = 'block';
            paymentMethodSection.style.display = 'block';
            paymentCta.style.display = 'flex';
            updateStepper(2);
            
            updateTotalPrice();
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
            const methodCards = document.querySelectorAll('.method-radio');
            methodCards.forEach(input => input.closest('.checkbox-card').style.borderColor = 'var(--border-color)');
            
            const input = document.querySelector(`input[name="payment_method"][value="${method}"]`);
            if (input) {
                input.checked = true;
                input.closest('.checkbox-card').style.borderColor = '#E31E26';
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
                const normalized = planParam.toLowerCase();
                const targetRadio = document.querySelector(`.plan-radio[data-plan-code="${normalized}"]`);
                if (targetRadio) {
                    const planId = parseInt(targetRadio.value, 10);
                    const planPrice = parseFloat(targetRadio.dataset.planPrice);
                    const planCode = targetRadio.dataset.planCode;
                    selectPlan(planId, planPrice, planCode);
                    setTimeout(() => {
                        durationSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 300);
                }
            }
        }
        
        init();
    </script>
    <script src="js/loader.js"></script>
</body>
</html>
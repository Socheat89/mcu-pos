<?php
// public/renew.php
require_once __DIR__ . '/../core/bootstrap_session.php';
require_once __DIR__ . '/../core/classes/Database.php';
require_once __DIR__ . '/../core/classes/Auth.php';
require_once __DIR__ . '/../core/classes/Tenant.php';
require_once __DIR__ . '/../core/helpers/url.php';


$urlPrefix = mc_base_path();

if (!Auth::check()) {
    header("Location: $urlPrefix/public/login.php");
    exit;
}

$db = Database::getInstance();
$tenantId = $_SESSION['tenant_id'];
$tenant = $db->fetchOne("SELECT * FROM tenants WHERE id = ?", [$tenantId]);

if (!$tenant) {
    die("Invalid tenant access.");
}

// Get current plan if any
$currentPlan = $db->fetchOne("
    SELECT s.* FROM tenant_systems ts 
    JOIN systems s ON ts.system_id = s.id 
    WHERE ts.tenant_id = ? ORDER BY ts.subscribed_at DESC LIMIT 1", 
    [$tenantId]
);

$plans = $db->fetchAll("SELECT * FROM systems WHERE status = 'active' ORDER BY price ASC");

// Annual promo definition: plan price => free months
$annualPromos = [
    30.00 => 1,   // Buy 12 months, get 1 free (pay for 11)
    99.99 => 3,   // Buy 12 months, get 3 free (pay for 9)
];
// Build JSON for JS
$annualPromosJson = json_encode($annualPromos);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Renew Subscription - Mekong CyberUnit</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@400;500;600;700&family=Sora:wght@300;400;500;600;700&family=Battambang:wght@300;400;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="css/landing.css">
    
    <!-- Favicon -->
    <link rel="icon" href="<?php echo mc_url('public/images/my-logo.jpg'); ?>" type="image/jpeg">
    <link rel="shortcut icon" href="<?php echo mc_url('public/images/my-logo.jpg'); ?>" type="image/jpeg">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
</head>
<body class="auth-page">

    <div class="page-loader" id="pageLoader">
        <div class="loader-card">
            <div class="loader-logo">
                <img src="<?php echo mc_url('public/images/my-logo.jpg'); ?>" alt="MCU" style="width:100%;height:100%;object-fit:contain;">
            </div>
            <p class="loader-title">Mekong CyberUnit</p>
            <p class="loader-caption">Loading renewal portal</p>
            <div class="loader-spinner"></div>
            <div class="loader-progress"><span></span></div>
        </div>
    </div>
    <main class="auth-shell">
        <div class="auth-card auth-card--compact">
            <div class="auth-header">
                <a href="/" class="auth-logo">
                    <img src="<?php echo mc_url('public/images/my-logo.jpg'); ?>" alt="MCU" style="width:36px;height:36px;object-fit:contain;border-radius:10px;"> <span>Mekong CyberUnit</span>
                </a>
                <h2>Renew Your Subscription</h2>
                <p>Business: <strong><?php echo htmlspecialchars($tenant['name']); ?></strong></p>
            </div>

        <form id="renewForm">
            <div class="plan-group">
                <label style="display: block; font-weight: 700; font-size: 0.85rem; text-transform: uppercase; margin-bottom: 1rem;">Select Plan</label>

                <?php foreach ($plans as $p): ?>
                <?php $isSelected = ($currentPlan && $currentPlan['id'] == $p['id']); ?>
                <div class="plan-item <?php echo $isSelected ? 'active' : ''; ?>" onclick="selectPlan(<?php echo $p['id']; ?>, <?php echo $p['price']; ?>, '<?php echo strtolower($p['name']); ?>')">
                    <input type="radio" name="plan" value="<?php echo $p['id']; ?>" <?php echo $isSelected ? 'checked' : ''; ?>>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-weight: 700;"><?php echo htmlspecialchars($p['name']); ?></div>
                            <div class="checkbox-desc"><?php echo htmlspecialchars($p['description']); ?></div>

                        </div>
                        <div class="plan-price">$<?php echo number_format($p['price'], 2); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="panel-card" style="margin-top: 1.5rem;">
                <label style="font-weight: 700; font-size: 0.85rem; display: block; margin-bottom: 0.5rem;">Renewal Period</label>
                <select id="duration" class="form-control" onchange="updateTotal()">

                    <?php for($i=1; $i<=12; $i++): ?>
                        <?php
                        // Check if any plan has annual promo for 12 months
                        $annualNote = '';
                        // We can't know selected plan at render, so show generic note on 12
                        if ($i === 12) {
                            $annualNote = ' — Annual Promo applies!';
                        }
                        ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?> Month<?php echo $i>1?'s':''; ?><?php echo $annualNote; ?></option>
                    <?php endfor; ?>
                </select>

                <!-- Annual promo info box (shown dynamically by JS) -->
                <div id="annualPromoBox" style="display:none; margin-top:0.75rem; padding:0.65rem 0.9rem; background:linear-gradient(135deg,rgba(245,158,11,0.08),rgba(245,158,11,0.04)); border:1px dashed rgba(245,158,11,0.5); border-radius:0.6rem; font-size:0.83rem; color:#92400e; font-weight:600;">
                    <i class="ph-bold ph-gift" style="color:#F59E0B;"></i>
                    <span id="annualPromoText"></span>
                </div>

                <div class="price-summary" style="margin-top: 1rem;">
                    <span>Total Payment:</span>
                    <span id="totalDisplay" class="price-highlight">$0.00</span>
                </div>
                <div id="savingsNote" style="display:none; text-align:right; font-size:0.78rem; color:#059669; font-weight:700; margin-top:0.3rem;"></div>
            </div>

            <button type="button" class="btn btn-primary full-width" style="margin-top: 2rem;" onclick="startRenewal()">
                Proceed to Payment <i class="ph-bold ph-arrow-right"></i>
            </button>
            <div style="text-align: center; margin-top: 1rem;">
                <a href="login.php" class="link-strong">Cancel and Back</a>
            </div>
        </form>
        </div>
    </main>

    <!-- Payment Modal -->
    <div id="paymentModal" class="modal">
        <div class="modal-content modal-content--sm modal-content--center">
            <h3>Bakong KHQR</h3>
            <div class="payment-amount" id="modalAmount">$0.00</div>
            <div class="qr-code-container qr-code-container--center" id="qrContainer">
                <i class="ph-bold ph-spinner ph-spin"></i>
            </div>
            <p class="payment-instruction">Scan with any banking app and notify us.</p>
            
            <button type="button" id="confirmBtn" class="btn btn-primary full-width" style="display: none;" onclick="notifyAdmin()">
                I Have Paid (Notify Admin)
            </button>
            <button type="button" class="btn btn-outline full-width" style="margin-top: 10px;" onclick="closeModal()">Close</button>

            <div id="apiStatus" style="font-size: 10px; color: #94a3b8; margin-top: 10px; font-family: monospace;"></div>
        </div>
    </div>

    <script>
        let selectedPrice = 0;
        let selectedPlanId = null;
        let selectedPlanName = '';
        let currentMd5 = null;
        let pollingInterval = null;

        // Annual promo map from PHP: { price: freeMonths }
        const annualPromos = <?php echo $annualPromosJson; ?>;

        function getFreeMonths(price) {
            for (const [promoPrice, freeMonths] of Object.entries(annualPromos)) {
                if (Math.abs(price - parseFloat(promoPrice)) < 0.01) return freeMonths;
            }
            return 0;
        }

        function selectPlan(id, price, name) {
            selectedPlanId = id;
            selectedPrice = price;
            selectedPlanName = name;
            
            document.querySelectorAll('.plan-item').forEach(el => el.classList.remove('active'));
            event.currentTarget.classList.add('active');
            event.currentTarget.querySelector('input').checked = true;
            updateTotal();
        }

        function updateTotal() {
            const months = parseInt(document.getElementById('duration').value);
            const freeMonths = getFreeMonths(selectedPrice);
            const promoBox = document.getElementById('annualPromoBox');
            const promoText = document.getElementById('annualPromoText');
            const savingsNote = document.getElementById('savingsNote');

            let billableMonths = months;
            let savings = 0;

            if (months === 12 && freeMonths > 0) {
                billableMonths = 12 - freeMonths;
                savings = selectedPrice * freeMonths;
                promoText.textContent = `Annual Deal: Pay for ${billableMonths} months, enjoy ${12} months! (Save $${savings.toFixed(2)})`;
                promoBox.style.display = 'flex';
                promoBox.style.gap = '0.5rem';
                promoBox.style.alignItems = 'center';
                savingsNote.textContent = `You save $${savings.toFixed(2)}!`;
                savingsNote.style.display = 'block';
            } else {
                promoBox.style.display = 'none';
                savingsNote.style.display = 'none';
            }

            const total = selectedPrice * billableMonths;
            document.getElementById('totalDisplay').textContent = '$' + total.toFixed(2);
        }

        async function startRenewal() {
            const total = parseFloat(document.getElementById('totalDisplay').textContent.replace('$', ''));
            if (total <= 0) { alert("Please select a plan first."); return; }

            // Store the actual subscription months (always 12 for annual, even if payment is discounted)
            const selectedMonths = parseInt(document.getElementById('duration').value);
            const freeMonths = getFreeMonths(selectedPrice);
            // actualMonths = what the tenant gets (full 12 even if paying for fewer)
            window._actualSubscriptionMonths = selectedMonths; // always what was selected

            document.getElementById('modalAmount').textContent = '$' + total.toFixed(2);
            document.getElementById('paymentModal').classList.add('active');
            
            // Get QR
            try {
                const res = await fetch(`api/final_qr.php?amount=${total}&plan=${selectedPlanName}&t=${Date.now()}`);
                const data = await res.json();
                if (data.success) {
                    document.getElementById('qrContainer').innerHTML = `<img src="${data.image}" style="max-width: 100%; border-radius: 0.5rem;">`;
                    currentMd5 = data.md5;
                    document.getElementById('confirmBtn').style.display = 'block';
                }
            } catch (e) { alert("Error generating QR."); }
        }

        async function notifyAdmin() {
            const btn = document.getElementById('confirmBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="ph-bold ph-spinner ph-spin"></i> Notifying...';

            const total = document.getElementById('modalAmount').textContent.replace('$', '');
            
            try {
                const res = await fetch('api/telegram_notify.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        md5: currentMd5,
                        amount: total,
                        plan: selectedPlanName,
                        type: 'renewal',
                        business_name: '<?php echo addslashes($tenant['name']); ?>',
                        tenant_id: <?php echo $tenantId; ?>
                    })
                });
                const data = await res.json();
                if (data.success) {
                    btn.innerHTML = 'Waiting for Approval...';
                    startPolling();
                }
            } catch (e) { alert("Network error."); btn.disabled = false; btn.innerHTML = 'Try Again'; }
        }

        function startPolling() {
            pollingInterval = setInterval(async () => {
                try {
                    const res = await fetch(`api/check_approval.php?md5=${currentMd5}&t=${Date.now()}`);
                    const data = await res.json();
                    
                    document.getElementById('apiStatus').textContent = `Status: ${data.status}`;

                    // Case-insensitive status check
                    const statusUpper = (data.status || '').toUpperCase();
                    if (data.success && (statusUpper === 'SUCCESS' || statusUpper === 'APPROVED')) {
                        clearInterval(pollingInterval);
                        const subMonths = window._actualSubscriptionMonths || document.getElementById('duration').value;
                        window.location.href = `renew_process.php?ref=${currentMd5}&months=${subMonths}&plan_id=${selectedPlanId}`;
                    }
                } catch (e) {}
            }, 3000);
        }

        function closeModal() {
            document.getElementById('paymentModal').classList.remove('active');
            if (pollingInterval) clearInterval(pollingInterval);
        }

        // Init with first active plan
        const firstActive = document.querySelector('.plan-item.active');
        if (firstActive) firstActive.click();
    </script>
    <script src="<?php echo mc_url('public/js/loader.js'); ?>"></script>
</body>
</html>

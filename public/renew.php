<?php
// public/renew.php
require_once __DIR__ . '/../core/classes/Database.php';
require_once __DIR__ . '/../core/classes/Auth.php';
require_once __DIR__ . '/../core/classes/Tenant.php';
require_once __DIR__ . '/../core/helpers/url.php';

session_start();

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Renew Subscription - Mekong CyberUnit</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/landing.css">
    
    <!-- Favicon -->
    <link rel="icon" href="<?php echo mc_url('public/images/logo.png'); ?>" type="image/png">
    <link rel="shortcut icon" href="<?php echo mc_url('public/images/logo.png'); ?>" type="image/png">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { background: #f8fafc; padding: 40px 20px; font-family: 'Inter', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .auth-card { background: white; padding: 2.5rem; border-radius: 1.5rem; box-shadow: var(--shadow-xl); width: 100%; max-width: 550px; border: 1px solid var(--border-color); }
        .auth-header { text-align: center; margin-bottom: 2rem; }
        .auth-logo { display: inline-flex; align-items: center; gap: 0.5rem; font-weight: 800; font-size: 1.25rem; margin-bottom: 1rem; text-decoration: none; color: var(--text-main); }
        
        .plan-item { border: 1.5px solid #e2e8f0; border-radius: 0.75rem; padding: 1.25rem; margin-bottom: 1rem; cursor: pointer; transition: all 0.2s; position: relative; }
        .plan-item.active { border-color: #E31E26; background: #fffcfc; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .plan-item input { position: absolute; opacity: 0; }
        .plan-price { font-weight: 800; color: #E31E26; font-size: 1.25rem; }
        
        .duration-box { background: #f1f5f9; padding: 1rem; border-radius: 0.75rem; margin-top: 1.5rem; }
        .total-box { display: flex; justify-content: space-between; align-items: center; margin-top: 1rem; padding-top: 1rem; border-top: 1px dashed #cbd5e1; font-weight: 700; font-size: 1.1rem; }
        
        .btn-full { width: 100%; height: 3.5rem; font-weight: 800; font-size: 1.1rem; }
        
        .modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
        .modal.active { display: flex; }
        .modal-content { background: white; width: 90%; max-width: 400px; padding: 2rem; border-radius: 1.5rem; text-align: center; }
        .qr-placeholder { width: 200px; height: 200px; background: #f1f5f9; border-radius: 1rem; margin: 1.5rem auto; display: flex; align-items: center; justify-content: center; border: 2px dashed #e2e8f0; }
    </style>
</head>
<body>
    <div class="page-loader" id="pageLoader">
        <div class="loader-card">
            <div class="loader-logo">
                <i class="ph-bold ph-cube"></i>
            </div>
            <p class="loader-title">Mekong CyberUnit</p>
            <p class="loader-caption">Loading renewal portal</p>
            <div class="loader-spinner"></div>
            <div class="loader-progress"><span></span></div>
        </div>
    </div>
    <div class="auth-card">
        <div class="auth-header">
            <a href="/" class="auth-logo">
                <i class="ph-bold ph-cube"></i> <span>Mekong CyberUnit</span>
            </a>
            <h2>Renew Your Subscription</h2>
            <p style="color: #64748b;">Business: <strong><?php echo htmlspecialchars($tenant['name']); ?></strong></p>
        </div>

        <form id="renewForm">
            <div class="plan-group">
                <label style="display: block; font-weight: 700; font-size: 0.85rem; color: #475569; text-transform: uppercase; margin-bottom: 1rem;">Select Plan</label>
                <?php foreach ($plans as $p): ?>
                <?php $isSelected = ($currentPlan && $currentPlan['id'] == $p['id']); ?>
                <div class="plan-item <?php echo $isSelected ? 'active' : ''; ?>" onclick="selectPlan(<?php echo $p['id']; ?>, <?php echo $p['price']; ?>, '<?php echo strtolower($p['name']); ?>')">
                    <input type="radio" name="plan" value="<?php echo $p['id']; ?>" <?php echo $isSelected ? 'checked' : ''; ?>>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-weight: 700;"><?php echo htmlspecialchars($p['name']); ?></div>
                            <div style="font-size: 0.8rem; color: #64748b;"><?php echo htmlspecialchars($p['description']); ?></div>
                        </div>
                        <div class="plan-price">$<?php echo number_format($p['price'], 2); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="duration-box">
                <label style="font-weight: 700; font-size: 0.85rem; color: #475569; display: block; margin-bottom: 0.5rem;">Renewal Period</label>
                <select id="duration" class="form-control" style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1.5px solid #e2e8f0;" onchange="updateTotal()">
                    <?php for($i=1; $i<=12; $i++): ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?> Month<?php echo $i>1?'s':''; ?></option>
                    <?php endfor; ?>
                </select>
                <div class="total-box">
                    <span>Total Payment:</span>
                    <span id="totalDisplay" style="color: #E31E26; font-size: 1.3rem;">$0.00</span>
                </div>
            </div>

            <button type="button" class="btn btn-primary btn-full" style="margin-top: 2rem; background: #E31E26; border: none;" onclick="startRenewal()">
                Proceed to Payment <i class="ph-bold ph-arrow-right" style="margin-left: 8px;"></i>
            </button>
            <div style="text-align: center; margin-top: 1rem;">
                <a href="login.php" style="color: #64748b; font-size: 0.9rem; text-decoration: none;">Cancel and Back</a>
            </div>
        </form>
    </div>

    <!-- Payment Modal -->
    <div id="paymentModal" class="modal">
        <div class="modal-content">
            <h3 style="border-bottom: 1px solid #f1f5f9; padding-bottom: 1rem;">Bakong KHQR</h3>
            <div style="font-size: 1.5rem; font-weight: 800; color: #E31E26; margin-top: 1rem;" id="modalAmount">$0.00</div>
            <div class="qr-placeholder" id="qrContainer">
                <i class="ph-bold ph-spinner ph-spin" style="font-size: 2rem; color: #E31E26;"></i>
            </div>
            <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 1.5rem;">Scan with any banking app and notify us.</p>
            
            <button type="button" id="confirmBtn" class="btn btn-primary btn-full" style="background: #16a34a; border: none; display: none;" onclick="notifyAdmin()">
                I Have Paid (Notify Admin)
            </button>
            <button type="button" class="btn btn-outline btn-full" style="margin-top: 10px;" onclick="closeModal()">Close</button>
            <div id="apiStatus" style="font-size: 10px; color: #94a3b8; margin-top: 10px; font-family: monospace;"></div>
        </div>
    </div>

    <script>
        let selectedPrice = 0;
        let selectedPlanId = null;
        let selectedPlanName = '';
        let currentMd5 = null;
        let pollingInterval = null;

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
            const total = selectedPrice * months;
            document.getElementById('totalDisplay').textContent = '$' + total.toFixed(2);
        }

        async function startRenewal() {
            const total = parseFloat(document.getElementById('totalDisplay').textContent.replace('$', ''));
            if (total <= 0) { alert("Please select a plan first."); return; }

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
                        window.location.href = `renew_process.php?ref=${currentMd5}&months=${document.getElementById('duration').value}&plan_id=${selectedPlanId}`;
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

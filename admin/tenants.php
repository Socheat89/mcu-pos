<?php
require_once __DIR__ . '/../middleware/SuperAdminMiddleware.php';
SuperAdminMiddleware::handle();
require_once __DIR__ . '/../core/classes/Database.php';
$db = Database::getInstance();

// Handle Actions
if (isset($_POST['action'])) {
    if ($_POST['action'] == 'update_expiry') {
        $tenant_id = $_POST['tenant_id'];
        $system_id = $_POST['system_id'];
        $new_expiry = $_POST['expires_at'];
        $status = $_POST['status'];
        
        $db->update('tenant_systems', 
            ['expires_at' => $new_expiry, 'status' => $status], 
            'tenant_id = ? AND system_id = ?', 
            [$tenant_id, $system_id]
        );
        header("Location: tenants.php?success=1");
        exit;
    }

    if ($_POST['action'] == 'update_features') {
        $tenant_id = $_POST['tenant_id'];
        $module = $_POST['module_name']; // e.g., 'pos'
        
        // Define known features for POS module (should ideally be dynamic or config-based)
        $knownFeatures = ['core', 'holds', 'inventory', 'customers', 'reports', 'settings', 'digital_menu'];
        
        foreach ($knownFeatures as $feat) {
            $key = "feature_" . $feat;
            $action = $_POST[$key] ?? 'default'; // default, grant, deny
            
            // Remove existing override first
            $db->query("DELETE FROM tenant_features WHERE tenant_id = ? AND module_name = ? AND feature_key = ?", [$tenant_id, $module, $feat]);
            
            if ($action === 'grant' || $action === 'deny') {
                $db->insert('tenant_features', [
                    'tenant_id' => $tenant_id,
                    'module_name' => $module,
                    'feature_key' => $feat,
                    'action' => $action
                ]);
            }
        }
        
        header("Location: tenants.php?success=features_updated");
        exit;
    }
}

$tenants = $db->fetchAll("
    SELECT t.id as tenant_id, t.name as business_name, t.subdomain, 
           s.id as system_id, s.name as plan_name, 
           ts.subscribed_at, ts.expires_at, ts.status
    FROM tenants t
    JOIN tenant_systems ts ON t.id = ts.tenant_id
    JOIN systems s ON ts.system_id = s.id
    ORDER BY ts.subscribed_at DESC
");

include 'header.php';
?>

<div class="header">
    <h1 class="page-title">Manage Tenant Subscriptions</h1>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="card" style="background: #d1fae5; color: #065f46; border-color: #34d399; padding: 1rem; margin-bottom: 2rem;">
        Action completed successfully!
    </div>
<?php endif; ?>

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Business / URL</th>
                    <th>Plan</th>
                    <th>Subscribed</th>
                    <th>Expires At</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tenants as $t): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($t['business_name']); ?></strong><br>
                        <small style="color: var(--text-muted);"><?php echo $t['subdomain']; ?>.mekongcyberunit.app</small>
                    </td>
                    <td><span class="badge badge-outline"><?php echo htmlspecialchars($t['plan_name']); ?></span></td>
                    <td><?php echo date('Y-m-d', strtotime($t['subscribed_at'])); ?></td>
                    <td>
                        <?php if ($t['expires_at']): ?>
                            <span style="<?php echo strtotime($t['expires_at']) < time() ? 'color: var(--danger); font-weight: 700;' : ''; ?>">
                                <?php echo date('Y-m-d H:i', strtotime($t['expires_at'])); ?>
                            </span>
                        <?php else: ?>
                            <span style="color: var(--text-muted);">Lifetime</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge <?php echo $t['status'] == 'active' ? 'badge-success' : 'badge-danger'; ?>">
                            <?php echo $t['status']; ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-outline btn-sm" onclick='editExpiry(<?php echo json_encode($t); ?>)'>
                            <i class="ph-bold ph-calendar-edit"></i> Edit
                        </button>
                        <button class="btn btn-outline btn-sm" onclick='editFeatures(<?php echo json_encode($t); ?>)'>
                            <i class="ph-bold ph-list-checks"></i> Features
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal for Expiry Update -->
<div id="expiryModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 100%; max-width: 400px; margin: 0 auto;">
        <h3>Update Subscription</h3>
        <form method="POST">
            <input type="hidden" name="action" value="update_expiry">
            <input type="hidden" name="tenant_id" id="modal_tenant_id">
            <input type="hidden" name="system_id" id="modal_system_id">
            
            <div class="form-group" style="margin-top: 1.5rem;">
                <label>Expiration Date</label>
                <input type="datetime-local" name="expires_at" id="modal_expires_at" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status" id="modal_status" class="form-control">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="expired">Expired</option>
                </select>
            </div>

            <div class="actions" style="justify-content: flex-end; margin-top: 2rem;">
                <button type="button" class="btn btn-outline" onclick="closeModal('expiryModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal for Feature Management -->
<div id="featuresModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 100%; max-width: 500px; margin: 0 auto; max-height: 90vh; overflow-y: auto;">
        <h3>Manage POS Features</h3>
        <p style="font-size: 0.9em; color: var(--text-muted); margin-bottom: 20px;">Override default plan features for this tenant.</p>
        
        <form method="POST">
            <input type="hidden" name="action" value="update_features">
            <input type="hidden" name="tenant_id" id="feat_tenant_id">
            <input type="hidden" name="module_name" value="pos">
            
            <div id="feature_list">
                <!-- Populated by JS -->
            </div>

            <div class="actions" style="justify-content: flex-end; margin-top: 2rem;">
                <button type="button" class="btn btn-outline" onclick="closeModal('featuresModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function editExpiry(data) {
    document.getElementById('modal_tenant_id').value = data.tenant_id;
    document.getElementById('modal_system_id').value = data.system_id;
    
    if (data.expires_at) {
        let date = new Date(data.expires_at);
        let isoStr = new Date(date.getTime() - (date.getTimezoneOffset() * 60000)).toISOString().slice(0, 16);
        document.getElementById('modal_expires_at').value = isoStr;
    }
    
    document.getElementById('modal_status').value = data.status;
    document.getElementById('expiryModal').style.display = 'flex';
}

function editFeatures(data) {
    document.getElementById('feat_tenant_id').value = data.tenant_id;
    
    // We ideally need to fetch current overrides via AJAX, but for now we'll just show the form
    // In a real app, you'd fetch /admin/api/get_tenant_features.php?tenant_id=...
    
    const features = [
        {key: 'core', label: 'POS Core (Terminals)'},
        {key: 'inventory', label: 'Inventory Management'},
        {key: 'customers', label: 'Customer CRM'},
        {key: 'reports', label: 'Analytics & Reports'},
        {key: 'holds', label: 'Order Holding'},
        {key: 'digital_menu', label: 'Digital Menu (QR)'},
        {key: 'settings', label: 'POS Settings'}
    ];
    
    let html = '';
    features.forEach(f => {
        html += `
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #eee;">
                <div>
                    <strong>${f.label}</strong>
                    <div style="font-size: 0.8em; color: #888;">Key: ${f.key}</div>
                </div>
                <select name="feature_${f.key}" style="padding: 6px; border-radius: 6px; border: 1px solid #ddd;">
                    <option value="default">Default (Plan)</option>
                    <option value="grant">Grant (Force Enable)</option>
                    <option value="deny">Deny (Force Disable)</option>
                </select>
            </div>
        `;
    });
    
    document.getElementById('feature_list').innerHTML = html;
    document.getElementById('featuresModal').style.display = 'flex';
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}
</script>

<?php include 'footer.php'; ?>

<?php
require_once __DIR__ . '/../middleware/SuperAdminMiddleware.php';
SuperAdminMiddleware::handle();
require_once __DIR__ . '/../core/classes/Database.php';
$db = Database::getInstance();

// 1. Define Module Features (The small pieces inside each big module)
$moduleDefinition = [
    'pos' => [
        'label' => 'Point of Sale (POS)',
        'features' => [
            'core' => 'Basic Ordering & Payment (Dashboard, POS)',
            'orders' => 'Order History & Management',
            'inventory' => 'Product & Stock List',
            'customers' => 'Customer Management',
            'reports' => 'Sales Reporting & Analytics',
            'holds' => 'Hold Orders (Suspends)',
            'digital_menu' => 'Digital Menu (QR)',
            'settings' => 'POS General Settings'
        ]
    ],
    'inventory' => [
        'label' => 'Inventory System',
        'features' => [
            'stock_in' => 'Stock-In / Purchase',
            'transfers' => 'Inventory Transfers',
            'dashboard' => 'Inventory Analytics'
        ]
    ],
    'hr' => [
        'label' => 'HR & Payroll',
        'features' => [
            'staff' => 'Staff Management',
            'attendance' => 'Attendance / Check-in',
            'payroll' => 'Payroll Calculation'
        ]
    ]
];

// 2. Handle Actions
if (isset($_POST['action']) && $_POST['action'] == 'save_features') {
    $systemId = $_POST['system_id'];
    $selectedFeatures = $_POST['features'] ?? []; // Array: ['pos' => ['core', 'inventory'], 'hr' => [...]]
    
    // Clear existing for this plan
    $db->delete('system_modules', 'system_id = ?', [$systemId]);
    
    // Insert selection
    foreach ($selectedFeatures as $module => $features) {
        foreach ($features as $featureKey) {
            $db->insert('system_modules', [
                'system_id' => $systemId,
                'module_name' => $module,
                'feature_key' => $featureKey
            ]);
        }
    }
    header("Location: modules.php?success=1&system_id=" . $systemId);
    exit;
}

$systems = $db->fetchAll("SELECT * FROM systems WHERE status = 'active'");

// 3. Load Current Mapping
$existing = $db->fetchAll("SELECT * FROM system_modules");
$mappings = []; // Structure: mappings[system_id][module_name][] = feature_key
foreach ($existing as $m) {
    $mappings[$m['system_id']][$m['module_name']][] = $m['feature_key'];
}

include 'header.php';
?>

<div class="header">
    <h1 class="page-title">Granular Feature Control</h1>
</div>

<div class="card" style="background: #eff6ff; border-left: 4px solid var(--primary); margin-bottom: 2rem;">
    <p style="font-size: 0.875rem; color: #1e40af; margin-bottom: 0;">
        <i class="ph-bold ph-gear-six" style="font-size: 1.1rem; vertical-align: middle; margin-right: 0.5rem;"></i>
        Define exactly what <strong>sub-features</strong> each pricing plan can access. Users will only see the menus and buttons enabled here.
    </p>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="card" style="background: #d1fae5; color: #065f46; border-color: #34d399; padding: 1rem; margin-bottom: 2rem;">
        Plan features updated successfully!
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 2rem;">
    <?php foreach ($systems as $system): ?>
    <div class="card" style="border: 1px solid #e2e8f0; border-top: 4px solid var(--primary);">
        <div style="padding-bottom: 1rem; border-bottom: 1px dashed #e2e8f0; margin-bottom: 1.5rem;">
            <h3 style="display: flex; justify-content: space-between; align-items: center;">
                <?php echo htmlspecialchars($system['name']); ?>
                <span class="badge badge-outline" style="color: var(--primary);">$<?php echo number_format($system['price'], 2); ?></span>
            </h3>
        </div>
        
        <form method="POST">
            <input type="hidden" name="action" value="save_features">
            <input type="hidden" name="system_id" value="<?php echo $system['id']; ?>">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <?php foreach ($moduleDefinition as $moduleKey => $def): ?>
                <div class="module-group">
                    <h5 style="text-transform: uppercase; font-size: 0.75rem; color: #94a3b8; margin-bottom: 0.75rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.25rem;">
                         <?php echo $def['label']; ?>
                    </h5>
                    
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <?php foreach ($def['features'] as $fKey => $fLabel): ?>
                        <?php $isChecked = (isset($mappings[$system['id']][$moduleKey]) && in_array($fKey, $mappings[$system['id']][$moduleKey])); ?>
                        <label style="display: flex; align-items: center; gap: 0.6rem; font-size: 0.875rem; cursor: pointer; color: <?php echo $isChecked ? '#1e293b' : '#94a3b8'; ?>;">
                            <input type="checkbox" name="features[<?php echo $moduleKey; ?>][]" value="<?php echo $fKey; ?>" 
                                <?php echo $isChecked ? 'checked' : ''; ?>
                                style="accent-color: var(--primary);">
                            <?php echo $fLabel; ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 2rem; padding: 0.8rem;">
                <i class="ph-bold ph-floppy-disk"></i> Update <?php echo htmlspecialchars($system['name']); ?> Features
            </button>
        </form>
    </div>
    <?php endforeach; ?>
</div>

<style>
    .module-group { background: #fcfdfe; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid #f1f5f9; }
    .module-group:hover { border-color: #e2e8f0; }
</style>

<?php include 'footer.php'; ?>

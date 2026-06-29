<?php
require_once __DIR__ . '/../middleware/SuperAdminMiddleware.php';
SuperAdminMiddleware::handle();
require_once __DIR__ . '/../core/classes/Database.php';
$db = Database::getInstance();

// Handle Actions
if (isset($_POST['action'])) {
    if ($_POST['action'] == 'save') {
        $data = [
            'name' => $_POST['name'],
            'price' => $_POST['price'],
            'description' => $_POST['description'],
            'status' => $_POST['status']
        ];
        if (!empty($_POST['id'])) {
            $db->update('systems', $data, 'id = ?', [$_POST['id']]);
        } else {
            $db->insert('systems', $data);
        }
        header("Location: plans.php?success=1");
        exit;
    }
    
    if ($_POST['action'] == 'delete') {
        if (!empty($_POST['id'])) {
            $db->delete('systems', 'id = ?', [$_POST['id']]);
            header("Location: plans.php?deleted=1");
            exit;
        }
    }
}

$systems = $db->fetchAll("SELECT * FROM systems");

include 'header.php';
?>

<div class="header">
    <h1 class="page-title">Pricing Plans</h1>
    <button class="btn btn-primary" onclick="openModal()">
        <i class="ph-bold ph-plus"></i> Add New Plan
    </button>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="card" style="background: #d1fae5; color: #065f46; border-color: #34d399; padding: 1rem; margin-bottom: 2rem;">
        Plan saved successfully!
    </div>
<?php endif; ?>

<?php if (isset($_GET['deleted'])): ?>
    <div class="card" style="background: #fee2e2; color: #991b1b; border-color: #fecaca; padding: 1rem; margin-bottom: 2rem;">
        Plan deleted successfully!
    </div>
<?php endif; ?>

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($systems as $system): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($system['name']); ?></strong></td>
                    <td style="color: var(--primary); font-weight: 700;">$<?php echo number_format($system['price'], 2); ?></td>
                    <td style="color: var(--text-muted); font-size: 0.875rem;"><?php echo htmlspecialchars($system['description']); ?></td>
                    <td>
                        <span class="badge <?php echo $system['status'] == 'active' ? 'badge-success' : 'badge-danger'; ?>">
                            <?php echo $system['status']; ?>
                        </span>
                    </td>
                    <td class="actions">
                        <button class="btn btn-outline" onclick='editPlan(<?php echo json_encode($system); ?>)'>
                            <i class="ph-bold ph-pencil"></i> Edit
                        </button>
                        <button class="btn btn-outline" style="color: var(--danger); border-color: #fecaca;" onclick="deletePlan(<?php echo $system['id']; ?>, '<?php echo addslashes($system['name']); ?>')">
                            <i class="ph-bold ph-trash"></i> Delete
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal for Add/Edit -->
<div id="planModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 100%; max-width: 500px; margin: 0 auto;">
        <h3 id="modalTitle" style="margin-bottom: 1.5rem;">Add New Plan</h3>
        <form method="POST">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" id="plan_id">
            
            <div class="form-group">
                <label>Plan Name</label>
                <input type="text" name="name" id="plan_name" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Price ($)</label>
                <input type="number" step="0.01" name="price" id="plan_price" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="plan_desc" class="form-control" rows="3"></textarea>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status" id="plan_status" class="form-control">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="actions" style="justify-content: flex-end; margin-top: 2rem;">
                <button type="button" class="btn btn-outline" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Plan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal() {
    document.getElementById('modalTitle').innerText = 'Add New Plan';
    document.getElementById('plan_id').value = '';
    document.getElementById('plan_name').value = '';
    document.getElementById('plan_price').value = '';
    document.getElementById('plan_desc').value = '';
    document.getElementById('plan_status').value = 'active';
    document.getElementById('planModal').style.display = 'flex';
}

function editPlan(plan) {
    document.getElementById('modalTitle').innerText = 'Edit Plan: ' + plan.name;
    document.getElementById('plan_id').value = plan.id;
    document.getElementById('plan_name').value = plan.name;
    document.getElementById('plan_price').value = plan.price;
    document.getElementById('plan_desc').value = plan.description;
    document.getElementById('plan_status').value = plan.status;
    document.getElementById('planModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('planModal').style.display = 'none';
}

function deletePlan(id, name) {
    if (confirm('Are you sure you want to delete the plan "' + name + '"? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include 'footer.php'; ?>

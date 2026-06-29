<?php
// admin/security.php
require_once __DIR__ . '/../middleware/SuperAdminMiddleware.php';
SuperAdminMiddleware::handle();

require_once __DIR__ . '/../core/classes/Database.php';
require_once __DIR__ . '/../core/classes/Auth.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $currentPass = $_POST['current_password'] ?? '';
    $newPass = $_POST['new_password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';

    if (empty($currentPass) || empty($newPass) || empty($confirmPass)) {
        $error = 'All fields are required.';
    } elseif ($newPass !== $confirmPass) {
        $error = 'New passwords do not match.';
    } elseif (strlen($newPass) < 6) {
        $error = 'New password must be at least 6 characters.';
    } else {
        $db = Database::getInstance();
        $userId = $_SESSION['user_id'];
        
        $user = $db->fetchOne("SELECT password_hash FROM users WHERE id = ?", [$userId]);
        
        if ($user && password_verify($currentPass, $user['password_hash'])) {
            $newHash = password_hash($newPass, PASSWORD_DEFAULT);
            $db->update('users', ['password_hash' => $newHash], 'id = ?', [$userId]);
            $message = 'Password updated successfully!';
        } else {
            $error = 'Current password is incorrect.';
        }
    }
}

include 'header.php';
?>

<div class="header">
    <h1 class="page-title">Security Settings</h1>
</div>

<?php if ($message): ?>
    <div class="card" style="background: #d1fae5; color: #065f46; border-color: #34d399; padding: 1rem; margin-bottom: 2rem;">
        <i class="ph-bold ph-check-circle"></i> <?php echo $message; ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="card" style="background: #fee2e2; color: #991b1b; border-color: #fecaca; padding: 1rem; margin-bottom: 2rem;">
        <i class="ph-bold ph-warning-circle"></i> <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="card" style="max-width: 500px;">
    <h3 style="margin-bottom: 1.5rem;"><i class="ph-bold ph-key"></i> Change Password</h3>
    <form method="POST">
        <input type="hidden" name="action" value="change_password">
        
        <div class="form-group">
            <label>Current Password</label>
            <input type="password" name="current_password" required class="form-control">
        </div>

        <div class="form-group">
            <label>New Password</label>
            <input type="password" name="new_password" required class="form-control">
        </div>

        <div class="form-group">
            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" required class="form-control">
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%;">
            Update Password
        </button>
    </form>
</div>

<?php include 'footer.php'; ?>

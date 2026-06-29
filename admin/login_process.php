<?php
// admin/login_process.php
session_start();
require_once __DIR__ . '/../core/classes/Database.php';

        // Dynamic URL Prefix
        $urlPrefix = '';
        if ($_SERVER['HTTP_HOST'] === 'localhost' || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false) {
            $urlPrefix = '/Mekong_CyberUnit';
        }


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    header("Location: login.php?error=" . urlencode('Credentials required'));
    exit;
}

try {
    $db = Database::getInstance();
    // In our system, SAAS admins belong to tenant_id = 1 (System)
    // and must have role level 3
    $user = $db->fetchOne(
        "SELECT u.*, r.level as role_level 
         FROM users u 
         JOIN roles r ON u.role_id = r.id 
         WHERE u.username = ? AND r.level = 3 AND u.status = 'active'",
        [$username]
    );

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['tenant_id'] = $user['tenant_id'];
        $_SESSION['role_level'] = $user['role_level'];
        
        // Success: Go to admin dashboard
        header("Location: index.php");
        exit;
    } else {
        header("Location: login.php?error=" . urlencode('Invalid master credentials or unauthorized role.'));
        exit;
    }
} catch (Exception $e) {
    header("Location: login.php?error=" . urlencode('System error occurred.'));
    exit;
}
?>

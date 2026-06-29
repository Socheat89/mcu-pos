<?php
// public/login_process.php
<<<<<<< HEAD
=======
// Extend Session Lifetime to 30 Days (1 Month) before starting session
ini_set('session.cookie_lifetime', 2592000); // 30 days
ini_set('session.gc_maxlifetime', 2592000);   // 30 days
>>>>>>> 062e3cc8d9b9f40dc40c6d6c6835e28f6f8a0d77
session_start();
require_once __DIR__ . '/../core/classes/Database.php';
require_once __DIR__ . '/../core/classes/Auth.php';
require_once __DIR__ . '/../core/helpers/url.php';

// Dynamic URL Prefix
$urlPrefix = mc_base_path();


$isAjax = isset($_POST['ajax']);

if ($isAjax) {
    header('Content-Type: application/json');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        echo json_encode(['success' => false, 'error' => 'Invalid request method']);
        exit;
    }
<<<<<<< HEAD
    header("Location: $urlPrefix/public/login.php");
=======
    header("Location: $urlPrefix/login.php");
>>>>>>> 062e3cc8d9b9f40dc40c6d6c6835e28f6f8a0d77
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    if ($isAjax) {
        echo json_encode(['success' => false, 'error' => 'Username and password are required']);
        exit;
    }
<<<<<<< HEAD
    header("Location: $urlPrefix/public/login.php?error=" . urlencode('Username and password are required'));
=======
    header("Location: $urlPrefix/login.php?error=" . urlencode('Username and password are required'));
>>>>>>> 062e3cc8d9b9f40dc40c6d6c6835e28f6f8a0d77
    exit;
}

try {
    // For login, we need to determine the tenant
    $db = Database::getInstance();
    $user = $db->fetchOne(
        "SELECT u.*, t.subdomain, r.name as role_name, r.level as role_level 
         FROM users u 
         JOIN tenants t ON u.tenant_id = t.id 
         JOIN roles r ON u.role_id = r.id 
         WHERE u.username = ? AND u.status = 'active' AND t.status = 'active'",
        [$username]
    );

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['tenant_id'] = $user['tenant_id'];
        $_SESSION['tenant_subdomain'] = $user['subdomain'];
        $_SESSION['role_level'] = $user['role_level'];

        $redirect = '';
        // Redirect based on role
        if ($user['role_level'] == 3) { // Super admin
            $redirect = "$urlPrefix/admin/index.php";
        } else {
            // Redirect to tenant dashboard
            $redirect = "$urlPrefix/{$user['subdomain']}/dashboard";
        }

        if ($isAjax) {
            echo json_encode(['success' => true, 'redirect' => $redirect]);
            exit;
        }

        header('Location: ' . $redirect);
        exit;
    } else {
        if ($isAjax) {
            echo json_encode(['success' => false, 'error' => 'Invalid username or password']);
            exit;
        }
<<<<<<< HEAD
        header("Location: $urlPrefix/public/login.php?error=" . urlencode('Invalid username or password'));
=======
        header("Location: $urlPrefix/login.php?error=" . urlencode('Invalid username or password'));
>>>>>>> 062e3cc8d9b9f40dc40c6d6c6835e28f6f8a0d77
        exit;
    }
} catch (Exception $e) {
    error_log("Login Error: " . $e->getMessage());
    if ($isAjax) {
        // Don't expose technical details to user in production, but for now we can
        // or just say "System error"
        echo json_encode(['success' => false, 'error' => 'System error: ' . $e->getMessage()]);
        exit;
    }
<<<<<<< HEAD
    header("Location: $urlPrefix/public/login.php?error=" . urlencode('System error occurred. Please try again.'));
=======
    header("Location: $urlPrefix/login.php?error=" . urlencode('System error occurred. Please try again.'));
>>>>>>> 062e3cc8d9b9f40dc40c6d6c6835e28f6f8a0d77
    exit;
}
?>
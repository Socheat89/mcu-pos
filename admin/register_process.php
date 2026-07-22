<?php
// admin/register_process.php
require_once __DIR__ . '/../core/bootstrap_session.php';
require_once __DIR__ . '/../core/classes/Database.php';
require_once __DIR__ . '/../config/env.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register.php");
    exit;
}

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$secret = $_POST['secret_key'] ?? '';

// Basic Validations
if (empty($username) || empty($email) || empty($password)) {
    header("Location: register.php?error=" . urlencode('All fields are required'));
    exit;
}

if ($password !== $confirm_password) {
    header("Location: register.php?error=" . urlencode('Passwords do not match'));
    exit;
}

if (strlen($password) < 12) {
    header("Location: register.php?error=" . urlencode('Password must be at least 12 characters'));
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: register.php?error=" . urlencode('Valid email is required'));
    exit;
}

$expectedSecret = mc_env('MC_ADMIN_SETUP_SECRET', '');
if ($expectedSecret === '') {
    header("Location: register.php?error=" . urlencode('Admin registration is disabled until MC_ADMIN_SETUP_SECRET is configured.'));
    exit;
}

if (!hash_equals((string) $expectedSecret, (string) $secret)) {
    header("Location: register.php?error=" . urlencode('Invalid Admin Secret Key'));
    exit;
}

try {
    $db = Database::getInstance();

    // 1. Check if username/email exists in Tenant 1
    $exists = $db->fetchOne(
        "SELECT id FROM users WHERE (username = ? OR email = ?) AND tenant_id = 1",
        [$username, $email]
    );

    if ($exists) {
        header("Location: register.php?error=" . urlencode('Username or Email already exists'));
        exit;
    }

    // 2. Get Super Admin Role ID (Level 3)
    $role = $db->fetchOne("SELECT id FROM roles WHERE level = 3 LIMIT 1");
    
    // If permission roles aren't seeded yet, handle gracefully or insert one
    // But assuming the system is set up.
    if (!$role) {
         header("Location: register.php?error=" . urlencode('System Error: Super Admin role (Level 3) not defined in database.'));
         exit;
    }

    // 3. Create User
    $userId = $db->insert('users', [
        'tenant_id' => 1, // System Tenant
        'username' => $username,
        'email' => $email,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'role_id' => $role['id'],
        'status' => 'active'
    ]);

    // Success redirect
    header("Location: login.php?success=" . urlencode('Account created successfully! Please login.')); 
    exit;

} catch (Exception $e) {
    // Log error for debug
    error_log("Register Error: " . $e->getMessage());
    header("Location: register.php?error=" . urlencode('System error occurred.'));
    exit;
}

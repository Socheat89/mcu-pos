<?php
// public/register_process.php
require_once __DIR__ . '/../core/classes/Database.php';
require_once __DIR__ . '/../core/classes/Settings.php';
require_once __DIR__ . '/../core/helpers/url.php';

$urlPrefix = mc_base_path();


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

// Get form data
$businessName = trim($_POST['business_name']);
$subdomain = trim($_POST['subdomain']);
$adminEmail = trim($_POST['admin_email']);
$adminUsername = trim($_POST['admin_username']);
$adminPassword = $_POST['admin_password'];
$confirmPassword = $_POST['confirm_password'];
$paymentStatus = $_POST['payment_status'] ?? 'pending';
$selectedSystems = $_POST['systems'] ?? [];

// Validation
$errors = [];

if (empty($businessName)) {
    $errors[] = 'Business name is required';
}

if (empty($subdomain)) {
    $errors[] = 'Subdomain is required';
} elseif (!preg_match('/^[a-zA-Z0-9]+$/', $subdomain)) {
    $errors[] = 'Subdomain can only contain letters and numbers';
}

if (empty($adminEmail) || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid admin email is required';
}

if (empty($adminUsername)) {
    $errors[] = 'Admin username is required';
}

if (empty($adminPassword)) {
    $errors[] = 'Admin password is required';
} elseif (strlen($adminPassword) < 8) {
    $errors[] = 'Password must be at least 8 characters';
}

if ($adminPassword !== $confirmPassword) {
    $errors[] = 'Passwords do not match';
}

if (empty($selectedSystems)) {
    $errors[] = 'Please select at least one system';
}

if ($paymentStatus !== 'paid') {
    $errors[] = 'Payment is required to create an account';
}

if (!empty($errors)) {
    $errorMsg = implode(', ', $errors);
    header("Location: $urlPrefix/public/register.php?error=" . urlencode($errorMsg));
    exit;
}

try {
    $db = Database::getInstance();

    // Check if subdomain is unique
    $existingTenant = $db->fetchOne("SELECT id FROM tenants WHERE subdomain = ?", [$subdomain]);
    if ($existingTenant) {
        header("Location: register.php?error=" . urlencode('Subdomain already taken'));
        exit;
    }

    // Check if email is unique across tenants
    $existingUser = $db->fetchOne("SELECT id FROM users WHERE email = ?", [$adminEmail]);
    if ($existingUser) {
        header("Location: register.php?error=" . urlencode('Email already registered'));
        exit;
    }

    // Start transaction
    $db->getConnection()->beginTransaction();

    // Create tenant
    $tenantId = $db->insert('tenants', [
        'name' => $businessName,
        'subdomain' => $subdomain,
        'status' => 'active'
    ]);

    // Initialize default settings for the tenant
    Settings::initializeDefaults($tenantId);

    // Get tenant admin role
    $role = $db->fetchOne("SELECT id FROM roles WHERE name = 'tenant_admin'");
    if (!$role) {
        throw new Exception('Tenant admin role not found');
    }

    // Create admin user
    $passwordHash = password_hash($adminPassword, PASSWORD_DEFAULT);
    $userId = $db->insert('users', [
        'tenant_id' => $tenantId,
        'username' => $adminUsername,
        'email' => $adminEmail,
        'password_hash' => $passwordHash,
        'role_id' => $role['id'],
        'status' => 'active'
    ]);

    // Subscribe to selected systems
    $expiryDate = date('Y-m-d H:i:s', strtotime('+30 days'));
    foreach ($selectedSystems as $systemId) {
        $db->insert('tenant_systems', [
            'tenant_id' => $tenantId,
            'system_id' => $systemId,
            'status' => 'active',
            'expires_at' => $expiryDate
        ]);
    }

    // Commit transaction
    $db->getConnection()->commit();

    // Success - redirect to success page with details
    header("Location: $urlPrefix/public/success.php?subdomain=" . urlencode($subdomain) . "&name=" . urlencode($businessName));

} catch (Exception $e) {
    // Rollback on error
    if (isset($db)) {
        $db->getConnection()->rollBack();
    }

    error_log('Registration error: ' . $e->getMessage());
    header("Location: $urlPrefix/public/register.php?error=" . urlencode('Registration failed. Please try again.'));
}
?>
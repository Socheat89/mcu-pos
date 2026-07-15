<?php
// public/register_process.php
require_once __DIR__ . '/../core/classes/Database.php';
require_once __DIR__ . '/../core/classes/Settings.php';
require_once __DIR__ . '/../core/helpers/url.php';


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . mc_url('public/register.php'));
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

// Initialize DB early for plan validation
$db = Database::getInstance();

// Check if selected plan is a free trial (price = 0)
$isFreeTrial = false;
if (!empty($selectedSystems)) {
    $planCheck = $db->fetchOne("SELECT price FROM systems WHERE id = ? AND status = 'active'", [$selectedSystems[0]]);
    if ($planCheck && (float)$planCheck['price'] === 0.00) {
        $isFreeTrial = true;
    }
}

// For free plans: any payment_status is accepted (trial or paid)
// For paid plans: only 'paid' is accepted — prevents trial bypass on paid plans
if (!$isFreeTrial && $paymentStatus !== 'paid') {
    $errors[] = 'Payment is required to create an account';
}

if (!empty($errors)) {
    $errorMsg = implode(', ', $errors);
    // Redirect back to setup.php preserving plan and trial/paid params
    $planParam = $_POST['plan_code'] ?? $_POST['systems'][0] ?? '';
    $trialParam = ($paymentStatus === 'trial') ? '&trial=true' : '&paid=true';
    header("Location: " . mc_url("public/setup.php?plan=" . urlencode($planParam) . $trialParam . "&error=" . urlencode($errorMsg)));
    exit;
}

try {

    // Check if subdomain is unique
    $existingTenant = $db->fetchOne("SELECT id FROM tenants WHERE subdomain = ?", [$subdomain]);
    if ($existingTenant) {
        $planParam = $_POST['plan_code'] ?? $_POST['systems'][0] ?? '';
        $trialParam = ($paymentStatus === 'trial') ? '&trial=true' : '&paid=true';
        header("Location: " . mc_url("public/setup.php?plan=" . urlencode($planParam) . $trialParam . "&error=" . urlencode('Subdomain already taken')));
        exit;
    }

    // Check if email is unique across tenants
    $existingUser = $db->fetchOne("SELECT id FROM users WHERE email = ?", [$adminEmail]);
    if ($existingUser) {
        $planParam = $_POST['plan_code'] ?? $_POST['systems'][0] ?? '';
        $trialParam = ($paymentStatus === 'trial') ? '&trial=true' : '&paid=true';
        header("Location: " . mc_url("public/setup.php?plan=" . urlencode($planParam) . $trialParam . "&error=" . urlencode('Email already registered')));
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
    // Free trial: 7 days expiry; Paid: 30 days expiry
    $expiryDays = $isFreeTrial ? 7 : 30;
    $expiryDate = date('Y-m-d H:i:s', strtotime("+{$expiryDays} days"));
    foreach ($selectedSystems as $systemId) {
        $db->insert('tenant_systems', [
            'tenant_id'  => $tenantId,
            'system_id'  => $systemId,
            'status'     => 'active',
            'is_trial'   => $isFreeTrial ? 1 : 0,
            'expires_at' => $expiryDate
        ]);
    }

    // Commit transaction
    $db->getConnection()->commit();

    // Success - redirect to success page with details
    header("Location: " . mc_url("public/success.php?subdomain=" . urlencode($subdomain) . "&name=" . urlencode($businessName)));

} catch (Exception $e) {
    // Rollback on error
    if (isset($db)) {
        $db->getConnection()->rollBack();
    }

    error_log('Registration error: ' . $e->getMessage());
    $planParam = $_POST['plan_code'] ?? $_POST['systems'][0] ?? '';
    $trialParam = ($paymentStatus === 'trial') ? '&trial=true' : '&paid=true';
    header("Location: " . mc_url("public/setup.php?plan=" . urlencode($planParam) . $trialParam . "&error=" . urlencode('Registration failed. Please try again.')));
}
?>
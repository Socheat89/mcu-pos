<?php
// public/login_process.php
require_once __DIR__ . '/../core/bootstrap_session.php';
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
    header("Location: $urlPrefix/login.php");
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    if ($isAjax) {
        echo json_encode(['success' => false, 'error' => 'Username and password are required']);
        exit;
    }
    header("Location: $urlPrefix/login.php?error=" . urlencode('Username and password are required'));
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
        $_SESSION['user_id']           = $user['id'];
        $_SESSION['tenant_id']         = $user['tenant_id'];
        $_SESSION['tenant_subdomain']  = $user['subdomain'];
        $_SESSION['role_level']        = $user['role_level'];

        // ── Issue "Remember Me" persistent token ─────────────────────────────
        // Always set — keeps PWA / mobile users logged in across app restarts
        _issue_remember_me_token((int)$user['id'], $db);

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
        header("Location: $urlPrefix/login.php?error=" . urlencode('Invalid username or password'));
        exit;
    }
} catch (Exception $e) {
    error_log("Login Error: " . $e->getMessage());
    if ($isAjax) {
        echo json_encode(['success' => false, 'error' => 'System error: ' . $e->getMessage()]);
        exit;
    }
    header("Location: $urlPrefix/login.php?error=" . urlencode('System error occurred. Please try again.'));
    exit;
}

/**
 * Create a secure remember_me token, store its hash in DB, set a 90-day cookie.
 */
function _issue_remember_me_token(int $userId, $db): void {
    try {
        $token  = bin2hex(random_bytes(32)); // 64-char hex token
        $hash   = hash('sha256', $token);
        $expiry = date('Y-m-d H:i:s', strtotime('+90 days'));

        // Clean old tokens for this user (keep at most 5 devices)
        $db->execute(
            "DELETE FROM remember_tokens WHERE user_id = ?
             AND id NOT IN (
                 SELECT id FROM (SELECT id FROM remember_tokens WHERE user_id = ? ORDER BY expires_at DESC LIMIT 4) t
             )",
            [$userId, $userId]
        );

        // Insert new token
        $db->execute(
            "INSERT INTO remember_tokens (user_id, token_hash, expires_at) VALUES (?, ?, ?)",
            [$userId, $hash, $expiry]
        );

        // Set cookie
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

        setcookie('remember_me', $token, [
            'expires'  => strtotime('+90 days'),
            'path'     => '/',
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    } catch (Exception $e) {
        error_log("Remember Me Error: " . $e->getMessage());
    }
}
?>
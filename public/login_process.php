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
        } elseif ($user['role_level'] == 1) { // Cashier
            $redirect = "$urlPrefix/{$user['subdomain']}/pos/pos";
        } else {
            // Tenant Admin / Manager -> POS Terminal
            $redirect = "$urlPrefix/{$user['subdomain']}/pos/pos";
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
} catch (Throwable $e) {
    error_log("Login Error: " . $e->getMessage());
    if ($isAjax) {
        echo json_encode(['success' => false, 'error' => 'System error: ' . $e->getMessage()]);
        exit;
    }
    header("Location: $urlPrefix/login.php?error=" . urlencode('System error occurred: ' . $e->getMessage()));
    exit;
}

/**
 * Create a secure remember_me token, store its hash in DB,
 * encrypt the raw token with AES-256-GCM, then set a 90-day cookie.
 */
function _issue_remember_me_token(int $userId, $db): void {
    try {
        require_once __DIR__ . '/../core/classes/CookieCrypt.php';

        // Auto-create table if not exists
        $db->execute("CREATE TABLE IF NOT EXISTS `remember_tokens` (
            `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id`    INT UNSIGNED NOT NULL,
            `token_hash` VARCHAR(64)  NOT NULL,
            `expires_at` DATETIME     NOT NULL,
            `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_token` (`token_hash`),
            KEY `idx_user` (`user_id`),
            KEY `idx_expires` (`expires_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $token  = bin2hex(random_bytes(32)); // 64-char hex — raw plaintext token
        $hash   = hash('sha256', $token);
        $expiry = date('Y-m-d H:i:s', strtotime('+90 days'));

        // Clean expired tokens and limit total active devices per user to 5
        $db->execute("DELETE FROM remember_tokens WHERE expires_at < NOW()");
        $userTokens = $db->fetchAll("SELECT id FROM remember_tokens WHERE user_id = ? ORDER BY id DESC", [$userId]);
        if (count($userTokens) >= 5) {
            $oldIds = array_column(array_slice($userTokens, 4), 'id');
            if (!empty($oldIds)) {
                $inClause = implode(',', array_map('intval', $oldIds));
                $db->execute("DELETE FROM remember_tokens WHERE id IN ($inClause)");
            }
        }

        // Insert new token (only the SHA-256 hash is stored — never the plaintext)
        $db->execute(
            "INSERT INTO remember_tokens (user_id, token_hash, expires_at) VALUES (?, ?, ?)",
            [$userId, $hash, $expiry]
        );

        // ── Encrypt the cookie value before sending ───────────────────────────
        $crypt  = CookieCrypt::fromConfig();
        $sealed = $crypt->seal($token); // AES-256-GCM + HMAC-SHA256

        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
            || (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on')
            || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

        setcookie('remember_me', $sealed, [
            'expires'  => strtotime('+90 days'),
            'path'     => '/',
            'secure'   => $isHttps,
            'httponly' => true,   // JS cannot read this cookie
            'samesite' => 'Lax',
        ]);
    } catch (Throwable $e) {
        error_log("Remember Me Error: " . $e->getMessage());
    }
}
?>
<?php
// public/login_process.php
require_once __DIR__ . '/../core/bootstrap_session.php';
require_once __DIR__ . '/../core/classes/Database.php';
require_once __DIR__ . '/../core/classes/Auth.php';
require_once __DIR__ . '/../core/helpers/url.php';

// Dynamic URL Prefix
$urlPrefix = mc_base_path();

$isAjax = isset($_POST['ajax']);

// ── Login Brute-Force Protection ─────────────────────────────────────────────
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_MINUTES', 30);

/**
 * Ensure the login_attempts table exists.
 */
function _ensure_login_attempts_table($db): void {
    // Use exec() directly for DDL — PDO::prepare() on CREATE TABLE
    // can silently fail on some MySQL/MariaDB configurations.
    $db->getConnection()->exec("CREATE TABLE IF NOT EXISTS `login_attempts` (
        `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `username`    VARCHAR(255) NOT NULL,
        `ip_address`  VARCHAR(45)  NOT NULL DEFAULT '',
        `attempted_at` DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_user_ip` (`username`, `ip_address`),
        KEY `idx_attempted_at` (`attempted_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

/**
 * Return lockout info for a username/IP combination.
 * Checks BOTH per-username AND per-IP to prevent bypass by switching usernames.
 * Returns ['locked' => bool, 'remaining_seconds' => int, 'attempts' => int]
 */
function _get_login_lockout_info($db, string $username, string $ip): array {
    try {
        _ensure_login_attempts_table($db);

        // Clean up old attempts beyond the lockout window
        $db->execute(
            "DELETE FROM login_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL ? MINUTE)",
            [LOGIN_LOCKOUT_MINUTES]
        );

        // 1. Check per-username attempts
        $rowUser = $db->fetchOne(
            "SELECT COUNT(*) as cnt, MAX(attempted_at) as last_attempt
             FROM login_attempts
             WHERE username = ? AND attempted_at >= DATE_SUB(NOW(), INTERVAL ? MINUTE)",
            [$username, LOGIN_LOCKOUT_MINUTES]
        );

        // 2. Check per-IP attempts (catches username-switching brute force)
        $rowIp = $db->fetchOne(
            "SELECT COUNT(*) as cnt, MAX(attempted_at) as last_attempt
             FROM login_attempts
             WHERE ip_address = ? AND attempted_at >= DATE_SUB(NOW(), INTERVAL ? MINUTE)",
            [$ip, LOGIN_LOCKOUT_MINUTES]
        );

        $attemptsUser = (int)($rowUser['cnt'] ?? 0);
        $attemptsIp   = (int)($rowIp['cnt']   ?? 0);

        // Lock if EITHER username OR IP has exceeded the threshold
        $locked = ($attemptsUser >= LOGIN_MAX_ATTEMPTS) || ($attemptsIp >= LOGIN_MAX_ATTEMPTS);

        if ($locked) {
            // Use whichever triggered the lock to calculate remaining time
            $lastAttemptUser = !empty($rowUser['last_attempt']) ? strtotime($rowUser['last_attempt']) : 0;
            $lastAttemptIp   = !empty($rowIp['last_attempt'])   ? strtotime($rowIp['last_attempt'])   : 0;
            $lastAttempt     = max($lastAttemptUser, $lastAttemptIp);
            $lockoutEnds     = $lastAttempt + (LOGIN_LOCKOUT_MINUTES * 60);
            $remaining       = max(0, $lockoutEnds - time());
            $attempts        = max($attemptsUser, $attemptsIp);
            return ['locked' => true, 'remaining_seconds' => $remaining, 'attempts' => $attempts];
        }

        // Return the higher attempt count so the UI shows the correct "X remaining"
        $attempts = max($attemptsUser, $attemptsIp);
        return ['locked' => false, 'remaining_seconds' => 0, 'attempts' => $attempts];
    } catch (Throwable $e) {
        error_log("Login lockout check error: " . $e->getMessage());
        return ['locked' => false, 'remaining_seconds' => 0, 'attempts' => 0];
    }
}

/**
 * Record a failed login attempt.
 */
function _record_failed_attempt($db, string $username, string $ip): void {
    try {
        _ensure_login_attempts_table($db);
        $db->execute(
            "INSERT INTO login_attempts (username, ip_address, attempted_at) VALUES (?, ?, NOW())",
            [$username, $ip]
        );
    } catch (Throwable $e) {
        error_log("Record login attempt error: " . $e->getMessage());
    }
}

/**
 * Clear all login attempts for a username (called on successful login).
 */
function _clear_login_attempts($db, string $username): void {
    try {
        _ensure_login_attempts_table($db);
        $db->execute("DELETE FROM login_attempts WHERE username = ?", [$username]);
    } catch (Throwable $e) {
        error_log("Clear login attempts error: " . $e->getMessage());
    }
}

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

    // ── Brute-force check ────────────────────────────────────────────────────
    $clientIp   = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $clientIp   = trim(explode(',', $clientIp)[0]); // first IP if behind proxy
    $lockoutInfo = _get_login_lockout_info($db, $username, $clientIp);

    if ($lockoutInfo['locked']) {
        $remainingMin = (int)ceil($lockoutInfo['remaining_seconds'] / 60);
        $remainingSec = (int)$lockoutInfo['remaining_seconds'];
        $errorMsg = 'Account temporarily locked. Too many failed login attempts. Please try again in ' . $remainingMin . ' minute(s).';

        if ($isAjax) {
            echo json_encode([
                'success'          => false,
                'error'            => $errorMsg,
                'locked'           => true,
                'remaining_seconds'=> $remainingSec,
                'remaining_minutes'=> $remainingMin,
            ]);
            exit;
        }
        header("Location: $urlPrefix/login.php?error=" . urlencode($errorMsg) . "&locked=1&remaining=" . $remainingSec);
        exit;
    }

    $user = $db->fetchOne(
        "SELECT u.*, t.subdomain, r.name as role_name, r.level as role_level
         FROM users u
         JOIN tenants t ON u.tenant_id = t.id
         JOIN roles r ON u.role_id = r.id
         WHERE u.username = ? AND u.status = 'active' AND t.status = 'active'",
        [$username]
    );

    if ($user && password_verify($password, $user['password_hash'])) {
        // ── Successful login: clear any previous failed attempts ──────────────
        _clear_login_attempts($db, $username);

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
        // ── Failed login: record the attempt ────────────────────────────────
        _record_failed_attempt($db, $username, $clientIp);

        // Re-check how many attempts remain
        $newLockoutInfo  = _get_login_lockout_info($db, $username, $clientIp);
        $attemptsUsed    = $newLockoutInfo['attempts'];
        $attemptsLeft    = max(0, LOGIN_MAX_ATTEMPTS - $attemptsUsed);

        if ($newLockoutInfo['locked']) {
            $remainingMin = (int)ceil($newLockoutInfo['remaining_seconds'] / 60);
            $remainingSec = (int)$newLockoutInfo['remaining_seconds'];
            $errorMsg = 'Account locked for ' . LOGIN_LOCKOUT_MINUTES . ' minutes due to too many failed login attempts.';
        } else {
            $remainingSec = 0;
            $errorMsg = 'Invalid username or password. ' . $attemptsLeft . ' attempt(s) remaining before lockout.';
        }

        if ($isAjax) {
            echo json_encode([
                'success'          => false,
                'error'            => $errorMsg,
                'locked'           => $newLockoutInfo['locked'],
                'attempts_left'    => $attemptsLeft,
                'remaining_seconds'=> $remainingSec,
            ]);
            exit;
        }
        $query = "error=" . urlencode($errorMsg);
        if ($newLockoutInfo['locked']) {
            $query .= "&locked=1&remaining=" . $remainingSec;
        }
        header("Location: $urlPrefix/login.php?" . $query);
        exit;
    }
} catch (Throwable $e) {
    error_log("Login Error: " . $e->getMessage());
    if ($isAjax) {
        echo json_encode(['success' => false, 'error' => 'System error occurred']);
        exit;
    }
    header("Location: $urlPrefix/login.php?error=" . urlencode('System error occurred'));
    exit;
}

/**
 * Create a secure remember_me token, store its hash in DB,
 * encrypt the raw token with AES-256-GCM, then set a 90-day cookie.
 */
function _issue_remember_me_token(int $userId, $db): void {
    try {
        require_once __DIR__ . '/../core/classes/CookieCrypt.php';

        // Use exec() for DDL — prepare() on CREATE TABLE can fail silently
        $db->getConnection()->exec("CREATE TABLE IF NOT EXISTS `remember_tokens` (
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

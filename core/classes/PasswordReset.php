<?php
// core/classes/PasswordReset.php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../helpers/url.php';

class PasswordReset {
    private const TOKEN_TTL_SECONDS = 3600;

    public static function ensureTable(): void {
        $db = Database::getInstance();
        $db->query("
            CREATE TABLE IF NOT EXISTS password_resets (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                token_hash CHAR(64) NOT NULL,
                expires_at DATETIME NOT NULL,
                used_at DATETIME NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                UNIQUE KEY unique_password_reset_token (token_hash),
                INDEX idx_password_reset_user (user_id),
                INDEX idx_password_reset_expires (expires_at)
            )
        ");
    }

    public static function requestReset(string $identity): ?array {
        self::ensureTable();

        $identity = trim($identity);
        if ($identity === '') {
            return null;
        }

        $db = Database::getInstance();
        $user = $db->fetchOne(
            "SELECT u.id, u.username, u.email, t.subdomain
             FROM users u
             JOIN tenants t ON u.tenant_id = t.id
             WHERE (u.username = ? OR u.email = ?)
               AND u.status = 'active'
               AND t.status = 'active'
             LIMIT 1",
            [$identity, $identity]
        );

        if (!$user) {
            return null;
        }

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $ttlMinutes = (int) (self::TOKEN_TTL_SECONDS / 60);

        $db->query(
            "UPDATE password_resets SET used_at = NOW() WHERE user_id = ? AND used_at IS NULL",
            [$user['id']]
        );

        $db->query(
            "INSERT INTO password_resets (user_id, token_hash, expires_at)
             VALUES (?, ?, DATE_ADD(NOW(), INTERVAL {$ttlMinutes} MINUTE))",
            [$user['id'], $tokenHash]
        );

        $resetUrl = mc_url('public/reset_password.php?token=' . urlencode($token), true);
        $emailSent = self::sendResetEmail($user['email'], $user['username'], $resetUrl);
        if (self::shouldExposeResetLink() || getenv('MC_LOG_RESET_LINKS') === '1') {
            self::logResetLink($user, $resetUrl, $emailSent);
        }

        return [
            'user' => $user,
            'reset_url' => $resetUrl,
            'email_sent' => $emailSent,
        ];
    }

    public static function findValidToken(string $token): ?array {
        if (!preg_match('/^[a-f0-9]{64}$/i', $token)) {
            return null;
        }

        self::ensureTable();
        $db = Database::getInstance();
        $tokenHash = hash('sha256', $token);

        $row = $db->fetchOne(
            "SELECT pr.*, u.username, u.email
             FROM password_resets pr
             JOIN users u ON pr.user_id = u.id
             JOIN tenants t ON u.tenant_id = t.id
             WHERE pr.token_hash = ?
               AND pr.used_at IS NULL
               AND pr.expires_at > NOW()
               AND u.status = 'active'
               AND t.status = 'active'
             LIMIT 1",
            [$tokenHash]
        );

        return $row ?: null;
    }

    public static function resetPassword(string $token, string $password): bool {
        $reset = self::findValidToken($token);
        if (!$reset) {
            return false;
        }

        $db = Database::getInstance();
        $pdo = $db->getConnection();

        try {
            $pdo->beginTransaction();
            $db->query(
                "UPDATE users SET password_hash = ? WHERE id = ?",
                [password_hash($password, PASSWORD_DEFAULT), $reset['user_id']]
            );
            $db->query(
                "UPDATE password_resets SET used_at = NOW() WHERE user_id = ? AND used_at IS NULL",
                [$reset['user_id']]
            );
            $pdo->commit();
            return true;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Password reset failed: ' . $e->getMessage());
            return false;
        }
    }

    public static function shouldExposeResetLink(): bool {
        if (getenv('MC_SHOW_RESET_LINKS') === '1') {
            return true;
        }

        $host = strtolower($_SERVER['HTTP_HOST'] ?? '');
        $host = trim($host, '[]');
        $hostWithoutPort = preg_replace('/:\d+$/', '', $host);

        return in_array($hostWithoutPort, ['localhost', '127.0.0.1', '::1'], true)
            || strpos($hostWithoutPort, '192.168.') === 0
            || strpos($hostWithoutPort, '10.') === 0;
    }

    private static function sendResetEmail(string $email, string $username, string $resetUrl): bool {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $subject = 'Reset your Mekong POS password';
        $message = "Hi {$username},\n\n"
            . "Use this secure link to reset your password. The link expires in 1 hour:\n\n"
            . $resetUrl . "\n\n"
            . "If you did not request this, you can ignore this email.";

        $host = preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? 'localhost');
        $from = 'no-reply@' . preg_replace('/[^a-z0-9.-]/i', '', $host ?: 'localhost');
        $headers = "From: Mekong POS <{$from}>\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n";

        return @mail($email, $subject, $message, $headers);
    }

    private static function logResetLink(array $user, string $resetUrl, bool $emailSent): void {
        $logDir = dirname(__DIR__, 2) . '/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0775, true);
        }

        $line = sprintf(
            "[%s] user_id=%s username=%s email=%s email_sent=%s reset_url=%s%s",
            date('c'),
            $user['id'],
            $user['username'],
            $user['email'],
            $emailSent ? 'yes' : 'no',
            $resetUrl,
            PHP_EOL
        );

        @file_put_contents($logDir . '/password_reset_links.log', $line, FILE_APPEND | LOCK_EX);
    }
}
?>

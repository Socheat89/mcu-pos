<?php
// admin/settings.php
require_once __DIR__ . '/../middleware/SuperAdminMiddleware.php';
SuperAdminMiddleware::handle();

$message = '';
$error = '';
$localFile = dirname(__DIR__) . '/config/telegram.local.php';

// Handle save settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_settings') {
    $botToken = trim($_POST['telegram_bot_token'] ?? '');

    try {
        // Store plain token in git-ignored local config file.
        // The file is already protected server-side by .htaccess (denies direct HTTP access).
        // Encryption at the DB layer is handled separately when writing to tenant_telegram_config.
        $safe    = addslashes($botToken);
        $content = "<?php\n// Git-ignored local Telegram bot token.\n// This file is blocked from direct HTTP access by .htaccess.\nreturn '{$safe}';\n";

        if (file_put_contents($localFile, $content) === false) {
            throw new Exception('Cannot write to config/telegram.local.php — check server file permissions.');
        }

        $message = 'Telegram bot token saved successfully!';
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

// Fetch current value
$currentToken = '';
if (is_file($localFile)) {
    $currentToken = (string) (require $localFile);
}

include 'header.php';
?>

<div class="header">
    <h1 class="page-title">System Settings</h1>
</div>

<?php if ($message): ?>
    <div class="card" style="background: #d1fae5; color: #065f46; border-color: #34d399; padding: 1rem; margin-bottom: 2rem;">
        <i class="ph-bold ph-check-circle"></i> <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="card" style="background: #fee2e2; color: #991b1b; border-color: #fecaca; padding: 1rem; margin-bottom: 2rem;">
        <i class="ph-bold ph-warning-circle"></i> <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<div class="card" style="max-width: 620px;">
    <h3 style="margin-bottom: 0.5rem;"><i class="ph-bold ph-robot"></i> Global Telegram Bot</h3>
    <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1.5rem;">
        This token is used as the system-wide fallback bot for all tenants who have not paired their own bot.
        The value is stored in a server-side config file that is excluded from Git and blocked from direct HTTP access.
    </p>
    <form method="POST">
        <input type="hidden" name="action" value="save_settings">

        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">
                <i class="ph-bold ph-key"></i> Global Telegram Bot Token
            </label>
            <input
                type="text"
                name="telegram_bot_token"
                value="<?php echo htmlspecialchars($currentToken); ?>"
                placeholder="e.g. 1234567890:ABCdef..."
                class="form-control"
                style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; font-family: monospace; font-size: 0.9rem;"
            >
            <small style="color: var(--text-muted); display: block; margin-top: 0.35rem;">
                Get this from <strong>@BotFather</strong> on Telegram. Stored securely on the server, never committed to Git.
            </small>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-weight: 600;">
            <i class="ph-bold ph-floppy-disk"></i> Save Settings
        </button>
    </form>
</div>

<?php include 'footer.php'; ?>

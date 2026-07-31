<?php
// admin/settings.php
require_once __DIR__ . '/../middleware/SuperAdminMiddleware.php';
SuperAdminMiddleware::handle();

if (!class_exists('CookieCrypt')) {
    require_once __DIR__ . '/../core/classes/CookieCrypt.php';
}

$message = '';
$error = '';
$localFile = dirname(__DIR__) . '/config/telegram.local.php';

// Handle save settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_settings') {
    $botToken = trim($_POST['telegram_bot_token'] ?? '');

    // Encrypt bot token
    $encryptedToken = CookieCrypt::encrypt($botToken);

    try {
        // Save to config/telegram.local.php
        $content = "<?php\n// Git-ignored local Telegram configuration\nreturn '" . addslashes($encryptedToken) . "';\n";
        if (file_put_contents($localFile, $content) === false) {
            throw new Exception('Failed to write to config/telegram.local.php. Please check file permissions.');
        }

        $message = 'System settings updated successfully!';
    } catch (Exception $e) {
        $error = 'Failed to save settings: ' . $e->getMessage();
    }
}

// Fetch current values
$currentToken = '';
if (is_file($localFile)) {
    $encrypted = require $localFile;
    if (!empty($encrypted)) {
        $currentToken = CookieCrypt::decrypt($encrypted);
    }
}

include 'header.php';
?>

<div class="header">
    <h1 class="page-title">System Settings</h1>
</div>

<?php if ($message): ?>
    <div class="card" style="background: #d1fae5; color: #065f46; border-color: #34d399; padding: 1rem; margin-bottom: 2rem;">
        <i class="ph-bold ph-check-circle"></i> <?php echo $message; ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="card" style="background: #fee2e2; color: #991b1b; border-color: #fecaca; padding: 1rem; margin-bottom: 2rem;">
        <i class="ph-bold ph-warning-circle"></i> <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="card" style="max-width: 600px;">
    <h3 style="margin-bottom: 1.5rem;"><i class="ph-bold ph-gear"></i> Global Telegram Configuration</h3>
    <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1.5rem;">
        Configure the global system Telegram bot that acts as the fallback for all tenants who do not pair their own custom bot.
    </p>
    <form method="POST">
        <input type="hidden" name="action" value="save_settings">
        
        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Global Telegram Bot Token</label>
            <input type="text" name="telegram_bot_token" value="<?php echo htmlspecialchars($currentToken); ?>" placeholder="E.g., 123456789:ABCdef..." class="form-control" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px;">
            <small style="color: var(--text-muted); display: block; margin-top: 0.25rem;">Your Telegram bot token from @BotFather. This value will be stored securely using AES-256 GCM encryption.</small>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-weight: 600;">
            Save Settings
        </button>
    </form>
</div>

<?php include 'footer.php'; ?>

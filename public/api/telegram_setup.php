<?php
// public/api/telegram_setup.php
// Set up the Telegram Bot webhook so the bot can respond to /start and /chatid commands
// Run this once to register the webhook URL

header('Content-Type: application/json');

$root = dirname(__DIR__, 2);
$config = require $root . '/config/telegram.php';
$botToken = $config['bot_token'] ?? null;

if (!$botToken) {
    echo json_encode(['success' => false, 'error' => 'No bot token configured in config/telegram.php']);
    exit;
}

// Build the webhook URL
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
$webhookUrl = $scheme . '://' . $host . $basePath . '/gps_telegram_callback.php';

// Check current webhook info
$getWebhookUrl = "https://api.telegram.org/bot{$botToken}/getWebhookInfo";
$currentInfo = @file_get_contents($getWebhookUrl);
$currentData = json_decode($currentInfo, true);

// Set the webhook
$setWebhookUrl = "https://api.telegram.org/bot{$botToken}/setWebhook";
$postData = http_build_query([
    'url' => $webhookUrl,
    'allowed_updates' => json_encode(['message', 'my_chat_member']),
    'drop_pending_updates' => false
]);

$ctx = stream_context_create([
    'http' => [
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => $postData,
        'ignore_errors' => true
    ]
]);
$result = @file_get_contents($setWebhookUrl, false, $ctx);
$resultData = json_decode($result, true);

// Get bot info
$botInfo = @file_get_contents("https://api.telegram.org/bot{$botToken}/getMe");
$botData = json_decode($botInfo, true);

echo json_encode([
    'success'      => $resultData['ok'] ?? false,
    'description'  => $resultData['description'] ?? 'Unknown',
    'webhook_url'  => $webhookUrl,
    'bot_info'     => $botData['result'] ?? null,
    'previous'     => $currentData['result']['url'] ?? 'none',
    'instructions' => [
        'bot_username' => $botData['result']['username'] ?? 'unknown',
        'how_to_use' => [
            '1. Add @' . ($botData['result']['username'] ?? 'your_bot') . ' to your Telegram group as ADMIN',
            '2. Bot will auto-send a 6-digit setup code in the group',
            '3. Go to MCU POS → GPS Tracking → Telegram Setup',
            '4. Enter the 6-digit code → Done! ✅',
            '5. No Chat IDs or API calls needed!',
        ]
    ]
], JSON_PRETTY_PRINT);
exit;

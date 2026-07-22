<?php
// public/api/telegram_notify.php
// VERSION: V5_FINAL_TELEGRAM_ONLY
error_reporting(E_ALL);
ini_set('display_errors', 0); // Suppress errors in JSON output

// --- CONFIGURATION ---
$root = dirname(__DIR__, 2);
require_once $root . '/core/helpers/api.php';

mc_api_preflight('POST, OPTIONS');

$tgConfig = require $root . '/config/telegram.php';

$TELEGRAM_BOT_TOKEN = $tgConfig['bot_token'] ?? '';
$TELEGRAM_CHAT_ID = $tgConfig['chat_id'] ?? '';
$callbackUrl = $tgConfig['callback_url'] ?? '';
$webhookSecret = $tgConfig['webhook_secret'] ?? '';

if (empty($callbackUrl)) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/public/api/telegram_notify.php'));
    $callbackUrl = $scheme . '://' . $host . rtrim($scriptDir, '/') . '/telegram_callback.php';
}
// ---------------------

// 1. Get POST Data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    mc_json_error('No data received', 400);
}

$md5 = $data['md5'] ?? '';
$amount = $data['amount'] ?? '0.00';
$plan = $data['plan'] ?? 'Unknown';
$method = $data['method'] ?? 'bakong';
$type = $data['type'] ?? 'registration'; // registration or renewal
$businessName = $data['business_name'] ?? 'New Customer';
$tenantId = $data['tenant_id'] ?? null;

if (empty($md5) || !preg_match('/^[A-Za-z0-9._:-]{6,128}$/', (string) $md5)) {
    mc_json_error('Payment reference missing', 400);
}

$amount = max(0, (float) $amount);
$plan = substr(preg_replace('/[^a-z0-9 _-]/i', '', (string) $plan), 0, 80);
$method = substr(preg_replace('/[^a-z0-9 _-]/i', '', (string) $method), 0, 40);
$type = in_array($type, ['registration', 'renewal'], true) ? $type : 'registration';
$businessName = substr(strip_tags((string) $businessName), 0, 120);

// 2. Save Transaction as PENDING (Main Log)
require_once __DIR__ . '/TransactionLogger.php';

$txData = [
    'amount' => $amount,
    'plan' => $plan,
    'method' => $method,
    'status' => 'PENDING',
    'timestamp' => time(),
    'ip' => $_SERVER['REMOTE_ADDR'],
    'type' => $type,
    'business_name' => $businessName,
    'tenant_id' => $tenantId
];

TransactionLogger::save($md5, $txData);

// 2b. Database Backup (Secondary Log)
try {
    require_once __DIR__ . '/../../core/classes/Database.php';
    $db = Database::getInstance();
    $db->insert('payment_approvals', [
        'reference_id' => $md5,
        'plan' => $plan,
        'amount' => $amount,
        'status' => 'pending'
    ]);
} catch (Exception $e) {}

// 3. Send Telegram Notification
if (empty($TELEGRAM_BOT_TOKEN)) {
    mc_json(['success' => true, 'status' => 'PENDING', 'message' => 'Payment is pending review.']);
}

if (stripos($callbackUrl, 'https://') === 0) {
    ensureWebhookRegistered($TELEGRAM_BOT_TOKEN, $callbackUrl, $webhookSecret);
}

$title = ($type === 'renewal') ? "🔄 Subscription Renewal Request" : "🔔 New Registration Payment";

$message = "<b>$title</b>\n\n";
$message .= "<b>🏢 Business:</b> " . htmlspecialchars($businessName) . "\n";
$message .= "<b>💰 Amount:</b> $" . htmlspecialchars($amount) . "\n";
$message .= "<b>📦 Plan:</b> " . htmlspecialchars(ucfirst($plan)) . "\n";
$message .= "<b>💳 Method:</b> " . htmlspecialchars(ucfirst($method)) . "\n";
$message .= "<b>🔑 Ref:</b> <code>" . htmlspecialchars($md5) . "</code>\n";
$message .= "<b>⏰ Time:</b> " . date('Y-m-d H:i:s') . "\n\n";
$message .= "Please verify and approve this transaction.";

// Inline callback buttons allow direct approval inside Telegram
$keyboard = [
    'inline_keyboard' => [
        [
            ['text' => '✅ Approve', 'callback_data' => "approve::$md5"],
            ['text' => '❌ Reject', 'callback_data' => "reject::$md5"]
        ]
    ]
];

$response = telegramApiRequest($TELEGRAM_BOT_TOKEN, 'sendMessage', [
    'chat_id' => $TELEGRAM_CHAT_ID,
    'text' => $message,
    'parse_mode' => 'HTML',
    'reply_markup' => json_encode($keyboard)
]);

if (isset($response['ok']) && $response['ok']) {
    mc_json(['success' => true, 'status' => 'WAITING_APPROVAL']);
} else {
    error_log('Telegram payment notification failed: ' . json_encode($response));
    mc_json_error('Unable to notify payment reviewer', 502);
}

function telegramApiRequest(string $token, string $method, array $params = []): array {
    $url = "https://api.telegram.org/bot$token/$method";
    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($params),
            'ignore_errors' => true
        ]
    ];
    $response = @file_get_contents($url, false, stream_context_create($options));
    if ($response === false) {
        $error = error_get_last();
        return ['ok' => false, 'error' => $error['message'] ?? 'Network error'];
    }
    return json_decode($response, true) ?: ['ok' => false, 'error' => 'Invalid JSON response'];
}

function ensureWebhookRegistered(string $token, string $expectedUrl, string $secret = ''): void {
    static $checked = false;
    if ($checked || empty($token) || empty($expectedUrl)) {
        return;
    }

    $checked = true;
    $info = telegramApiRequest($token, 'getWebhookInfo');
    $currentUrl = $info['result']['url'] ?? '';
    if (!empty($currentUrl) && rtrim($currentUrl, '/') === rtrim($expectedUrl, '/')) {
        return;
    }

    $params = ['url' => $expectedUrl];
    if ($secret !== '') {
        $params['secret_token'] = $secret;
    }

    telegramApiRequest($token, 'setWebhook', $params);
}
?>

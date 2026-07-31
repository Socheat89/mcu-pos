<?php
// public/api/gps_claim_code.php
// Simple API: Tenant claims a Telegram setup code to link their group
// POST { setup_code: "ABC123" }

$root = dirname(__DIR__, 2);
require_once $root . '/core/helpers/api.php';

mc_api_preflight('POST, OPTIONS');

try {

require_once $root . '/core/bootstrap_session.php';
require_once $root . '/core/classes/Database.php';
require_once $root . '/core/classes/Tenant.php';
require_once $root . '/core/classes/Auth.php';

if (!Auth::check()) {
    mc_json_error('Authentication required', 401);
}

if (!Auth::isTenantAdmin()) {
    mc_json_error('Admin access required', 403);
}

$db = Database::getInstance();
$tenantId = Tenant::getId();

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['setup_code'])) {
    mc_json_error('Missing setup_code', 400);
}

$setupCode = strtoupper(trim($input['setup_code']));

if (strlen($setupCode) !== 6) {
    mc_json_error('លេខកូដត្រូវតែ ៦ ខ្ទង់ / Code must be 6 characters', 400);
}

// Find the pending link
$pending = $db->fetchOne(
    "SELECT * FROM telegram_pending_links 
     WHERE setup_code = ? AND claimed_by_tenant_id IS NULL AND expires_at > NOW()
     ORDER BY created_at DESC LIMIT 1",
    [$setupCode]
);

if (!$pending) {
    mc_json([
        'success' => false, 
        'error' => 'លេខកូដមិនត្រឹមត្រូវ ឬផុតកំណត់ហើយ។ សូមវាយ /code ក្នុងក្រុម Telegram ដើម្បីទទួលបានលេខកូដថ្មី។ / Invalid or expired code. Type /code in your Telegram group.'
    ], 404);
}

require_once $root . '/core/classes/TelegramBot.php';
$sysConfig = TelegramBot::getSystemConfig();
$botToken = $sysConfig['bot_token'] ?? ($pending['bot_token'] ?? '');
if ($botToken === '') {
    mc_json_error('Telegram bot is not configured', 500);
}

// Claim it
$existingConfig = $db->fetchOne(
    "SELECT id FROM tenant_telegram_config WHERE tenant_id = ?",
    [$tenantId]
);

require_once $root . '/core/classes/CookieCrypt.php';
$configData = [
    'chat_id'    => $pending['chat_id'],
    'chat_title' => $pending['chat_title'],
    'setup_code' => $setupCode,
    'bot_token'  => $botToken,
    'is_active'  => 1,
];

if ($existingConfig) {
    $db->update('tenant_telegram_config', $configData, 'id = ?', [$existingConfig['id']]);
} else {
    $configData['tenant_id'] = $tenantId;
    $db->insert('tenant_telegram_config', $configData);
}

// Mark as claimed
$db->update('telegram_pending_links', [
    'claimed_by_tenant_id' => $tenantId,
    'claimed_at'           => date('Y-m-d H:i:s')
], 'id = ?', [$pending['id']]);

// Confirm to group
$tenant = $db->fetchOne("SELECT name FROM tenants WHERE id = ?", [$tenantId]);
$msg = "✅ <b>បានភ្ជាប់ដោយជោគជ័យ! / Connected!</b>\n\n";
$msg .= "🏪 ហាង / Store: " . ($tenant['name'] ?? 'N/A') . "\n";
$msg .= "📋 ក្រុមនេះនឹងទទួលបានការជូនដំណឹង GPS និងរបាយការណ៍លក់។\n";
$msg .= "🟢 ប្រព័ន្ធបានត្រៀមរួចរាល់!";

@file_get_contents("https://api.telegram.org/bot{$botToken}/sendMessage?" . http_build_query([
    'chat_id' => $pending['chat_id'], 'text' => $msg, 'parse_mode' => 'HTML'
]));

mc_json([
    'success'    => true,
    'message'    => '✅ បានភ្ជាប់ដោយជោគជ័យ! ក្រុម Telegram របស់អ្នកបានតភ្ជាប់ហើយ។ / Connected successfully!',
    'chat_title' => $pending['chat_title'],
    'chat_id'    => $pending['chat_id']
]);

} catch (Throwable $e) {
    mc_log_exception('GPS claim code error', $e);
    mc_json_error('Server error', 500);
}

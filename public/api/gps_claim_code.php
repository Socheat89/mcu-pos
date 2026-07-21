<?php
// public/api/gps_claim_code.php
// Simple API: Tenant claims a Telegram setup code to link their group
// POST { setup_code: "ABC123" }
// This avoids the tenant ever needing to know Chat IDs

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

$root = dirname(__DIR__, 2);
require_once $root . '/core/bootstrap_session.php';
require_once $root . '/core/classes/Database.php';
require_once $root . '/core/classes/Tenant.php';
require_once $root . '/core/classes/Auth.php';

if (!Auth::check()) {
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

if (!Auth::isTenantAdmin()) {
    echo json_encode(['success' => false, 'error' => 'Admin access required']);
    exit;
}

$db = Database::getInstance();
$tenantId = Tenant::getId();

$input = json_decode(file_get_contents('php://input'), true);
$setupCode = strtoupper(trim($input['setup_code'] ?? ''));

if (strlen($setupCode) !== 6) {
    echo json_encode(['success' => false, 'error' => 'លេខកូដត្រូវតែ ៦ ខ្ទង់ / Code must be 6 characters']);
    exit;
}

// Find the pending link
$pending = $db->fetchOne(
    "SELECT * FROM telegram_pending_links 
     WHERE setup_code = ? AND claimed_by_tenant_id IS NULL AND expires_at > NOW()
     ORDER BY created_at DESC LIMIT 1",
    [$setupCode]
);

if (!$pending) {
    echo json_encode([
        'success' => false, 
        'error' => 'លេខកូដមិនត្រឹមត្រូវ ឬផុតកំណត់ហើយ។ សូមវាយ /code ក្នុងក្រុម Telegram ដើម្បីទទួលបានលេខកូដថ្មី។'
    ]);
    exit;
}

// Claim it! Save/update tenant_telegram_config
$existingConfig = $db->fetchOne(
    "SELECT id FROM tenant_telegram_config WHERE tenant_id = ?",
    [$tenantId]
);

$configData = [
    'chat_id'    => $pending['chat_id'],
    'chat_title' => $pending['chat_title'],
    'setup_code' => $setupCode,
    'is_active'  => 1,
];

if ($existingConfig) {
    $db->update('tenant_telegram_config', $configData, 'id = ?', [$existingConfig['id']]);
} else {
    $configData['tenant_id'] = $tenantId;
    $configData['bot_token'] = $pending['bot_token'];
    $db->insert('tenant_telegram_config', $configData);
}

// Mark as claimed
$db->update('telegram_pending_links', [
    'claimed_by_tenant_id' => $tenantId,
    'claimed_at'           => date('Y-m-d H:i:s')
], 'id = ?', [$pending['id']]);

// Send confirmation to the group
$botToken = $pending['bot_token'];
if ($botToken) {
    $tenant = $db->fetchOne("SELECT name FROM tenants WHERE id = ?", [$tenantId]);
    $msg = "✅ <b>បានភ្ជាប់ដោយជោគជ័យ!</b>\n\n";
    $msg .= "🏪 ហាង៖ " . ($tenant['name'] ?? 'Store') . "\n";
    $msg .= "📋 ក្រុមនេះនឹងទទួលបាន៖\n";
    $msg .= "  📍 ការជូនដំណឹងទីតាំង GPS\n";
    $msg .= "  💰 របាយការណ៍លក់\n";
    $msg .= "  🔔 ការជូនដំណឹងពេលបើក/បិទវគ្គលក់\n\n";
    $msg .= "🟢 ប្រព័ន្ធបានត្រៀមរួចរាល់!";

    $ctx = stream_context_create(['http' => [
        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
        'method' => 'POST',
        'content' => http_build_query(['chat_id' => $pending['chat_id'], 'text' => $msg, 'parse_mode' => 'HTML']),
        'ignore_errors' => true
    ]]);
    @file_get_contents("https://api.telegram.org/bot{$botToken}/sendMessage", false, $ctx);
}

echo json_encode([
    'success'    => true,
    'message'    => '✅ បានភ្ជាប់ដោយជោគជ័យ! ក្រុម Telegram របស់អ្នកបានតភ្ជាប់ហើយ។',
    'chat_title' => $pending['chat_title'],
    'chat_id'    => $pending['chat_id']
]);
exit;

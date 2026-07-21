<?php
// public/api/gps_telegram_config.php
// API for managing tenant-specific Telegram bot configuration

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

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

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Get current config
    $config = $db->fetchOne(
        "SELECT * FROM tenant_telegram_config WHERE tenant_id = ?",
        [$tenantId]
    );

    // If no custom config, return system defaults
    if (!$config) {
        $sysConfig = require $root . '/config/telegram.php';
        $config = [
            'bot_token'            => $sysConfig['bot_token'] ?? '',
            'chat_id'              => $sysConfig['chat_id'] ?? '',
            'notify_session_open'  => 1,
            'notify_session_close' => 1,
            'notify_sales_report'  => 1,
            'notify_gps_start'     => 1,
            'notify_gps_stop'      => 1,
            'is_active'            => 1,
        ];
    }

    echo json_encode(['success' => true, 'config' => $config]);

} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
        exit;
    }

    $existing = $db->fetchOne(
        "SELECT id FROM tenant_telegram_config WHERE tenant_id = ?",
        [$tenantId]
    );

    $data = [
        'bot_token'            => $input['bot_token'] ?? null,
        'chat_id'              => $input['chat_id'] ?? null,
        'notify_session_open'  => isset($input['notify_session_open']) ? (int)$input['notify_session_open'] : 1,
        'notify_session_close' => isset($input['notify_session_close']) ? (int)$input['notify_session_close'] : 1,
        'notify_sales_report'  => isset($input['notify_sales_report']) ? (int)$input['notify_sales_report'] : 1,
        'notify_gps_start'     => isset($input['notify_gps_start']) ? (int)$input['notify_gps_start'] : 1,
        'notify_gps_stop'      => isset($input['notify_gps_stop']) ? (int)$input['notify_gps_stop'] : 1,
        'is_active'            => isset($input['is_active']) ? (int)$input['is_active'] : 1,
    ];

    if ($existing) {
        $db->update('tenant_telegram_config', $data, 'id = ?', [$existing['id']]);
    } else {
        $data['tenant_id'] = $tenantId;
        $db->insert('tenant_telegram_config', $data);
    }

    echo json_encode(['success' => true, 'message' => 'Telegram configuration saved']);

} else {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}

exit;

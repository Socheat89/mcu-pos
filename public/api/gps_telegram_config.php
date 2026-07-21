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

try {

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

    // Build data array — only include fields that are explicitly provided
    $data = [];
    $allowedFields = ['bot_token', 'chat_id', 'chat_title', 'setup_code',
        'notify_session_open', 'notify_session_close', 'notify_sales_report',
        'notify_gps_start', 'notify_gps_stop', 'is_active'];
    
    foreach ($allowedFields as $field) {
        if (array_key_exists($field, $input)) {
            $data[$field] = $input[$field];
        }
    }

    if (empty($data)) {
        echo json_encode(['success' => false, 'error' => 'No fields to update']);
        exit;
    }

    if ($existing) {
        $db->update('tenant_telegram_config', $data, 'id = ?', [$existing['id']]);
    } else {
        $data['tenant_id'] = $tenantId;
        // Only set bot_token default if creating new and not provided
        if (!isset($data['bot_token'])) {
            $sysConfig = require $root . '/config/telegram.php';
            $data['bot_token'] = $sysConfig['bot_token'] ?? '';
        }
        $db->insert('tenant_telegram_config', $data);
    }

    echo json_encode(['success' => true, 'message' => 'Telegram configuration saved']);

} else {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}

} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}

exit;

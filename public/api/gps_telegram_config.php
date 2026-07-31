<?php
// public/api/gps_telegram_config.php
// API for managing tenant-specific Telegram bot configuration.

$root = dirname(__DIR__, 2);
require_once $root . '/core/helpers/api.php';

mc_api_preflight('GET, POST, OPTIONS');

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
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        mc_json(['success' => true, 'config' => publicTelegramConfig($db, $tenantId, $root)]);
    }

    if ($method !== 'POST') {
        mc_json_error('Method not allowed', 405);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        mc_json_error('Invalid JSON', 400);
    }

    if (($input['action'] ?? '') === 'test') {
        sendTelegramTest($db, $tenantId, $root);
        mc_json(['success' => true, 'message' => 'Test notification sent']);
    }

    $existing = $db->fetchOne(
        "SELECT id FROM tenant_telegram_config WHERE tenant_id = ?",
        [$tenantId]
    );

    $data = [];
    $stringFields = ['chat_id', 'chat_title', 'setup_code'];
    $booleanFields = [
        'notify_session_open',
        'notify_session_close',
        'notify_sales_report',
        'notify_gps_start',
        'notify_gps_stop',
        'is_active'
    ];

    foreach ($stringFields as $field) {
        if (array_key_exists($field, $input)) {
            $data[$field] = substr(trim((string) $input[$field]), 0, 255);
        }
    }

    foreach ($booleanFields as $field) {
        if (array_key_exists($field, $input)) {
            $data[$field] = !empty($input[$field]) ? 1 : 0;
        }
    }

    if (empty($data)) {
        mc_json_error('No fields to update', 400);
    }

    if ($existing) {
        $db->update('tenant_telegram_config', $data, 'id = ?', [$existing['id']]);
    } else {
        $sysConfig = require $root . '/config/telegram.php';
        $data['tenant_id'] = $tenantId;
        $data['bot_token'] = $sysConfig['bot_token'] ?? '';
        $db->insert('tenant_telegram_config', $data);
    }

    mc_json(['success' => true, 'message' => 'Telegram configuration saved']);
} catch (Throwable $e) {
    mc_log_exception('GPS Telegram config error', $e);
    mc_json_error('Server error', 500);
}
function publicTelegramConfig(Database $db, int $tenantId, string $root): array {
    $config = $db->fetchOne(
        "SELECT chat_id, chat_title, setup_code, notify_session_open, notify_session_close,
                notify_sales_report, notify_gps_start, notify_gps_stop, is_active,
                CASE WHEN bot_token IS NULL OR bot_token = '' THEN 0 ELSE 1 END AS bot_configured
           FROM tenant_telegram_config
          WHERE tenant_id = ?",
        [$tenantId]
    );

    if (!$config) {
        $sysConfig = require $root . '/config/telegram.php';
        $config = [
            'chat_id'              => $sysConfig['chat_id'] ?? '',
            'chat_title'           => '',
            'setup_code'           => '',
            'notify_session_open'  => 1,
            'notify_session_close' => 1,
            'notify_sales_report'  => 1,
            'notify_gps_start'     => 1,
            'notify_gps_stop'      => 1,
            'is_active'            => 1,
            'bot_configured'       => empty($sysConfig['bot_token']) ? 0 : 1,
        ];
    }

    return $config;
}

function sendTelegramTest(Database $db, int $tenantId, string $root): void {
    $config = $db->fetchOne(
        "SELECT bot_token, chat_id FROM tenant_telegram_config WHERE tenant_id = ? AND is_active = 1",
        [$tenantId]
    );

    if (!$config || empty($config['chat_id'])) {
        require_once $root . '/core/classes/TelegramBot.php';
        $sysConfig = TelegramBot::getSystemConfig();
        $config = [
            'bot_token' => $sysConfig['bot_token'] ?? '',
            'chat_id'   => $sysConfig['chat_id'] ?? '',
        ];
    }

    require_once $root . '/core/classes/CookieCrypt.php';
    $botToken = CookieCrypt::decrypt($config['bot_token'] ?? '');

    if (empty($botToken) || empty($config['chat_id'])) {
        mc_json_error('Telegram is not configured', 400);
    }

    $message = "MCU POS test notification\n\nYour Telegram connection is working.";
    $response = telegramApiRequest($botToken, 'sendMessage', [
        'chat_id'    => $config['chat_id'],
        'text'       => $message,
        'parse_mode' => 'HTML',
    ]);

    if (empty($response['ok'])) {
        mc_json_error('Telegram test failed', 502);
    }
}

function telegramApiRequest(string $token, string $method, array $params = []): array {
    $url = "https://api.telegram.org/bot{$token}/{$method}";
    $context = stream_context_create(['http' => [
        'header'        => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'        => 'POST',
        'content'       => http_build_query($params),
        'ignore_errors' => true,
        'timeout'       => 10,
    ]]);

    $response = @file_get_contents($url, false, $context);
    if ($response === false) {
        return ['ok' => false];
    }

    return json_decode($response, true) ?: ['ok' => false];
}

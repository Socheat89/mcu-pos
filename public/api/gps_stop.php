<?php
// public/api/gps_stop.php
// API to stop GPS tracking (called when POS session closes)

$root = dirname(__DIR__, 2);
require_once $root . '/core/helpers/api.php';
mc_api_preflight('POST, OPTIONS');
require_once $root . '/core/bootstrap_session.php';
require_once $root . '/core/classes/Database.php';
require_once $root . '/core/classes/Tenant.php';
require_once $root . '/core/classes/Auth.php';

if (!Auth::check()) {
    mc_json_error('Authentication required', 401);
}

$db = Database::getInstance();
$tenantId = Tenant::getId();
$userId = Auth::user()['id'];

$trackingSession = $db->fetchOne(
    "SELECT id FROM gps_tracking_sessions
     WHERE tenant_id = ? AND user_id = ? AND status = 'active'
     ORDER BY started_at DESC LIMIT 1",
    [$tenantId, $userId]
);

if ($trackingSession) {
    $db->update(
        'gps_tracking_sessions',
        ['status' => 'stopped', 'ended_at' => date('Y-m-d H:i:s')],
        'id = ? AND tenant_id = ?',
        [$trackingSession['id'], $tenantId]
    );

    // Try to send Telegram notification
    try {
        $tgConfig = $db->fetchOne(
            "SELECT * FROM tenant_telegram_config WHERE tenant_id = ? AND is_active = 1 AND notify_gps_stop = 1",
            [$tenantId]
        );

        $botToken = $tgConfig['bot_token'] ?? null;
        $chatId = $tgConfig['chat_id'] ?? null;

        if (!$botToken || !$chatId) {
            $sysConfig = require $root . '/config/telegram.php';
            $botToken = $sysConfig['bot_token'] ?? null;
            $chatId = $sysConfig['chat_id'] ?? null;
        }

        if ($botToken && $chatId) {
            $user = Auth::user();
            $tenant = Tenant::getCurrent();
            $lastLoc = $db->fetchOne(
                "SELECT latitude, longitude FROM gps_locations WHERE tracking_session_id = ? ORDER BY recorded_at DESC LIMIT 1",
                [$trackingSession['id']]
            );

            $message = "🔴 <b>GPS Tracking Stopped</b>\n";
            $message .= "🏪 " . ($tenant['name'] ?? 'Store') . "\n";
            $message .= "👤 " . ($user['username'] ?? 'Seller') . "\n";
            if ($lastLoc) {
                $message .= "📍 <a href=\"https://maps.google.com/?q={$lastLoc['latitude']},{$lastLoc['longitude']}\">Last Location</a>\n";
            }
            $message .= "🕐 " . date('Y-m-d H:i:s');

            $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
            $data = [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML'
            ];
            $ctx = stream_context_create([
                'http' => [
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method' => 'POST',
                    'content' => http_build_query($data),
                    'ignore_errors' => true
                ]
            ]);
            @file_get_contents($url, false, $ctx);
        }
    } catch (Exception $e) {
        // Silent fail - don't break the main flow
    }
}

mc_json(['success' => true, 'message' => 'Tracking stopped']);

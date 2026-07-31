<?php
// modules/pos/controllers/GpsController.php
require_once __DIR__ . '/../../../core/classes/Database.php';
require_once __DIR__ . '/../../../core/classes/Tenant.php';
require_once __DIR__ . '/../../../core/classes/Auth.php';
require_once __DIR__ . '/../../../core/classes/TelegramBot.php';
require_once __DIR__ . '/../../../core/helpers/api.php';
require_once __DIR__ . '/../../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../../middleware/TenantMiddleware.php';

class GpsController {

    /* ========== Owner Dashboard ========== */

    public function dashboard() {
        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if (!Tenant::hasModule('pos')) {
            die('POS system not subscribed for your plan');
        }

        if (!Auth::isTenantAdmin()) {
            die('Only admin can view GPS tracking dashboard');
        }

        $db = Database::getInstance();
        $tenantId = Tenant::getId();
        $tenantName = Tenant::getCurrent()['name'] ?? 'Tenant';

        // Get all active tracking sessions for this tenant
        $activeSessions = $db->fetchAll(
            "SELECT gts.*, u.username, u.email,
                    s.name as store_name, s.code as store_code,
                    ps.opening_balance, ps.opened_at as pos_opened_at
             FROM gps_tracking_sessions gts
             JOIN users u ON gts.user_id = u.id
             LEFT JOIN stores s ON gts.store_id = s.id
             LEFT JOIN pos_sessions ps ON gts.pos_session_id = ps.id
             WHERE gts.tenant_id = ? AND gts.status = 'active'
             ORDER BY gts.started_at DESC",
            [$tenantId]
        );

        // Get latest location for each active session
        foreach ($activeSessions as &$session) {
            $lastLoc = $db->fetchOne(
                "SELECT latitude, longitude, accuracy, speed, heading, battery_level, recorded_at
                 FROM gps_locations
                 WHERE tracking_session_id = ?
                 ORDER BY recorded_at DESC LIMIT 1",
                [$session['id']]
            );
            $session['last_location'] = $lastLoc;
        }

        // Recent tracking history (last 50 sessions)
        $recentSessions = $db->fetchAll(
            "SELECT gts.*, u.username,
                    s.name as store_name,
                    ps.total_sales,
                    (SELECT COUNT(*) FROM gps_locations WHERE tracking_session_id = gts.id) as location_count
             FROM gps_tracking_sessions gts
             JOIN users u ON gts.user_id = u.id
             LEFT JOIN stores s ON gts.store_id = s.id
             LEFT JOIN pos_sessions ps ON gts.pos_session_id = ps.id
             WHERE gts.tenant_id = ?
             ORDER BY gts.started_at DESC
             LIMIT 50",
            [$tenantId]
        );

        // Telegram config for this tenant
        $telegramConfig = $db->fetchOne(
            "SELECT * FROM tenant_telegram_config WHERE tenant_id = ? AND is_active = 1",
            [$tenantId]
        );

        include __DIR__ . '/../views/gps_dashboard.php';
    }

    /* ========== API: Get live locations for dashboard (AJAX polling) ========== */

    public function apiLiveLocations() {
        mc_api_apply_cors('GET, OPTIONS');

        TenantMiddleware::handle();
        AuthMiddleware::handle();

        $db = Database::getInstance();
        $tenantId = Tenant::getId();

        $activeSessions = $db->fetchAll(
            "SELECT gts.id, gts.user_id, gts.store_id, gts.started_at,
                    u.username, s.name as store_name, s.code as store_code
             FROM gps_tracking_sessions gts
             JOIN users u ON gts.user_id = u.id
             LEFT JOIN stores s ON gts.store_id = s.id
             WHERE gts.tenant_id = ? AND gts.status = 'active'",
            [$tenantId]
        );

        $result = [];
        foreach ($activeSessions as $session) {
            $lastLoc = $db->fetchOne(
                "SELECT latitude, longitude, accuracy, speed, heading, battery_level, recorded_at
                 FROM gps_locations
                 WHERE tracking_session_id = ?
                 ORDER BY recorded_at DESC LIMIT 1",
                [$session['id']]
            );
            if ($lastLoc) {
                $result[] = array_merge($session, ['last_location' => $lastLoc]);
            }
        }

        echo json_encode(['success' => true, 'sessions' => $result]);
        exit;
    }

    /* ========== API: Get location history for a specific session ========== */

    public function apiLocationHistory() {
        mc_api_apply_cors('GET, OPTIONS');

        TenantMiddleware::handle();
        AuthMiddleware::handle();

        $db = Database::getInstance();
        $tenantId = Tenant::getId();
        $trackingId = isset($_GET['tracking_id']) ? (int)$_GET['tracking_id'] : 0;
        $limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 500) : 200;

        if (!$trackingId) {
            echo json_encode(['success' => false, 'error' => 'Missing tracking_id']);
            exit;
        }

        // Verify ownership
        $session = $db->fetchOne(
            "SELECT id FROM gps_tracking_sessions WHERE id = ? AND tenant_id = ?",
            [$trackingId, $tenantId]
        );
        if (!$session) {
            echo json_encode(['success' => false, 'error' => 'Session not found']);
            exit;
        }

        $locations = $db->fetchAll(
            "SELECT latitude, longitude, accuracy, speed, heading, battery_level, recorded_at
             FROM gps_locations
             WHERE tracking_session_id = ?
             ORDER BY recorded_at ASC
             LIMIT ?",
            [$trackingId, $limit]
        );

        echo json_encode(['success' => true, 'locations' => $locations]);
        exit;
    }

    /* ========== API: Receive GPS ping from seller's device ========== */

    public function apiTrack() {
        mc_api_apply_cors('POST, OPTIONS');

        // Must be authenticated
        AuthMiddleware::handle();

        $db = Database::getInstance();
        $tenantId = Tenant::getId();
        $userId = Auth::user()['id'];

        // Only accept POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'POST required']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
            exit;
        }

        $latitude  = isset($input['latitude']) ? (float)$input['latitude'] : null;
        $longitude = isset($input['longitude']) ? (float)$input['longitude'] : null;

        if ($latitude === null || $longitude === null || $latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            echo json_encode(['success' => false, 'error' => 'Missing latitude/longitude']);
            exit;
        }

        // Find the active GPS tracking session for this user
        $trackingSession = $db->fetchOne(
            "SELECT id FROM gps_tracking_sessions
             WHERE tenant_id = ? AND user_id = ? AND status = 'active'
             ORDER BY started_at DESC LIMIT 1",
            [$tenantId, $userId]
        );

        if (!$trackingSession) {
            // Auto-create a tracking session if there's an active POS session and no tracking session
            $posSession = $db->fetchOne(
                "SELECT id, store_id FROM pos_sessions
                 WHERE tenant_id = ? AND user_id = ? AND status = 'open'
                 ORDER BY opened_at DESC LIMIT 1",
                [$tenantId, $userId]
            );

            if (!$posSession) {
                echo json_encode(['success' => false, 'error' => 'No active POS session. Open a session first.', 'code' => 'NO_POS_SESSION']);
                exit;
            }

            // Auto-create tracking session
            $trackingId = $db->insert('gps_tracking_sessions', [
                'tenant_id'      => $tenantId,
                'store_id'       => $posSession['store_id'] ?? null,
                'user_id'        => $userId,
                'pos_session_id' => $posSession['id'],
                'status'         => 'active',
                'device_info'    => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                'started_at'     => date('Y-m-d H:i:s')
            ]);
            $trackingSessionId = $trackingId;

            // Send Telegram notification for GPS tracking start
            $this->sendTelegramGpsStart($tenantId, $userId, $latitude, $longitude);
        } else {
            $trackingSessionId = $trackingSession['id'];
        }

        // Store the location point
        $db->insert('gps_locations', [
            'tracking_session_id' => $trackingSessionId,
            'tenant_id'           => $tenantId,
            'user_id'             => $userId,
            'latitude'            => $latitude,
            'longitude'           => $longitude,
            'accuracy'            => isset($input['accuracy']) ? (float)$input['accuracy'] : null,
            'altitude'            => isset($input['altitude']) ? (float)$input['altitude'] : null,
            'speed'               => isset($input['speed']) ? (float)$input['speed'] : null,
            'heading'             => isset($input['heading']) ? (float)$input['heading'] : null,
            'battery_level'       => isset($input['battery_level']) ? (float)$input['battery_level'] : null,
            'recorded_at'         => date('Y-m-d H:i:s')
        ]);

        echo json_encode([
            'success' => true,
            'tracking_id' => $trackingSessionId,
            'message' => 'Location recorded'
        ]);
        exit;
    }

    /* ========== API: Stop GPS tracking (called when session closes) ========== */

    public function apiStop() {
        mc_api_apply_cors('POST, OPTIONS');

        AuthMiddleware::handle();

        $db = Database::getInstance();
        $tenantId = Tenant::getId();
        $userId = Auth::user()['id'];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'POST required']);
            exit;
        }

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

            // Send Telegram notification for GPS tracking stop
            $this->sendTelegramGpsStop($tenantId, $userId, $trackingSession['id']);
        }

        echo json_encode(['success' => true, 'message' => 'Tracking stopped']);
        exit;
    }

    /* ========== Telegram Helpers ========== */

    private function sendTelegramGpsStart($tenantId, $userId, $lat, $lng) {
        $db = Database::getInstance();
        $tgConfig = $db->fetchOne(
            "SELECT * FROM tenant_telegram_config WHERE tenant_id = ? AND is_active = 1 AND notify_gps_start = 1",
            [$tenantId]
        );

        $user = $db->fetchOne("SELECT username FROM users WHERE id = ?", [$userId]);
        $tenant = $db->fetchOne("SELECT name FROM tenants WHERE id = ?", [$tenantId]);

        $botToken = $tgConfig['bot_token'] ?? null;
        $chatId = $tgConfig['chat_id'] ?? null;

        if (!$botToken || !$chatId) {
            // Fallback to system Telegram config
            $sysConfig = require __DIR__ . '/../../../config/telegram.php';
            $botToken = $sysConfig['bot_token'] ?? null;
            $chatId = $sysConfig['chat_id'] ?? null;
        }

        if (!$botToken || !$chatId) return;

        $message = "🟢 <b>GPS Tracking Started</b>\n";
        $message .= "🏪 Store: " . ($tenant['name'] ?? 'N/A') . "\n";
        $message .= "👤 Seller: " . ($user['username'] ?? 'N/A') . "\n";
        $message .= "📍 Location: <a href=\"https://maps.google.com/?q={$lat},{$lng}\">View on Map</a>\n";
        $message .= "🕐 Time: " . date('Y-m-d H:i:s');

        $this->sendTelegramRaw($botToken, $chatId, $message);
    }

    private function sendTelegramGpsStop($tenantId, $userId, $trackingId) {
        $db = Database::getInstance();
        $tgConfig = $db->fetchOne(
            "SELECT * FROM tenant_telegram_config WHERE tenant_id = ? AND is_active = 1 AND notify_gps_stop = 1",
            [$tenantId]
        );

        $user = $db->fetchOne("SELECT username FROM users WHERE id = ?", [$userId]);
        $tenant = $db->fetchOne("SELECT name FROM tenants WHERE id = ?", [$tenantId]);

        // Get session stats
        $locationCount = $db->fetchOne(
            "SELECT COUNT(*) as cnt FROM gps_locations WHERE tracking_session_id = ?",
            [$trackingId]
        );
        $lastLoc = $db->fetchOne(
            "SELECT latitude, longitude FROM gps_locations WHERE tracking_session_id = ? ORDER BY recorded_at DESC LIMIT 1",
            [$trackingId]
        );

        $botToken = $tgConfig['bot_token'] ?? null;
        $chatId = $tgConfig['chat_id'] ?? null;

        if (!$botToken || !$chatId) {
            $sysConfig = require __DIR__ . '/../../../config/telegram.php';
            $botToken = $sysConfig['bot_token'] ?? null;
            $chatId = $sysConfig['chat_id'] ?? null;
        }

        if (!$botToken || !$chatId) return;

        $message = "🔴 <b>GPS Tracking Stopped</b>\n";
        $message .= "🏪 Store: " . ($tenant['name'] ?? 'N/A') . "\n";
        $message .= "👤 Seller: " . ($user['username'] ?? 'N/A') . "\n";
        $message .= "📍 Points Recorded: " . ($locationCount['cnt'] ?? 0) . "\n";
        if ($lastLoc) {
            $message .= "📍 Last Location: <a href=\"https://maps.google.com/?q={$lastLoc['latitude']},{$lastLoc['longitude']}\">View on Map</a>\n";
        }
        $message .= "🕐 Time: " . date('Y-m-d H:i:s');

        $this->sendTelegramRaw($botToken, $chatId, $message);
    }

    public function sendTelegramRaw($botToken, $chatId, $message) {
        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
        $data = [
            'chat_id'    => $chatId,
            'text'       => $message,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => false
        ];

        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
                'ignore_errors' => true
            ]
        ];
        $context = stream_context_create($options);
        return @file_get_contents($url, false, $context);
    }

    /* ========== API: Save Telegram Config for tenant ========== */

    public function apiSaveTelegramConfig() {
        mc_api_apply_cors('POST, OPTIONS');

        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if (!Auth::isTenantAdmin()) {
            echo json_encode(['success' => false, 'error' => 'Admin only']);
            exit;
        }

        $db = Database::getInstance();
        $tenantId = Tenant::getId();

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
            'chat_id'             => isset($input['chat_id']) ? substr(trim((string) $input['chat_id']), 0, 100) : null,
            'notify_session_open' => isset($input['notify_session_open']) ? (int)$input['notify_session_open'] : 1,
            'notify_session_close'=> isset($input['notify_session_close']) ? (int)$input['notify_session_close'] : 1,
            'notify_sales_report' => isset($input['notify_sales_report']) ? (int)$input['notify_sales_report'] : 1,
            'notify_gps_start'    => isset($input['notify_gps_start']) ? (int)$input['notify_gps_start'] : 1,
            'notify_gps_stop'     => isset($input['notify_gps_stop']) ? (int)$input['notify_gps_stop'] : 1,
        ];

        if ($existing) {
            $db->update('tenant_telegram_config', $data, 'id = ?', [$existing['id']]);
        } else {
            $sysConfig = require __DIR__ . '/../../../config/telegram.php';
            require_once __DIR__ . '/../../core/classes/CookieCrypt.php';
            $data['tenant_id'] = $tenantId;
            $data['bot_token'] = CookieCrypt::encrypt($sysConfig['bot_token'] ?? '');
            $db->insert('tenant_telegram_config', $data);
        }

        echo json_encode(['success' => true, 'message' => 'Telegram config saved']);
        exit;
    }

    /* ========== API: Get Telegram Config ========== */

    public function apiGetTelegramConfig() {
        mc_api_apply_cors('GET, OPTIONS');

        TenantMiddleware::handle();
        AuthMiddleware::handle();

        $db = Database::getInstance();
        $tenantId = Tenant::getId();

        $config = $db->fetchOne(
            "SELECT chat_id, chat_title, setup_code, notify_session_open, notify_session_close,
                    notify_sales_report, notify_gps_start, notify_gps_stop, is_active,
                    CASE WHEN bot_token IS NULL OR bot_token = '' THEN 0 ELSE 1 END AS bot_configured
               FROM tenant_telegram_config WHERE tenant_id = ?",
            [$tenantId]
        );

        echo json_encode(['success' => true, 'config' => $config]);
        exit;
    }

    /* ========== API: Send test Telegram message ========== */

    public function apiTestTelegram() {
        mc_api_apply_cors('POST, OPTIONS');

        TenantMiddleware::handle();
        AuthMiddleware::handle();

        if (!Auth::isTenantAdmin()) {
            echo json_encode(['success' => false, 'error' => 'Admin only']);
            exit;
        }

        $db = Database::getInstance();
        $tenantId = Tenant::getId();

        $tgConfig = $db->fetchOne(
            "SELECT * FROM tenant_telegram_config WHERE tenant_id = ? AND is_active = 1",
            [$tenantId]
        );

        $botToken = $tgConfig['bot_token'] ?? null;
        $chatId = $tgConfig['chat_id'] ?? null;

        if (!$botToken || !$chatId) {
            require_once __DIR__ . '/../../../core/classes/TelegramBot.php';
            $sysConfig = TelegramBot::getSystemConfig();
            $botToken = $sysConfig['bot_token'];
            $chatId = $sysConfig['chat_id'];
        }

        require_once __DIR__ . '/../../core/classes/CookieCrypt.php';
        $botToken = CookieCrypt::decrypt($botToken);

        if (!$botToken || !$chatId) {
            echo json_encode(['success' => false, 'error' => 'No Telegram bot configured. Please configure first.']);
            exit;
        }

        $tenant = Tenant::getCurrent();
        $message = "✅ <b>Test Message from MCU POS</b>\n";
        $message .= "🏪 Store: " . ($tenant['name'] ?? 'N/A') . "\n";
        $message .= "🕐 Time: " . date('Y-m-d H:i:s') . "\n";
        $message .= "💬 If you see this, your Telegram bot is working correctly!";

        $result = $this->sendTelegramRaw($botToken, $chatId, $message);

        echo json_encode(['success' => true, 'message' => 'Test message sent']);
        exit;
    }
}

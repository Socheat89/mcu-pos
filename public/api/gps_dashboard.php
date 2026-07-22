<?php
// public/api/gps_dashboard.php
// Live GPS Dashboard API - Returns real-time locations for owner dashboard

$root = dirname(__DIR__, 2);
require_once $root . '/core/helpers/api.php';
mc_api_preflight('GET, OPTIONS');

try {

require_once $root . '/core/bootstrap_session.php';
require_once $root . '/core/classes/Database.php';
require_once $root . '/core/classes/Tenant.php';
require_once $root . '/core/classes/Auth.php';

// Must be authenticated
if (!Auth::check()) {
    mc_json_error('Authentication required', 401);
}

$db = Database::getInstance();
$tenantId = Tenant::getId();

// Admin only for full dashboard
if (!Auth::isTenantAdmin()) {
    mc_json_error('Admin access required', 403);
}

$action = $_GET['action'] ?? 'live';

switch ($action) {

    case 'live':
        // Get all active tracking sessions with latest location
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

        mc_json(['success' => true, 'sessions' => $result]);

    case 'history':
        // Get location history for a specific tracking session
        $trackingId = isset($_GET['tracking_id']) ? (int)$_GET['tracking_id'] : 0;
        $limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 500) : 200;

        if (!$trackingId) {
            mc_json_error('Missing tracking_id', 400);
        }

        // Verify ownership
        $session = $db->fetchOne(
            "SELECT id FROM gps_tracking_sessions WHERE id = ? AND tenant_id = ?",
            [$trackingId, $tenantId]
        );
        if (!$session) {
            mc_json_error('Session not found', 404);
        }

        $locations = $db->fetchAll(
            "SELECT latitude, longitude, accuracy, speed, heading, battery_level, recorded_at
             FROM gps_locations
             WHERE tracking_session_id = ?
             ORDER BY recorded_at ASC
             LIMIT ?",
            [$trackingId, $limit]
        );

        mc_json(['success' => true, 'locations' => $locations]);

    case 'stats':
        // Get tracking statistics for this tenant
        $todayStart = date('Y-m-d 00:00:00');

        $activeCount = $db->fetchOne(
            "SELECT COUNT(*) as cnt FROM gps_tracking_sessions WHERE tenant_id = ? AND status = 'active'",
            [$tenantId]
        );

        $todaySessions = $db->fetchOne(
            "SELECT COUNT(*) as cnt FROM gps_tracking_sessions WHERE tenant_id = ? AND started_at >= ?",
            [$tenantId, $todayStart]
        );

        $todayPoints = $db->fetchOne(
            "SELECT COUNT(*) as cnt FROM gps_locations WHERE tenant_id = ? AND recorded_at >= ?",
            [$tenantId, $todayStart]
        );

        mc_json([
            'success' => true,
            'stats' => [
                'active_trackers'   => (int)($activeCount['cnt'] ?? 0),
                'today_sessions'    => (int)($todaySessions['cnt'] ?? 0),
                'today_points'      => (int)($todayPoints['cnt'] ?? 0),
            ]
        ]);

    default:
        mc_json_error('Unknown action', 400);
}

} catch (Throwable $e) {
    mc_log_exception('GPS dashboard error', $e);
    mc_json_error('Server error', 500);
}

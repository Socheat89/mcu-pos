<?php
// public/api/gps_dashboard.php
// Live GPS Dashboard API - Returns real-time locations for owner dashboard

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {

$root = dirname(__DIR__, 2);
require_once $root . '/core/bootstrap_session.php';
require_once $root . '/core/classes/Database.php';
require_once $root . '/core/classes/Tenant.php';
require_once $root . '/core/classes/Auth.php';

// Must be authenticated
if (!Auth::check()) {
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

$db = Database::getInstance();
$tenantId = Tenant::getId();

// Admin only for full dashboard
if (!Auth::isTenantAdmin()) {
    echo json_encode(['success' => false, 'error' => 'Admin access required']);
    exit;
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

        echo json_encode(['success' => true, 'sessions' => $result]);
        break;

    case 'history':
        // Get location history for a specific tracking session
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
        break;

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

        echo json_encode([
            'success' => true,
            'stats' => [
                'active_trackers'   => (int)($activeCount['cnt'] ?? 0),
                'today_sessions'    => (int)($todaySessions['cnt'] ?? 0),
                'today_points'      => (int)($todayPoints['cnt'] ?? 0),
            ]
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Unknown action']);
        break;
}

} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}

exit;

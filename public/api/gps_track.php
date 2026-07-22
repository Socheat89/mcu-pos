<?php
// public/api/gps_track.php
// GPS Tracking API - Receives location pings from seller devices
// This is the main endpoint called by the GPS tracker JS on seller's POS page

// Bootstrap session and required classes
$root = dirname(__DIR__, 2);
require_once $root . '/core/helpers/api.php';
mc_api_preflight('POST, OPTIONS', 'Content-Type, X-API-Key');
require_once $root . '/core/bootstrap_session.php';
require_once $root . '/core/classes/Database.php';
require_once $root . '/core/classes/Tenant.php';
require_once $root . '/core/classes/Auth.php';
require_once $root . '/config/telegram.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mc_json_error('POST required', 405);
}

// Check authentication via session (same domain) or API key (mobile)
$userId = null;
$tenantId = null;

if (isset($_SESSION['user_id']) && isset($_SESSION['tenant_id'])) {
    $userId = $_SESSION['user_id'];
    $tenantId = $_SESSION['tenant_id'];
} else {
    // Try API key authentication (for mobile apps)
    $input = json_decode(file_get_contents('php://input'), true);
    $apiKey = $input['api_key'] ?? ($_SERVER['HTTP_X_API_KEY'] ?? ($_GET['api_key'] ?? null));
    if ($apiKey) {
        $db = Database::getInstance();
        $keyData = $db->fetchOne(
            "SELECT u.id as user_id, u.tenant_id, u.status
             FROM users u
             JOIN settings s ON s.tenant_id = u.tenant_id AND s.key_name = 'gps_api_key' AND s.value = ?
             WHERE u.status = 'active'
             LIMIT 1",
            [$apiKey]
        );
        if ($keyData) {
            $userId = $keyData['user_id'];
            $tenantId = $keyData['tenant_id'];
        }
    }

    if (!$userId) {
        mc_json_error('Authentication required', 401, 'AUTH_REQUIRED');
    }
}

// Re-read input if API key was consumed above
$input = json_decode(file_get_contents('php://input'), true);
$latitude  = isset($input['latitude']) ? (float)$input['latitude'] : null;
$longitude = isset($input['longitude']) ? (float)$input['longitude'] : null;

if ($latitude === null || $longitude === null || $latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
    mc_json_error('Invalid latitude/longitude', 400);
}

$db = Database::getInstance();

// Find active GPS tracking session for this user
$trackingSession = $db->fetchOne(
    "SELECT id FROM gps_tracking_sessions
     WHERE tenant_id = ? AND user_id = ? AND status = 'active'
     ORDER BY started_at DESC LIMIT 1",
    [$tenantId, $userId]
);

if (!$trackingSession) {
    // Check if there's an active POS session
    $posSession = $db->fetchOne(
        "SELECT id, store_id FROM pos_sessions
         WHERE tenant_id = ? AND user_id = ? AND status = 'open'
         ORDER BY opened_at DESC LIMIT 1",
        [$tenantId, $userId]
    );

    if (!$posSession) {
        mc_json_error('No active POS session', 409, 'NO_POS_SESSION');
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
} else {
    $trackingSessionId = $trackingSession['id'];
}

// Store the location
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

mc_json([
    'success'     => true,
    'tracking_id' => $trackingSessionId,
    'message'     => 'Location recorded'
]);

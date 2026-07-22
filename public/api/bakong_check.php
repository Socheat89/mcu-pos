<?php
// public/api/bakong_check.php
error_reporting(0);
ini_set('display_errors', 0);
ob_start();
$root = dirname(__DIR__, 2);
require_once $root . '/core/helpers/api.php';
mc_api_preflight('GET, OPTIONS');
require_once __DIR__ . '/../../core/classes/BakongRelay.php';

$md5 = $_GET['md5'] ?? '';

if (empty($md5) || !preg_match('/^[a-f0-9]{32}$/i', $md5)) {
    ob_clean();
    mc_json_error('Invalid payment reference', 400);
}

try {
    $bakong = new BakongRelay();
    $result = $bakong->checkTransaction($md5);

    if (isset($result['success']) && $result['success']) {
        $fullData = $result['data'];
        $status = 'PENDING';
        
        // Comprehensive check for status in different API structures
        if (isset($fullData['data']['trackingStatus'])) $status = $fullData['data']['trackingStatus'];
        elseif (isset($fullData['data']['status'])) $status = $fullData['data']['status'];
        elseif (isset($fullData['status'])) $status = $fullData['status'];
        elseif (isset($fullData['trackingStatus'])) $status = $fullData['trackingStatus'];
        elseif (isset($fullData['responseMessage'])) $status = $fullData['responseMessage'];
        
        // Force SUCCESS if responseCode is 0 (Official NBC Success)
        if (isset($fullData['responseCode']) && ((int)$fullData['responseCode'] === 0 || $fullData['responseCode'] === '00')) {
            $status = 'SUCCESS';
        }
        
        // Check for common success messages in responseMessage
        if (isset($fullData['responseMessage']) && stripos($fullData['responseMessage'], 'success') !== false) {
            $status = 'SUCCESS';
        }
        
        // Log for debugging
        $logDir = __DIR__ . '/../../logs';
        if (is_writable($logDir)) {
            file_put_contents($logDir . '/payment_debug.log', date('[Y-m-d H:i:s] ') . "MD5: $md5 | Status Found: $status\n", FILE_APPEND);
        }

        ob_clean();
        mc_json([
            'success' => true,
            'status' => strtoupper($status)
        ]);
    } else {
        $errorMsg = $result['error'] ?? 'Unknown API error';
        $logDir = __DIR__ . '/../../logs';
        if (is_writable($logDir)) {
            file_put_contents($logDir . '/payment_debug.log', date('[Y-m-d H:i:s] ') . "MD5: $md5 | Error: $errorMsg\n", FILE_APPEND);
        }
        ob_clean();
        mc_json_error('Unable to verify payment status', 502);
    }
} catch (Throwable $e) {
    error_log('Bakong check error: ' . $e->getMessage());
    ob_clean();
    mc_json_error('Unable to verify payment status', 500);
}

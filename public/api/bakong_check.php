<?php
// public/api/bakong_check.php
error_reporting(0);
ini_set('display_errors', 0);
ob_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/classes/BakongRelay.php';

$md5 = $_GET['md5'] ?? '';

if (empty($md5)) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Missing MD5']);
    exit;
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
            file_put_contents($logDir . '/payment_debug.log', date('[Y-m-d H:i:s] ') . "MD5: $md5 | Status Found: $status | Full Response: " . json_encode($fullData) . "\n", FILE_APPEND);
        }

        ob_clean();
        echo json_encode([
            'success' => true,
            'status' => strtoupper($status),
            'raw' => $fullData
        ]);
    } else {
        $errorMsg = $result['error'] ?? 'Unknown API error';
        $logDir = __DIR__ . '/../../logs';
        if (is_writable($logDir)) {
            file_put_contents($logDir . '/payment_debug.log', date('[Y-m-d H:i:s] ') . "MD5: $md5 | Error: $errorMsg\n", FILE_APPEND);
        }
        ob_clean();
        echo json_encode(['success' => false, 'error' => $errorMsg]);
    }
} catch (Throwable $e) {
    ob_clean();
    echo json_encode([
        'success' => false, 
        'error' => 'System Error: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}

<?php
// core/classes/BakongRelay.php

// Polyfill for mbstring if missing (common in some XAMPP/PHP installs)
if (!function_exists('mb_strlen')) {
    function mb_strlen($string, $encoding = null) {
        return strlen($string);
    }
}

class BakongRelay {
    private $token;
    private $baseUrl;
    private $bankAccount;
    private $merchantName;
    private $merchantCity;
    private $storeLabel;
    private $phoneNumber;
    private $terminalLabel;

    public function __construct() {
        $config = require __DIR__ . '/../../config/bakong.php';
        // Debug Config Load
        $logDir = __DIR__ . '/../../logs';
        if (is_writable($logDir)) {
             // file_put_contents($logDir . '/payment_debug.log', date('[Y-m-d H:i:s] ') . "Config Loaded: " . json_encode($config) . "\n", FILE_APPEND);
        }
        
        $this->token = $config['api_token'];
        $this->baseUrl = rtrim($config['base_url'], '/');
        $this->bankAccount = $config['bank_account'];
        $this->merchantName = $config['merchant_name'];
        $this->merchantCity = $config['merchant_city'] ?? 'Phnom Penh';
        $this->storeLabel = $config['store_label'] ?? 'Main Store';
        $this->phoneNumber = $config['phone_number'] ?? '85516367859';
        $this->terminalLabel = $config['terminal_label'] ?? 'Web Checkout';
    }

    public function generateQR($amount, $currency = 'USD') {
        $logDir = __DIR__ . '/../../logs';
        $logFile = $logDir . '/payment_debug.log';
        if (is_writable($logDir)) {
            file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Starting generateQR: Amount=$amount, Currency=$currency\n", FILE_APPEND);
        }
        
        try {
            // Use the newly installed library for local generation
            $autoloadPath = __DIR__ . '/../../vendor/autoload.php';
            if (!file_exists($autoloadPath)) {
                file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Error: Autoload not found at $autoloadPath\n", FILE_APPEND);
                return ['success' => false, 'error' => 'Autoloader missing'];
            }
            require_once $autoloadPath;
            
            $amount = (float)$amount;
            
            // For USD, ensure we are passing a clean float, but log the intended format
            // The library requires float, so we can't pass string "0.10" directly to the model
            // However, we can ensure the float value is precise.
            
            $billNumber = 'BILL' . date('ymd') . rand(1000, 9999);
            
            file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Model info: BankAcc={$this->bankAccount}, Merchant={$this->merchantName}" . "\n", FILE_APPEND);

            if (strpos($this->bankAccount, '@') !== false) {
                // Individual Account
                file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Initializing IndividualInfo...\n", FILE_APPEND);
                
                $amountFormatted = $amount;
                if ($currency === 'USD') {
                    $amountFormatted = number_format($amount, 2, '.', '');
                }

                $optional = [
                    'amount' => $amountFormatted,
                    'currency' => ($currency === 'USD') ? \KHQR\Helpers\KHQRData::CURRENCY_USD : \KHQR\Helpers\KHQRData::CURRENCY_KHR,
                    'storeLabel' => $this->storeLabel,
                    'mobileNumber' => $this->phoneNumber, // Individual model uses mobileNumber
                    'billNumber' => $billNumber,
                    'terminalLabel' => $this->terminalLabel
                ];
                
                $info = \KHQR\Models\IndividualInfo::withOptionalArray(
                    $this->bankAccount,
                    $this->merchantName,
                    $this->merchantCity,
                    $optional
                );
                
                file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Calling generateIndividual...\n", FILE_APPEND);
                $response = \KHQR\BakongKHQR::generateIndividual($info);
            } else {
                // Merchant Account
                file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Initializing MerchantInfo...\n", FILE_APPEND);
                
                $amountFormatted = $amount;
                if ($currency === 'USD') {
                    $amountFormatted = number_format($amount, 2, '.', '');
                }

                $optional = [
                    'amount' => $amountFormatted,
                    'currency' => ($currency === 'USD') ? \KHQR\Helpers\KHQRData::CURRENCY_USD : \KHQR\Helpers\KHQRData::CURRENCY_KHR,
                    'storeLabel' => $this->storeLabel,
                    'billNumber' => $billNumber,
                    'terminalLabel' => $this->terminalLabel
                ];
                
                // Merchant requires merchantID and acquiringBank
                $info = \KHQR\Models\MerchantInfo::withOptionalArray(
                    $this->bankAccount,
                    $this->merchantName,
                    $this->merchantCity,
                    $this->bankAccount, // Using account as ID
                    'Mekong CyberUnit', // Acquiring bank
                    $optional
                );
                
                file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Calling generateMerchant...\n", FILE_APPEND);
                $response = \KHQR\BakongKHQR::generateMerchant($info);
            }

            if ($response && $response->data) {
                file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Local Generation Success: MD5=" . $response->data['md5'] . "\n", FILE_APPEND);
                return [
                    'success' => true,
                    'data' => [
                        'data' => [
                            'qr' => $response->data['qr'],
                            'md5' => $response->data['md5']
                        ]
                    ]
                ];
            } else {
                file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Error: Library response data is empty\n", FILE_APPEND);
                return ['success' => false, 'error' => 'Failed to generate KHQR string'];
            }
        } catch (\Exception $e) {
            if (is_writable($logDir)) {
                file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Exception in generateQR: " . $e->getMessage() . "\n", FILE_APPEND);
            }
            return ['success' => false, 'error' => 'Local Generation Error: ' . $e->getMessage()];
        } catch (\Error $e) {
            if (is_writable($logDir)) {
                file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Fatal Error in generateQR: " . $e->getMessage() . "\n", FILE_APPEND);
            }
            return ['success' => false, 'error' => 'System Error: ' . $e->getMessage()];
        }
    }

    public function checkTransaction($md5) {
        $logDir = __DIR__ . '/../../logs';
        $logFile = $logDir . '/payment_debug.log';
        // Use case-insensitive check
        $isOfficial = (stripos($this->baseUrl, 'nbc.gov.kh') !== false || stripos($this->baseUrl, 'nbc.org.kh') !== false);
        
        // Debug Logging
        if (is_writable($logDir)) {
            file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "DEBUG checkTransaction (v2): MD5=$md5 | BaseURL={$this->baseUrl} | isOfficial=" . ($isOfficial ? 'TRUE' : 'FALSE') . "\n", FILE_APPEND);
        }
        
        // CASE A: Official NBC API - Use Library Directly (Better compatibility)
        if ($isOfficial) {
            $autoloadPath = __DIR__ . '/../../vendor/autoload.php';
            if (!file_exists($autoloadPath)) {
                return ['success' => false, 'error' => 'Vendor folder missing. Please upload the vendor folder.'];
            }
            require_once $autoloadPath;
            
            try {
                $khqr = new \KHQR\BakongKHQR($this->token);
                // Auto-detect SIT based on URL or token if possible
                $isTest = (stripos($this->baseUrl, 'sit-') !== false || stripos($this->baseUrl, 'test') !== false);
                $officialResult = $khqr->checkTransactionByMD5($md5, $isTest);
                
                if (isset($officialResult['responseCode']) && ($officialResult['responseCode'] === 0 || $officialResult['responseCode'] === '00')) {
                    return ['success' => true, 'data' => $officialResult];
                } else {
                    $msg = $officialResult['responseMessage'] ?? ($officialResult['error'] ?? 'NBC API Rejected');
                    return ['success' => false, 'error' => 'NBC Error: ' . $msg, 'response' => $officialResult];
                }
            } catch (\Exception $e) {
                 if (is_writable($logDir)) {
                    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "NBC Library Exception (Caught - Falling back): " . $e->getMessage() . "\n", FILE_APPEND);
                 }
                // Fallthrough to Case B
            }
        }

        // CASE B: Bakong Relay/Gateway Service (or Fallback)
        $endpoint = rtrim($this->baseUrl, '/') . '/v1/check_transaction_by_md5';
        $payload = ['md5' => $md5];
        
        if (is_writable($logDir)) {
             file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Attempting Manual POST to: $endpoint\n", FILE_APPEND);
        }

        $result = $this->post($endpoint, $payload);

        if ($result['success']) {
            return $result;
        }

        // Fallback for Relay 401 
        if (!$result['success'] && (isset($result['response']['errorCode']) || (isset($result['error']) && strpos($result['error'], '401') !== false))) {
             if (is_writable($logDir)) {
                 file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Relay 401 error. Original Library fallback skipped as it already failed or wasn't used.\n", FILE_APPEND);
             }
             return ['success' => false, 'error' => 'Relay/API Unauthorized: Please check your token.'];
        }
        
        // Log final error
        if (is_writable($logDir)) {
            file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Final Error: " . json_encode($result) . "\n", FILE_APPEND);
        }

        return $result;
    }

    public function generateQRImage($qrString) {
        $endpoint = "{$this->baseUrl}/v1/generate_khqr_image";
        $payload = [
            'qr' => $qrString
        ];

        $result = $this->post($endpoint, $payload);

        // If relay succeeds, use its image
        if ($result['success'] && isset($result['data']['data']['image'])) {
            return $result;
        }

        // Fallback for any failure (including 401 Unauthorized or connection issues)
        // We use a free public QR generator to ensure the user always sees a QR code
        $fallbackImage = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qrString);
        
        return [
            'success' => true,
            'data' => [
                'data' => [
                    'image' => $fallbackImage
                ]
            ],
            'is_fallback' => true
        ];
    }

    protected function post($url, $data) {
        $payload = json_encode($data);
        $authorization = (strpos($this->token, 'Bearer ') === 0 ? $this->token : 'Bearer ' . $this->token);
        
        $logDir = __DIR__ . '/../../logs';
        $logFile = $logDir . '/payment_debug.log';

        // User Agents to rotate
        $userAgents = [
            'okhttp/3.12.1', 
            'BakongMobile/1.0 (iOS)', 
            'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Mobile Safari/537.36',
            'PostmanRuntime/7.29.0'
        ];

        // Retry Logic (Max 3 attempts)
        for ($attempt = 0; $attempt < 3; $attempt++) {
            $userAgent = $userAgents[$attempt % count($userAgents)];
            
            // Common Headers
            $headers = [
                'Content-Type: application/json',
                'Authorization: ' . $authorization,
                'Accept: application/json',
                'User-Agent: ' . $userAgent
            ];

            // --- STRATEGY A: cURL ---
            if (function_exists('curl_init')) {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                
                $curlHeaders = [];
                foreach ($headers as $key => $val) {
                    if (is_int($key)) $curlHeaders[] = $val;
                    else $curlHeaders[] = "$key: $val";
                }
                curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);
                curl_setopt($ch, CURLOPT_USERAGENT, $userAgent); // Set explicit UA
                
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
                curl_setopt($ch, CURLOPT_TIMEOUT, 20);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = curl_error($ch);
                curl_close($ch);

                if ($httpCode === 200 || $httpCode === 201) {
                    return ['success' => true, 'data' => json_decode($response, true)];
                }
                
                // If 403/429/5xx, wait and continue to next attempt
                if ($httpCode >= 400) {
                    if (is_writable($logDir)) {
                        file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Retry $attempt (cURL): Failed $httpCode. Retrying...\n", FILE_APPEND);
                    }
                    sleep(1); 
                    continue; 
                }
            }

            // --- STRATEGY B: Stream Context (Fallback) ---
            try {
                $streamHeaders = "";
                foreach ($headers as $key => $val) {
                    if (is_int($key)) $streamHeaders .= "$val\r\n";
                    else $streamHeaders .= "$key: $val\r\n";
                }

                $opts = [
                    'http' => [
                        'header'  => $streamHeaders,
                        'method'  => 'POST',
                        'content' => $payload,
                        'ignore_errors' => true,
                        'timeout' => 20
                    ],
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ]
                ];
                
                $context  = stream_context_create($opts);
                $response = @file_get_contents($url, false, $context);
                
                if ($response !== false) {
                    $json = json_decode($response, true);
                    if ($json && !isset($json['errorCode'])) {
                        return ['success' => true, 'data' => $json];
                    }
                }
            } catch (\Exception $e) { }

            // Sleep before next retry
            sleep(1);
        }

        // If all retries failed
        return [
            'success' => false,
            'error' => "API Error: Max Retries Exceeded (Likely IP Blocked)",
            'response' => $response ?? null
        ];
    }
}

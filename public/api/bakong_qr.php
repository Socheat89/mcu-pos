<?php
// public/api/bakong_qr.php
// VERSION: FINAL_V5_FIX
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow CORS for testing

// -------------------------------------------------------------
// EMBEDDED Check - Rename Class to avoid conflicts
// -------------------------------------------------------------
if (!class_exists('BakongRelayEmbed')) {
    class BakongRelayEmbed {
        private $token;
        private $baseUrl;
        private $bankAccount;
        private $merchantName;
        private $merchantCity;
        private $storeLabel;
        private $phoneNumber;
        private $terminalLabel;

        public function __construct() {
            // FIX: Use DOCUMENT_ROOT to find config reliably
            // We force looking at the PROJECT ROOT
            
            $root = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 2); // Fallback for CLI
            
            // Try explicit paths suitable for this project structure
            // 1. Direct subfolder match (e.g., /home/user/public_html/Mekong_CyberUnit/config/bakong.php)
            // 2. Direct root match (e.g., /home/user/public_html/config/bakong.php)
            // 3. Relative from API file (e.g., ../../config/bakong.php)
            $possiblePaths = [
                $root . '/Mekong_CyberUnit/config/bakong.php',
                $root . '/config/bakong.php',
                dirname(__DIR__, 2) . '/config/bakong.php',
                __DIR__ . '/../../config/bakong.php'
            ];

            $configPath = null;
            foreach ($possiblePaths as $path) {
                if (file_exists($path)) {
                    $configPath = $path;
                    break;
                }
            }

            if (!$configPath) {
                // Return detailed error for debugging
                $searched = implode(" || ", $possiblePaths);
                throw new Exception("V5 Config Missing! Searched in: " . $searched);
            }

            $config = require $configPath;
            
            // Validate Config
            if (!is_array($config) || !isset($config['api_token'])) {
                throw new Exception("Invalid Config Config content at: " . $configPath);
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
            try {
                $root = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 2);
                $possibleAutoloads = [
                    $root . '/Mekong_CyberUnit/vendor/autoload.php',
                    $root . '/vendor/autoload.php',
                    dirname(__DIR__, 2) . '/vendor/autoload.php',
                    __DIR__ . '/../../vendor/autoload.php'
                ];

                $autoloadPath = null;
                foreach ($possibleAutoloads as $path) {
                    if (file_exists($path)) {
                        $autoloadPath = $path;
                        break;
                    }
                }

                if (!$autoloadPath) {
                    $searched = implode(" || ", $possibleAutoloads);
                    return ['success' => false, 'error' => 'Vendor Autoload missing. Searched: ' . $searched];
                }
                
                require_once $autoloadPath;
                
                $amount = (float)$amount;
                $billNumber = 'BILL' . date('ymd') . rand(1000, 9999);
                
                if (strpos($this->bankAccount, '@') !== false) {
                    // Individual
                    $amountFormatted = ($currency === 'USD') ? number_format($amount, 2, '.', '') : $amount;
                    $optional = [
                        'amount' => $amountFormatted,
                        'currency' => ($currency === 'USD') ? \KHQR\Helpers\KHQRData::CURRENCY_USD : \KHQR\Helpers\KHQRData::CURRENCY_KHR,
                        'storeLabel' => $this->storeLabel,
                        'mobileNumber' => $this->phoneNumber, 
                        'billNumber' => $billNumber,
                        'terminalLabel' => $this->terminalLabel
                    ];
                    
                    $info = \KHQR\Models\IndividualInfo::withOptionalArray(
                        $this->bankAccount, $this->merchantName, $this->merchantCity, $optional
                    );
                    $response = \KHQR\BakongKHQR::generateIndividual($info);
                } else {
                    // Merchant
                    $amountFormatted = ($currency === 'USD') ? number_format($amount, 2, '.', '') : $amount;
                    $optional = [
                        'amount' => $amountFormatted,
                        'currency' => ($currency === 'USD') ? \KHQR\Helpers\KHQRData::CURRENCY_USD : \KHQR\Helpers\KHQRData::CURRENCY_KHR,
                        'storeLabel' => $this->storeLabel,
                        'billNumber' => $billNumber,
                        'terminalLabel' => $this->terminalLabel
                    ];
                    
                    $info = \KHQR\Models\MerchantInfo::withOptionalArray(
                        $this->bankAccount, $this->merchantName, $this->merchantCity, 
                        $this->bankAccount, 'Mekong CyberUnit', $optional
                    );
                    $response = \KHQR\BakongKHQR::generateMerchant($info);
                }

                if ($response && $response->data) {
                    return [
                        'success' => true,
                        'data' => [
                            'data' => [
                                'qr' => $response->data['qr'],
                                'md5' => $response->data['md5']
                            ]
                        ]
                    ];
                }
                return ['success' => false, 'error' => 'Failed to generate KHQR string'];
            } catch (\Exception $e) {
                return ['success' => false, 'error' => 'Local Gen Error: ' . $e->getMessage()];
            }
        }

        public function generateQRImage($qrString) {
            $fallbackImage = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qrString);
            return [
                'success' => true,
                'data' => [ 'data' => [ 'image' => $fallbackImage ] ]
            ];
        }
    }
}
// -------------------------------------------------------------

$plan = $_GET['plan'] ?? '';
$method = $_GET['method'] ?? 'bakong';
$amount = 0;

// Base values
if ($plan === 'starter') $amount = 0.10;
elseif ($plan === 'professional') $amount = 50;
elseif ($plan === 'enterprise') $amount = 100;

// Allow override
if (isset($_GET['amount']) && is_numeric($_GET['amount'])) {
    $amount = (float)$_GET['amount'];
}

if ($amount <= 0) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Invalid plan or amount']);
    exit;
}

// Case 1: ACLEDA (Static QR)
if ($method === 'acleda') {
    ob_clean();
    echo json_encode([
        'success' => true,
        'qr' => 'STATIC_ACLEDA_' . $amount,
        'md5' => 'static_acleda_' . $amount,
        'amount' => $amount,
        'image' => "/Mekong_CyberUnit/public/images/acleda_$amount.png",
        'is_static' => true
    ]);
    exit;
}

// Case 2: Bakong (Dynamic KHQR)
try {
    // Instantiating the EMBEDDED class
    $bakong = new BakongRelayEmbed();
    $result = $bakong->generateQR($amount);

    if (isset($result['success']) && $result['success']) {
        $qrData = $result['data']['data'];
        $imageResult = $bakong->generateQRImage($qrData['qr']);
        
        ob_clean();
        echo json_encode([
            'success' => true,
            'qr' => $qrData['qr'],
            'md5' => $qrData['md5'],
            'amount' => $amount,
            'image' => $imageResult['data']['data']['image'],
            'is_static' => false
        ]);
    } else {
        $errorMsg = $result['error'] ?? 'QR generation failed';
        ob_clean();
        echo json_encode(['success' => false, 'error' => $errorMsg]);
    }
} catch (Exception $e) {
    ob_clean();
    // V5 Error Message to verify this file is active
    echo json_encode(['success' => false, 'error' => 'V5 Error: ' . $e->getMessage()]);
}
?>

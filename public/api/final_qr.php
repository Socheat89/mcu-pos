<?php
// public/api/final_qr.php
// This is the FINAL FIXED version V5
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_start();
$root = dirname(__DIR__, 2);
require_once $root . '/core/helpers/api.php';
mc_api_preflight('GET, OPTIONS');

// -------------------------------------------------------------
// STANDALONE CLASS (No external file needed)
// -------------------------------------------------------------
class BakongFinal {
    private $token;
    private $baseUrl;
    private $bankAccount;
    // ... params
    private $merchantName;
    private $merchantCity;
    private $storeLabel;
    private $phoneNumber;
    private $terminalLabel;

    public function __construct() {
        $config = require dirname(__DIR__, 2) . '/config/bakong.php';
        if (empty($config['api_token']) || empty($config['bank_account']) || empty($config['merchant_name'])) {
            throw new Exception('Bakong credentials are not configured.');
        }
        
        $this->token = $config['api_token'];
        $this->baseUrl = rtrim($config['base_url'], '/');
        $this->bankAccount = $config['bank_account'];
        // Optional params with defaults
        $this->merchantName = $config['merchant_name'] ?? 'Mekong CyberUnit';
        $this->merchantCity = $config['merchant_city'] ?? 'Phnom Penh';
        $this->storeLabel = $config['store_label'] ?? 'Main Store';
        $this->phoneNumber = $config['phone_number'] ?? '85512345678';
        $this->terminalLabel = $config['terminal_label'] ?? 'Online';
    }

    public function generateQR($amount, $currency = 'USD') {
        try {
            $root = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 2);
            $projectRoot = dirname(__DIR__, 2);
            $normalizedRoot = rtrim(str_replace('\\', '/', $root), '/');
            $possibleAutoloads = array_unique(array_filter([
                $projectRoot . '/vendor/autoload.php',
                __DIR__ . '/../../vendor/autoload.php',
                $normalizedRoot ? ($normalizedRoot . '/vendor/autoload.php') : null
            ]));

            $autoloadPath = null;
            foreach ($possibleAutoloads as $path) {
                if (file_exists($path)) {
                    $autoloadPath = $path;
                    break;
                }
            }

            if (!$autoloadPath) {
                return ['success' => false, 'error' => 'Payment library is unavailable.'];
            }
            require_once $autoloadPath;
            
            // 1. Generate Info
            $amount = (float)$amount;
            $billNumber = 'BILL' . date('ymd') . rand(1000, 9999);
            
            // Auto-detect type
            $isIndividual = (strpos($this->bankAccount, '@') !== false);
            
            $amountFormatted = ($currency === 'USD') ? number_format($amount, 2, '.', '') : $amount;
            
            $optional = [
                'amount' => $amountFormatted,
                'currency' => ($currency === 'USD') ? \KHQR\Helpers\KHQRData::CURRENCY_USD : \KHQR\Helpers\KHQRData::CURRENCY_KHR,
                'storeLabel' => $this->storeLabel,
                'mobileNumber' => $this->phoneNumber, 
                'billNumber' => $billNumber,
                'terminalLabel' => $this->terminalLabel
            ];
            
            if ($isIndividual) {
                $info = \KHQR\Models\IndividualInfo::withOptionalArray(
                    $this->bankAccount, $this->merchantName, $this->merchantCity, $optional
                );
                $response = \KHQR\BakongKHQR::generateIndividual($info);
            } else {
                $info = \KHQR\Models\MerchantInfo::withOptionalArray(
                    $this->bankAccount, $this->merchantName, $this->merchantCity, 
                    $this->bankAccount, 'Mekong CyberUnit', $optional
                );
                $response = \KHQR\BakongKHQR::generateMerchant($info);
            }

            if ($response && $response->data) {
                return [
                    'success' => true,
                    'qr' => $response->data['qr'],
                    'md5' => $response->data['md5']
                ];
            }
            return ['success' => false, 'error' => 'Failed to generate KHQR data'];

        } catch (\Exception $e) {
            error_log('Final QR generation error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'QR generation failed'];
        }
    }
}

// -------------------------------------------------------------
// MAIN LOGIC
// -------------------------------------------------------------
$plan = $_GET['plan'] ?? '';
$amount = 0;

if ($plan === 'starter') $amount = 0.10;
elseif ($plan === 'professional') $amount = 50;
elseif ($plan === 'enterprise') $amount = 100;
if (isset($_GET['amount']) && is_numeric($_GET['amount'])) $amount = (float)$_GET['amount'];

if ($amount <= 0) {
    ob_clean();
    mc_json_error('Invalid amount', 400);
}

try {
    $engine = new BakongFinal();
    $result = $engine->generateQR($amount);

    if ($result['success']) {
        // Generate Image URL (using Public API to avoid local GD/Curl issues)
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($result['qr']);
        
        ob_clean();
        mc_json([
            'success' => true,
            'qr' => $result['qr'], // String
            'md5' => $result['md5'],
            'image' => $qrUrl,      // Image URL
            'is_static' => false
        ]);
    } else {
        error_log('Final QR error: ' . ($result['error'] ?? 'Unknown error'));
        ob_clean();
        mc_json_error('QR generation failed', 502);
    }
} catch (Exception $e) {
    error_log('Final QR error: ' . $e->getMessage());
    ob_clean();
    mc_json_error('QR generation failed', 500);
}
?>

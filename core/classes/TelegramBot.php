<?php
// core/classes/TelegramBot.php

class TelegramBot {
    private $token;
    private $chatId;
    private $tenantId;

    public static function getSystemConfig() {
        $token = null;
        $localFile = __DIR__ . '/../../config/telegram.local.php';
        if (is_file($localFile)) {
            $encrypted = require $localFile;
            if (!empty($encrypted)) {
                if (!class_exists('CookieCrypt')) {
                    require_once __DIR__ . '/CookieCrypt.php';
                }
                $token = CookieCrypt::decrypt($encrypted);
            }
        }

        $config = require __DIR__ . '/../../config/telegram.php';
        $chatId = $config['chat_id'] ?? '';

        if (empty($token)) {
            if (!class_exists('CookieCrypt')) {
                require_once __DIR__ . '/CookieCrypt.php';
            }
            $token = CookieCrypt::decrypt($config['bot_token'] ?? '');
        }

        return ['bot_token' => $token, 'chat_id' => $chatId];
    }

    public function __construct($tenantId = null) {
        $this->tenantId = $tenantId;
        if (!class_exists('CookieCrypt')) {
            require_once __DIR__ . '/CookieCrypt.php';
        }
        
        // If tenant ID is provided, try to use tenant-specific config
        if ($tenantId) {
            $db = Database::getInstance();
            $tenantConfig = $db->fetchOne(
                "SELECT bot_token, chat_id FROM tenant_telegram_config WHERE tenant_id = ? AND is_active = 1",
                [$tenantId]
            );
            if ($tenantConfig && !empty($tenantConfig['bot_token']) && !empty($tenantConfig['chat_id'])) {
                $this->token = CookieCrypt::decrypt($tenantConfig['bot_token']);
                $this->chatId = $tenantConfig['chat_id'];
                return;
            }
        }
        
        // Fallback to system config
        $sys = self::getSystemConfig();
        $this->token = $sys['bot_token'];
        $this->chatId = $sys['chat_id'];
    }

    /**
     * Set custom bot token and chat ID (for tenant-specific overrides)
     */
    public function setConfig($botToken, $chatId) {
        $this->token = $botToken;
        $this->chatId = $chatId;
    }

    /**
     * Get the current bot token
     */
    public function getToken() {
        return $this->token;
    }

    /**
     * Get the current chat ID
     */
    public function getChatId() {
        return $this->chatId;
    }

    public function sendMessage($message, $keyboard = null) {
        $url = "https://api.telegram.org/bot{$this->token}/sendMessage";
        $data = [
            'chat_id' => $this->chatId,
            'text' => $message,
            'parse_mode' => 'HTML'
        ];

        if ($keyboard) {
            $data['reply_markup'] = json_encode($keyboard);
        }

        return $this->post($url, $data);
    }

    /**
     * Send GPS tracking start notification
     */
    public function sendGpsStart($username, $storeName, $latitude, $longitude) {
        $message = "🟢 <b>GPS Tracking Started</b>\n";
        $message .= "👤 Seller: {$username}\n";
        if ($storeName) $message .= "🏪 Store: {$storeName}\n";
        $message .= "📍 <a href=\"https://maps.google.com/?q={$latitude},{$longitude}\">View Location on Map</a>\n";
        $message .= "🕐 " . date('Y-m-d H:i:s');
        return $this->sendMessage($message);
    }

    /**
     * Send GPS tracking stop notification
     */
    public function sendGpsStop($username, $storeName, $latitude, $longitude, $pointCount) {
        $message = "🔴 <b>GPS Tracking Stopped</b>\n";
        $message .= "👤 Seller: {$username}\n";
        if ($storeName) $message .= "🏪 Store: {$storeName}\n";
        $message .= "📍 Points Recorded: {$pointCount}\n";
        if ($latitude && $longitude) {
            $message .= "📍 <a href=\"https://maps.google.com/?q={$latitude},{$longitude}\">Last Location</a>\n";
        }
        $message .= "🕐 " . date('Y-m-d H:i:s');
        return $this->sendMessage($message);
    }

    /**
     * Send POS session open notification
     */
    public function sendSessionOpen($username, $storeName, $openingBalance) {
        $message = "🟢 <b>POS Session Opened</b>\n";
        $message .= "👤 {$username}\n";
        if ($storeName) $message .= "🏪 {$storeName}\n";
        $message .= "💰 Opening Balance: <b>$" . number_format((float)$openingBalance, 2) . "</b>\n";
        $message .= "🕐 " . date('Y-m-d H:i:s');
        return $this->sendMessage($message);
    }

    /**
     * Send POS session close notification with sales report
     */
    public function sendSessionClose($username, $storeName, $totalSales, $paymentSummary = []) {
        $message = "🔴 <b>POS Session Closed</b>\n";
        $message .= "👤 {$username}\n";
        if ($storeName) $message .= "🏪 {$storeName}\n";
        $message .= "━━━━━━━━━━━━━━━━\n";
        $message .= "📊 <b>Sales Report</b>\n";
        $message .= "💰 Total Sales: <b>$" . number_format((float)$totalSales, 2) . "</b>\n";
        
        if (!empty($paymentSummary)) {
            foreach ($paymentSummary as $method => $amount) {
                $icon = match(strtolower($method)) {
                    'cash' => '💵', 'khqr' => '📱', 'card' => '💳',
                    default => '💲'
                };
                $message .= $icon . " " . ucfirst($method) . ": $" . number_format((float)$amount, 2) . "\n";
            }
        }
        $message .= "🕐 " . date('Y-m-d H:i:s');
        return $this->sendMessage($message);
    }

    private function post($url, $data) {
        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
                'ignore_errors' => true
            ]
        ];
        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);
        return $result ? json_decode($result, true) : null;
    }
}

<?php
// public/api/gps_telegram_callback.php
// Telegram Bot Webhook Callback Handler
// This receives incoming messages from Telegram when users interact with the bot
// Store owners can get their Chat ID by sending /start to the bot

header('Content-Type: application/json');

$root = dirname(__DIR__, 2);
require_once $root . '/core/classes/Database.php';

// Read incoming update from Telegram
$update = json_decode(file_get_contents('php://input'), true);

if (!$update) {
    echo json_encode(['ok' => true]);
    exit;
}

// Handle /start command
if (isset($update['message'])) {
    $message = $update['message'];
    $chatId = $message['chat']['id'] ?? null;
    $text = $message['text'] ?? '';
    $chatType = $message['chat']['type'] ?? 'private';

    if ($chatType === 'private' && strpos($text, '/start') === 0) {
        // Someone DMed the bot - give them their Chat ID info
        $botToken = getBotToken();
        if ($botToken) {
            sendTelegramMessage($botToken, $chatId, 
                "🤖 <b>MCU POS - GPS Tracking Bot</b>\n\n" .
                "Your Chat ID is: <code>{$chatId}</code>\n\n" .
                "📋 <b>How to use:</b>\n" .
                "1. Add this bot to your Telegram group as an <b>admin</b>\n" .
                "2. Send a message in the group\n" .
                "3. The bot will reply with the group's Chat ID\n" .
                "4. Enter this Chat ID in your MCU POS GPS Settings\n\n" .
                "You will then receive:\n" .
                "📍 GPS tracking notifications\n" .
                "💰 Sales reports when sessions close\n" .
                "🔔 POS session open/close alerts"
            );
        }
    } elseif (in_array($chatType, ['group', 'supergroup'])) {
        // Someone messaged in a group
        if (strpos($text, '/chatid') === 0 || strpos($text, '/start') === 0) {
            $botToken = getBotToken();
            if ($botToken) {
                sendTelegramMessage($botToken, $chatId,
                    "✅ <b>Group Chat ID</b>\n\n" .
                    "This group's Chat ID is: <code>{$chatId}</code>\n\n" .
                    "📋 Enter this ID in your MCU POS GPS Settings → Telegram Setup."
                );
            }
        }
    }
}

// Handle new chat member (bot added to group)
if (isset($update['message']['new_chat_members'])) {
    $chatId = $update['message']['chat']['id'] ?? null;
    $chatType = $update['message']['chat']['type'] ?? '';
    $chatTitle = $update['message']['chat']['title'] ?? 'Group';

    if (in_array($chatType, ['group', 'supergroup'])) {
        $botToken = getBotToken();
        if ($botToken) {
            // Check if our bot is among the new members
            $botUsername = null;
            // Get bot info
            $botInfo = @file_get_contents("https://api.telegram.org/bot{$botToken}/getMe");
            $botData = json_decode($botInfo, true);
            if ($botData && $botData['ok']) {
                $botUsername = $botData['result']['username'] ?? null;
            }

            $isOurBot = false;
            foreach ($update['message']['new_chat_members'] as $member) {
                if ($botUsername && ($member['username'] ?? '') === $botUsername) {
                    $isOurBot = true;
                    break;
                }
            }

            if ($isOurBot) {
                sendTelegramMessage($botToken, $chatId,
                    "🤖 <b>MCU POS Bot is now active!</b>\n\n" .
                    "📋 <b>Group:</b> {$chatTitle}\n" .
                    "🆔 <b>Chat ID:</b> <code>{$chatId}</code>\n\n" .
                    "⬆️ Copy this Chat ID and paste it in your MCU POS GPS Settings → Telegram Setup.\n\n" .
                    "You will receive:\n" .
                    "📍 GPS tracking notifications\n" .
                    "💰 Sales reports when sessions close\n" .
                    "🔔 POS session alerts\n\n" .
                    "Type /chatid anytime to see this ID again."
                );
            }
        }
    }
}

echo json_encode(['ok' => true]);
exit;

// ===== Helpers =====

function getBotToken() {
    $config = require $root . '/config/telegram.php';
    // Check for tenant token from database
    $db = Database::getInstance();
    // For callback, we use the system default bot token
    return $config['bot_token'] ?? null;
}

function sendTelegramMessage($token, $chatId, $text) {
    $url = "https://api.telegram.org/bot{$token}/sendMessage";
    $data = [
        'chat_id'    => $chatId,
        'text'       => $text,
        'parse_mode' => 'HTML'
    ];
    $ctx = stream_context_create([
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
            'ignore_errors' => true
        ]
    ]);
    return @file_get_contents($url, false, $ctx);
}

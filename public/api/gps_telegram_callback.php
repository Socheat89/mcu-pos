<?php
// public/api/gps_telegram_callback.php
// Smart Telegram Bot — auto-detects groups, generates simple 6-char setup codes
// NO Chat IDs needed! User just enters a 6-char code in MCU POS.

header('Content-Type: application/json');

$root = dirname(__DIR__, 2);
require_once $root . '/core/classes/Database.php';
require_once $root . '/config/telegram.php';

$sysConfig = require $root . '/config/telegram.php';
$BOT_TOKEN = $sysConfig['bot_token'] ?? '';
$webhookSecret = $sysConfig['webhook_secret'] ?? '';

if ($webhookSecret !== '') {
    $providedSecret = $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ?? '';
    if (!hash_equals($webhookSecret, $providedSecret)) {
        http_response_code(403);
        echo json_encode(['ok' => false]);
        exit;
    }
} elseif (empty($sysConfig['is_local'])) {
    http_response_code(403);
    echo json_encode(['ok' => false]);
    exit;
}

if ($BOT_TOKEN === '') {
    echo json_encode(['ok' => true]);
    exit;
}

$update = json_decode(file_get_contents('php://input'), true);
if (!$update) { echo json_encode(['ok' => true]); exit; }

$db = Database::getInstance();

// ═══════════════════════════════════════
//  PRIVATE CHAT — /start
// ═══════════════════════════════════════
if (isset($update['message']['chat']['type']) && $update['message']['chat']['type'] === 'private') {
    $chatId = $update['message']['chat']['id'];
    $text = $update['message']['text'] ?? '';

    if (strpos($text, '/start') === 0) {
        $botUser = getBotUsername($BOT_TOKEN);
        $keyboard = [
            'inline_keyboard' => [
                [['text' => '➕ បន្ថែម Bot ចូលក្រុម', 'url' => "https://t.me/{$botUser}?startgroup=true"]],
                [['text' => '📖 របៀបប្រើ / How to Use', 'callback_data' => 'help']],
            ]
        ];
        $msg = "🤖 <b>MCU POS — Telegram Bot</b>\n\n";
        $msg .= "👋 <b>សួស្តី!</b> ខ្ញុំជា bot សម្រាប់ផ្ញើការជូនដំណឹងពីប្រព័ន្ធ POS!\n\n";
        $msg .= "📋 <b>ខ្ញុំអាចផ្ញើ៖</b>\n";
        $msg .= "  📍 ទីតាំង GPS អ្នកលក់\n";
        $msg .= "  💰 របាយការណ៍លក់\n";
        $msg .= "  🔔 ការជូនដំណឹងពេលបើក/បិទវគ្គលក់\n\n";
        $msg .= "⬇️ <b>ជំហានងាយៗ ៣ ជំហាន៖</b>\n";
        $msg .= "1️⃣ ចុច <b>បន្ថែម Bot ចូលក្រុម</b> ខាងក្រោម\n";
        $msg .= "2️⃣ Bot នឹងផ្ញើ <b>លេខកូដ ៦ ខ្ទង់</b> ក្នុងក្រុម\n";
        $msg .= "3️⃣ យកលេខកូដទៅបញ្ចូលក្នុង <b>MCU POS → GPS → Telegram</b>\n\n";
        $msg .= "⏱️ <b>ចំណាយពេលតែ ១ នាទី!</b>";
        sendMessage($BOT_TOKEN, $chatId, $msg, $keyboard);
    } else {
        sendMessage($BOT_TOKEN, $chatId, "សូមចុច /start ដើម្បីចាប់ផ្តើម។\nPlease type /start to begin.");
    }
}

// ═══════════════════════════════════════
//  INLINE BUTTON CALLBACKS
// ═══════════════════════════════════════
if (isset($update['callback_query'])) {
    $cb = $update['callback_query'];
    $cbChatId = $cb['message']['chat']['id'];
    $data = $cb['data'];

    if ($data === 'help') {
        $msg = "📖 <b>របៀបតំឡើង / How to Setup</b>\n\n";
        $msg .= "1️⃣ បន្ថែម bot ចូលក្រុម Telegram របស់អ្នក\n";
        $msg .= "2️⃣ bot នឹងផ្ញើ <b>លេខកូដ ៦ ខ្ទង់</b> (ឧ. <code>ABC123</code>)\n";
        $msg .= "3️⃣ ចូលទៅ <b>MCU POS → GPS → Telegram Setup</b>\n";
        $msg .= "4️⃣ បញ្ចូលលេខកូដ → រួចរាល់! ✅\n\n";
        $msg .= "<i>មិនចាំបាច់ដឹង Chat ID ឬ API អ្វីទាំងអស់!</i>";
        answerCallback($BOT_TOKEN, $cb['id']);
        sendMessage($BOT_TOKEN, $cbChatId, $msg);
    }
    
    if (strpos($data, 'copy_') === 0) {
        $code = substr($data, 5);
        answerCallback($BOT_TOKEN, $cb['id'], "✅ លេខកូដ៖ {$code} — យកទៅបញ្ចូលក្នុង MCU POS ឥឡូវនេះ!");
    }

    echo json_encode(['ok' => true]);
    exit;
}

// ═══════════════════════════════════════
//  GROUP MESSAGES
// ═══════════════════════════════════════
if (isset($update['message']['chat']['type']) && in_array($update['message']['chat']['type'], ['group', 'supergroup'])) {
    $chatId = $update['message']['chat']['id'];
    $chatTitle = $update['message']['chat']['title'] ?? 'Group';
    $chatType = $update['message']['chat']['type'];
    $text = $update['message']['text'] ?? '';

    if (strpos($text, '/start') === 0 || strpos($text, '/setup') === 0 || strpos($text, '/code') === 0) {
        $code = generateOrGetCode($db, $chatId, $chatTitle, $chatType, $BOT_TOKEN);
        sendSetupCodeToGroup($BOT_TOKEN, $chatId, $chatTitle, $code);
    }
}

// ═══════════════════════════════════════
//  BOT ADDED TO GROUP
// ═══════════════════════════════════════
if (isset($update['message']['new_chat_members'])) {
    $chatId = $update['message']['chat']['id'];
    $chatTitle = $update['message']['chat']['title'] ?? 'Group';
    $chatType = $update['message']['chat']['type'] ?? 'group';

    $botUser = getBotUsername($BOT_TOKEN);
    $isOurBot = false;
    foreach ($update['message']['new_chat_members'] as $m) {
        if ($botUser && ($m['username'] ?? '') === $botUser) { $isOurBot = true; break; }
    }

    if ($isOurBot && in_array($chatType, ['group', 'supergroup'])) {
        $code = generateOrGetCode($db, $chatId, $chatTitle, $chatType, $BOT_TOKEN);
        sendSetupCodeToGroup($BOT_TOKEN, $chatId, $chatTitle, $code);
    }
}

// ═══════════════════════════════════════
//  BOT STATUS CHANGE (promoted etc.)
// ═══════════════════════════════════════
if (isset($update['my_chat_member'])) {
    $chat = $update['my_chat_member']['chat'];
    $chatId = $chat['id'];
    $chatTitle = $chat['title'] ?? 'Group';
    $chatType = $chat['type'] ?? 'group';
    $newStatus = $update['my_chat_member']['new_chat_member']['status'] ?? '';

    if ($newStatus === 'administrator' || $newStatus === 'member') {
        $code = generateOrGetCode($db, $chatId, $chatTitle, $chatType, $BOT_TOKEN);
        sendSetupCodeToGroup($BOT_TOKEN, $chatId, $chatTitle, $code);
    }
}

echo json_encode(['ok' => true]);
exit;

// ═══════════════════════════════════════
//  HELPERS
// ═══════════════════════════════════════

function generateOrGetCode($db, $chatId, $chatTitle, $chatType, $botToken) {
    $existing = $db->fetchOne(
        "SELECT setup_code FROM telegram_pending_links 
         WHERE chat_id = ? AND claimed_by_tenant_id IS NULL AND expires_at > NOW()
         ORDER BY created_at DESC LIMIT 1",
        [$chatId]
    );
    if ($existing) return $existing['setup_code'];

    $code = generateUniqueCode($db);
    $db->insert('telegram_pending_links', [
        'setup_code' => $code, 'chat_id' => $chatId,
        'chat_title' => $chatTitle, 'chat_type' => $chatType,
        'bot_token'  => null,
        'expires_at' => date('Y-m-d H:i:s', strtotime('+24 hours'))
    ]);
    return $code;
}

function generateUniqueCode($db) {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    for ($i = 0; $i < 20; $i++) {
        $code = '';
        for ($j = 0; $j < 6; $j++) $code .= $chars[random_int(0, strlen($chars) - 1)];
        $exists = $db->fetchOne("SELECT id FROM telegram_pending_links WHERE setup_code = ? AND expires_at > NOW()", [$code]);
        if (!$exists) return $code;
    }
    return strtoupper(substr(md5(uniqid()), 0, 6));
}

function sendSetupCodeToGroup($token, $chatId, $chatTitle, $code) {
    $keyboard = [
        'inline_keyboard' => [
            [['text' => '📋 ចម្លងលេខកូដ / Copy Code', 'callback_data' => 'copy_' . $code]]
        ]
    ];
    $msg = "🤖 <b>MCU POS Bot បានត្រៀមរួចរាល់!</b>\n\n";
    $msg .= "📋 ក្រុម៖ {$chatTitle}\n";
    $msg .= "🔑 លេខកូដតំឡើង៖ <code>{$code}</code>\n\n";
    $msg .= "⬆️ ចម្លងលេខកូដខាងលើ យកទៅបញ្ចូលក្នុង៖\n";
    $msg .= "<b>MCU POS → 🛰️ GPS Tracking → ⚙️ Telegram</b>\n\n";
    $msg .= "⏱️ លេខកូដផុតកំណត់ក្នុង ២៤ ម៉ោង\n";
    $msg .= "💬 វាយ /code ដើម្បីមើលលេខកូដម្តងទៀត";
    sendMessage($token, $chatId, $msg, $keyboard);
}

function getBotUsername($token) {
    $info = @file_get_contents("https://api.telegram.org/bot{$token}/getMe");
    $data = json_decode($info, true);
    return $data['result']['username'] ?? 'mcu_pos_bot';
}

function answerCallback($token, $callbackId, $text = '') {
    $data = ['callback_query_id' => $callbackId];
    if ($text) $data['text'] = $text;
    $ctx = stream_context_create(['http' => [
        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
        'method' => 'POST', 'content' => http_build_query($data), 'ignore_errors' => true
    ]]);
    @file_get_contents("https://api.telegram.org/bot{$token}/answerCallbackQuery", false, $ctx);
}

function sendMessage($token, $chatId, $text, $keyboard = null) {
    $data = ['chat_id' => $chatId, 'text' => $text, 'parse_mode' => 'HTML', 'disable_web_page_preview' => true];
    if ($keyboard) $data['reply_markup'] = json_encode($keyboard);
    $ctx = stream_context_create(['http' => [
        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
        'method' => 'POST', 'content' => http_build_query($data), 'ignore_errors' => true
    ]]);
    return @file_get_contents("https://api.telegram.org/bot{$token}/sendMessage", false, $ctx);
}

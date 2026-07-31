<?php
// config/telegram.php

require_once __DIR__ . '/env.php';

$isLocal = mc_is_local_request();

// Priority 1: plain token stored via Admin Panel → config/telegram.local.php (git-ignored)
$botToken = '';
$localFile = __DIR__ . '/telegram.local.php';
if (is_file($localFile)) {
    $localToken = require $localFile;
    if (!empty($localToken)) {
        $botToken = (string) $localToken;
    }
}

// Priority 2: environment variable MC_TELEGRAM_BOT_TOKEN
if ($botToken === '') {
    $botToken = mc_env('MC_TELEGRAM_BOT_TOKEN', '');
}

return [
    'bot_token'      => $botToken,
    'chat_id'        => mc_env('MC_TELEGRAM_CHAT_ID', ''),
    'webhook_secret' => mc_env('MC_TELEGRAM_WEBHOOK_SECRET', 'mcu_tele_sec_99'),
    // Local = empty (use polling/sync_telegram.php), Live = webhook URL
    'callback_url'   => mc_env('MC_TELEGRAM_CALLBACK_URL', $isLocal ? '' : ''),
    'is_local'       => $isLocal,
];

<?php
// config/telegram.php

require_once __DIR__ . '/env.php';
if (!class_exists('CookieCrypt')) {
    require_once __DIR__ . '/../core/classes/CookieCrypt.php';
}

$isLocal = mc_is_local_request();

return [
    'bot_token'      => CookieCrypt::decrypt(mc_env('MC_TELEGRAM_BOT_TOKEN', '')),
    'chat_id'        => mc_env('MC_TELEGRAM_CHAT_ID', ''),
    'webhook_secret' => mc_env('MC_TELEGRAM_WEBHOOK_SECRET', 'mcu_tele_sec_99'),
    // Local = empty (use polling/sync_telegram.php), Live = webhook URL
    'callback_url'   => mc_env('MC_TELEGRAM_CALLBACK_URL', $isLocal ? '' : ''),
    'is_local'       => $isLocal,
];

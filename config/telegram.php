<?php
// config/telegram.php

$isLocal = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1', '::1'])
    || (strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false);

return [
    'bot_token'    => '8688625817:AAHSiH0UAjrdZiSIEUieudrhIGK3leNgFyY',
    'chat_id'      => '7372079283',
    // Local = empty (use polling/sync_telegram.php), Live = webhook URL
    'callback_url' => $isLocal ? '' : 'https://mcu-pos.me/public/api/gps_telegram_callback.php',
    'is_local'     => $isLocal,
];

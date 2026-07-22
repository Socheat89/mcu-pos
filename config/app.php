<?php
// config/app.php

require_once __DIR__ . '/env.php';

$isProduction = mc_is_production_request();
$encodedKey = mc_env('MC_COOKIE_ENCRYPTION_KEY');
$hmacKey = mc_env('MC_COOKIE_HMAC_KEY');

if (($encodedKey === null || $encodedKey === '') && $isProduction) {
    $encodedKey = mc_required_env('MC_COOKIE_ENCRYPTION_KEY');
}

if (($hmacKey === null || $hmacKey === '') && $isProduction) {
    $hmacKey = mc_required_env('MC_COOKIE_HMAC_KEY');
}

if ($encodedKey !== null && $encodedKey !== '') {
    $cookieKey = base64_decode((string) $encodedKey, true);
    if ($cookieKey === false || strlen($cookieKey) !== 32) {
        throw new RuntimeException('MC_COOKIE_ENCRYPTION_KEY must be base64-encoded 32-byte key material.');
    }
} else {
    // Development-only fallback. Production must provide MC_COOKIE_ENCRYPTION_KEY.
    $seed = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
    $cookieKey = hash('sha256', 'mcu-pos-dev-cookie-encryption:' . $seed, true);
}

if ($hmacKey === null || $hmacKey === '') {
    // Development-only fallback. Production must provide MC_COOKIE_HMAC_KEY.
    $seed = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
    $hmacKey = hash('sha256', 'mcu-pos-dev-cookie-hmac:' . $seed);
}

return [
    'cookie_encryption_key' => $cookieKey,
    'cookie_hmac_key'       => (string) $hmacKey,
];

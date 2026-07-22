<?php
// config/database.php

require_once __DIR__ . '/env.php';

$localConfigPath = __DIR__ . '/database.local.php';
if (is_file($localConfigPath)) {
    $localConfig = require $localConfigPath;
    $requiredKeys = ['host', 'database', 'username', 'password', 'charset'];

    if (!is_array($localConfig) || array_diff($requiredKeys, array_keys($localConfig))) {
        throw new RuntimeException('Invalid local database configuration.');
    }

    return $localConfig;
}

$isProduction = mc_is_production_request();

return [
    'host'     => $isProduction ? mc_required_env('MC_DB_HOST') : mc_env('MC_DB_HOST', '127.0.0.1;port=3307'),
    'database' => $isProduction ? mc_required_env('MC_DB_DATABASE') : mc_env('MC_DB_DATABASE', 'mekocclj_mekong_saas'),
    'username' => $isProduction ? mc_required_env('MC_DB_USERNAME') : mc_env('MC_DB_USERNAME', 'root'),
    'password' => $isProduction ? mc_required_env('MC_DB_PASSWORD') : mc_env('MC_DB_PASSWORD', ''),
    'charset'  => mc_env('MC_DB_CHARSET', 'utf8mb4'),
];
?>

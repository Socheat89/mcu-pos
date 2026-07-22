<?php
// config/database.php

require_once __DIR__ . '/env.php';

$isProduction = mc_is_production_request();

return [
    'host'     => $isProduction ? mc_required_env('MC_DB_HOST') : mc_env('MC_DB_HOST', '127.0.0.1;port=3307'),
    'database' => $isProduction ? mc_required_env('MC_DB_DATABASE') : mc_env('MC_DB_DATABASE', 'mekocclj_mekong_saas'),
    'username' => $isProduction ? mc_required_env('MC_DB_USERNAME') : mc_env('MC_DB_USERNAME', 'root'),
    'password' => $isProduction ? mc_required_env('MC_DB_PASSWORD') : mc_env('MC_DB_PASSWORD', ''),
    'charset'  => mc_env('MC_DB_CHARSET', 'utf8mb4'),
];
?>

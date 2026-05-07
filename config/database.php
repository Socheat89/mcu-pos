<?php
// config/database.php

// Determine environment
$isProduction = false;
if (isset($_SERVER['HTTP_HOST']) && (
    strpos($_SERVER['HTTP_HOST'], 'mekongcyberunit.app') !== false || 
    strpos($_SERVER['HTTP_HOST'], 'mekongcy') !== false ||
    strpos($_SERVER['HTTP_HOST'], 'mcu-pos.me') !== false
)) {
    $isProduction = true;
}

if ($isProduction) {
    // Production Credentials
    return [
        'host' => 'localhost',
        'database' => 'mekocclj_mekong_saas',
        'username' => 'mekocclj_mekong_saas',
        'password' => 'Socheat@2026',
        'charset' => 'utf8mb4'
    ];
} else {
    // Local Development Credentials - with fallback for cPanel environments
    // Try cPanel hosted credentials first
    return [
        'host' => 'localhost',
        'database' => 'mekocclj_mekong_saas',
        'username' => 'mekocclj_mekong_saas',
        'password' => 'Socheat@2026',
        'charset' => 'utf8mb4'
    ];
}
?>
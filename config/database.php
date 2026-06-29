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
<<<<<<< HEAD
        'host' => 'localhost',
        'database' => 'mekong_saas',
=======
        'host' => '127.0.0.1;port=3307',
        'database' => 'mekocclj_mekong_saas',
>>>>>>> 062e3cc8d9b9f40dc40c6d6c6835e28f6f8a0d77
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4'
    ];
}
?>
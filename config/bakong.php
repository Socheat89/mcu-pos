<?php
// config/bakong.php

require_once __DIR__ . '/env.php';

return [
    'api_token'      => mc_env('MC_BAKONG_API_TOKEN', ''),
    'base_url'       => mc_env('MC_BAKONG_BASE_URL', 'https://api-bakong.nbc.gov.kh'),
    'bank_account'   => mc_env('MC_BAKONG_BANK_ACCOUNT', ''),
    'merchant_name'  => mc_env('MC_BAKONG_MERCHANT_NAME', ''),
    'merchant_city'  => mc_env('MC_BAKONG_MERCHANT_CITY', 'Phnom Penh'),
    'store_label'    => mc_env('MC_BAKONG_STORE_LABEL', 'Mekong CyberUnit'),
    'phone_number'   => mc_env('MC_BAKONG_PHONE_NUMBER', ''),
    'terminal_label' => mc_env('MC_BAKONG_TERMINAL_LABEL', 'Web Checkout')
];

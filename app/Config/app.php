<?php
/**
 * MYSLIP.IN — Application Configuration
 */

return [
    'name' => env('APP_NAME', 'MYSLIP.IN'),
    'tagline' => env('APP_TAGLINE', 'Simple Billing & Accounting for Your Business'),
    'url' => env('APP_URL', 'http://localhost/myslip.in/public'),
    'base_path' => env('APP_BASE_PATH', '/myslip.in/public'),
    'env' => env('APP_ENV', 'development'),
    'debug' => (bool)env('APP_DEBUG', true),
    'timezone' => env('APP_TIMEZONE', 'Asia/Kolkata'),
    'locale' => 'en_IN',
    'currency' => 'INR',
    'currency_symbol' => '₹',
    'version' => '1.0.0',
    'session' => [
        'name' => 'MYSLIP_SESSID',
        'lifetime' => 86400 * 7, // 7 days
        'secure' => (bool)env('SESSION_SECURE', false),
        'httponly' => true,
        'samesite' => 'Lax'
    ]
];

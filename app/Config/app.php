<?php
/**
 * MYSLIP.IN — Application Configuration
 */

return [
    'name' => getenv('APP_NAME') ?: 'MYSLIP.IN',
    'tagline' => getenv('APP_TAGLINE') ?: 'Simple Billing & Accounting for Your Business',
    'url' => getenv('APP_URL') ?: 'http://localhost/myslip.in/public',
    'base_path' => getenv('APP_BASE_PATH') !== false ? getenv('APP_BASE_PATH') : '/myslip.in/public',
    'env' => getenv('APP_ENV') ?: 'development', // 'development' or 'production'
    'debug' => getenv('APP_DEBUG') !== false ? filter_var(getenv('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN) : true,
    'timezone' => getenv('APP_TIMEZONE') ?: 'Asia/Kolkata',
    'locale' => 'en_IN',
    'currency' => 'INR',
    'currency_symbol' => '₹',
    'version' => '1.0.0',
    'session' => [
        'name' => 'MYSLIP_SESSID',
        'lifetime' => 86400 * 7, // 7 days
        'secure' => getenv('SESSION_SECURE') !== false ? filter_var(getenv('SESSION_SECURE'), FILTER_VALIDATE_BOOLEAN) : false, // Set true in HTTPS production
        'httponly' => true,
        'samesite' => 'Lax'
    ]
];

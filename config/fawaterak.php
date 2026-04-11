<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Fawaterak IFrame
    |--------------------------------------------------------------------------
    | مفاتيح من لوحة فواتيرك: Integrations → Fawaterak (API Key = Vendor، Provider Key).
    | نطاق الـ Domain في الـ HMAC يجب أن يطابق ما أدخلته في لوحة فواتيرك (HTTPS بدون / في النهاية).
    */
    'env' => env('FAWATERAK_ENV', 'test'), // test | live

    'vendor_key' => env('FAWATERAK_VENDOR_KEY', ''),
    'provider_key' => env('FAWATERAK_PROVIDER_KEY', ''),

    /**
     * قيمة حقل Domain في سلسلة الـ HMAC (مثال: https://muallimx.com أو النطاق كما في لوحة فواتيرك).
     * إن تركتها فارغة يُستخرج host من APP_URL.
     */
    'iframe_domain' => env('FAWATERAK_IFRAME_DOMAIN', ''),

    'version' => env('FAWATERAK_VERSION', '0'),

    'currency' => env('FAWATERAK_CURRENCY', 'EGP'),

    'test' => [
        'plugin_url' => env('FAWATERAK_TEST_PLUGIN_URL', 'https://staging.fawaterk.com/fawaterkPlugin/fawaterkPlugin.min.js?v=1.2'),
    ],
    'live' => [
        'plugin_url' => env('FAWATERAK_LIVE_PLUGIN_URL', 'https://app.fawaterk.com/fawaterkPlugin/fawaterkPlugin.min.js'),
    ],
];

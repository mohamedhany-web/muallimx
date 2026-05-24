<?php

return [

    /*
    |--------------------------------------------------------------------------
    | إصدار شهادة المنصة تلقائياً عند اكتمال الكورس
    |--------------------------------------------------------------------------
    */
    'platform_auto_issue' => filter_var(
        env('CERTIFICATE_PLATFORM_AUTO_ON_COMPLETE', true),
        FILTER_VALIDATE_BOOL
    ),

    /** اسم الأكاديمية الظاهر على الشهادة */
    'academy_name' => env('CERTIFICATE_ACADEMY_NAME', ''),

    /** ألوان تصميم الشهادة المعتمد (MUALLIMX Enhanced) */
    'primary' => env('CERTIFICATE_COLOR_PRIMARY', '#1B2C6E'),
    'secondary' => env('CERTIFICATE_COLOR_SECONDARY', '#E84E0E'),
    'cream' => env('CERTIFICATE_COLOR_CREAM', '#FAFAF8'),
    'accent_light' => env('CERTIFICATE_COLOR_ACCENT', '#C9A84C'),

    'director_name' => env('CERTIFICATE_DIRECTOR_NAME', 'المدير العام'),
    'director_title' => env('CERTIFICATE_DIRECTOR_TITLE', 'الإدارة التنفيذية'),
];

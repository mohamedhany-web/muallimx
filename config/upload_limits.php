<?php

$curriculumMaterialMaxKb = (int) env('CURRICULUM_MATERIAL_MAX_UPLOAD_KB', 150 * 1024);

return [

    /*
    |--------------------------------------------------------------------------
    | الحد الأقصى لحجم الملفات المرفوعة عبر التطبيق (وCloudflare)
    |--------------------------------------------------------------------------
    |
    | Laravel يعبّر عن قاعدة max للملفات بالكيلوبايت (وحدة 1024 بايت).
    | 40 ميجابايت ≈ 40 × 1024 = 40960 كيلوبايت.
    |
    */

    'max_upload_kb' => (int) env('MAX_UPLOAD_KB', 40960),

    'max_upload_bytes' => (int) env('MAX_UPLOAD_BYTES', 40 * 1024 * 1024),

    /*
    |--------------------------------------------------------------------------
    | مواد مكتبة المناهج (رفع إلى R2)
    |--------------------------------------------------------------------------
    |
    | افتراضي 150 × 1024 كيلوبايت = 150 ميجابايت (ميغابايت ثنائي).
    | يجب أن يكون upload_max_filesize و post_max_size في PHP ≥ هذا الحد.
    |
    */

    'curriculum_material_max_kb' => $curriculumMaterialMaxKb,

    'curriculum_material_max_bytes' => $curriculumMaterialMaxKb * 1024,

];

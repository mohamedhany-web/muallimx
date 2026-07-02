<?php

/**
 * إعدادات اتصال مزوّد التوليد النصي (Muallimx AI).
 * أسماء متغيرات البيئة التالية تبقى للتوافق مع الإصدارات السابقة.
 */
return [
    'enabled' => filter_var(env('GEMINI_ENABLED', false), FILTER_VALIDATE_BOOLEAN),

    'api_key' => env('GEMINI_API_KEY'),

    /** معرّف النموذج في مسار REST: models/{model}:generateContent */
    'model' => env('GEMINI_MODEL', 'gemini-flash-latest'),

    /** قاعدة REST (بدون / في النهاية) */
    'base_url' => rtrim((string) env('GEMINI_API_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'), '/'),

    /** مهلة HTTP بالثواني */
    'http_timeout' => (int) env('GEMINI_HTTP_TIMEOUT', 120),

    /** إعادة المحاولة عند 503/ضغط مؤقت */
    'retry_attempts' => (int) env('GEMINI_RETRY_ATTEMPTS', 3),
    'retry_delay_ms' => (int) env('GEMINI_RETRY_DELAY_MS', 1500),

    /** نماذج احتياطية عند فشل النموذج الأساسي (مفصولة بفاصلة) */
    'fallback_models' => env('GEMINI_FALLBACK_MODELS', 'gemini-flash-latest,gemini-2.5-flash-lite'),

    /** حد مخرجات تقريبي */
    'max_output_tokens' => (int) env('GEMINI_MAX_OUTPUT_TOKENS', 8192),

    /** حد أعلى لملف لعبة HTML (ألعاب تعليمية) عند الحاجة */
    'max_output_tokens_educational_game' => (int) env('GEMINI_MAX_OUTPUT_TOKENS_GAME', 16384),

    /**
     * لنماذج Gemini 2.5+ — 0 يعطّل نص التفكير الداخلي في المخرجات.
     * null = لا يُرسل thinkingConfig (للنماذج الأقدم).
     */
    'thinking_budget' => env('GEMINI_THINKING_BUDGET', '0') === '' ? null : (int) env('GEMINI_THINKING_BUDGET', 0),
];

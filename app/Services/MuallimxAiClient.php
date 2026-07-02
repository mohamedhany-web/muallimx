<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * عميل توليد النصوص لـ Muallimx AI (REST generateContent).
 */
class MuallimxAiClient
{
    /**
     * يعمل عند وجود مفتاح صالح. يُعطّل فقط إذا عُطّل التفعيل صراحةً في .env (قيمة false/0/off/no).
     */
    public function isConfigured(): bool
    {
        if (! filled(config('muallimx_ai.api_key'))) {
            return false;
        }

        if (app()->runningUnitTests()) {
            return (bool) config('muallimx_ai.enabled', false);
        }

        $raw = env('GEMINI_ENABLED');
        if ($raw === null || $raw === '') {
            return true;
        }

        return filter_var($raw, FILTER_VALIDATE_BOOLEAN);
    }

    public function isTransientError(\Throwable $e): bool
    {
        $msg = strtolower((string) $e->getMessage());

        return str_contains($msg, '503')
            || str_contains($msg, '502')
            || str_contains($msg, '504')
            || str_contains($msg, '429')
            || str_contains($msg, 'high demand')
            || str_contains($msg, 'unavailable')
            || str_contains($msg, 'overloaded')
            || str_contains($msg, 'resource_exhausted')
            || str_contains($msg, 'timed out')
            || str_contains($msg, 'timeout');
    }

    /**
     * ترجمة أخطاء الـAPI إلى رسالة للمستخدم.
     */
    public function userFacingErrorMessage(\Throwable $e): string
    {
        $msg = strtolower((string) $e->getMessage());

        if ($this->isTransientError($e)) {
            if (str_contains($msg, '429') || str_contains($msg, 'quota') || str_contains($msg, 'resource_exhausted')) {
                return __('student.full_ai_suite.muallimx_ai_error_quota');
            }

            return __('student.full_ai_suite.muallimx_ai_error_busy');
        }

        if (str_contains($msg, '401') || str_contains($msg, '403') || str_contains($msg, 'api key not valid')) {
            return __('student.full_ai_suite.muallimx_ai_error_auth');
        }

        if (str_contains($msg, '404') || str_contains($msg, 'not found')) {
            return __('student.full_ai_suite.muallimx_ai_error_model');
        }

        if (str_contains($msg, 'safety') || str_contains($msg, 'blocklist')) {
            return __('student.full_ai_suite.muallimx_ai_error_safety');
        }

        if (config('app.debug')) {
            return __('student.full_ai_suite.muallimx_ai_error_debug', [
                'detail' => mb_substr((string) $e->getMessage(), 0, 400),
            ]);
        }

        return __('student.full_ai_suite.muallimx_ai_error_generic');
    }

    /**
     * @param  int|null  $maxOutputTokensOverride  عند الحاجة لمخرجات أطول (مثل ملف HTML كامل)
     *
     * @throws \RuntimeException عند فشل الشبكة أو الاستجابة أو عدم وجود نص في المخرجات
     */
    public function generateFromPrompt(string $prompt, ?int $maxOutputTokensOverride = null): string
    {
        $primary = (string) config('muallimx_ai.model', 'gemini-flash-latest');
        $models = array_values(array_unique(array_filter(array_merge(
            [$primary],
            $this->fallbackModels()
        ))));

        $lastException = null;

        foreach ($models as $model) {
            try {
                return $this->generateWithModel($prompt, $model, $maxOutputTokensOverride);
            } catch (\Throwable $e) {
                $lastException = $e;
                if (! $this->isTransientError($e)) {
                    throw $e;
                }
                Log::warning('Muallimx AI model failed, trying next if any', [
                    'model' => $model,
                    'message' => mb_substr($e->getMessage(), 0, 300),
                ]);
            }
        }

        throw $lastException ?? new \RuntimeException('Muallimx AI request failed.');
    }

    /**
     * @return list<string>
     */
    private function fallbackModels(): array
    {
        $raw = (string) config('muallimx_ai.fallback_models', 'gemini-flash-latest,gemini-2.5-flash-lite');
        $models = array_map('trim', explode(',', $raw));

        return array_values(array_filter($models));
    }

    private function generateWithModel(string $prompt, string $model, ?int $maxOutputTokensOverride): string
    {
        $attempts = max(1, (int) config('muallimx_ai.retry_attempts', 3));
        $delayMs = max(0, (int) config('muallimx_ai.retry_delay_ms', 1500));
        $lastException = null;

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            try {
                return $this->sendGenerateRequest($prompt, $model, $maxOutputTokensOverride);
            } catch (\Throwable $e) {
                $lastException = $e;
                if (! $this->isTransientError($e) || $attempt >= $attempts) {
                    throw $e;
                }
                Log::info('Muallimx AI transient error, retrying', [
                    'model' => $model,
                    'attempt' => $attempt,
                    'message' => mb_substr($e->getMessage(), 0, 200),
                ]);
                if ($delayMs > 0) {
                    usleep($delayMs * 1000 * $attempt);
                }
            }
        }

        throw $lastException ?? new \RuntimeException('Muallimx AI request failed.');
    }

    private function sendGenerateRequest(string $prompt, string $model, ?int $maxOutputTokensOverride): string
    {
        $key = trim((string) config('muallimx_ai.api_key'));
        $base = rtrim((string) config('muallimx_ai.base_url', 'https://generativelanguage.googleapis.com/v1beta'), '/');
        $timeout = (int) config('muallimx_ai.http_timeout', 120);
        $maxOut = $maxOutputTokensOverride ?? (int) config('muallimx_ai.max_output_tokens', 8192);

        $generationConfig = [
            'maxOutputTokens' => $maxOut,
        ];

        $thinkingBudget = config('muallimx_ai.thinking_budget');
        if ($thinkingBudget !== null && $this->modelSupportsThinkingBudget($model)) {
            $generationConfig['thinkingConfig'] = [
                'thinkingBudget' => (int) $thinkingBudget,
            ];
        }

        $url = "{$base}/models/{$model}:generateContent";

        $response = Http::timeout($timeout)
            ->acceptJson()
            ->withQueryParameters(['key' => $key])
            ->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => $generationConfig,
            ]);

        return $this->parseSuccessfulResponse($response, $model);
    }

    private function parseSuccessfulResponse(Response $response, string $model): string
    {
        if (! $response->successful()) {
            $body = $response->json();
            $msg = is_array($body) ? (string) data_get($body, 'error.message', $response->body()) : $response->body();
            Log::warning('Muallimx AI HTTP error', [
                'model' => $model,
                'status' => $response->status(),
                'message' => mb_substr($msg, 0, 500),
            ]);

            throw new \RuntimeException('HTTP '.$response->status().': '.mb_substr($msg, 0, 200));
        }

        $json = $response->json();
        if (! is_array($json)) {
            throw new \RuntimeException('Invalid JSON response');
        }

        if (isset($json['error']['message'])) {
            throw new \RuntimeException((string) $json['error']['message']);
        }

        $finish = data_get($json, 'candidates.0.finishReason');
        if ($finish === 'SAFETY' || $finish === 'BLOCKLIST') {
            throw new \RuntimeException('Response blocked by safety filters.');
        }

        $text = $this->extractTextFromResponse($json);
        if ($text === '') {
            Log::warning('Muallimx AI empty or unparsed candidates', [
                'model' => $model,
                'finishReason' => $finish,
                'keys' => array_keys($json),
                'candidate_preview' => mb_substr(json_encode(data_get($json, 'candidates.0'), JSON_UNESCAPED_UNICODE), 0, 800),
            ]);

            throw new \RuntimeException('No text in model response.');
        }

        return $text;
    }

    /**
     * @param  array<string, mixed>  $json
     */
    private function extractTextFromResponse(array $json): string
    {
        $candidates = data_get($json, 'candidates');
        if (! is_array($candidates)) {
            return '';
        }

        foreach ($candidates as $candidate) {
            if (! is_array($candidate)) {
                continue;
            }
            $parts = data_get($candidate, 'content.parts');
            if (! is_array($parts)) {
                continue;
            }
            $chunks = [];
            foreach ($parts as $part) {
                if (! is_array($part) || ! isset($part['text']) || ! is_string($part['text'])) {
                    continue;
                }
                if (! empty($part['thought'])) {
                    continue;
                }
                $text = $part['text'];
                if ($this->looksLikeThinkingLeak($text)) {
                    continue;
                }
                $chunks[] = $text;
            }
            $joined = trim(implode('', $chunks));
            if ($joined !== '') {
                return $joined;
            }
        }

        return '';
    }

    private function modelSupportsThinkingBudget(string $model): bool
    {
        $m = strtolower($model);

        return str_contains($m, 'gemini-2.5') || str_contains($m, 'gemini-3');
    }

    private function looksLikeThinkingLeak(string $text): bool
    {
        return (bool) preg_match('/^(THINK|THOUGHTS|REASONING)\s*:/iu', trim($text));
    }
}

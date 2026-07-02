<?php

namespace Tests\Unit;

use App\Services\MuallimxAiClient;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MuallimxAiClientTest extends TestCase
{
    public function test_is_configured_requires_enabled_and_key(): void
    {
        Config::set('muallimx_ai.enabled', false);
        Config::set('muallimx_ai.api_key', 'secret');
        $this->assertFalse(app(MuallimxAiClient::class)->isConfigured());

        Config::set('muallimx_ai.enabled', true);
        Config::set('muallimx_ai.api_key', '');
        $this->assertFalse(app(MuallimxAiClient::class)->isConfigured());

        Config::set('muallimx_ai.enabled', true);
        Config::set('muallimx_ai.api_key', 'secret');
        $this->assertTrue(app(MuallimxAiClient::class)->isConfigured());
    }

    public function test_generate_from_prompt_concatenates_parts(): void
    {
        Config::set('muallimx_ai.enabled', true);
        Config::set('muallimx_ai.api_key', 'test-api-key');
        Config::set('muallimx_ai.model', 'gemini-flash-latest');
        Config::set('muallimx_ai.base_url', 'https://generativelanguage.googleapis.com/v1beta');
        Config::set('muallimx_ai.http_timeout', 10);
        Config::set('muallimx_ai.max_output_tokens', 256);

        Http::fake([
            '*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Hello '],
                                ['text' => 'world'],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $out = app(MuallimxAiClient::class)->generateFromPrompt('[SYSTEM] test');

        $this->assertSame('Hello world', $out);

        Http::assertSentCount(1);
    }

    public function test_extract_skips_thinking_leak_parts(): void
    {
        Config::set('muallimx_ai.enabled', true);
        Config::set('muallimx_ai.api_key', 'test-api-key');
        Config::set('muallimx_ai.model', 'gemini-2.5-flash');
        Config::set('muallimx_ai.thinking_budget', null);

        Http::fake([
            '*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'THINK: reasoning here'],
                                ['text' => 'مرحباً'],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $out = app(MuallimxAiClient::class)->generateFromPrompt('test');

        $this->assertSame('مرحباً', $out);
    }

    public function test_retries_on_503_then_succeeds(): void
    {
        Config::set('muallimx_ai.enabled', true);
        Config::set('muallimx_ai.api_key', 'test-api-key');
        Config::set('muallimx_ai.model', 'gemini-2.5-flash');
        Config::set('muallimx_ai.fallback_models', '');
        Config::set('muallimx_ai.retry_attempts', 3);
        Config::set('muallimx_ai.retry_delay_ms', 0);

        Http::fake([
            '*' => Http::sequence()
                ->push(['error' => ['message' => 'high demand']], 503)
                ->push([
                    'candidates' => [
                        ['content' => ['parts' => [['text' => 'نجح']]]],
                    ],
                ], 200),
        ]);

        $out = app(MuallimxAiClient::class)->generateFromPrompt('test');

        $this->assertSame('نجح', $out);
        Http::assertSentCount(2);
    }

    public function test_user_facing_message_for_503_is_busy_not_raw_http(): void
    {
        $client = app(MuallimxAiClient::class);
        $msg = $client->userFacingErrorMessage(new \RuntimeException('HTTP 503: high demand'));

        $this->assertStringNotContainsString('HTTP 503', $msg);
        $this->assertSame(__('student.full_ai_suite.muallimx_ai_error_busy'), $msg);
    }
}

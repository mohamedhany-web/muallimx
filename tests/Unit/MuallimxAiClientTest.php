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
}

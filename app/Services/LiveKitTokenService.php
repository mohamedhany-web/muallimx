<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Mint LiveKit access tokens for classroom pilot rooms.
 * Secrets come from config/services.php (env) — never expose to the browser.
 */
class LiveKitTokenService
{
    public function isConfigured(): bool
    {
        $url = (string) config('services.livekit.url', '');
        $key = (string) config('services.livekit.api_key', '');
        $secret = (string) config('services.livekit.api_secret', '');

        return $url !== '' && $key !== '' && $secret !== '';
    }

    public function wsUrl(): string
    {
        return rtrim((string) config('services.livekit.url', ''), '/');
    }

    /**
     * @param  array{
     *   canPublish?:bool,
     *   canSubscribe?:bool,
     *   canPublishData?:bool,
     *   roomAdmin?:bool,
     *   canPublishSources?:list<string>
     * }  $grants
     */
    public function createToken(
        string $roomName,
        string $identity,
        string $displayName = '',
        array $grants = [],
        int $ttlSeconds = 7200
    ): string {
        if (! $this->isConfigured()) {
            throw new RuntimeException('LiveKit is not configured (LIVEKIT_URL / API_KEY / API_SECRET).');
        }

        $apiKey = (string) config('services.livekit.api_key');
        $apiSecret = (string) config('services.livekit.api_secret');
        $now = time();

        $video = [
            'roomJoin' => true,
            'room' => $roomName,
            'canPublish' => (bool) ($grants['canPublish'] ?? true),
            'canSubscribe' => (bool) ($grants['canSubscribe'] ?? true),
            'canPublishData' => (bool) ($grants['canPublishData'] ?? true),
        ];
        if (! empty($grants['roomAdmin'])) {
            $video['roomAdmin'] = true;
        }
        if (! empty($grants['canPublishSources']) && is_array($grants['canPublishSources'])) {
            $sources = array_values(array_filter(array_map('strval', $grants['canPublishSources'])));
            if ($sources !== []) {
                $video['canPublishSources'] = $sources;
            }
        }

        $header = ['alg' => 'HS256', 'typ' => 'JWT', 'kid' => $apiKey];
        $payload = [
            'iss' => $apiKey,
            'sub' => $identity,
            'nbf' => $now - 10,
            'exp' => $now + max(60, $ttlSeconds),
            'name' => $displayName !== '' ? $displayName : $identity,
            'video' => $video,
        ];

        try {
            return $this->encodeJwt($header, $payload, $apiSecret);
        } catch (\Throwable $e) {
            Log::error('LiveKit token mint failed', ['error' => $e->getMessage()]);
            throw new RuntimeException('Failed to mint LiveKit token.');
        }
    }

    /**
     * Minimal HS256 JWT (no external JWT package required).
     *
     * @param  array<string, mixed>  $header
     * @param  array<string, mixed>  $payload
     */
    private function encodeJwt(array $header, array $payload, string $secret): string
    {
        $segments = [
            $this->base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)),
            $this->base64UrlEncode(json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)),
        ];
        $signingInput = implode('.', $segments);
        $signature = hash_hmac('sha256', $signingInput, $secret, true);
        $segments[] = $this->base64UrlEncode($signature);

        return implode('.', $segments);
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}

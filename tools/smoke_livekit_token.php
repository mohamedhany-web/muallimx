<?php

/**
 * Smoke: mint a LiveKit token via LiveKitTokenService (no HTTP).
 * Usage: php tools/smoke_livekit_token.php
 * Requires LIVEKIT_* in .env (or environment).
 */

declare(strict_types=1);

use App\Services\LiveKitTokenService;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$svc = app(LiveKitTokenService::class);

if (! $svc->isConfigured()) {
    fwrite(STDERR, "FAIL: LiveKit not configured (set LIVEKIT_URL / LIVEKIT_API_KEY / LIVEKIT_API_SECRET).\n");
    exit(1);
}

$token = $svc->createToken('Muallimx-SMOKE', 'smoke-tester', 'Smoke', [
    'canPublish' => true,
    'canSubscribe' => true,
]);

$parts = explode('.', $token);
if (count($parts) !== 3) {
    fwrite(STDERR, "FAIL: token is not a JWT\n");
    exit(1);
}

$payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')) ?: '', true);
if (! is_array($payload) || ($payload['video']['room'] ?? null) !== 'Muallimx-SMOKE') {
    fwrite(STDERR, "FAIL: payload room mismatch\n");
    exit(1);
}

echo "OK: LiveKit token minted\n";
echo 'URL: '.$svc->wsUrl()."\n";
echo 'iss: '.($payload['iss'] ?? '')."\n";
echo 'exp: '.($payload['exp'] ?? '')."\n";
exit(0);

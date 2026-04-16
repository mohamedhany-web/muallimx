<?php

declare(strict_types=1);

/**
 * Quick local test for n8n callback endpoint.
 *
 * Usage (PowerShell):
 *   php tools/test_n8n_callback.php 1 test-token
 */

$reportId = (int) ($argv[1] ?? 1);
$token = (string) ($argv[2] ?? 'test-token');

$url = "http://127.0.0.1:8000/api/n8n/live-session-reports/{$reportId}";

$payload = [
    'status' => 'completed',
    'title' => 'dummy updated',
    'summary' => 'test summary from tools/test_n8n_callback.php',
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "X-N8N-Token: {$token}",
    'Accept: application/json',
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));

$body = curl_exec($ch);
$err = curl_error($ch);
$code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP {$code}\n";
if ($err) {
    echo "cURL error: {$err}\n";
}
echo (string) $body;
echo "\n";


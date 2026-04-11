<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FawaterakApiService
{
    public function integrationMode(): string
    {
        $m = strtolower(trim((string) config('fawaterak.integration', 'iframe')));

        return $m === 'api' ? 'api' : 'iframe';
    }

    public function isConfigured(): bool
    {
        return $this->bearerToken() !== '';
    }

    public function baseUrl(): string
    {
        $configured = trim((string) config('fawaterak.api.base_url', ''));
        if ($configured !== '') {
            return rtrim($configured, '/');
        }

        return config('fawaterak.env', 'test') === 'live'
            ? 'https://app.fawaterk.com/api/v2'
            : 'https://staging.fawaterk.com/api/v2';
    }

    /**
     * @return array{ok: bool, status: int, json: ?array, body: string}
     */
    public function getPaymentMethods(): array
    {
        return $this->request('GET', '/getPaymentmethods', null);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{ok: bool, status: int, json: ?array, body: string}
     */
    public function invoiceInitPay(array $payload): array
    {
        return $this->request('POST', '/invoiceInitPay', $payload);
    }

    /**
     * @param  array<string, mixed>|null  $jsonBody
     * @return array{ok: bool, status: int, json: ?array, body: string}
     */
    private function request(string $method, string $path, ?array $jsonBody): array
    {
        $url = $this->baseUrl().$path;
        $timeout = (int) config('fawaterak.api.timeout', 45);
        if ($timeout < 5) {
            $timeout = 5;
        }

        try {
            $req = Http::timeout($timeout)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$this->bearerToken(),
                ]);

            $response = $method === 'GET'
                ? $req->get($url)
                : $req->asJson()->post($url, $jsonBody ?? []);

            $body = $response->body();
            $decoded = $response->json();

            return [
                'ok' => $response->successful(),
                'status' => $response->status(),
                'json' => is_array($decoded) ? $decoded : null,
                'body' => $body,
            ];
        } catch (\Throwable $e) {
            Log::error('Fawaterak API request failed', [
                'method' => $method,
                'path' => $path,
                'message' => $e->getMessage(),
            ]);

            return [
                'ok' => false,
                'status' => 0,
                'json' => null,
                'body' => '',
            ];
        }
    }

    private function bearerToken(): string
    {
        return trim((string) config('fawaterak.api.token', ''));
    }
}

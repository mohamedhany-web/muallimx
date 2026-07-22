<?php

namespace App\Services;

/**
 * حل أزرار صفحات الهبوط إلى روابط جاهزة للعرض.
 */
class LandingPageCtaResolver
{
    /**
     * @param  array<string, mixed>  $button
     * @return array{label: string, url: string, action: string}|null
     */
    public static function resolve(array $button, ?string $utmSource = null, ?string $utmCampaign = null): ?array
    {
        $label = trim((string) ($button['label'] ?? ''));
        $action = (string) ($button['action'] ?? '');

        if ($label === '' || $action === '') {
            return null;
        }

        $url = match ($action) {
            'register' => route('register'),
            'pricing' => route('public.pricing'),
            'whatsapp' => self::whatsappUrl(
                (string) ($button['whatsapp_number'] ?? ''),
                (string) ($button['whatsapp_message'] ?? '')
            ),
            'custom' => self::safeUrl((string) ($button['url'] ?? '')),
            default => null,
        };

        if ($url === null || $url === '') {
            return null;
        }

        if (in_array($action, ['register', 'pricing'], true)) {
            $url = self::appendUtm($url, $utmSource, $utmCampaign);
        }

        return [
            'label' => $label,
            'url' => $url,
            'action' => $action,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $buttons
     * @return list<array{label: string, url: string, action: string}>
     */
    public static function resolveMany(array $buttons, ?string $utmSource = null, ?string $utmCampaign = null): array
    {
        $out = [];
        foreach ($buttons as $button) {
            if (! is_array($button)) {
                continue;
            }
            $resolved = self::resolve($button, $utmSource, $utmCampaign);
            if ($resolved !== null) {
                $out[] = $resolved;
            }
        }

        return $out;
    }

    private static function whatsappUrl(string $number, string $message): ?string
    {
        $digits = preg_replace('/\D+/', '', $number) ?? '';
        if ($digits === '') {
            return null;
        }

        $query = $message !== '' ? '?text='.rawurlencode($message) : '';

        return 'https://wa.me/'.$digits.$query;
    }

    private static function safeUrl(string $url): ?string
    {
        $url = trim($url);
        if ($url === '') {
            return null;
        }

        if (str_starts_with($url, '/')) {
            return url($url);
        }

        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }

        return null;
    }

    private static function appendUtm(string $url, ?string $source, ?string $campaign): string
    {
        $params = [];
        if (is_string($source) && $source !== '') {
            $params['utm_source'] = $source;
        }
        if (is_string($campaign) && $campaign !== '') {
            $params['utm_campaign'] = $campaign;
        }
        if ($params === []) {
            return $url;
        }

        $sep = str_contains($url, '?') ? '&' : '?';

        return $url.$sep.http_build_query($params);
    }
}

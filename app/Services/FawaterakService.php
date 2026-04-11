<?php

namespace App\Services;

class FawaterakService
{
    private string $env;

    public function __construct()
    {
        $this->env = config('fawaterak.env', 'test') === 'live' ? 'live' : 'test';
    }

    public function envType(): string
    {
        return $this->env === 'live' ? 'live' : 'test';
    }

    public function pluginScriptUrl(): string
    {
        $url = config("fawaterak.{$this->env}.plugin_url");

        return is_string($url) && $url !== '' ? $url : 'https://app.fawaterk.com/fawaterkPlugin/fawaterkPlugin.min.js';
    }

    public function isConfigured(): bool
    {
        return $this->vendorSecret() !== '' && $this->providerKey() !== '';
    }

    public function versionString(): string
    {
        $v = config('fawaterak.version', '0');

        return (string) $v;
    }

    /**
     * النطاق في سلسلة HMAC يجب أن يطابق حرفياً ما يرسله سكربت فواتيرك في ترويسة FAWATERAK-DOMAIN
     * (السكربت يبني: "https://" + document.location.hostname — بدون مسار وبدون شرطة أخيرة).
     */
    public function domainForHash(): string
    {
        $configured = trim((string) config('fawaterak.iframe_domain', ''));
        if ($configured !== '') {
            return rtrim($configured, '/');
        }

        $appUrl = (string) config('app.url', '');
        $host = parse_url($appUrl, PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            return 'https://localhost';
        }

        // يطابق الإضافة: دائماً https:// + hostname (كما في fawaterkPlugin.min.js)
        return 'https://'.$host;
    }

    public function generateHashKey(): string
    {
        $domain = $this->domainForHash();
        $providerKey = $this->providerKey();
        $queryParam = 'Domain='.$domain.'&ProviderKey='.$providerKey;

        return hash_hmac('sha256', $queryParam, $this->vendorSecret(), false);
    }

    private function vendorSecret(): string
    {
        return trim((string) config('fawaterak.vendor_key', ''));
    }

    private function providerKey(): string
    {
        return trim((string) config('fawaterak.provider_key', ''));
    }
}

<?php

if (! function_exists('asset_version')) {
    /**
     * رقم إصدار ثابت للأصول المحلية — يتغيّر عند النشر (ASSET_VERSION أو تاريخ composer.lock).
     */
    function asset_version(): string
    {
        static $version = null;

        if ($version !== null) {
            return $version;
        }

        $configured = config('app.asset_version');
        if (filled($configured)) {
            $version = (string) $configured;

            return $version;
        }

        $lock = base_path('composer.lock');
        if (is_file($lock)) {
            $version = (string) filemtime($lock);

            return $version;
        }

        $version = (string) config('app.version', '1');

        return $version;
    }
}

if (! function_exists('vasset')) {
    /**
     * رابط أصل محلي مع ?v= لكسر كاش المتصفح بعد التحديثات.
     */
    function vasset(string $path): string
    {
        $url = asset($path);
        $separator = str_contains($url, '?') ? '&' : '?';

        return $url.$separator.'v='.rawurlencode(asset_version());
    }
}

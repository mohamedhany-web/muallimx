<?php

namespace App\Services;

/**
 * استخراج وتشغيل روابط يوتيوب داخل المنصة (بدون التوجيه لليوتيوب).
 */
class YouTubeVideoService
{
    public static function extractId(?string $url): ?string
    {
        if ($url === null || trim($url) === '') {
            return null;
        }

        $url = trim($url);

        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $url)) {
            return $url;
        }

        $patterns = [
            '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/',
            '/youtube\.com\/shorts\/([a-zA-Z0-9_-]{11})/',
            '/youtube\.com\/live\/([a-zA-Z0-9_-]{11})/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $m)) {
                return $m[1];
            }
        }

        return null;
    }

    public static function isValidUrl(?string $url): bool
    {
        return self::extractId($url) !== null;
    }

    public static function embedUrl(string $youtubeId, array $params = []): string
    {
        $defaults = [
            'rel' => '0',
            'modestbranding' => '1',
            'playsinline' => '1',
            'controls' => '1',
        ];

        $query = http_build_query(array_merge($defaults, $params));

        return 'https://www.youtube.com/embed/'.$youtubeId.'?'.$query;
    }

    public static function thumbnailUrl(string $youtubeId, string $quality = 'hqdefault'): string
    {
        $allowed = ['default', 'mqdefault', 'hqdefault', 'sddefault', 'maxresdefault'];
        if (! in_array($quality, $allowed, true)) {
            $quality = 'hqdefault';
        }

        return "https://img.youtube.com/vi/{$youtubeId}/{$quality}.jpg";
    }

    public static function watchUrl(string $youtubeId): string
    {
        return 'https://www.youtube.com/watch?v='.$youtubeId;
    }

    /**
     * @return array{youtube_id: string, youtube_url: string, thumbnail_url: string}
     */
    public static function normalizeFromInput(string $urlOrId): array
    {
        $id = self::extractId($urlOrId);
        if (! $id) {
            throw new \InvalidArgumentException('رابط يوتيوب غير صالح.');
        }

        return [
            'youtube_id' => $id,
            'youtube_url' => self::watchUrl($id),
            'thumbnail_url' => self::thumbnailUrl($id),
        ];
    }

    public static function formatDuration(?int $seconds): ?string
    {
        if ($seconds === null || $seconds <= 0) {
            return null;
        }

        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);
        $s = $seconds % 60;

        if ($h > 0) {
            return sprintf('%d:%02d:%02d', $h, $m, $s);
        }

        return sprintf('%d:%02d', $m, $s);
    }
}

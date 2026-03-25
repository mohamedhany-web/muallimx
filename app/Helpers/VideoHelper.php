<?php

namespace App\Helpers;

class VideoHelper
{
    /**
     * تحويل رابط الفيديو إلى رابط قابل للتضمين
     */
    public static function getEmbedUrl($url)
    {
        if (empty($url)) {
            return null;
        }

        // Bunny.net (Bunny Stream) - iframe أو player.mediadelivery.net
        if (preg_match('/(?:iframe|player)\.mediadelivery\.net\/(embed|play)\/(\d+)\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            $mode = $matches[1];
            $libraryId = $matches[2];
            $videoId = $matches[3];
            // NOTE: Prefer iframe host for embedding, preserve play/embed mode.
            $base = "https://iframe.mediadelivery.net/{$mode}/{$libraryId}/{$videoId}";
            $parsed = parse_url($url);
            $query = isset($parsed['query']) ? '?' . $parsed['query'] : '';
            return $base . $query;
        }

        return null;
    }

    /**
     * تحديد نوع مصدر الفيديو
     */
    public static function getVideoSource($url)
    {
        if (empty($url)) {
            return 'unknown';
        }

        if (strpos($url, 'iframe.mediadelivery.net') !== false || strpos($url, 'mediadelivery.net') !== false) {
            return 'bunny';
        }

        return 'other';
    }

    /**
     * الحصول على صورة مصغرة للفيديو
     */
    public static function getThumbnail($url)
    {
        if (empty($url)) {
            return null;
        }

        // YouTube thumbnail
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches)) {
            $videoId = $matches[1];
            return "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg";
        }

        // Vimeo thumbnail (يحتاج API call، لكن يمكن استخدام صورة افتراضية)
        if (preg_match('/vimeo\.com\/(\d+)/', $url, $matches)) {
            // يمكن تطوير هذا لاحقاً للحصول على الصورة المصغرة من Vimeo API
            return null;
        }

        return null;
    }

    /**
     * التحقق من صحة رابط الفيديو
     */
    public static function isValidVideoUrl($url)
    {
        if (empty($url)) {
            return false;
        }

        return (bool) preg_match('/(?:iframe|player)\.mediadelivery\.net\/(embed|play)\/(\d+)\/([a-zA-Z0-9_-]+)/', $url);
    }

    /**
     * إنشاء كود HTML لتضمين الفيديو
     */
    public static function generateEmbedHtml($url, $width = '100%', $height = '400px')
    {
        $embedUrl = self::getEmbedUrl($url);
        $source = self::getVideoSource($url);

        if (!$embedUrl) {
            return '<div class="bg-red-100 text-red-700 p-4 rounded-lg">رابط الفيديو غير صحيح أو غير مدعوم</div>';
        }

        switch ($source) {
            case 'youtube':
            case 'vimeo':
            case 'google_drive':
            case 'bunny':
                return "<iframe src='{$embedUrl}' width='{$width}' height='{$height}' frameborder='0' allow='encrypted-media' class='w-full h-full' style='border: none;'></iframe>";
            
            case 'direct':
                return "<video width='{$width}' height='{$height}' class='w-full h-full' controlsList='nodownload noplaybackrate nofullscreen noremoteplayback' disablePictureInPicture disableRemotePlayback><source src='{$embedUrl}' type='video/mp4'>متصفحك لا يدعم تشغيل الفيديو.</video>";
            
            default:
                return "<div class='bg-yellow-100 text-yellow-700 p-4 rounded-lg h-full flex items-center justify-center'>نوع الفيديو غير مدعوم حالياً</div>";
        }
    }
}

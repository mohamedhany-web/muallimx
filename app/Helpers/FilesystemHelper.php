<?php

if (!function_exists('community_disk')) {
    /**
     * قرص تخزين ملفات المجتمع (تقديمات المساهمين).
     * يُفضّل القراءة من .env إن وُجدت لتجنب مشكلة كاش الإعدادات.
     *
     * @return string 'r2' أو 'local'
     */
    function community_disk(): string
    {
        $envDisk = env('FILESYSTEM_DISK_COMMUNITY');
        if ($envDisk !== null && $envDisk !== '' && in_array($envDisk, ['r2', 'local'], true)) {
            return $envDisk;
        }
        return config('filesystems.community_disk', 'local');
    }
}

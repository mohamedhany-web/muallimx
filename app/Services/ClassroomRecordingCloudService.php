<?php

namespace App\Services;

use App\Models\ClassroomMeeting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * ربط/التحقق من ملفات تسجيل Classroom على Cloudflare R2.
 */
class ClassroomRecordingCloudService
{
    /**
     * تحقق مرن من وجود الملف على R2 (exists ثم size).
     */
    public function objectExists(string $path): bool
    {
        $path = ltrim($path, '/');
        if ($path === '' || str_contains($path, '..')) {
            return false;
        }

        $disk = Storage::disk('live_recordings_r2');

        try {
            if ($disk->exists($path)) {
                return true;
            }
        } catch (\Throwable $e) {
            Log::warning('classroom recording exists() failed', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
        }

        try {
            $size = (int) $disk->size($path);

            return $size > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * ابحث عن ملف يتيم على R2 يطابق room_name واربطه بالاجتماع.
     */
    public function tryLinkOrphanForMeeting(ClassroomMeeting $meeting): ?string
    {
        if (! empty($meeting->recording_path) && $this->objectExists((string) $meeting->recording_path)) {
            return (string) $meeting->recording_path;
        }

        $room = trim((string) ($meeting->room_name ?? ''));
        if ($room === '') {
            return null;
        }

        $needle = strtolower($room);
        $disk = Storage::disk('live_recordings_r2');

        $candidates = [];
        $prefixes = array_values(array_unique([
            'classroom-recordings/'.now()->format('Y/m'),
            'classroom-recordings/'.now()->subMonth()->format('Y/m'),
            'classroom-recordings/'.now()->format('Y'),
            'classroom-recordings',
        ]));

        foreach ($prefixes as $prefix) {
            try {
                $files = $disk->files($prefix);
            } catch (\Throwable $e) {
                try {
                    $files = $disk->allFiles($prefix);
                } catch (\Throwable $e2) {
                    continue;
                }
            }

            foreach ($files as $file) {
                $base = strtolower(basename((string) $file));
                $full = strtolower((string) $file);
                if (! str_contains($full, $needle) && ! str_contains($base, $needle)) {
                    continue;
                }
                if (! preg_match('/\.(mp4|webm|mkv)$/i', $file)) {
                    continue;
                }
                $candidates[] = (string) $file;
            }

            if ($candidates !== []) {
                break;
            }
        }

        if ($candidates === []) {
            return null;
        }

        // الأحدث غالباً آخر اسم زمنياً
        rsort($candidates, SORT_STRING);
        $chosen = $candidates[0];

        $size = 0;
        try {
            $size = (int) $disk->size($chosen);
        } catch (\Throwable $e) {
        }

        $meeting->update([
            'recording_disk' => 'live_recordings_r2',
            'recording_path' => $chosen,
            'recording_mime_type' => str_ends_with(strtolower($chosen), '.webm') ? 'video/webm' : 'video/mp4',
            'recording_size' => $size > 0 ? $size : (int) ($meeting->recording_size ?? 0),
            'recording_uploaded_at' => $meeting->recording_uploaded_at ?: now(),
            'recording_status' => 'ready',
        ]);

        Log::info('Linked orphan classroom recording from R2', [
            'meeting_id' => $meeting->id,
            'room_name' => $room,
            'path' => $chosen,
        ]);

        return $chosen;
    }
}

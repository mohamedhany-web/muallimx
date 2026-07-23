<?php

namespace App\Services;

use App\Models\ClassroomMeeting;
use App\Support\ClassroomWhiteboardSceneSanitizer;
use Illuminate\Support\Facades\Cache;

class ClassroomWhiteboardSceneService
{
    public static function cacheKey(ClassroomMeeting $meeting): string
    {
        return 'mx_classroom_wb_scene_'.$meeting->id;
    }

    /**
     * @return array{version: int, elements: array, updated_by: string|null, ts: int}
     */
    public static function get(ClassroomMeeting $meeting): array
    {
        $payload = Cache::get(self::cacheKey($meeting));
        if (! is_array($payload)) {
            return [
                'version' => 0,
                'elements' => [],
                'updated_by' => null,
                'ts' => 0,
            ];
        }

        return [
            'version' => (int) ($payload['version'] ?? 0),
            'elements' => is_array($payload['elements'] ?? null) ? $payload['elements'] : [],
            'updated_by' => isset($payload['updated_by']) ? (string) $payload['updated_by'] : null,
            'ts' => (int) ($payload['ts'] ?? 0),
        ];
    }

    /**
     * @param  mixed  $elements
     * @return array{version: int, elements: array, updated_by: string|null, ts: int}
     */
    public static function put(ClassroomMeeting $meeting, $elements, string $updatedBy): array
    {
        $clean = ClassroomWhiteboardSceneSanitizer::elements($elements);
        $current = self::get($meeting);
        $next = [
            'version' => $current['version'] + 1,
            'elements' => $clean,
            'updated_by' => mb_substr($updatedBy, 0, 80),
            'ts' => now()->timestamp,
        ];
        Cache::put(self::cacheKey($meeting), $next, now()->addHours(12));

        return $next;
    }
}

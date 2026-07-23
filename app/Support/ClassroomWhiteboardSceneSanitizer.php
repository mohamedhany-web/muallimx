<?php

namespace App\Support;

class ClassroomWhiteboardSceneSanitizer
{
    public const MAX_ELEMENTS = 2500;

    public const MAX_JSON_BYTES = 900000;

    /**
     * @param  mixed  $input
     * @return array<int, mixed>
     */
    public static function elements($input): array
    {
        if (! is_array($input)) {
            return [];
        }

        $slice = array_values(array_slice($input, 0, self::MAX_ELEMENTS));
        $encoded = json_encode($slice, JSON_UNESCAPED_UNICODE);
        if ($encoded === false || strlen($encoded) > self::MAX_JSON_BYTES) {
            // قص تدريجي حتى يدخل الحد
            while (count($slice) > 50) {
                $slice = array_slice($slice, 0, (int) floor(count($slice) * 0.7));
                $encoded = json_encode($slice, JSON_UNESCAPED_UNICODE);
                if ($encoded !== false && strlen($encoded) <= self::MAX_JSON_BYTES) {
                    break;
                }
            }
            if ($encoded === false || strlen($encoded) > self::MAX_JSON_BYTES) {
                return [];
            }
        }

        $decoded = json_decode($encoded, true);

        return is_array($decoded) ? array_values($decoded) : [];
    }
}

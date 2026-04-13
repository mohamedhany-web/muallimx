<?php

namespace App\Support;

class ShareAnnotationSanitizer
{
    /**
     * @param  mixed  $input
     * @return array<int, array<int, array{0: float, 1: float}>>
     */
    public static function polylines($input): array
    {
        if (! is_array($input)) {
            return [];
        }

        $out = [];
        foreach (array_slice($input, 0, 100) as $line) {
            if (! is_array($line)) {
                continue;
            }
            $seg = [];
            foreach (array_slice($line, 0, 800) as $pt) {
                if (! is_array($pt) || count($pt) < 2) {
                    continue;
                }
                $nx = (float) $pt[0];
                $ny = (float) $pt[1];
                $nx = max(0.0, min(1.0, $nx));
                $ny = max(0.0, min(1.0, $ny));
                $seg[] = [round($nx, 5), round($ny, 5)];
            }
            if (count($seg) > 1) {
                $out[] = $seg;
            }
        }

        return $out;
    }
}

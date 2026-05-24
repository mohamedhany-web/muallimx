<?php

namespace App\Support;

final class TeacherPlanKeys
{
    public const FREE = 'teacher_free';

    public const STARTER = 'teacher_starter';

    public const PRO = 'teacher_pro';

    /** @return list<string> */
    public static function ordered(): array
    {
        return [self::FREE, self::STARTER, self::PRO];
    }

    public static function routePattern(): string
    {
        return implode('|', self::ordered());
    }

    public static function isValid(string $key): bool
    {
        return in_array($key, self::ordered(), true);
    }

    public static function isFree(string $key): bool
    {
        return $key === self::FREE;
    }

    /** مدة الباقة المجانية بالأيام (من إعدادات الأدمن). */
    public static function freeDurationDays(array $planConfig): int
    {
        $days = (int) ($planConfig['duration_days'] ?? 14);

        return max(1, min(365, $days));
    }

    public static function freeAllowsRepeat(array $planConfig): bool
    {
        return filter_var($planConfig['allow_repeat_activation'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    public static function rank(string $key): int
    {
        return match ($key) {
            self::FREE => 0,
            self::STARTER => 1,
            self::PRO => 2,
            default => -1,
        };
    }

    /** @return array<string, int> */
    public static function rankMap(): array
    {
        return [
            self::FREE => 0,
            self::STARTER => 1,
            self::PRO => 2,
        ];
    }
}

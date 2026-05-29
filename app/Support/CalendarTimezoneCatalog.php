<?php

namespace App\Support;

class CalendarTimezoneCatalog
{
    /**
     * @return array<string, string> IANA => Arabic label
     */
    public static function options(): array
    {
        return [
            'Africa/Cairo' => 'مصر (القاهرة)',
            'Africa/Casablanca' => 'المغرب',
            'Asia/Riyadh' => 'السعودية',
            'Asia/Dubai' => 'الإمارات',
            'Asia/Kuwait' => 'الكويت',
            'Asia/Qatar' => 'قطر',
            'Asia/Baghdad' => 'العراق',
            'Asia/Amman' => 'الأردن',
            'Asia/Beirut' => 'لبنان',
            'Asia/Gaza' => 'فلسطين',
            'Europe/Istanbul' => 'تركيا',
            'Europe/London' => 'بريطانيا',
            'Europe/Paris' => 'فرنسا',
            'Europe/Berlin' => 'ألمانيا',
            'Europe/Rome' => 'إيطاليا',
            'Europe/Madrid' => 'إسبانيا',
            'America/New_York' => 'الولايات المتحدة (شرق)',
            'America/Chicago' => 'الولايات المتحدة (وسط)',
            'America/Los_Angeles' => 'الولايات المتحدة (غرب)',
            'UTC' => 'UTC',
        ];
    }

    public static function isValid(string $timezone): bool
    {
        return in_array($timezone, timezone_identifiers_list(), true);
    }

    public static function label(string $timezone): string
    {
        return self::options()[$timezone] ?? $timezone;
    }
}

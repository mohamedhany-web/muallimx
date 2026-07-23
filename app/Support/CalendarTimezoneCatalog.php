<?php

namespace App\Support;

class CalendarTimezoneCatalog
{
    /**
     * مناطق عربية شائعة أولاً، ثم باقي العالم.
     *
     * @return array<string, string>
     */
    public static function popular(): array
    {
        return [
            'Africa/Cairo' => 'مصر — القاهرة',
            'Africa/Casablanca' => 'المغرب — الدار البيضاء',
            'Asia/Riyadh' => 'السعودية — الرياض',
            'Asia/Dubai' => 'الإمارات — دبي',
            'Asia/Kuwait' => 'الكويت',
            'Asia/Qatar' => 'قطر — الدوحة',
            'Asia/Bahrain' => 'البحرين',
            'Asia/Baghdad' => 'العراق — بغداد',
            'Asia/Amman' => 'الأردن — عمّان',
            'Asia/Beirut' => 'لبنان — بيروت',
            'Asia/Gaza' => 'فلسطين — غزة',
            'Asia/Hebron' => 'فلسطين — الخليل',
            'Asia/Damascus' => 'سوريا — دمشق',
            'Asia/Jerusalem' => 'القدس',
            'Europe/Istanbul' => 'تركيا — إسطنبول',
            'Europe/London' => 'بريطانيا — لندن',
            'Europe/Paris' => 'فرنسا — باريس',
            'Europe/Berlin' => 'ألمانيا — برلين',
            'Europe/Rome' => 'إيطاليا — روما',
            'Europe/Madrid' => 'إسبانيا — مدريد',
            'Europe/Amsterdam' => 'هولندا — أمستردام',
            'America/New_York' => 'أمريكا — نيويورك (شرق)',
            'America/Chicago' => 'أمريكا — شيكاغو (وسط)',
            'America/Denver' => 'أمريكا — دنفر (جبلي)',
            'America/Los_Angeles' => 'أمريكا — لوس أنجلوس (غرب)',
            'America/Phoenix' => 'أمريكا — فينكس (أريزونا)',
            'America/Anchorage' => 'أمريكا — ألاسكا',
            'Pacific/Honolulu' => 'أمريكا — هاواي',
            'America/Toronto' => 'كندا — تورونتو',
            'America/Vancouver' => 'كندا — فانكوفر',
            'Australia/Sydney' => 'أستراليا — سيدني',
            'UTC' => 'UTC (توقيت عالمي)',
        ];
    }

    /**
     * كل مناطق IANA مع تسمية عربية مقروءة.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $popular = self::popular();
        $all = [];

        foreach (timezone_identifiers_list() as $id) {
            $all[$id] = self::buildLabel($id);
        }

        // الشائعة أولاً ثم الباقي أبجدياً
        $rest = array_diff_key($all, $popular);
        asort($rest, SORT_NATURAL | SORT_FLAG_CASE);

        return $popular + $rest;
    }

    /**
     * تجميع حسب القارة/المنطقة.
     *
     * @return array<string, array<string, string>>
     */
    public static function grouped(): array
    {
        $groups = [];
        foreach (self::options() as $id => $label) {
            $region = self::regionLabel($id);
            $groups[$region][$id] = $label;
        }

        return $groups;
    }

    /**
     * ولايات أمريكا → منطقة زمنية IANA (بدون API خارجي).
     *
     * @return array<string, array{name: string, timezone: string}>
     */
    public static function usStates(): array
    {
        return [
            'AL' => ['name' => 'Alabama', 'timezone' => 'America/Chicago'],
            'AK' => ['name' => 'Alaska', 'timezone' => 'America/Anchorage'],
            'AZ' => ['name' => 'Arizona', 'timezone' => 'America/Phoenix'],
            'AR' => ['name' => 'Arkansas', 'timezone' => 'America/Chicago'],
            'CA' => ['name' => 'California', 'timezone' => 'America/Los_Angeles'],
            'CO' => ['name' => 'Colorado', 'timezone' => 'America/Denver'],
            'CT' => ['name' => 'Connecticut', 'timezone' => 'America/New_York'],
            'DE' => ['name' => 'Delaware', 'timezone' => 'America/New_York'],
            'FL' => ['name' => 'Florida', 'timezone' => 'America/New_York'],
            'GA' => ['name' => 'Georgia', 'timezone' => 'America/New_York'],
            'HI' => ['name' => 'Hawaii', 'timezone' => 'Pacific/Honolulu'],
            'ID' => ['name' => 'Idaho', 'timezone' => 'America/Boise'],
            'IL' => ['name' => 'Illinois', 'timezone' => 'America/Chicago'],
            'IN' => ['name' => 'Indiana', 'timezone' => 'America/Indiana/Indianapolis'],
            'IA' => ['name' => 'Iowa', 'timezone' => 'America/Chicago'],
            'KS' => ['name' => 'Kansas', 'timezone' => 'America/Chicago'],
            'KY' => ['name' => 'Kentucky', 'timezone' => 'America/New_York'],
            'LA' => ['name' => 'Louisiana', 'timezone' => 'America/Chicago'],
            'ME' => ['name' => 'Maine', 'timezone' => 'America/New_York'],
            'MD' => ['name' => 'Maryland', 'timezone' => 'America/New_York'],
            'MA' => ['name' => 'Massachusetts', 'timezone' => 'America/New_York'],
            'MI' => ['name' => 'Michigan', 'timezone' => 'America/Detroit'],
            'MN' => ['name' => 'Minnesota', 'timezone' => 'America/Chicago'],
            'MS' => ['name' => 'Mississippi', 'timezone' => 'America/Chicago'],
            'MO' => ['name' => 'Missouri', 'timezone' => 'America/Chicago'],
            'MT' => ['name' => 'Montana', 'timezone' => 'America/Denver'],
            'NE' => ['name' => 'Nebraska', 'timezone' => 'America/Chicago'],
            'NV' => ['name' => 'Nevada', 'timezone' => 'America/Los_Angeles'],
            'NH' => ['name' => 'New Hampshire', 'timezone' => 'America/New_York'],
            'NJ' => ['name' => 'New Jersey', 'timezone' => 'America/New_York'],
            'NM' => ['name' => 'New Mexico', 'timezone' => 'America/Denver'],
            'NY' => ['name' => 'New York', 'timezone' => 'America/New_York'],
            'NC' => ['name' => 'North Carolina', 'timezone' => 'America/New_York'],
            'ND' => ['name' => 'North Dakota', 'timezone' => 'America/Chicago'],
            'OH' => ['name' => 'Ohio', 'timezone' => 'America/New_York'],
            'OK' => ['name' => 'Oklahoma', 'timezone' => 'America/Chicago'],
            'OR' => ['name' => 'Oregon', 'timezone' => 'America/Los_Angeles'],
            'PA' => ['name' => 'Pennsylvania', 'timezone' => 'America/New_York'],
            'RI' => ['name' => 'Rhode Island', 'timezone' => 'America/New_York'],
            'SC' => ['name' => 'South Carolina', 'timezone' => 'America/New_York'],
            'SD' => ['name' => 'South Dakota', 'timezone' => 'America/Chicago'],
            'TN' => ['name' => 'Tennessee', 'timezone' => 'America/Chicago'],
            'TX' => ['name' => 'Texas', 'timezone' => 'America/Chicago'],
            'UT' => ['name' => 'Utah', 'timezone' => 'America/Denver'],
            'VT' => ['name' => 'Vermont', 'timezone' => 'America/New_York'],
            'VA' => ['name' => 'Virginia', 'timezone' => 'America/New_York'],
            'WA' => ['name' => 'Washington', 'timezone' => 'America/Los_Angeles'],
            'WV' => ['name' => 'West Virginia', 'timezone' => 'America/New_York'],
            'WI' => ['name' => 'Wisconsin', 'timezone' => 'America/Chicago'],
            'WY' => ['name' => 'Wyoming', 'timezone' => 'America/Denver'],
            'DC' => ['name' => 'Washington DC', 'timezone' => 'America/New_York'],
        ];
    }

    public static function timezoneForUsState(string $code): ?string
    {
        $code = strtoupper(trim($code));
        $states = self::usStates();

        return $states[$code]['timezone'] ?? null;
    }

    /**
     * بحث في التوقيتات (لـ API الواجهة).
     *
     * @return array<string, string>
     */
    public static function search(string $query, int $limit = 40): array
    {
        $query = trim(mb_strtolower($query));
        if ($query === '') {
            return array_slice(self::popular(), 0, $limit, true);
        }

        $queryCompact = preg_replace('/[\s_\-]+/u', '', $query) ?? $query;

        $matches = [];
        foreach (self::options() as $id => $label) {
            $hay = mb_strtolower($id.' '.$label);
            $hayCompact = preg_replace('/[\s_\-]+/u', '', $hay) ?? $hay;
            if (str_contains($hay, $query) || str_contains($hayCompact, $queryCompact)) {
                $matches[$id] = $label;
                if (count($matches) >= $limit) {
                    break;
                }
            }
        }

        return $matches;
    }

    public static function isValid(string $timezone): bool
    {
        return in_array($timezone, timezone_identifiers_list(), true)
            || $timezone === 'UTC';
    }

    public static function label(string $timezone): string
    {
        $popular = self::popular();
        if (isset($popular[$timezone])) {
            return $popular[$timezone];
        }

        return self::buildLabel($timezone);
    }

    public static function buildLabel(string $timezone): string
    {
        if ($timezone === 'UTC') {
            return 'UTC (توقيت عالمي)';
        }

        $parts = explode('/', $timezone);
        $city = str_replace('_', ' ', end($parts));
        $region = self::regionLabel($timezone);

        try {
            $tz = new \DateTimeZone($timezone);
            $offset = $tz->getOffset(new \DateTime('now', $tz));
            $hours = intdiv($offset, 3600);
            $mins = abs(intdiv($offset % 3600, 60));
            $sign = $hours >= 0 ? '+' : '-';
            $offsetLabel = sprintf('UTC%s%d:%02d', $sign, abs($hours), $mins);
        } catch (\Throwable) {
            $offsetLabel = '';
        }

        return trim($region.' — '.$city.($offsetLabel !== '' ? ' ('.$offsetLabel.')' : ''));
    }

    public static function regionLabel(string $timezone): string
    {
        $region = explode('/', $timezone)[0] ?? $timezone;

        return match ($region) {
            'Africa' => 'أفريقيا',
            'America' => 'أمريكا',
            'Asia' => 'آسيا',
            'Europe' => 'أوروبا',
            'Pacific' => 'المحيط الهادئ',
            'Atlantic' => 'الأطلسي',
            'Indian' => 'المحيط الهندي',
            'Australia' => 'أستراليا',
            'Antarctica' => 'أنتاركتيكا',
            'Arctic' => 'القطب الشمالي',
            'UTC' => 'عالمي',
            default => $region,
        };
    }
}

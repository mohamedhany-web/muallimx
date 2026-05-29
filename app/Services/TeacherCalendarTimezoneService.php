<?php

namespace App\Services;

use App\Support\CalendarTimezoneCatalog;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use InvalidArgumentException;

class TeacherCalendarTimezoneService
{
    public function resolveTeacherTimezone(?string $preferred = null): string
    {
        $tz = $preferred ?: config('calendar.teacher_default_timezone', 'Africa/Cairo');

        if (! CalendarTimezoneCatalog::isValid($tz)) {
            return 'Africa/Cairo';
        }

        return $tz;
    }

    /**
     * @return array{
     *   starts_at_utc: CarbonInterface,
     *   ends_at_utc: CarbonInterface,
     *   family_local: CarbonInterface,
     *   teacher_local: CarbonInterface
     * }
     */
    public function buildOccurrenceTimes(
        string $date,
        string $time,
        string $familyTimezone,
        string $teacherTimezone,
        int $durationMinutes
    ): array {
        if (! CalendarTimezoneCatalog::isValid($familyTimezone) || ! CalendarTimezoneCatalog::isValid($teacherTimezone)) {
            throw new InvalidArgumentException('توقيت غير صالح.');
        }

        $familyLocal = Carbon::createFromFormat('Y-m-d H:i', $date.' '.$time, $familyTimezone);
        $teacherLocal = $familyLocal->copy()->setTimezone($teacherTimezone);
        $startsUtc = $familyLocal->copy()->utc();
        $endsUtc = $startsUtc->copy()->addMinutes(max(5, $durationMinutes));

        return [
            'starts_at_utc' => $startsUtc,
            'ends_at_utc' => $endsUtc,
            'family_local' => $familyLocal,
            'teacher_local' => $teacherLocal,
        ];
    }

    public function formatDualTimePreview(
        string $date,
        string $time,
        string $familyTimezone,
        string $teacherTimezone
    ): string {
        $times = $this->buildOccurrenceTimes($date, $time, $familyTimezone, $teacherTimezone, 60);

        $familyLabel = CalendarTimezoneCatalog::label($familyTimezone);
        $teacherLabel = CalendarTimezoneCatalog::label($teacherTimezone);

        return sprintf(
            '%s %s (%s) → %s %s (%s)',
            $times['family_local']->format('d/m/Y H:i'),
            $familyLabel,
            $familyTimezone,
            $times['teacher_local']->format('d/m/Y H:i'),
            $teacherLabel,
            $teacherTimezone
        );
    }

    /**
     * @return list<string> Y-m-d
     */
    public function datesForWeekdayInMonth(int $weekday, string $monthKey): array
    {
        [$year, $month] = array_map('intval', explode('-', $monthKey));
        $start = Carbon::create($year, $month, 1)->startOfDay();
        $end = $start->copy()->endOfMonth();
        $dates = [];

        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            if ((int) $day->dayOfWeek === $weekday) {
                $dates[] = $day->format('Y-m-d');
            }
        }

        return $dates;
    }
}

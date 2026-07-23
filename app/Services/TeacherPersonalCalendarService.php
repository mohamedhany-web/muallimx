<?php

namespace App\Services;

use App\Models\TeacherCalendarAppointment;
use App\Models\TeacherCalendarOccurrence;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TeacherPersonalCalendarService
{
    public function __construct(
        protected TeacherCalendarTimezoneService $timezoneService,
        protected TeacherCalendarReminderService $reminderService
    ) {}

    public function create(User $user, array $data): TeacherCalendarAppointment
    {
        return DB::transaction(function () use ($user, $data) {
            $teacherTz = $this->timezoneService->resolveTeacherTimezone(
                $data['teacher_timezone'] ?? $user->calendar_timezone
            );

            $appointment = TeacherCalendarAppointment::create([
                'user_id' => $user->id,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'location' => $data['location'] ?? null,
                'schedule_type' => $data['schedule_type'],
                'family_timezone' => $data['family_timezone'],
                'family_time' => $data['family_time'],
                'duration_minutes' => (int) ($data['duration_minutes'] ?? 60),
                'teacher_timezone' => $teacherTz,
                'weekday' => $data['weekday'] ?? null,
                'month_key' => $data['month_key'] ?? null,
                'selected_dates' => $data['selected_dates'] ?? [],
                'color' => $data['color'] ?? '#8B5CF6',
                'notify_platform' => (bool) ($data['notify_platform'] ?? true),
                'notify_email' => (bool) ($data['notify_email'] ?? true),
                'reminder_minutes' => (int) ($data['reminder_minutes'] ?? 5),
                'status' => 'active',
            ]);

            $this->syncOccurrences($appointment);

            if ($teacherTz !== $user->calendar_timezone) {
                $user->update(['calendar_timezone' => $teacherTz]);
            }

            return $appointment->load('occurrences');
        });
    }

    public function update(TeacherCalendarAppointment $appointment, array $data): TeacherCalendarAppointment
    {
        if ($appointment->user_id !== ($data['user_id'] ?? $appointment->user_id)) {
            throw new InvalidArgumentException('غير مصرح.');
        }

        return DB::transaction(function () use ($appointment, $data) {
            $appointment->fill([
                'title' => $data['title'] ?? $appointment->title,
                'description' => $data['description'] ?? $appointment->description,
                'location' => $data['location'] ?? $appointment->location,
                'family_timezone' => $data['family_timezone'] ?? $appointment->family_timezone,
                'family_time' => $data['family_time'] ?? $appointment->familyTimeString(),
                'duration_minutes' => (int) ($data['duration_minutes'] ?? $appointment->duration_minutes),
                'teacher_timezone' => $this->timezoneService->resolveTeacherTimezone(
                    $data['teacher_timezone'] ?? $appointment->teacher_timezone
                ),
                'weekday' => $data['weekday'] ?? $appointment->weekday,
                'month_key' => $data['month_key'] ?? $appointment->month_key,
                'selected_dates' => $data['selected_dates'] ?? $appointment->selected_dates,
                'color' => $data['color'] ?? $appointment->color,
                'notify_platform' => array_key_exists('notify_platform', $data)
                    ? (bool) $data['notify_platform']
                    : $appointment->notify_platform,
                'notify_email' => array_key_exists('notify_email', $data)
                    ? (bool) $data['notify_email']
                    : $appointment->notify_email,
                'reminder_minutes' => (int) ($data['reminder_minutes'] ?? $appointment->reminder_minutes),
            ]);
            $appointment->save();

            // مزامنة الجلسات للمواعيد الثابتة والمؤقتة (وقت + تذكيرات)
            $this->syncOccurrences($appointment);

            return $appointment->fresh('occurrences');
        });
    }

    public function cancel(TeacherCalendarAppointment $appointment): void
    {
        $appointment->update(['status' => 'cancelled']);
        $appointment->occurrences()->update(['removed_at' => now()]);
    }

    public function syncOccurrences(TeacherCalendarAppointment $appointment): void
    {
        $dates = $this->resolveDates($appointment);
        if ($dates === []) {
            throw new InvalidArgumentException('يجب اختيار يوم واحد على الأقل.');
        }

        $time = $appointment->familyTimeString();
        $existing = $appointment->occurrences()->active()->pluck('occurrence_date')->map->format('Y-m-d')->all();

        $toRemove = array_diff($existing, $dates);
        if ($toRemove !== []) {
            $appointment->occurrences()
                ->active()
                ->whereIn('occurrence_date', $toRemove)
                ->update(['removed_at' => now()]);
        }

        foreach ($dates as $date) {
            if (in_array($date, $existing, true)) {
                $occurrence = $appointment->occurrences()
                    ->active()
                    ->whereDate('occurrence_date', $date)
                    ->first();
                if ($occurrence) {
                    $this->refreshOccurrenceTimes($occurrence, $appointment, $date, $time);
                }
                continue;
            }

            $times = $this->timezoneService->buildOccurrenceTimes(
                $date,
                $time,
                $appointment->family_timezone,
                $appointment->teacher_timezone,
                $appointment->duration_minutes
            );

            TeacherCalendarOccurrence::create([
                'appointment_id' => $appointment->id,
                'user_id' => $appointment->user_id,
                'starts_at' => $times['starts_at_utc'],
                'ends_at' => $times['ends_at_utc'],
                'occurrence_date' => $date,
                'auto_remove_after_end' => $appointment->isTemporary(),
            ]);
        }

        $appointment->load('occurrences');
        foreach ($appointment->occurrences()->active()->get() as $occurrence) {
            $this->reminderService->scheduleOccurrenceReminder($occurrence);
        }
    }

    protected function refreshOccurrenceTimes(
        TeacherCalendarOccurrence $occurrence,
        TeacherCalendarAppointment $appointment,
        string $date,
        string $time
    ): void {
        $times = $this->timezoneService->buildOccurrenceTimes(
            $date,
            $time,
            $appointment->family_timezone,
            $appointment->teacher_timezone,
            $appointment->duration_minutes
        );

        $timesChanged = ! $occurrence->starts_at->equalTo($times['starts_at_utc'])
            || ! $occurrence->ends_at->equalTo($times['ends_at_utc']);

        $payload = [
            'starts_at' => $times['starts_at_utc'],
            'ends_at' => $times['ends_at_utc'],
        ];

        // إعادة التذكير فقط عند تغيّر الوقت أو إعدادات التذكير
        if ($timesChanged || $appointment->wasChanged([
            'reminder_minutes', 'notify_platform', 'notify_email',
            'family_time', 'family_timezone', 'teacher_timezone', 'duration_minutes', 'selected_dates',
        ])) {
            $payload['reminder_sent_at'] = null;
        }

        $occurrence->update($payload);
    }

    /**
     * @return list<string>
     */
    protected function resolveDates(TeacherCalendarAppointment $appointment): array
    {
        if ($appointment->isTemporary()) {
            $dates = $appointment->selected_dates ?? [];
            return array_values(array_unique(array_filter($dates)));
        }

        $selected = $appointment->selected_dates ?? [];
        if ($selected !== []) {
            return array_values(array_unique(array_filter($selected)));
        }

        if ($appointment->weekday !== null && $appointment->month_key) {
            return $this->timezoneService->datesForWeekdayInMonth(
                (int) $appointment->weekday,
                $appointment->month_key
            );
        }

        return [];
    }

    /**
     * @return Collection<int, object>
     */
    public function calendarItemsForUser(User $user, $startDate = null, $endDate = null): Collection
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : now()->subMonths(1);
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : now()->addMonths(3);

        $occurrences = TeacherCalendarOccurrence::query()
            ->active()
            ->where('user_id', $user->id)
            ->whereHas('appointment', fn ($q) => $q->where('status', 'active'))
            ->whereBetween('starts_at', [$start, $end])
            ->with('appointment')
            ->orderBy('starts_at')
            ->get();

        return $occurrences->map(function (TeacherCalendarOccurrence $occurrence) use ($user) {
            $appointment = $occurrence->appointment;
            $teacherTz = $appointment->teacher_timezone;
            $familyTz = $appointment->family_timezone;
            $teacherStart = $occurrence->starts_at->copy()->setTimezone($teacherTz);
            $familyStart = $occurrence->starts_at->copy()->setTimezone($familyTz);

            $subtitle = sprintf(
                '%s (%s) · الأسرة %s (%s)',
                $teacherStart->format('H:i'),
                $teacherTz,
                $familyStart->format('H:i'),
                $familyTz
            );

            return (object) [
                'calendar_id' => 'personal_'.$occurrence->id,
                'id' => $occurrence->id,
                'appointment_id' => $appointment->id,
                'title' => $appointment->title.($appointment->isTemporary() ? ' (مؤقت)' : ''),
                'description' => trim(($appointment->description ?? '')."\n".$subtitle),
                'start_date' => $teacherStart,
                'end_date' => $occurrence->ends_at->copy()->setTimezone($teacherTz),
                'is_all_day' => false,
                'type' => 'personal',
                'color' => $appointment->color,
                'priority' => 'high',
                'url' => null,
                'location' => $appointment->location,
                'is_personal' => true,
                'schedule_type' => $appointment->schedule_type,
                'appointment_id' => $appointment->id,
                'occurrence_id' => $occurrence->id,
            ];
        });
    }

    public function cleanupExpiredTemporary(): int
    {
        $count = 0;
        $expired = TeacherCalendarOccurrence::query()
            ->active()
            ->where('auto_remove_after_end', true)
            ->where('ends_at', '<', now()->subHour())
            ->with('appointment')
            ->get();

        foreach ($expired as $occurrence) {
            $occurrence->update(['removed_at' => now()]);
            if ($occurrence->appointment?->isTemporary()) {
                $occurrence->appointment->update(['status' => 'cancelled']);
            }
            $count++;
        }

        return $count;
    }
}

<?php

namespace App\Services;

use App\Jobs\SendTeacherCalendarOccurrenceReminderJob;
use App\Models\Notification;
use App\Models\TeacherCalendarOccurrence;
use App\Support\CalendarTimezoneCatalog;
use Illuminate\Support\Facades\Log;

class TeacherCalendarReminderService
{
    public function __construct(
        protected EmailNotificationService $emailService
    ) {}

    /**
     * يُستدعى من cron كل دقيقة — يحترم reminder_minutes لكل موعد.
     */
    public function sendDueReminders(): int
    {
        $occurrences = TeacherCalendarOccurrence::query()
            ->active()
            ->whereNull('reminder_sent_at')
            ->where('starts_at', '>', now())
            ->whereHas('appointment', fn ($q) => $q->where('status', 'active'))
            ->with(['appointment', 'user'])
            ->get();

        $sent = 0;

        foreach ($occurrences as $occurrence) {
            if (! $this->isDueNow($occurrence)) {
                continue;
            }

            try {
                if ($this->sendForOccurrence($occurrence)) {
                    $sent++;
                }
            } catch (\Throwable $e) {
                Log::warning('Calendar reminder failed', [
                    'occurrence_id' => $occurrence->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $sent;
    }

    public function sendForOccurrenceId(int $occurrenceId): bool
    {
        $occurrence = TeacherCalendarOccurrence::query()
            ->active()
            ->whereNull('reminder_sent_at')
            ->whereKey($occurrenceId)
            ->whereHas('appointment', fn ($q) => $q->where('status', 'active'))
            ->with(['appointment', 'user'])
            ->first();

        if (! $occurrence || ! $this->isDueNow($occurrence, allowGraceAfter: true)) {
            return false;
        }

        return $this->sendForOccurrence($occurrence);
    }

    protected function isDueNow(TeacherCalendarOccurrence $occurrence, bool $allowGraceAfter = false): bool
    {
        $appointment = $occurrence->appointment;
        if (! $appointment) {
            return false;
        }

        $minutes = max(1, (int) ($appointment->reminder_minutes ?? 5));
        $dueAt = $occurrence->starts_at->copy()->subMinutes($minutes);
        $windowEnd = $dueAt->copy()->addMinutes(2);

        if ($allowGraceAfter) {
            $windowEnd = $occurrence->starts_at;
        }

        return now()->between($dueAt, $windowEnd);
    }

    protected function sendForOccurrence(TeacherCalendarOccurrence $occurrence): bool
    {
        if ($occurrence->reminder_sent_at) {
            return false;
        }

        $appointment = $occurrence->appointment;
        $user = $occurrence->user;
        if (! $appointment || ! $user) {
            return false;
        }

        if (! $appointment->notify_platform && ! $appointment->notify_email) {
            $occurrence->update(['reminder_sent_at' => now()]);

            return false;
        }

        $minutes = max(1, (int) ($appointment->reminder_minutes ?? 5));
        $this->notifyForOccurrence($occurrence, $minutes);
        $occurrence->update(['reminder_sent_at' => now()]);

        return true;
    }

    protected function notifyForOccurrence(TeacherCalendarOccurrence $occurrence, int $minutesBefore): void
    {
        $appointment = $occurrence->appointment;
        $user = $occurrence->user;
        if (! $appointment || ! $user) {
            return;
        }

        $teacherTz = $appointment->teacher_timezone;
        $startTeacher = $occurrence->starts_at->copy()->setTimezone($teacherTz);
        $startFamily = $occurrence->starts_at->copy()->setTimezone($appointment->family_timezone);

        $message = sprintf(
            'تذكير: «%s» تبدأ بعد %d دقائق — موعدك %s (%s) · توقيت الأسرة %s (%s).',
            $appointment->title,
            $minutesBefore,
            $startTeacher->format('d/m/Y H:i'),
            CalendarTimezoneCatalog::label($teacherTz),
            $startFamily->format('H:i'),
            CalendarTimezoneCatalog::label($appointment->family_timezone)
        );

        if ($appointment->notify_platform) {
            Notification::create([
                'user_id' => $user->id,
                'sender_id' => null,
                'title' => 'تذكير حصة: '.$appointment->title,
                'message' => $message,
                'type' => 'reminder',
                'priority' => 'urgent',
                'audience' => 'student',
                'action_url' => route('calendar'),
                'action_text' => 'فتح التقويم',
                'expires_at' => $occurrence->ends_at,
                'data' => [
                    'occurrence_id' => $occurrence->id,
                    'appointment_id' => $appointment->id,
                    'reminder_minutes' => $minutesBefore,
                ],
            ]);
        }

        if ($appointment->notify_email && $user->email) {
            $this->emailService->sendToUser(
                $user,
                $message."\n\nرابط التقويم: ".route('calendar'),
                'تذكير: '.$appointment->title.' — Muallimx'
            );
        }
    }

    public function scheduleOccurrenceReminder(TeacherCalendarOccurrence $occurrence): void
    {
        $occurrence->loadMissing('appointment');
        $appointment = $occurrence->appointment;
        if (! $appointment || $appointment->status !== 'active') {
            return;
        }

        if (! $appointment->notify_platform && ! $appointment->notify_email) {
            return;
        }

        $minutes = max(1, (int) ($appointment->reminder_minutes ?? 5));
        $runAt = $occurrence->starts_at->copy()->subMinutes($minutes);

        if ($runAt->isPast()) {
            if ($this->isDueNow($occurrence, allowGraceAfter: true)) {
                $this->sendForOccurrence($occurrence);
            }

            return;
        }

        if (config('queue.default') === 'sync') {
            return;
        }

        SendTeacherCalendarOccurrenceReminderJob::dispatch($occurrence->id)
            ->delay($runAt);
    }
}

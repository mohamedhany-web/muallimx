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
     * يُستدعى من cron كل دقيقة — وأيضاً عند فتح التقويم كاحتياطي.
     */
    public function sendDueReminders(?int $userId = null): int
    {
        $query = TeacherCalendarOccurrence::query()
            ->active()
            ->whereNull('reminder_sent_at')
            ->where('starts_at', '>', now())
            ->whereHas('appointment', fn ($q) => $q->where('status', 'active'))
            ->with(['appointment', 'user']);

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        $occurrences = $query->get();
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

        if (! $occurrence || ! $this->isDueNow($occurrence)) {
            return false;
        }

        return $this->sendForOccurrence($occurrence);
    }

    /**
     * مستحق إذا حان وقت التذكير ولم يبدأ الموعد بعد (نافذة واسعة حتى لا يُفوت بسبب cron).
     */
    protected function isDueNow(TeacherCalendarOccurrence $occurrence): bool
    {
        $appointment = $occurrence->appointment;
        if (! $appointment) {
            return false;
        }

        if ($occurrence->starts_at->lte(now())) {
            return false;
        }

        $minutes = max(1, (int) ($appointment->reminder_minutes ?? 5));
        $dueAt = $occurrence->starts_at->copy()->subMinutes($minutes);

        return now()->gte($dueAt);
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
        $result = $this->notifyForOccurrence($occurrence, $minutes);

        if ($result['platform_ok'] || $result['email_ok']) {
            $occurrence->update(['reminder_sent_at' => now()]);

            return true;
        }

        return false;
    }

    /**
     * @return array{platform_ok: bool, email_ok: bool, platform_skipped: bool, email_attempted: bool}
     */
    protected function notifyForOccurrence(TeacherCalendarOccurrence $occurrence, int $minutesBefore): array
    {
        $appointment = $occurrence->appointment;
        $user = $occurrence->user;
        $out = [
            'platform_ok' => false,
            'email_ok' => false,
            'platform_skipped' => ! $appointment->notify_platform,
            'email_attempted' => false,
        ];

        if (! $appointment || ! $user) {
            return $out;
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
            try {
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
                $out['platform_ok'] = true;
            } catch (\Throwable $e) {
                Log::error('Calendar platform notification failed', [
                    'occurrence_id' => $occurrence->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($appointment->notify_email && $user->email) {
            $out['email_attempted'] = true;
            $emailResult = $this->emailService->sendToUser(
                $user,
                $message."\n\nرابط التقويم: ".route('calendar'),
                'تذكير: '.$appointment->title.' — Muallimx'
            );
            $out['email_ok'] = (bool) ($emailResult['success'] ?? false);
            if (! $out['email_ok']) {
                Log::warning('Calendar reminder email failed', [
                    'occurrence_id' => $occurrence->id,
                    'user_id' => $user->id,
                    'error' => $emailResult['error'] ?? 'unknown',
                ]);
            }
        }

        return $out;
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
            if ($this->isDueNow($occurrence)) {
                $this->sendForOccurrence($occurrence);
            }

            return;
        }

        // مع QUEUE_CONNECTION=sync لا يعمل delay — نعتمد على cron + فتح التقويم
        if (config('queue.default') === 'sync') {
            return;
        }

        SendTeacherCalendarOccurrenceReminderJob::dispatch($occurrence->id)
            ->delay($runAt);
    }
}

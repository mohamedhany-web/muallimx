<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\TeacherCalendarOccurrence;
use App\Support\CalendarTimezoneCatalog;
use Illuminate\Support\Facades\Log;

class TeacherCalendarReminderService
{
    public function __construct(
        protected EmailNotificationService $emailService
    ) {}

    public function sendDueReminders(int $minutesBefore = 5): int
    {
        $windowStart = now()->addMinutes($minutesBefore - 1);
        $windowEnd = now()->addMinutes($minutesBefore + 1);

        $occurrences = TeacherCalendarOccurrence::query()
            ->active()
            ->whereNull('reminder_sent_at')
            ->whereBetween('starts_at', [$windowStart, $windowEnd])
            ->whereHas('appointment', fn ($q) => $q->where('status', 'active'))
            ->with(['appointment', 'user'])
            ->get();

        $sent = 0;

        foreach ($occurrences as $occurrence) {
            try {
                $this->notifyForOccurrence($occurrence, $minutesBefore);
                $occurrence->update(['reminder_sent_at' => now()]);
                $sent++;
            } catch (\Throwable $e) {
                Log::warning('Calendar reminder failed', [
                    'occurrence_id' => $occurrence->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $sent;
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
}

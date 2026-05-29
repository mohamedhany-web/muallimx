<?php

namespace App\Console\Commands;

use App\Models\TeacherCalendarOccurrence;
use App\Services\TeacherCalendarReminderService;
use Illuminate\Console\Command;

class ScheduleCalendarAppointmentRemindersCommand extends Command
{
    protected $signature = 'calendar:schedule-reminders';

    protected $description = 'Schedule delayed reminder jobs for all upcoming personal calendar occurrences';

    public function handle(TeacherCalendarReminderService $service): int
    {
        $count = 0;
        TeacherCalendarOccurrence::query()
            ->active()
            ->whereNull('reminder_sent_at')
            ->where('starts_at', '>', now())
            ->whereHas('appointment', fn ($q) => $q->where('status', 'active'))
            ->with('appointment')
            ->orderBy('starts_at')
            ->chunkById(100, function ($occurrences) use ($service, &$count) {
                foreach ($occurrences as $occurrence) {
                    $service->scheduleOccurrenceReminder($occurrence);
                    $count++;
                }
            });

        $this->info("Scheduled reminders for {$count} occurrence(s).");

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Services\TeacherCalendarReminderService;
use Illuminate\Console\Command;

class SendCalendarAppointmentRemindersCommand extends Command
{
    protected $signature = 'calendar:send-reminders {--minutes=5 : Minutes before appointment}';

    protected $description = 'Send platform and email reminders before teacher personal calendar sessions';

    public function handle(TeacherCalendarReminderService $service): int
    {
        $minutes = (int) $this->option('minutes');
        $sent = $service->sendDueReminders($minutes);
        $this->info("Sent {$sent} calendar reminder(s) (~{$minutes} min before).");

        return self::SUCCESS;
    }
}

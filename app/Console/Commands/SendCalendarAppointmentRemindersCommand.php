<?php

namespace App\Console\Commands;

use App\Services\TeacherCalendarReminderService;
use Illuminate\Console\Command;

class SendCalendarAppointmentRemindersCommand extends Command
{
    protected $signature = 'calendar:send-reminders';

    protected $description = 'Send platform and email reminders before teacher personal calendar sessions (uses each appointment reminder_minutes)';

    public function handle(TeacherCalendarReminderService $service): int
    {
        $sent = $service->sendDueReminders();
        $this->info("Sent {$sent} calendar reminder(s).");

        return self::SUCCESS;
    }
}

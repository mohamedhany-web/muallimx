<?php

namespace App\Jobs;

use App\Services\TeacherCalendarReminderService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTeacherCalendarOccurrenceReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $occurrenceId) {}

    public function handle(TeacherCalendarReminderService $service): void
    {
        $service->sendForOccurrenceId($this->occurrenceId);
    }
}

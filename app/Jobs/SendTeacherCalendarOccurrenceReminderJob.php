<?php

namespace App\Jobs;

use App\Models\TeacherCalendarOccurrence;
use App\Services\TeacherCalendarReminderService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTeacherCalendarOccurrenceReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 10;

    public function __construct(public int $occurrenceId) {}

    public function handle(TeacherCalendarReminderService $service): void
    {
        if ($service->sendForOccurrenceId($this->occurrenceId)) {
            return;
        }

        $occurrence = TeacherCalendarOccurrence::query()->find($this->occurrenceId);
        if ($occurrence
            && ! $occurrence->reminder_sent_at
            && $occurrence->starts_at
            && $occurrence->starts_at->isFuture()) {
            $this->release(60);
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Services\TeacherPersonalCalendarService;
use Illuminate\Console\Command;

class CleanupTemporaryCalendarOccurrencesCommand extends Command
{
    protected $signature = 'calendar:cleanup-temporary';

    protected $description = 'Remove ended temporary calendar occurrences automatically';

    public function handle(TeacherPersonalCalendarService $service): int
    {
        $count = $service->cleanupExpiredTemporary();
        $this->info("Cleaned up {$count} temporary occurrence(s).");

        return self::SUCCESS;
    }
}

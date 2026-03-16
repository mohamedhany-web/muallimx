<?php

namespace App\Console\Commands;

use App\Models\LiveSession;
use App\Models\LiveSetting;
use App\Models\SessionAttendance;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoEndLiveSessionsCommand extends Command
{
    protected $signature = 'live:auto-end-sessions';

    protected $description = 'Automatically end live sessions that exceeded the maximum duration';

    public function handle(): int
    {
        $maxMinutes = (int) LiveSetting::get('auto_end_minutes', 180);

        $sessions = LiveSession::where('status', 'live')
            ->where('started_at', '<=', now()->subMinutes($maxMinutes))
            ->get();

        if ($sessions->isEmpty()) {
            $this->info('No sessions to auto-end.');
            return self::SUCCESS;
        }

        foreach ($sessions as $session) {
            SessionAttendance::where('session_id', $session->id)
                ->whereNull('left_at')
                ->each(function ($attendance) {
                    $attendance->markLeft();
                });

            $session->end();

            Log::info("Auto-ended live session #{$session->id} \"{$session->title}\" after {$maxMinutes} minutes.");
            $this->info("Ended: {$session->title} (ID: {$session->id})");
        }

        $this->info("Auto-ended {$sessions->count()} session(s).");

        return self::SUCCESS;
    }
}

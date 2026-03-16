<?php

namespace App\Console\Commands;

use App\Models\LiveSession;
use App\Models\User;
use App\Notifications\LiveSessionReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendLiveSessionRemindersCommand extends Command
{
    protected $signature = 'live:send-reminders {--minutes=10 : Minutes before session to send reminder}';

    protected $description = 'Send reminder notifications to enrolled students before upcoming live sessions';

    public function handle(): int
    {
        $minutes = (int) $this->option('minutes');

        $sessions = LiveSession::where('status', 'scheduled')
            ->whereBetween('scheduled_at', [
                now()->addMinutes($minutes - 1),
                now()->addMinutes($minutes + 1),
            ])
            ->whereDoesntHave('attendance', function ($q) {
                // skip sessions that already have sent reminders (tagged via settings)
            })
            ->with(['course', 'instructor'])
            ->get();

        if ($sessions->isEmpty()) {
            $this->info('No sessions starting in ~' . $minutes . ' minutes.');
            return self::SUCCESS;
        }

        $totalNotified = 0;

        foreach ($sessions as $session) {
            $students = $this->getEligibleStudents($session);

            foreach ($students as $student) {
                try {
                    $alreadySent = DB::table('notifications')
                        ->where('notifiable_id', $student->id)
                        ->where('type', LiveSessionReminderNotification::class)
                        ->where('data->session_id', $session->id)
                        ->exists();

                    if ($alreadySent) {
                        continue;
                    }

                    $student->notify(new LiveSessionReminderNotification($session, $minutes));
                    $totalNotified++;
                } catch (\Exception $e) {
                    Log::warning("Failed to send live session reminder to user {$student->id}: " . $e->getMessage());
                }
            }

            $this->info("Session \"{$session->title}\": notified {$totalNotified} students.");
        }

        $this->info("Done. Total notifications sent: {$totalNotified}");

        return self::SUCCESS;
    }

    private function getEligibleStudents(LiveSession $session)
    {
        if ($session->course_id && $session->require_enrollment) {
            $enrolledUserIds = DB::table('online_enrollments')
                ->where('advanced_course_id', $session->course_id)
                ->where('is_active', true)
                ->pluck('user_id');

            return User::whereIn('id', $enrolledUserIds)->get();
        }

        if ($session->course_id && !$session->require_enrollment) {
            return User::where('role', 'student')->get();
        }

        if (!$session->course_id) {
            return User::where('role', 'student')->get();
        }

        return collect();
    }
}

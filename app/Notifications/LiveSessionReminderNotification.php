<?php

namespace App\Notifications;

use App\Models\LiveSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LiveSessionReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public LiveSession $session,
        public int $minutesBefore = 10
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $courseName = $this->session->course?->title ?? 'جلسة عامة';
        $instructorName = $this->session->instructor?->name ?? '';
        $time = $this->session->scheduled_at?->format('H:i');

        return (new MailMessage)
            ->subject("تذكير: جلسة بث مباشر تبدأ بعد {$this->minutesBefore} دقيقة")
            ->greeting("مرحباً {$notifiable->name}")
            ->line("جلسة البث المباشر **{$this->session->title}** ستبدأ بعد {$this->minutesBefore} دقيقة.")
            ->line("الكورس: {$courseName}")
            ->line("المدرب: {$instructorName}")
            ->line("الموعد: {$time}")
            ->action('دخول البث المباشر', url('/student/live-sessions/' . $this->session->id))
            ->line('لا تفوّت الجلسة — جهّز نفسك الآن!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'live_session_reminder',
            'session_id' => $this->session->id,
            'session_title' => $this->session->title,
            'course_name' => $this->session->course?->title ?? 'جلسة عامة',
            'instructor_name' => $this->session->instructor?->name ?? '',
            'scheduled_at' => $this->session->scheduled_at?->toISOString(),
            'minutes_before' => $this->minutesBefore,
            'message' => "جلسة \"{$this->session->title}\" تبدأ بعد {$this->minutesBefore} دقيقة",
            'url' => '/student/live-sessions/' . $this->session->id,
        ];
    }
}

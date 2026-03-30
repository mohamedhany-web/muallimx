<?php

namespace App\Jobs;

use App\Mail\BroadcastEmailMail;
use App\Models\EmailBroadcast;
use App\Models\EmailBroadcastRecipient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendEmailBroadcastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $broadcastId)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $broadcast = EmailBroadcast::query()->find($this->broadcastId);
        if (! $broadcast) {
            return;
        }

        $broadcast->update(['status' => EmailBroadcast::STATUS_SENDING]);

        $recipients = EmailBroadcastRecipient::query()
            ->where('email_broadcast_id', $broadcast->id)
            ->where('status', EmailBroadcastRecipient::STATUS_QUEUED)
            ->limit(250)
            ->get();

        $sent = 0;
        $failed = 0;

        foreach ($recipients as $r) {
            try {
                Mail::to($r->email, $r->name)->send(new BroadcastEmailMail($broadcast, $r));
                $r->update([
                    'status' => EmailBroadcastRecipient::STATUS_SENT,
                    'sent_at' => now(),
                    'error' => null,
                ]);
                $sent++;
            } catch (Throwable $e) {
                $r->update([
                    'status' => EmailBroadcastRecipient::STATUS_FAILED,
                    'error' => mb_substr($e->getMessage(), 0, 2000),
                ]);
                $failed++;
            }
        }

        $remaining = EmailBroadcastRecipient::query()
            ->where('email_broadcast_id', $broadcast->id)
            ->where('status', EmailBroadcastRecipient::STATUS_QUEUED)
            ->count();

        $stats = $broadcast->stats ?? [];
        $stats['sent'] = ($stats['sent'] ?? 0) + $sent;
        $stats['failed'] = ($stats['failed'] ?? 0) + $failed;
        $stats['total'] = $stats['total'] ?? EmailBroadcastRecipient::query()->where('email_broadcast_id', $broadcast->id)->count();
        $stats['remaining'] = $remaining;

        if ($remaining > 0) {
            $broadcast->update(['stats' => $stats]);
            self::dispatch($broadcast->id);
            return;
        }

        $broadcast->update([
            'status' => $failed > 0 ? EmailBroadcast::STATUS_FAILED : EmailBroadcast::STATUS_SENT,
            'sent_at' => now(),
            'stats' => $stats,
        ]);
    }
}

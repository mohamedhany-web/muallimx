<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TwoFactorLog extends Model
{
    public const EVENT_CHALLENGE_SENT = 'challenge_sent';
    public const EVENT_VERIFIED = 'verified';
    public const EVENT_FAILED = 'failed';

    protected $fillable = [
        'user_id',
        'email',
        'event',
        'ip_address',
        'user_agent',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getEventLabelAttribute(): string
    {
        return match ($this->event) {
            self::EVENT_CHALLENGE_SENT => 'إرسال رمز',
            self::EVENT_VERIFIED => 'تحقق ناجح',
            self::EVENT_FAILED => 'فشل التحقق',
            default => $this->event,
        };
    }
}

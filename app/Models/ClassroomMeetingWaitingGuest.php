<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassroomMeetingWaitingGuest extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_ADMITTED = 'admitted';

    public const STATUS_DENIED = 'denied';

    public const STATUS_CONSUMED = 'consumed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'classroom_meeting_id',
        'waiting_token',
        'display_name',
        'status',
        'classroom_meeting_participant_id',
        'ip_address',
        'user_agent',
        'admitted_at',
        'denied_at',
        'consumed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'admitted_at' => 'datetime',
        'denied_at' => 'datetime',
        'consumed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(ClassroomMeeting::class, 'classroom_meeting_id');
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(ClassroomMeetingParticipant::class, 'classroom_meeting_participant_id');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isAdmitted(): bool
    {
        return $this->status === self::STATUS_ADMITTED;
    }

    public function isDenied(): bool
    {
        return $this->status === self::STATUS_DENIED;
    }

    public function isConsumed(): bool
    {
        return $this->status === self::STATUS_CONSUMED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, [
            self::STATUS_DENIED,
            self::STATUS_CANCELLED,
        ], true);
    }
}

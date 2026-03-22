<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ClassroomMeeting extends Model
{
    protected $fillable = [
        'user_id',
        'consultation_request_id',
        'code',
        'room_name',
        'title',
        'scheduled_for',
        'planned_duration_minutes',
        'max_participants',
        'participants_peak',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'max_participants' => 'integer',
        'participants_peak' => 'integer',
        'scheduled_for' => 'datetime',
        'planned_duration_minutes' => 'integer',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function consultationRequest(): BelongsTo
    {
        return $this->belongsTo(ConsultationRequest::class, 'consultation_request_id');
    }

    public function participants()
    {
        return $this->hasMany(ClassroomMeetingParticipant::class, 'classroom_meeting_id');
    }

    public function isLive(): bool
    {
        return $this->started_at && !$this->ended_at;
    }

    public static function generateCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (static::where('code', $code)->exists());

        return $code;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionAttendance extends Model
{
    protected $table = 'session_attendance';

    protected $fillable = [
        'session_id', 'user_id', 'joined_at', 'left_at',
        'duration_seconds', 'ip_address', 'user_agent', 'role_in_session',
    ];

    protected $casts = [
        'joined_at'        => 'datetime',
        'left_at'          => 'datetime',
        'duration_seconds' => 'integer',
    ];

    public function session()
    {
        return $this->belongsTo(LiveSession::class, 'session_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function markLeft(): void
    {
        $duration = 0;

        if ($this->joined_at) {
            $secs = (int) now()->diffInSeconds($this->joined_at);
            $duration = max(0, $secs);
        }

        $this->update([
            'left_at'          => now(),
            'duration_seconds' => $duration,
        ]);
    }

    public function getDurationForHumansAttribute(): string
    {
        $s = $this->duration_seconds;
        if ($s < 60) return "{$s} ثانية";
        $m = intdiv($s, 60);
        if ($m < 60) return "{$m} دقيقة";
        $h = intdiv($m, 60);
        $rm = $m % 60;
        return "{$h} ساعة" . ($rm > 0 ? " و{$rm} دقيقة" : '');
    }
}

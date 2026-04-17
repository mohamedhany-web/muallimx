<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassroomMeetingReport extends Model
{
    protected $fillable = [
        'classroom_meeting_id',
        'user_id',
        'title',
        'summary',
        'status',
        'n8n_execution_id',
        'audio_path',
        'storage_disk',
    ];

    protected $casts = [
        'classroom_meeting_id' => 'integer',
        'user_id' => 'integer',
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(ClassroomMeeting::class, 'classroom_meeting_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveSessionReport extends Model
{
    protected $fillable = [
        'live_session_id',
        'instructor_id',
        'live_recording_id',
        'title',
        'summary',
        'status',
        'n8n_execution_id',
        'audio_path',
        'storage_disk',
    ];

    protected $casts = [
        'live_session_id' => 'integer',
        'instructor_id' => 'integer',
        'live_recording_id' => 'integer',
    ];

    public function session()
    {
        return $this->belongsTo(LiveSession::class, 'live_session_id');
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function recording()
    {
        return $this->belongsTo(LiveRecording::class, 'live_recording_id');
    }
}


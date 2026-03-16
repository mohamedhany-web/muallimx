<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveRecording extends Model
{
    protected $fillable = [
        'session_id', 'title', 'file_path', 'external_url',
        'file_size', 'duration_seconds', 'status', 'is_published',
    ];

    protected $casts = [
        'file_size'        => 'integer',
        'duration_seconds' => 'integer',
        'is_published'     => 'boolean',
    ];

    public function session()
    {
        return $this->belongsTo(LiveSession::class, 'session_id');
    }

    public function getUrl(): ?string
    {
        if ($this->external_url) return $this->external_url;
        if ($this->file_path) return asset('storage/' . $this->file_path);
        return null;
    }

    public function getFileSizeForHumansAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes < 1024) return "{$bytes} B";
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        if ($bytes < 1073741824) return round($bytes / 1048576, 1) . ' MB';
        return round($bytes / 1073741824, 2) . ' GB';
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

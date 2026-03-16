<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveServer extends Model
{
    protected $fillable = [
        'name', 'domain', 'provider', 'status', 'ip_address',
        'max_participants', 'current_load', 'config', 'notes',
    ];

    protected $casts = [
        'config' => 'array',
        'max_participants' => 'integer',
        'current_load' => 'integer',
    ];

    public function sessions()
    {
        return $this->hasMany(LiveSession::class, 'server_id');
    }

    public function activeSessions()
    {
        return $this->hasMany(LiveSession::class, 'server_id')->where('status', 'live');
    }

    public function isAvailable(): bool
    {
        return $this->status === 'active' && $this->current_load < $this->max_participants;
    }

    public function getLoadPercentageAttribute(): int
    {
        return $this->max_participants > 0
            ? (int) round(($this->current_load / $this->max_participants) * 100)
            : 0;
    }
}

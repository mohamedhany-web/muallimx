<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentSavedAiGame extends Model
{
    protected $fillable = [
        'user_id',
        'storage_path',
        'title',
        'question_type',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** رابط نسبي يعمل مع أي host (127.0.0.1 أو النطاق). */
    public function publicRelativeUrl(): string
    {
        $path = ltrim(str_replace('\\', '/', $this->storage_path), '/');

        return '/storage/'.$path;
    }
}

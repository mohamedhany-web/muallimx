<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailBroadcast extends Model
{
    public const AUDIENCE_ALL_USERS = 'all_users';

    public const AUDIENCE_STUDENTS = 'students';

    public const AUDIENCE_INSTRUCTORS = 'instructors';

    public const AUDIENCE_EMPLOYEES = 'employees';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SENDING = 'sending';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'created_by',
        'audience',
        'subject',
        'body',
        'status',
        'sent_at',
        'stats',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'stats' => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(EmailBroadcastRecipient::class, 'email_broadcast_id');
    }

    /** @return array<string, string> */
    public static function audienceLabels(): array
    {
        return [
            self::AUDIENCE_ALL_USERS => 'كل المستخدمين',
            self::AUDIENCE_STUDENTS => 'الطلاب',
            self::AUDIENCE_INSTRUCTORS => 'المدربين',
            self::AUDIENCE_EMPLOYEES => 'الموظفين',
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RecruitmentTeacherPresentation extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_SHARED = 'shared_with_academy';

    public const STATUS_INTERESTED = 'academy_interested';

    public const STATUS_DECLINED = 'academy_declined';

    public const STATUS_HIRED = 'hired';

    public const STATUS_WITHDRAWN = 'withdrawn';

    protected $fillable = [
        'academy_opportunity_id',
        'user_id',
        'display_code',
        'status',
        'hide_identity',
        'curated_public_profile',
        'internal_notes',
        'academy_feedback',
        'shared_with_academy_at',
        'academy_responded_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'hide_identity' => 'boolean',
            'shared_with_academy_at' => 'datetime',
            'academy_responded_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->display_code)) {
                do {
                    $code = 'MX-'.strtoupper(Str::random(7));
                } while (self::where('display_code', $code)->exists());
                $model->display_code = $code;
            }
        });
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_DRAFT => 'مسودة داخلية',
            self::STATUS_SHARED => 'مُزوَّد للأكاديمية',
            self::STATUS_INTERESTED => 'الأكاديمية مهتمة',
            self::STATUS_DECLINED => 'غير مناسب (الأكاديمية)',
            self::STATUS_HIRED => 'تم التعاقد',
            self::STATUS_WITHDRAWN => 'سُحب العرض',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(AcademyOpportunity::class, 'academy_opportunity_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** الاسم الظاهر للأكاديمية حسب سياسة الخصوصية */
    public function displayNameForAcademy(): string
    {
        if ($this->hide_identity) {
            return 'مرشح — '.$this->display_code;
        }

        return $this->user->name ?? $this->display_code;
    }
}

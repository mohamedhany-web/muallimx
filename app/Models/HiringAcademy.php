<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class HiringAcademy extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_SUSPENDED = 'suspended';

    public const STATUS_LEAD = 'lead';

    protected $fillable = [
        'name',
        'slug',
        'legal_name',
        'city',
        'address',
        'contact_name',
        'contact_email',
        'contact_phone',
        'website',
        'tax_id',
        'status',
        'commercial_notes',
        'internal_notes',
        'created_by',
    ];

    public static function statusLabels(): array
    {
        return [
            self::STATUS_ACTIVE => 'نشطة',
            self::STATUS_SUSPENDED => 'موقوفة',
            self::STATUS_LEAD => 'عميل محتمل',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(AcademyOpportunity::class, 'hiring_academy_id');
    }

    public static function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'academy';
        $slug = $base;
        $i = 1;
        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }
}

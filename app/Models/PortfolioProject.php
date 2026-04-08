<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PortfolioProject extends Model
{
    protected $fillable = [
        'user_id',
        'academic_year_id',
        'advanced_course_id',
        'title',
        'project_type',
        'content_type',
        'description',
        'content_text',
        'project_url',
        'github_url',
        'video_url',
        'image_path',
        'status',
        'instructor_notes',
        'reviewed_by',
        'reviewed_at',
        'published_at',
        'rejected_reason',
        'admin_notes',
        'is_visible',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'published_at' => 'datetime',
        'is_visible' => 'boolean',
    ];

    public const CONTENT_GALLERY = 'gallery';
    public const CONTENT_VIDEO = 'video';
    public const CONTENT_TEXT = 'text';
    public const CONTENT_LINK = 'link';

    public static function contentTypeLabels(): array
    {
        return [
            self::CONTENT_GALLERY => __('student.portfolio_marketing.content_types.gallery'),
            self::CONTENT_VIDEO => __('student.portfolio_marketing.content_types.video'),
            self::CONTENT_TEXT => __('student.portfolio_marketing.content_types.text'),
            self::CONTENT_LINK => __('student.portfolio_marketing.content_types.link'),
        ];
    }

    public static function projectTypeLabels(): array
    {
        $keys = ['web_app', 'mobile_app', 'api', 'library', 'script', 'design', 'game', 'desktop', 'cli', 'other'];
        $out = [];
        foreach ($keys as $key) {
            $out[$key] = __('student.portfolio_marketing.project_types.'.$key);
        }

        return $out;
    }

    public const STATUS_PENDING_REVIEW = 'pending_review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_PUBLISHED = 'published';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function advancedCourse(): BelongsTo
    {
        return $this->belongsTo(AdvancedCourse::class, 'advanced_course_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function images(): HasMany
    {
        return $this->hasMany(PortfolioProjectImage::class)->orderBy('sort_order');
    }

    /** صورة المعاينة (الأولى من المعرض أو image_path القديم) */
    public function getPreviewImagePathAttribute(): ?string
    {
        if ($this->relationLoaded('images')) {
            $first = $this->images->first();

            return $first ? $first->image_path : $this->image_path;
        }
        $first = $this->images()->first();

        return $first ? $first->image_path : $this->image_path;
    }

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED)->where('is_visible', true);
    }

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function videoEmbedUrl(): ?string
    {
        $url = trim((string) ($this->video_url ?? ''));
        if ($url === '') {
            return null;
        }

        // YouTube watch?v= or youtu.be
        if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/)([A-Za-z0-9_-]{6,})~', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }
        // Vimeo
        if (preg_match('~vimeo\.com/(\d+)~', $url, $m)) {
            return 'https://player.vimeo.com/video/' . $m[1];
        }
        return null;
    }
}

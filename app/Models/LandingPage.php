<?php

namespace App\Models;

use App\Services\YouTubeVideoService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class LandingPage extends Model
{
    protected $table = 'landing_pages';

    protected $fillable = [
        'title',
        'slug',
        'meta_title',
        'meta_description',
        'og_image_path',
        'headline',
        'subheadline',
        'sections',
        'utm_source',
        'utm_campaign',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'sections' => 'array',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * صفحات منشورة الآن (نشطة وضمن فترة الجدولة إن وُجدت).
     */
    public function scopePublishedNow(Builder $query): Builder
    {
        $now = now();

        return $query->active()
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            });
    }

    public function isPublishedNow(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return false;
        }

        return true;
    }

    public function publicUrl(): string
    {
        return route('public.landing-pages.show', $this);
    }

    public function ogImageUrl(): ?string
    {
        if (! is_string($this->og_image_path) || $this->og_image_path === '') {
            return null;
        }

        $path = str_replace('\\', '/', ltrim($this->og_image_path, '/'));

        if (Storage::disk('public')->exists($path)) {
            return asset('storage/'.$path);
        }

        return null;
    }

    /**
     * أقسام مرتّبة جاهزة للعرض العام.
     *
     * @return list<array<string, mixed>>
     */
    public function orderedSections(): array
    {
        $sections = is_array($this->sections) ? $this->sections : [];

        usort($sections, function ($a, $b) {
            return ((int) ($a['sort'] ?? 0)) <=> ((int) ($b['sort'] ?? 0));
        });

        return array_values($sections);
    }

    /**
     * قالب إعلان جاهز لصفحة هبوط جديدة.
     *
     * @return list<array<string, mixed>>
     */
    public static function adTemplateSections(): array
    {
        return [
            [
                'type' => 'hero',
                'sort' => 0,
                'headline' => 'كل أدوات المعلم المحترف في باقة واحدة',
                'text' => 'كثير يسألوننا: أنتم بتقدّموا إيه؟ الإجابة: Muallimx منصة اشتراك للمعلمين — مناهج، فيديو، ذكاء اصطناعي، فصل افتراضي، وتسويق شخصي داخل حساب واحد.',
                'buttons' => [
                    ['label' => 'ابدأ مجاناً', 'action' => 'register'],
                    ['label' => 'تواصل واتساب', 'action' => 'whatsapp', 'whatsapp_number' => '', 'whatsapp_message' => 'مرحباً، أريد معرفة المزيد عن منصة Muallimx'],
                ],
            ],
            [
                'type' => 'video',
                'sort' => 1,
                'title' => 'شاهد شرح المنصة',
                'description' => 'فيديو قصير يوضّح ماذا نقدّم وكيف تبدأ.',
                'youtube_url' => '',
                'youtube_id' => null,
            ],
            [
                'type' => 'features',
                'sort' => 2,
                'title' => 'ماذا نقدّم؟',
                'items' => [
                    ['icon' => 'fa-book-open', 'title' => 'مكتبة مناهج تفاعلية', 'description' => 'محتوى جاهز للتحضير والتدريس.'],
                    ['icon' => 'fa-play-circle', 'title' => 'قنوات فيديو تعليمية', 'description' => 'فيديوهات منظمة داخل المنصة.'],
                    ['icon' => 'fa-robot', 'title' => 'أدوات ذكاء اصطناعي', 'description' => 'تحضير ونصائح وألعاب تعليمية بسرعة.'],
                    ['icon' => 'fa-chalkboard-teacher', 'title' => 'فصل افتراضي', 'description' => 'عقد حصص أونلاين ضمن الباقة.'],
                    ['icon' => 'fa-user-tie', 'title' => 'تسويق شخصي', 'description' => 'بناء ملفك وظهورك للأكاديميات.'],
                    ['icon' => 'fa-headset', 'title' => 'دعم ومتابعة', 'description' => 'مساعدة حتى تستفيد من أدواتك فعلياً.'],
                ],
            ],
            [
                'type' => 'cta',
                'sort' => 3,
                'title' => 'جاهز تبدأ؟',
                'text' => 'أنشئ حسابك مجاناً أو راسلنا على واتساب — باختيارك.',
                'buttons' => [
                    ['label' => 'إنشاء حساب', 'action' => 'register'],
                    ['label' => 'عرض الباقات', 'action' => 'pricing'],
                    ['label' => 'واتساب', 'action' => 'whatsapp', 'whatsapp_number' => '', 'whatsapp_message' => 'مرحباً، أريد الاشتراك في Muallimx'],
                ],
            ],
        ];
    }

    /**
     * تطبيع أقسام الفيديو (استخراج youtube_id) وتنظيف الأزرار.
     *
     * @param  array<int, mixed>  $sections
     * @return list<array<string, mixed>>
     */
    public static function normalizeSections(array $sections): array
    {
        $normalized = [];
        $sort = 0;

        foreach ($sections as $section) {
            if (! is_array($section)) {
                continue;
            }

            $type = (string) ($section['type'] ?? '');
            if (! in_array($type, ['hero', 'text', 'video', 'features', 'testimonials', 'cta'], true)) {
                continue;
            }

            $item = [
                'type' => $type,
                'sort' => $sort++,
            ];

            switch ($type) {
                case 'hero':
                    $item['headline'] = trim((string) ($section['headline'] ?? ''));
                    $item['text'] = trim((string) ($section['text'] ?? ''));
                    $item['buttons'] = self::normalizeButtons($section['buttons'] ?? []);
                    break;
                case 'text':
                    $item['title'] = trim((string) ($section['title'] ?? ''));
                    $item['body'] = trim((string) ($section['body'] ?? ''));
                    break;
                case 'video':
                    $item['title'] = trim((string) ($section['title'] ?? ''));
                    $item['description'] = trim((string) ($section['description'] ?? ''));
                    $url = trim((string) ($section['youtube_url'] ?? $section['youtube_id'] ?? ''));
                    $item['youtube_url'] = $url;
                    $item['youtube_id'] = null;
                    if ($url !== '') {
                        $id = YouTubeVideoService::extractId($url);
                        if ($id) {
                            $item['youtube_id'] = $id;
                            $item['youtube_url'] = YouTubeVideoService::watchUrl($id);
                        }
                    }
                    break;
                case 'features':
                    $item['title'] = trim((string) ($section['title'] ?? ''));
                    $item['items'] = [];
                    foreach ((array) ($section['items'] ?? []) as $feat) {
                        if (! is_array($feat)) {
                            continue;
                        }
                        $title = trim((string) ($feat['title'] ?? ''));
                        if ($title === '') {
                            continue;
                        }
                        $item['items'][] = [
                            'icon' => trim((string) ($feat['icon'] ?? 'fa-check')) ?: 'fa-check',
                            'title' => $title,
                            'description' => trim((string) ($feat['description'] ?? '')),
                        ];
                    }
                    break;
                case 'testimonials':
                    $item['title'] = trim((string) ($section['title'] ?? ''));
                    $item['items'] = [];
                    foreach ((array) ($section['items'] ?? []) as $t) {
                        if (! is_array($t)) {
                            continue;
                        }
                        $quote = trim((string) ($t['quote'] ?? ''));
                        if ($quote === '') {
                            continue;
                        }
                        $item['items'][] = [
                            'name' => trim((string) ($t['name'] ?? '')),
                            'role' => trim((string) ($t['role'] ?? '')),
                            'quote' => $quote,
                        ];
                    }
                    break;
                case 'cta':
                    $item['title'] = trim((string) ($section['title'] ?? ''));
                    $item['text'] = trim((string) ($section['text'] ?? ''));
                    $item['buttons'] = self::normalizeButtons($section['buttons'] ?? []);
                    break;
            }

            $normalized[] = $item;
        }

        return $normalized;
    }

    /**
     * @param  mixed  $buttons
     * @return list<array<string, string>>
     */
    public static function normalizeButtons($buttons): array
    {
        $out = [];
        foreach ((array) $buttons as $btn) {
            if (! is_array($btn)) {
                continue;
            }
            $label = trim((string) ($btn['label'] ?? ''));
            $action = (string) ($btn['action'] ?? 'custom');
            if ($label === '' || ! in_array($action, ['register', 'pricing', 'whatsapp', 'custom'], true)) {
                continue;
            }
            $row = [
                'label' => $label,
                'action' => $action,
            ];
            if ($action === 'whatsapp') {
                $row['whatsapp_number'] = preg_replace('/\D+/', '', (string) ($btn['whatsapp_number'] ?? '')) ?? '';
                $row['whatsapp_message'] = trim((string) ($btn['whatsapp_message'] ?? ''));
            }
            if ($action === 'custom') {
                $row['url'] = trim((string) ($btn['url'] ?? ''));
            }
            $out[] = $row;
        }

        return $out;
    }

    protected static function booted(): void
    {
        static::deleting(function (LandingPage $page) {
            if (is_string($page->og_image_path) && $page->og_image_path !== '') {
                Storage::disk('public')->delete($page->og_image_path);
            }
        });
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TeacherFeaturesController extends Controller
{
    protected string $cacheKey = 'teacher_features_settings';

    public function index()
    {
        $settings = $this->getSettings();

        return view('admin.teacher-features.index', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'plans' => 'required|array',
            'plans.teacher_starter.price' => 'required|numeric|min:0',
            'plans.teacher_pro.price' => 'required|numeric|min:0',
            'plans.teacher_premium.price' => 'required|numeric|min:0',
            'plans.*.label' => 'nullable|string|max:255',
            'plans.*.billing_cycle' => 'nullable|string|in:monthly,quarterly,yearly',
            'plans.*.features' => 'nullable|array',
            'plans.*.features.*' => 'nullable|string|max:100',
            'plans.*.limits.classroom_meetings_per_month' => 'nullable|integer|min:0|max:10000',
            'plans.*.limits.classroom_max_participants' => 'nullable|integer|min:1|max:1000',
            'plans.*.limits.classroom_default_duration_minutes' => 'nullable|integer|min:15|max:1440',
            'plans.*.limits.classroom_max_duration_minutes' => 'nullable|integer|min:30|max:1440',
            'plans.*.limits.personal_marketing_profile_sections' => 'nullable|integer|min:1|max:20',
            'plans.*.limits.personal_marketing_priority_score' => 'nullable|integer|min:0|max:100',
            'plans.*.limits.personal_marketing_monthly_featured_days' => 'nullable|integer|min:0|max:31',
            'plans.*.card_subtitle' => 'nullable|string|max:500',
            'plans.*.card_badge' => 'nullable|string|max:200',
            'plans.*.card_price_hint' => 'nullable|string|max:500',
            'plans.*.card_cta' => 'nullable|string|max:120',
            'plans.*.card_footer_note' => 'nullable|string|max:500',
        ]);

        $plans = $validated['plans'];

        // تأكيد أن كل خطة تحتوي على الحقول المطلوبة
        foreach (['teacher_starter', 'teacher_pro', 'teacher_premium'] as $key) {
            $plans[$key]['label'] = $plans[$key]['label'] ?? $this->defaultSettings()[$key]['label'] ?? '';
            $plans[$key]['billing_cycle'] = in_array($plans[$key]['billing_cycle'] ?? '', ['monthly', 'quarterly', 'yearly'], true)
                ? $plans[$key]['billing_cycle']
                : ($this->defaultSettings()[$key]['billing_cycle'] ?? 'monthly');
            $plans[$key]['features'] = isset($plans[$key]['features']) && is_array($plans[$key]['features'])
                ? array_values(array_filter(
                    $plans[$key]['features'],
                    static fn ($f) => $f !== null && $f !== '' && $f !== 'zoom_access'
                ))
                : [];
            $defaults = $this->defaultSettings()[$key]['limits'] ?? [];
            $limits = $plans[$key]['limits'] ?? [];
            $plans[$key]['limits'] = [
                'classroom_meetings_per_month' => (int) ($limits['classroom_meetings_per_month'] ?? ($defaults['classroom_meetings_per_month'] ?? 0)),
                'classroom_max_participants' => (int) ($limits['classroom_max_participants'] ?? ($defaults['classroom_max_participants'] ?? 25)),
                'classroom_default_duration_minutes' => (int) ($limits['classroom_default_duration_minutes'] ?? ($defaults['classroom_default_duration_minutes'] ?? 60)),
                'classroom_max_duration_minutes' => (int) ($limits['classroom_max_duration_minutes'] ?? ($defaults['classroom_max_duration_minutes'] ?? 120)),
                'personal_marketing_profile_sections' => (int) ($limits['personal_marketing_profile_sections'] ?? ($defaults['personal_marketing_profile_sections'] ?? 5)),
                'personal_marketing_priority_score' => (int) ($limits['personal_marketing_priority_score'] ?? ($defaults['personal_marketing_priority_score'] ?? 0)),
                'personal_marketing_monthly_featured_days' => (int) ($limits['personal_marketing_monthly_featured_days'] ?? ($defaults['personal_marketing_monthly_featured_days'] ?? 0)),
            ];
            if ($plans[$key]['limits']['classroom_default_duration_minutes'] > $plans[$key]['limits']['classroom_max_duration_minutes']) {
                $plans[$key]['limits']['classroom_default_duration_minutes'] = $plans[$key]['limits']['classroom_max_duration_minutes'];
            }

            $defCard = $this->defaultSettings()[$key];
            foreach (['card_subtitle', 'card_badge', 'card_price_hint', 'card_cta', 'card_footer_note'] as $cf) {
                $raw = $plans[$key][$cf] ?? ($defCard[$cf] ?? '');
                $plans[$key][$cf] = is_string($raw) ? trim($raw) : '';
            }
        }

        $now = now();
        $exists = DB::table('settings')->where('key', 'teacher_features')->exists();
        if ($exists) {
            DB::table('settings')->where('key', 'teacher_features')->update([
                'value' => json_encode($plans),
                'updated_at' => $now,
            ]);
        } else {
            DB::table('settings')->insert([
                'key' => 'teacher_features',
                'value' => json_encode($plans),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        Cache::forget($this->cacheKey);
        Cache::forget('teacher_features_settings_limits');

        return back()->with('success', 'تم تحديث مزايا باقات المعلمين بنجاح.');
    }

    public function getSettings(): array
    {
        return Cache::remember($this->cacheKey, 300, function () {
            if (!DB::getSchemaBuilder()->hasTable('settings')) {
                return $this->defaultSettings();
            }

            $row = DB::table('settings')->where('key', 'teacher_features')->first();

            if (!$row) {
                return $this->defaultSettings();
            }

            $decoded = json_decode($row->value, true);

            if (!is_array($decoded)) {
                return $this->defaultSettings();
            }

            $defaults = $this->defaultSettings();
            $merged = [];
            foreach (['teacher_starter', 'teacher_pro', 'teacher_premium'] as $planKey) {
                $merged[$planKey] = array_merge(
                    $defaults[$planKey] ?? [],
                    is_array($decoded[$planKey] ?? null) ? $decoded[$planKey] : []
                );
                if (isset($merged[$planKey]['features']) && is_array($merged[$planKey]['features'])) {
                    $merged[$planKey]['features'] = array_values(array_filter(
                        $merged[$planKey]['features'],
                        static fn ($f) => $f !== 'zoom_access'
                    ));
                }
            }

            return $merged;
        });
    }

    protected function defaultSettings(): array
    {
        return [
            'teacher_starter' => [
                'label' => 'باقة البداية',
                'price' => 200,
                'billing_cycle' => 'monthly',
                'card_subtitle' => 'ابدأ التدريس أونلاين بسهولة',
                'card_badge' => '',
                'card_price_hint' => 'أقل من 7 جنيه يوميًا.',
                'card_cta' => 'ابدأ الآن',
                'card_footer_note' => '',
                'features' => [
                    'library_access',
                    'ai_tools',
                    'classroom_access',
                    'support',
                ],
                'limits' => [
                    'classroom_meetings_per_month' => 8,
                    'classroom_max_participants' => 25,
                    'classroom_default_duration_minutes' => 60,
                    'classroom_max_duration_minutes' => 120,
                    'personal_marketing_profile_sections' => 5,
                    'personal_marketing_priority_score' => 10,
                    'personal_marketing_monthly_featured_days' => 0,
                ],
            ],
            'teacher_pro' => [
                'label' => 'باقة المعلم المحترف',
                'price' => 600,
                'billing_cycle' => 'quarterly',
                'card_subtitle' => 'أفضل اختيار للمعلمين الذين يريدون العمل أونلاين',
                'card_badge' => 'الأفضل للبدء أونلاين',
                'card_price_hint' => 'استثمار ربع سنوي يمنحك حضورًا مهنيًا وفرص عمل حقيقية.',
                'card_cta' => 'ابدأ العمل الآن',
                'card_footer_note' => 'فرص حقيقية للعمل مع أكاديميات، وليس مجرد أدوات.',
                'features' => [
                    'library_access',
                    'ai_tools',
                    'classroom_access',
                    'support',
                    'teacher_profile',
                    'visible_to_academies',
                    'can_apply_opportunities',
                    'full_ai_suite',
                ],
                'limits' => [
                    'classroom_meetings_per_month' => 30,
                    'classroom_max_participants' => 60,
                    'classroom_default_duration_minutes' => 90,
                    'classroom_max_duration_minutes' => 240,
                    'personal_marketing_profile_sections' => 8,
                    'personal_marketing_priority_score' => 50,
                    'personal_marketing_monthly_featured_days' => 4,
                ],
            ],
            'teacher_premium' => [
                'label' => 'باقة المعلم المميز',
                'price' => 1500,
                'billing_cycle' => 'yearly',
                'card_subtitle' => 'للمعلمين الجادين في بناء مسار مهني مستقر',
                'card_badge' => '',
                'card_price_hint' => 'اشتراك سنوي يمنحك استقرارًا وفرص تدريس مستمرة طوال العام.',
                'card_cta' => 'ابدأ رحلتك الآن',
                'card_footer_note' => 'نساعدك في الوصول إلى فرص تدريس حقيقية وبناء اسمك كمعلم أونلاين.',
                'features' => [
                    'library_access',
                    'ai_tools',
                    'classroom_access',
                    'support',
                    'teacher_profile',
                    'visible_to_academies',
                    'can_apply_opportunities',
                    'full_ai_suite',
                    'teacher_evaluation',
                    'recommended_to_academies',
                    'priority_opportunities',
                    'direct_support',
                ],
                'limits' => [
                    'classroom_meetings_per_month' => 9999,
                    'classroom_max_participants' => 150,
                    'classroom_default_duration_minutes' => 120,
                    'classroom_max_duration_minutes' => 480,
                    'personal_marketing_profile_sections' => 12,
                    'personal_marketing_priority_score' => 90,
                    'personal_marketing_monthly_featured_days' => 12,
                ],
            ],
        ];
    }
}


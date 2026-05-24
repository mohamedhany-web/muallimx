<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Support\TeacherPlanKeys;
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
            'plans.teacher_free.price' => 'required|numeric|in:0',
            'plans.teacher_free.is_active' => 'nullable|boolean',
            'plans.teacher_free.duration_days' => 'required|integer|min:1|max:365',
            'plans.teacher_free.allow_repeat_activation' => 'nullable|boolean',
            'plans.teacher_starter.price' => 'required|numeric|min:0',
            'plans.teacher_pro.price' => 'required|numeric|min:0',
            'plans.*.label' => 'nullable|string|max:255',
            'plans.*.billing_cycle' => 'nullable|string|in:monthly,trial',
            'plans.*.features' => 'nullable|array',
            'plans.*.features.*' => 'nullable|string|max:100',
            'plans.*.feature_descriptions' => 'nullable|array',
            'plans.*.feature_descriptions.*' => 'nullable|string|max:500',
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

        // استخدام مدخلات النموذج كاملة (المزايا/الحدود) بعد نجاح التحقق
        $plans = $request->input('plans', []);
        if (! is_array($plans)) {
            $plans = [];
        }

        // تأكيد أن كل خطة تحتوي على الحقول المطلوبة
        foreach (TeacherPlanKeys::ordered() as $key) {
            $plans[$key] = array_merge(
                $this->defaultSettings()[$key] ?? [],
                is_array($plans[$key] ?? null) ? $plans[$key] : []
            );
            $plans[$key]['label'] = $plans[$key]['label'] ?? $this->defaultSettings()[$key]['label'] ?? '';
            $plans[$key]['billing_cycle'] = 'monthly';
            if ($key === TeacherPlanKeys::FREE) {
                $plans[$key]['price'] = 0;
                $plans[$key]['billing_cycle'] = 'trial';
                $plans[$key]['is_active'] = filter_var($plans[$key]['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN);
                $plans[$key]['duration_days'] = max(1, min(365, (int) ($plans[$key]['duration_days'] ?? 14)));
                $plans[$key]['allow_repeat_activation'] = filter_var(
                    $plans[$key]['allow_repeat_activation'] ?? false,
                    FILTER_VALIDATE_BOOLEAN
                );
            }
            $plans[$key]['features'] = isset($plans[$key]['features']) && is_array($plans[$key]['features'])
                ? array_values(array_filter(
                    $plans[$key]['features'],
                    static fn ($f) => $f !== null && $f !== '' && $f !== 'zoom_access'
                ))
                : [];
            if ($key === TeacherPlanKeys::STARTER) {
                $plans[$key]['features'] = array_values(array_filter(
                    $plans[$key]['features'],
                    static fn ($f) => $f !== 'classroom_access'
                ));
            }
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
            if ($key === TeacherPlanKeys::STARTER) {
                // باقة البداية لا تحتوي على Muallimx Classroom نهائياً.
                $plans[$key]['limits']['classroom_meetings_per_month'] = 0;
                $plans[$key]['limits']['classroom_max_participants'] = 1;
                $plans[$key]['limits']['classroom_default_duration_minutes'] = 60;
                $plans[$key]['limits']['classroom_max_duration_minutes'] = 60;
            }

            $defaultDescriptions = $this->defaultSettings()[$key]['feature_descriptions'] ?? [];
            $rawDescriptions = $plans[$key]['feature_descriptions'] ?? [];
            $normalizedDescriptions = [];
            if (is_array($rawDescriptions)) {
                foreach ($rawDescriptions as $featureKey => $descriptionValue) {
                    if (!is_string($featureKey)) {
                        continue;
                    }
                    $normalizedDescriptions[$featureKey] = is_string($descriptionValue)
                        ? trim($descriptionValue)
                        : '';
                }
            }
            foreach ($defaultDescriptions as $featureKey => $defaultDescription) {
                if (!array_key_exists($featureKey, $normalizedDescriptions) || $normalizedDescriptions[$featureKey] === '') {
                    $normalizedDescriptions[$featureKey] = (string) $defaultDescription;
                }
            }
            $plans[$key]['feature_descriptions'] = $normalizedDescriptions;

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

        $synced = $this->syncActiveSubscriptionsFromPlans($plans);

        $message = 'تم تحديث مزايا باقات المعلمين بنجاح.';
        if ($synced > 0) {
            $message .= ' تم تحديث مزايا '.$synced.' اشتراك نشط ليطابق الإعدادات الجديدة.';
        }

        return back()->with('success', $message);
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
            foreach (TeacherPlanKeys::ordered() as $planKey) {
                $merged[$planKey] = array_merge(
                    $defaults[$planKey] ?? [],
                    is_array($decoded[$planKey] ?? null) ? $decoded[$planKey] : []
                );
                if ($planKey === TeacherPlanKeys::FREE) {
                    $merged[$planKey]['price'] = 0;
                    $merged[$planKey]['billing_cycle'] = 'trial';
                    $merged[$planKey]['duration_days'] = TeacherPlanKeys::freeDurationDays($merged[$planKey]);
                    $merged[$planKey]['is_active'] = filter_var($merged[$planKey]['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN);
                    $merged[$planKey]['allow_repeat_activation'] = TeacherPlanKeys::freeAllowsRepeat($merged[$planKey]);
                }
                if (isset($merged[$planKey]['features']) && is_array($merged[$planKey]['features'])) {
                    $merged[$planKey]['features'] = array_values(array_filter(
                        $merged[$planKey]['features'],
                        static fn ($f) => $f !== 'zoom_access'
                    ));
                }
                if ($planKey === TeacherPlanKeys::STARTER) {
                    $merged[$planKey]['features'] = array_values(array_filter(
                        $merged[$planKey]['features'],
                        static fn ($f) => $f !== 'classroom_access'
                    ));
                    $merged[$planKey]['limits']['classroom_meetings_per_month'] = 0;
                }
                $defaultFeatureDescriptions = $defaults[$planKey]['feature_descriptions'] ?? [];
                $decodedFeatureDescriptions = is_array($merged[$planKey]['feature_descriptions'] ?? null)
                    ? $merged[$planKey]['feature_descriptions']
                    : [];
                $merged[$planKey]['feature_descriptions'] = array_merge($defaultFeatureDescriptions, $decodedFeatureDescriptions);
            }

            return $merged;
        });
    }

    /**
     * مزامنة حقل features للاشتراكات النشطة مع إعدادات الباقة (بعد تعديل الأدمن).
     */
    protected function syncActiveSubscriptionsFromPlans(array $plans): int
    {
        $total = 0;

        foreach (TeacherPlanKeys::ordered() as $planKey) {
            if (! isset($plans[$planKey]) || ! is_array($plans[$planKey])) {
                continue;
            }

            $features = isset($plans[$planKey]['features']) && is_array($plans[$planKey]['features'])
                ? Subscription::normalizeFeatureKeys($plans[$planKey]['features'])
                : [];

            $total += Subscription::query()
                ->where('status', 'active')
                ->where('teacher_plan_key', $planKey)
                ->where(function ($q) {
                    $q->whereNull('end_date')->orWhereDate('end_date', '>=', now());
                })
                ->update(['features' => $features]);
        }

        return $total;
    }

    protected function defaultSettings(): array
    {
        return [
            TeacherPlanKeys::FREE => [
                'label' => 'الباقة المجانية',
                'price' => 0,
                'is_active' => true,
                'duration_days' => 14,
                'allow_repeat_activation' => false,
                'billing_cycle' => 'trial',
                'card_subtitle' => 'ابدأ مجاناً بمزايا محدودة لمدة محددة',
                'card_badge' => 'مجانية',
                'card_price_hint' => 'تفعيل فوري — تنتهي تلقائياً بعد المدة المحددة.',
                'card_cta' => 'فعّل الباقة المجانية',
                'card_footer_note' => 'الحدود والمزايا تُضبط من لوحة الإدارة.',
                'features' => [
                    'library_access',
                    'ai_tools',
                    'support',
                ],
                'feature_descriptions' => [
                    'library_access' => 'وصول محدود لمكتبة المحتوى التعليمي.',
                    'ai_tools' => 'أدوات ذكاء اصطناعي أساسية للتحضير.',
                    'classroom_access' => 'استخدام Muallimx Classroom بعدد اجتماعات محدود شهرياً.',
                    'support' => 'دعم فني عبر المنصة.',
                ],
                'limits' => [
                    'classroom_meetings_per_month' => 2,
                    'classroom_max_participants' => 15,
                    'classroom_default_duration_minutes' => 45,
                    'classroom_max_duration_minutes' => 60,
                    'personal_marketing_profile_sections' => 3,
                    'personal_marketing_priority_score' => 20,
                    'personal_marketing_monthly_featured_days' => 0,
                ],
            ],
            TeacherPlanKeys::STARTER => [
                'label' => 'الباقة الأساسية',
                'price' => 200,
                'billing_cycle' => 'monthly',
                'card_subtitle' => 'كل الخدمات التعليمية بدون الميتينج وخدماته',
                'card_badge' => '',
                'card_price_hint' => 'اشتراك شهري مناسب للبدء.',
                'card_cta' => 'ابدأ الآن',
                'card_footer_note' => '',
                'features' => [
                    'library_access',
                    'ai_tools',
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
                'feature_descriptions' => [
                    'library_access' => 'وصول كامل لمكتبة مناهج وأنشطة جاهزة للتطبيق مباشرة.',
                    'ai_tools' => 'أدوات ذكاء اصطناعي تساعدك على تجهيز المحتوى بسرعة.',
                    'support' => 'دعم فني لمساعدتك في أي مشكلة تشغيلية داخل المنصة.',
                    'teacher_profile' => 'بروفايل مهني يعرض خبراتك ومجالاتك التعليمية.',
                    'visible_to_academies' => 'ظهور ملفك للأكاديميات الباحثة عن معلمين.',
                    'can_apply_opportunities' => 'إمكانية التقديم على فرص التدريس المتاحة.',
                    'full_ai_suite' => 'مجموعة AI موسعة لتخطيط الدروس والمتابعة.',
                    'teacher_evaluation' => 'تقييم احترافي يساعدك على تحسين أدائك.',
                    'recommended_to_academies' => 'ترشيح ملفك للأكاديميات المناسبة لمهاراتك.',
                    'priority_opportunities' => 'أولوية أعلى في الوصول لبعض فرص التدريس.',
                    'direct_support' => 'دعم مباشر وسريع للحالات المهمة.',
                ],
                'limits' => [
                    'classroom_meetings_per_month' => 0,
                    'classroom_max_participants' => 1,
                    'classroom_default_duration_minutes' => 60,
                    'classroom_max_duration_minutes' => 60,
                    'personal_marketing_profile_sections' => 5,
                    'personal_marketing_priority_score' => 40,
                    'personal_marketing_monthly_featured_days' => 0,
                ],
            ],
            TeacherPlanKeys::PRO => [
                'label' => 'الباقة الشاملة',
                'price' => 600,
                'billing_cycle' => 'monthly',
                'card_subtitle' => 'كل الخدمات + الميتينج وجميع خدماته',
                'card_badge' => 'الأكثر شمولاً',
                'card_price_hint' => 'اشتراك شهري يشمل جميع الأدوات واللايف ميتينج.',
                'card_cta' => 'ابدأ العمل الآن',
                'card_footer_note' => 'تشمل كامل المزايا بدون استثناء.',
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
                'feature_descriptions' => [
                    'library_access' => 'وصول كامل لمكتبة مناهج وأنشطة جاهزة للتطبيق مباشرة.',
                    'ai_tools' => 'أدوات ذكاء اصطناعي تساعدك على تجهيز المحتوى بسرعة.',
                    'classroom_access' => 'استخدام Muallimx Classroom لعقد لايف ميتينج وإدارة الجلسات.',
                    'support' => 'دعم فني لمساعدتك في أي مشكلة تشغيلية داخل المنصة.',
                    'teacher_profile' => 'بروفايل مهني يعرض خبراتك ومجالاتك التعليمية.',
                    'visible_to_academies' => 'ظهور ملفك للأكاديميات الباحثة عن معلمين.',
                    'can_apply_opportunities' => 'إمكانية التقديم على فرص التدريس المتاحة.',
                    'full_ai_suite' => 'مجموعة AI موسعة لتخطيط الدروس والمتابعة.',
                    'teacher_evaluation' => 'تقييم احترافي يساعدك على تحسين أدائك.',
                    'recommended_to_academies' => 'ترشيح ملفك للأكاديميات المناسبة لمهاراتك.',
                    'priority_opportunities' => 'أولوية أعلى في الوصول لبعض فرص التدريس.',
                    'direct_support' => 'دعم مباشر وسريع للحالات المهمة.',
                ],
                'limits' => [
                    'classroom_meetings_per_month' => 9999,
                    'classroom_max_participants' => 150,
                    'classroom_default_duration_minutes' => 90,
                    'classroom_max_duration_minutes' => 480,
                    'personal_marketing_profile_sections' => 12,
                    'personal_marketing_priority_score' => 90,
                    'personal_marketing_monthly_featured_days' => 12,
                ],
            ],
        ];
    }
}


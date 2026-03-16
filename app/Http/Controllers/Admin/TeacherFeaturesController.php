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
        ]);

        $plans = $validated['plans'];

        // تأكيد أن كل خطة تحتوي على الحقول المطلوبة
        foreach (['teacher_starter', 'teacher_pro', 'teacher_premium'] as $key) {
            $plans[$key]['label'] = $plans[$key]['label'] ?? $this->defaultSettings()[$key]['label'] ?? '';
            $plans[$key]['billing_cycle'] = in_array($plans[$key]['billing_cycle'] ?? '', ['monthly', 'quarterly', 'yearly'], true)
                ? $plans[$key]['billing_cycle']
                : ($this->defaultSettings()[$key]['billing_cycle'] ?? 'monthly');
            $plans[$key]['features'] = isset($plans[$key]['features']) && is_array($plans[$key]['features'])
                ? array_values(array_filter($plans[$key]['features']))
                : [];
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

            return array_merge($this->defaultSettings(), $decoded);
        });
    }

    protected function defaultSettings(): array
    {
        return [
            'teacher_starter' => [
                'label' => 'باقة البداية',
                'price' => 200,
                'billing_cycle' => 'monthly',
                'features' => [
                    'library_access',
                    'ai_tools',
                    'classroom_access',
                    'zoom_access',
                    'support',
                ],
            ],
            'teacher_pro' => [
                'label' => 'باقة المعلم المحترف',
                'price' => 600,
                'billing_cycle' => 'quarterly',
                'features' => [
                    'library_access',
                    'ai_tools',
                    'classroom_access',
                    'zoom_access',
                    'support',
                    'teacher_profile',
                    'visible_to_academies',
                    'can_apply_opportunities',
                    'full_ai_suite',
                ],
            ],
            'teacher_premium' => [
                'label' => 'باقة المعلم المميز',
                'price' => 1500,
                'billing_cycle' => 'yearly',
                'features' => [
                    'library_access',
                    'ai_tools',
                    'classroom_access',
                    'zoom_access',
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
            ],
        ];
    }
}


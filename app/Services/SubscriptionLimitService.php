<?php

namespace App\Services;

use App\Models\ClassroomMeeting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SubscriptionLimitService
{
    public static function defaultPlans(): array
    {
        return [
            'teacher_starter' => [
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

    public static function teacherPlansFromSettings(): array
    {
        return Cache::remember('teacher_features_settings_limits', 300, function () {
            $defaults = self::defaultPlans();

            if (!DB::getSchemaBuilder()->hasTable('settings')) {
                return $defaults;
            }

            $row = DB::table('settings')->where('key', 'teacher_features')->first();
            if (!$row) {
                return $defaults;
            }

            $decoded = json_decode($row->value, true);
            if (!is_array($decoded)) {
                return $defaults;
            }

            $plans = array_merge($defaults, $decoded);
            foreach ($defaults as $planKey => $planDefaults) {
                $current = $plans[$planKey]['limits'] ?? [];
                $plans[$planKey]['limits'] = array_merge($planDefaults['limits'], is_array($current) ? $current : []);
            }

            return $plans;
        });
    }

    public static function limitsForUser(User $user): array
    {
        $plans = self::teacherPlansFromSettings();
        $sub = $user->activeSubscription();
        $planKey = $sub?->teacher_plan_key;

        if (!$planKey || !isset($plans[$planKey])) {
            $planKey = 'teacher_starter';
        }

        $limits = $plans[$planKey]['limits'] ?? self::defaultPlans()['teacher_starter']['limits'];
        return [
            'plan_key' => $planKey,
            'classroom_meetings_per_month' => max(0, (int) ($limits['classroom_meetings_per_month'] ?? 0)),
            'classroom_max_participants' => max(1, (int) ($limits['classroom_max_participants'] ?? 25)),
            'classroom_default_duration_minutes' => max(15, (int) ($limits['classroom_default_duration_minutes'] ?? 60)),
            'classroom_max_duration_minutes' => max(30, (int) ($limits['classroom_max_duration_minutes'] ?? 120)),
            'personal_marketing_profile_sections' => max(1, (int) ($limits['personal_marketing_profile_sections'] ?? 5)),
            'personal_marketing_priority_score' => max(0, min(100, (int) ($limits['personal_marketing_priority_score'] ?? 0))),
            'personal_marketing_monthly_featured_days' => max(0, min(31, (int) ($limits['personal_marketing_monthly_featured_days'] ?? 0))),
        ];
    }

    public static function monthlyClassroomUsage(User $user): int
    {
        return ClassroomMeeting::query()
            ->where('user_id', $user->id)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();
    }
}


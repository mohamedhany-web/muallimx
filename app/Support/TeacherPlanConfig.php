<?php

namespace App\Support;

use App\Http\Controllers\Admin\TeacherFeaturesController;
use App\Models\Subscription;
use App\Models\SubscriptionRequest;

/**
 * إعدادات باقات المعلم الحية من لوحة الأدمن (settings.teacher_features).
 */
class TeacherPlanConfig
{
    public static function settings(): array
    {
        return (new TeacherFeaturesController)->getSettings();
    }

    public static function plan(string $planKey): ?array
    {
        if (! TeacherPlanKeys::isValid($planKey)) {
            return null;
        }

        $settings = self::settings();

        return is_array($settings[$planKey] ?? null) ? $settings[$planKey] : null;
    }

    /**
     * @return list<string>
     */
    public static function featureKeysForPlan(string $planKey): array
    {
        $plan = self::plan($planKey);
        $features = $plan['features'] ?? SubscriptionRequest::planDefaults($planKey)['features'] ?? [];

        return Subscription::normalizeFeatureKeys(is_array($features) ? $features : []);
    }
}

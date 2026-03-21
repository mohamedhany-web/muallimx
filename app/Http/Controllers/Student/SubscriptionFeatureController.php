<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionFeatureController extends Controller
{
    /**
     * عرض صفحة الميزة المرتبطة بالاشتراك.
     * يتحقق من أن المستخدم لديه الميزة في باقته النشطة ثم يعرض الصفحة.
     */
    public function show(Request $request, string $feature)
    {
        $user = Auth::user();
        $config = config('student_subscription_features', []);

        if (!isset($config[$feature])) {
            abort(404);
        }

        if (!$user->hasSubscriptionFeature($feature)) {
            abort(403, 'هذه الميزة غير متاحة في باقتك الحالية. يمكنك ترقية اشتراكك من صفحة التسعير.');
        }

        if ($feature === 'classroom_access') {
            return redirect()->route('student.classroom.index');
        }
        if ($feature === 'support') {
            return redirect()->route('student.support.index');
        }
        if ($feature === 'visible_to_academies') {
            return redirect()->route('student.academies.visibility');
        }
        if ($feature === 'can_apply_opportunities') {
            return redirect()->route('student.opportunities.index');
        }

        $featureConfig = $config[$feature];
        $label = __('student.subscription_feature.' . $feature);
        $description = __('student.subscription_feature_desc.' . $feature);

        return view('student.features.show', [
            'feature' => $feature,
            'label' => $label,
            'description' => $description,
            'featureConfig' => $featureConfig,
        ]);
    }
}

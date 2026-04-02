<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\FullAiSuitePreviewRequest;
use App\Services\FullAiSuiteContextService;
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

        if ($feature === 'ai_tools') {
            if ($user->hasSubscriptionFeature('full_ai_suite')) {
                return redirect()->route('student.features.show', ['feature' => 'full_ai_suite']);
            }
            if ($user->hasSubscriptionFeature('library_access')) {
                return redirect()->route('curriculum-library.index')
                    ->with('info', 'من هنا تصل إلى مكتبة المناهج والمواد لدعم تحضير الدروس ضمن باقتك.');
            }
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
        if ($feature === 'direct_support') {
            return redirect()->route('student.support.index');
        }
        if ($feature === 'teacher_evaluation') {
            return redirect()->route('student.support.index')
                ->with('info', 'تقييم المعلم يتم بالتنسيق مع فريق المنصة عبر تذاكر الدعم.');
        }
        if (in_array($feature, ['recommended_to_academies', 'priority_opportunities'], true)) {
            if ($user->hasSubscriptionFeature('visible_to_academies')) {
                return redirect()->route('student.academies.visibility');
            }
            if ($user->hasSubscriptionFeature('can_apply_opportunities')) {
                return redirect()->route('student.opportunities.index');
            }
        }

        $featureConfig = $config[$feature];
        $label = __('student.subscription_feature.' . $feature);
        $description = __('student.subscription_feature_desc.' . $feature);

        if ($feature === 'full_ai_suite') {
            $courses = $user->activeCourses()
                ->select('advanced_courses.id', 'advanced_courses.title', 'advanced_courses.category')
                ->get();

            return view('student.features.full-ai-suite', [
                'feature' => $feature,
                'label' => $label,
                'description' => $description,
                'featureConfig' => $featureConfig,
                'courses' => $courses,
            ]);
        }

        return view('student.features.show', [
            'feature' => $feature,
            'label' => $label,
            'description' => $description,
            'featureConfig' => $featureConfig,
        ]);
    }

    /**
     * طبقة Laravel: التحقق من البيانات وبناء السياق — بدون استدعاء Gemini.
     */
    public function previewFullAiSuite(FullAiSuitePreviewRequest $request, FullAiSuiteContextService $service)
    {
        $ctx = $service->buildContext(
            $request->user(),
            (int) $request->validated('advanced_course_id'),
            $request->validated('question_type'),
            $request->validated('question')
        );

        return back()
            ->withInput($request->only(['advanced_course_id', 'question_type', 'question']))
            ->with('full_ai_preview', [
                'context' => $ctx,
                'prompt' => $service->buildPromptPreview($ctx),
            ]);
    }
}

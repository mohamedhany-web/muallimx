<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\AdvancedCourse;
use App\Models\PopupAd;
use App\Models\SiteTestimonial;
use App\Models\SiteService;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * الصفحة الرئيسية (Landing).
 * اللغة تُحدد عبر Middleware SetLandingLocale من ?lang= أو الجلسة.
 */
class LandingController extends Controller
{
    public function index(): View
    {
        $popupAd = null;
        $ad = PopupAd::activeNow()->first();
        if ($ad) {
            $key = 'popup_ad_' . $ad->id . '_views';
            $views = (int) session($key, 0);
            if ($views < $ad->max_views_per_visitor) {
                session([$key => $views + 1]);
                $popupAd = $ad;
            }
        }

        // باقات المعلمين من إعدادات مزايا اشتراك المعلمين (نفس بيانات /admin/teacher-features وصفحة الأسعار)
        $featuresController = new \App\Http\Controllers\Admin\TeacherFeaturesController();
        $teacherPlans = $featuresController->getSettings();

        $homeCategories = $this->buildHomeCategories();

        $homeTestimonials = SiteTestimonial::query()
            ->active()
            ->ordered()
            ->limit(24)
            ->get();

        $featureKeys = config('student_subscription_features', []);

        $homeStats = [
            'learners' => User::query()->where('role', 'student')->where('is_active', true)->count(),
            'features' => count($featureKeys),
            'plans' => collect($teacherPlans)->filter(function ($plan, $key) {
                if ($key === 'teacher_free') {
                    return filter_var($plan['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN);
                }

                return is_array($plan);
            })->count(),
            'services' => SiteService::active()->count(),
        ];

        return view('welcome', compact(
            'popupAd',
            'teacherPlans',
            'homeCategories',
            'homeTestimonials',
            'homeStats'
        ));
    }

    /**
     * بطاقات تميّز مجالات الخدمة على الصفحة الرئيسية — جميعها تؤدي إلى صفحة الخدمات.
     *
     * @return \Illuminate\Support\Collection<int, array{name: string, description: string, icon: string, url: string}>
     */
    private function buildHomeCategories(): \Illuminate\Support\Collection
    {
        $servicesUrl = route('public.services.index');

        return collect(range(1, 6))->map(function (int $i) use ($servicesUrl) {
            $iconKey = 'public.home_category_fallback_'.$i.'_icon';
            $iconRaw = __($iconKey);
            $icon = is_string($iconRaw) && preg_match('/\bfa-[a-z0-9-]+\b/i', $iconRaw, $m)
                ? strtolower($m[0])
                : 'fa-circle';

            return [
                'name' => __('public.home_category_fallback_'.$i.'_name'),
                'description' => __('public.home_category_fallback_'.$i.'_desc'),
                'icon' => $icon,
                'url' => $servicesUrl,
            ];
        });
    }

    /**
     * جلب المسارات التعليمية بنفس منطق صفحة المسارات (للاستخدام في الصفحة الرئيسية أو أي عرض عام).
     * @param int|null $limit عدد المسارات (null = بدون حد)
     */
    public static function getPublicLearningPaths(?int $limit = null): \Illuminate\Support\Collection
    {
        $query = AcademicYear::where('is_active', true)
            ->with(['linkedCourses' => function ($q) {
                $q->where('is_active', true);
            }, 'academicSubjects' => function ($q) {
                $q->where('is_active', true);
            }])
            ->withCount(['linkedCourses', 'academicSubjects'])
            ->orderBy('order');

        if ($limit !== null) {
            $query->limit($limit);
        }

        $academicYears = $query->get();

        return $academicYears->map(function ($year) {
            $linkedCourses = $year->linkedCourses ?? collect();
            $subjectCourses = collect();
            if ($year->academicSubjects && $year->academicSubjects->isNotEmpty()) {
                $subjectIds = $year->academicSubjects->pluck('id')->toArray();
                if (!empty($subjectIds)) {
                    $subjectCourses = AdvancedCourse::where('is_active', true)
                        ->whereIn('academic_subject_id', $subjectIds)
                        ->get();
                }
            }
            $courses = $linkedCourses->merge($subjectCourses)->unique('id');
            $slug = Str::slug($year->name);
            $thumb = $year->thumbnail ? str_replace('\\', '/', $year->thumbnail) : null;
            $imageUrl = $thumb ? asset('storage/' . $thumb) : null;

            return (object) [
                'id' => $year->id,
                'name' => $year->name,
                'description' => $year->description,
                'slug' => $slug,
                'price' => (float) ($year->price ?? 0),
                'courses_count' => $courses->count(),
                'thumbnail' => $year->thumbnail,
                'image_url' => $imageUrl,
                'icon' => $year->icon,
                'color' => $year->color,
                'code' => $year->code,
            ];
        });
    }
}

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Storage files (صور وملفات) - يجب أن يكون أول Route لضمان عدم اعتراضه
| يعمل عند عدم وجود symlink public/storage على الاستضافة
|--------------------------------------------------------------------------
*/
Route::get('/storage/{path}', function ($path) {
    $path = rawurldecode($path);
    $path = str_replace('..', '', $path);
    $path = ltrim($path, '/');

    $basePath = storage_path('app/public');
    $filePath = $basePath . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    $filePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $filePath);

    if (!@file_exists($filePath) || !@is_file($filePath)) {
        if (config('app.debug')) {
            \Log::warning('Storage file not found', ['requested_path' => $path]);
        }
        abort(404, 'File not found');
    }

    $realPath = @realpath($filePath) ?: $filePath;
    $allowedPath = @realpath($basePath) ?: $basePath;
    $normalizedRealPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $realPath);
    $normalizedAllowedPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $allowedPath);

    if ($allowedPath === '' || strpos($normalizedRealPath, $normalizedAllowedPath) !== 0) {
        abort(404, 'Access denied');
    }

    if (!@is_readable($realPath)) {
        abort(403, 'File not readable');
    }

    $mimeType = @mime_content_type($realPath);
    if (!$mimeType) {
        $extension = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif',
            'webp' => 'image/webp', 'svg' => 'image/svg+xml', 'pdf' => 'application/pdf',
            'doc' => 'application/msword', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];
        $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
    }

    $headers = [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=31536000, immutable',
    ];
    if ($mimeType === 'application/pdf') {
        $headers['Content-Disposition'] = 'inline; filename="' . basename($realPath) . '"';
    }

    return response()->file($realPath, $headers);
})->where('path', '.*')->name('storage.file')->middleware('web');

// Sitemap Route
Route::get('/sitemap.xml', function() {
    $sitemap = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
    
    // Home Page
    $sitemap .= '
    <url>
        <loc>' . url('/') . '</loc>
        <lastmod>' . date('Y-m-d') . '</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>';
    
    // Public Pages
    $publicPages = [
        ['url' => '/courses', 'priority' => '0.9', 'changefreq' => 'daily'],
        ['url' => '/about', 'priority' => '0.8', 'changefreq' => 'monthly'],
        ['url' => '/contact', 'priority' => '0.7', 'changefreq' => 'monthly'],
        ['url' => '/pricing', 'priority' => '0.8', 'changefreq' => 'weekly'],
        ['url' => '/faq', 'priority' => '0.7', 'changefreq' => 'monthly'],
        ['url' => '/terms', 'priority' => '0.5', 'changefreq' => 'yearly'],
        ['url' => '/privacy', 'priority' => '0.5', 'changefreq' => 'yearly'],
    ];
    
    foreach ($publicPages as $page) {
        $sitemap .= '
    <url>
        <loc>' . url($page['url']) . '</loc>
        <lastmod>' . date('Y-m-d') . '</lastmod>
        <changefreq>' . $page['changefreq'] . '</changefreq>
        <priority>' . $page['priority'] . '</priority>
    </url>';
    }
    
    // Active Courses
    try {
        $courses = \App\Models\AdvancedCourse::where('is_active', true)
            ->select('id', 'updated_at')
            ->orderBy('updated_at', 'desc')
            ->get();
        
        foreach ($courses as $course) {
            $sitemap .= '
    <url>
        <loc>' . url('/course/' . $course->id) . '</loc>
        <lastmod>' . $course->updated_at->format('Y-m-d') . '</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>';
        }
    } catch (\Exception $e) {
        // Skip if courses table doesn't exist
    }
    
    $sitemap .= '
</urlset>';
    
    return response($sitemap, 200)
        ->header('Content-Type', 'application/xml');
})->name('sitemap');

// الصفحة الرئيسية (Home) - الترجمة عبر SetLocale في مجموعة web
Route::get('/', [\App\Http\Controllers\Public\LandingController::class, 'index'])->name('home');

// الصفحات العامة
Route::get('/about', [\App\Http\Controllers\Public\PageController::class, 'about'])->name('public.about');
Route::get('/faq', [\App\Http\Controllers\Public\PageController::class, 'faq'])->name('public.faq');
Route::get('/terms', [\App\Http\Controllers\Public\PageController::class, 'terms'])->name('public.terms');
Route::get('/privacy', [\App\Http\Controllers\Public\PageController::class, 'privacy'])->name('public.privacy');
Route::get('/pricing', [\App\Http\Controllers\Public\PageController::class, 'pricing'])->name('public.pricing');
Route::get('/pricing/checkout/{plan}', [\App\Http\Controllers\Public\SubscriptionCheckoutController::class, 'show'])->name('public.subscription.checkout')->where('plan', 'teacher_starter|teacher_pro|teacher_premium');
Route::post('/pricing/checkout', [\App\Http\Controllers\Public\SubscriptionCheckoutController::class, 'store'])->name('public.subscription.checkout.store');
Route::get('/team', [\App\Http\Controllers\Public\PageController::class, 'team'])->name('public.team');
Route::get('/certificates', [\App\Http\Controllers\Public\PageController::class, 'certificates'])->name('public.certificates');
Route::get('/certificates/verify', [\App\Http\Controllers\Public\CertificateVerificationController::class, 'verify'])->name('public.certificates.verify');
Route::get('/certificates/verify/{code}', [\App\Http\Controllers\Public\CertificateVerificationController::class, 'verify'])->name('public.certificates.verify.code');
Route::get('/help', [\App\Http\Controllers\Public\PageController::class, 'help'])->name('public.help');
Route::get('/refund', [\App\Http\Controllers\Public\PageController::class, 'refund'])->name('public.refund');
Route::get('/testimonials', [\App\Http\Controllers\Public\PageController::class, 'testimonials'])->name('public.testimonials');
Route::get('/events', [\App\Http\Controllers\Public\PageController::class, 'events'])->name('public.events');
Route::get('/partners', [\App\Http\Controllers\Public\PageController::class, 'partners'])->name('public.partners');

// Mindlytics Portfolio (معرض أعمال الطلاب)
Route::get('/portfolio', [\App\Http\Controllers\Public\PortfolioController::class, 'index'])->name('public.portfolio.index');
Route::get('/portfolio/{id}', [\App\Http\Controllers\Public\PortfolioController::class, 'show'])->name('public.portfolio.show')->where('id', '[0-9]+');

// تم إيقاف مجتمع البيانات والذكاء الاصطناعي (مسابقات، داتاسيت، مجتمع) بالكامل، لذا أزيلت جميع مساراته.

// التواصل
Route::get('/contact', [\App\Http\Controllers\Public\ContactController::class, 'index'])->name('public.contact');
Route::post('/contact', [\App\Http\Controllers\Public\ContactController::class, 'store'])->name('public.contact.store');

// معرض الصور والفيديوهات
Route::get('/media', [\App\Http\Controllers\Public\MediaController::class, 'index'])->name('public.media.index');
Route::get('/media/{media}', [\App\Http\Controllers\Public\MediaController::class, 'show'])->name('public.media.show');

// صفحة الكورسات العامة
Route::get('/courses', function () {
    $coursesQuery = \App\Models\AdvancedCourse::where('is_active', true);
    
    $coursesCollection = $coursesQuery
        ->with(['academicSubject', 'academicYear', 'instructor:id,name'])
        ->withCount('lessons')
        ->orderBy('is_featured', 'desc')
        ->orderBy('created_at', 'desc')
        ->get();

    $courses = $coursesCollection->map(function ($course) {
        return [
            'id' => $course->id,
            'title' => $course->title ?? 'بدون عنوان',
            'description' => $course->description ?? '',
            'level' => $course->level ?? 'beginner',
            'price' => (float)($course->price ?? 0),
            'duration_hours' => (int)($course->duration_hours ?? 0),
            'is_featured' => (bool)($course->is_featured ?? false),
            'is_free' => (bool)($course->is_free ?? false),
            'lessons_count' => (int)($course->lessons_count ?? 0),
            'thumbnail' => $course->thumbnail ? str_replace('\\', '/', $course->thumbnail) : null,
            'academic_subject' => $course->academicSubject ? [
                'name' => $course->academicSubject->name ?? 'غير محدد'
            ] : null,
            'instructor' => $course->instructor ? [
                'name' => $course->instructor->name
            ] : null,
        ];
    })->values()->toArray();
    
    // جلب الباقات النشطة
    $packages = \App\Models\Package::active()
        ->with(['courses' => function($query) {
            $query->where('is_active', true);
        }])
        ->withCount('courses')
        ->orderBy('is_featured', 'desc')
        ->orderBy('is_popular', 'desc')
        ->orderBy('order')
        ->get();
    
    return view('courses', compact('courses', 'packages'));
})->name('public.courses');

// صفحة المدربين (الملفات التعريفية المعتمدة)
Route::get('/instructors', [\App\Http\Controllers\Public\InstructorController::class, 'index'])->name('public.instructors.index');
Route::get('/instructors/{instructor}', [\App\Http\Controllers\Public\InstructorController::class, 'show'])->name('public.instructors.show');

// صفحة تفاصيل الكورس العامة
Route::get('/course/{id}', function ($id) {
    $course = \App\Models\AdvancedCourse::where('id', $id)
        ->where('is_active', true)
        ->with(['academicSubject', 'academicYear', 'instructor'])
        ->withCount('lessons')
        ->firstOrFail();
    
    // التحقق من التسجيل في الكورس
    $isEnrolled = false;
    if(auth()->check()) {
        $isEnrolled = \App\Models\StudentCourseEnrollment::where('user_id', auth()->id())
            ->where('advanced_course_id', $course->id)
            ->where('status', 'active')
            ->exists();
    }
    
    // كورسات ذات صلة
    $relatedCourses = \App\Models\AdvancedCourse::where('is_active', true)
        ->where('id', '!=', $course->id)
        ->where(function($query) use ($course) {
            $query->where('level', $course->level)
                  ->orWhere('academic_subject_id', $course->academic_subject_id)
                  ->orWhere('is_featured', true);
        })
        ->with(['academicSubject'])
        ->withCount('lessons')
        ->limit(3)
        ->get();
    
    return view('course-show', compact('course', 'relatedCourses', 'isEnrolled'));
})->name('public.course.show');

// صفحة إتمام الطلب (Checkout)
Route::get('/course/{courseId}/checkout', [\App\Http\Controllers\Public\CheckoutController::class, 'show'])
    ->middleware('auth')
    ->name('public.course.checkout');

Route::post('/course/{courseId}/checkout/complete', [\App\Http\Controllers\Public\CheckoutController::class, 'complete'])
    ->middleware('auth')
    ->name('public.course.checkout.complete');

// التوجيه لبوابة الدفع كاشير (كورس)
Route::post('/course/{courseId}/checkout/kashier', [\App\Http\Controllers\Public\CheckoutController::class, 'redirectToKashier'])
    ->middleware('auth')
    ->name('public.course.checkout.kashier');

// تسجيل مجاني للكورسات المجانية
Route::post('/course/{courseId}/enroll-free', [\App\Http\Controllers\Public\CheckoutController::class, 'enrollFree'])
    ->middleware('auth')
    ->name('public.course.enroll.free');

// المسارات التعليمية ملغاة — التوجيه إلى الدورات
Route::redirect('/learning-paths', '/courses', 301)->name('public.learning-paths.index');
Route::get('/learning-path/{slug}', function () { return redirect('/courses', 301); })
    ->where('slug', '[a-z0-9-]+')
    ->name('public.learning-path.show');

// المسارات التعليمية ملغاة — توجيه كل مسارات المسارات والدفع إلى الدورات
Route::get('/learning-path/{slug}/checkout', function () { return redirect('/courses', 302); })->name('public.learning-path.checkout');
Route::post('/learning-path/{slug}/checkout/complete', function () { return redirect('/courses', 302); })->name('public.learning-path.checkout.complete');
Route::post('/learning-path/{slug}/checkout/kashier', function () { return redirect('/courses', 302); })->name('public.learning-path.checkout.kashier');

Route::get('/checkout/kashier/callback', [\App\Http\Controllers\Public\CheckoutController::class, 'kashierCallback'])
    ->name('public.checkout.kashier.callback');

Route::post('/learning-path/{slug}/enroll-free', function () { return redirect('/courses', 302); })->name('public.learning-path.enroll.free');
Route::post('/learning-path/{slug}/enroll', function () { return redirect('/courses', 302); })->name('public.learning-path.enroll');

// صفحة تفاصيل الباقة (للتوافق مع الروابط القديمة)
Route::get('/package/{slug}', function ($slug) {
    $package = \App\Models\Package::where('slug', $slug)
        ->where('is_active', true)
        ->with(['courses' => function($query) {
            $query->where('is_active', true)
                  ->with(['academicSubject', 'academicYear'])
                  ->withCount('lessons');
        }])
        ->firstOrFail();
    
    // باقات ذات صلة
    $relatedPackages = \App\Models\Package::where('is_active', true)
        ->where('id', '!=', $package->id)
        ->withCount('courses')
        ->limit(3)
        ->get();
    
    return view('package-show', compact('package', 'relatedPackages'));
})->name('public.package.show');

// مسارات المصادقة - محمية بحيث لا يمكن الوصول إليها إذا كان المستخدم مسجل دخول
Route::middleware(['guest', 'guest-only'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:20,15'); // 20 طلب كل 15 دقيقة — يتضمن الدخول + إعادة المحاولة مع 2FA
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    // Rate limiting للتسجيل: 5 محاولات في الدقيقة من نفس IP
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
    // نسيت كلمة المرور: طلب رابط إعادة التعيين + صفحة تعيين كلمة مرور جديدة
    Route::get('/forgot-password', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->middleware('throttle:5,1')->name('password.update');
});

// المصادقة الثنائية (2FA) - بعد إدخال البريد وكلمة المرور للمدربين/الإدمن/الموظفين
Route::middleware(['web', 'throttle:60,5'])->group(function () {
    Route::get('/2fa/challenge', [\App\Http\Controllers\Auth\TwoFactorController::class, 'showChallenge'])->name('two-factor.challenge');
    Route::post('/2fa/verify', [\App\Http\Controllers\Auth\TwoFactorController::class, 'verifyChallenge'])->name('two-factor.verify');
});

// تسجيل الخروج - يجب أن يكون المستخدم مسجل دخول
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// إعداد المصادقة الثنائية (للإدمن / المدربين / الموظفين)
Route::middleware(['auth'])->prefix('2fa')->name('two-factor.')->group(function () {
    Route::get('/setup', [\App\Http\Controllers\Auth\TwoFactorController::class, 'showSetup'])->name('setup');
    Route::post('/enable', [\App\Http\Controllers\Auth\TwoFactorController::class, 'enable'])->name('enable');
    Route::post('/disable', [\App\Http\Controllers\Auth\TwoFactorController::class, 'disable'])->name('disable');
});

// مسارات لوحة التحكم - محمية بالتأكد من تسجيل الدخول ومنع الجلسات المتزامنة
Route::middleware(['auth', 'prevent-concurrent'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // مسارات الطلاب
    Route::get('/academic-years', [\App\Http\Controllers\Student\AcademicYearController::class, 'index'])->name('academic-years');
    Route::get('/academic-years/{academicYear}/subjects', [\App\Http\Controllers\Student\AcademicYearController::class, 'subjects'])->name('academic-years.subjects');
    Route::get('/subjects/{academicSubject}/courses', [\App\Http\Controllers\Student\SubjectController::class, 'courses'])->name('subjects.courses');
    Route::get('/courses/{advancedCourse}', [\App\Http\Controllers\Student\CourseController::class, 'show'])->name('courses.show');
    
        // كورساتي المفعلة - محمية للطلاب فقط
        Route::middleware(['role:student'])->group(function () {
            Route::get('/my-courses', [\App\Http\Controllers\Student\MyCourseController::class, 'index'])->name('my-courses.index');
            Route::get('/my-courses/{course}', [\App\Http\Controllers\Student\MyCourseController::class, 'show'])
                ->middleware(['ownership:course,course'])
                ->name('my-courses.show');
            
            // المسار التعليمي ملغى — توجيه إلى لوحة التحكم
            Route::get('/student/learning-path/{slug}', function () { return redirect()->route('dashboard', [], 302); })->name('student.learning-path.show');
        Route::get('/my-courses/{course}/learn', [\App\Http\Controllers\Student\MyCourseController::class, 'learn'])
            ->middleware(['ownership:course,course'])
            ->name('my-courses.learn');
        Route::get('/my-courses/{course}/lectures/{lecture}', [\App\Http\Controllers\Student\MyCourseController::class, 'getLectureData'])
            ->middleware(['ownership:course,course'])
            ->name('my-courses.lectures.show');
        Route::get('/my-courses/{course}/lectures/{lecture}/materials/{material}/download', [\App\Http\Controllers\Student\MyCourseController::class, 'downloadLectureMaterial'])
            ->middleware(['ownership:course,course'])
            ->name('my-courses.lectures.material.download');
        Route::post('/my-courses/{course}/lectures/{lecture}/video-questions/{videoQuestion}/answer', [\App\Http\Controllers\Student\MyCourseController::class, 'submitLectureVideoQuestionAnswer'])
            ->middleware(['ownership:course,course'])
            ->name('my-courses.lectures.video-question.answer');
        Route::post('/my-courses/{course}/lectures/{lecture}/progress', [\App\Http\Controllers\Student\MyCourseController::class, 'updateLectureProgress'])
            ->middleware(['ownership:course,course'])
            ->name('my-courses.lectures.progress');
        Route::get('/my-courses/{course}/lessons/{lesson}/watch', [\App\Http\Controllers\Student\MyCourseController::class, 'watchLesson'])
            ->middleware([\App\Http\Middleware\VideoProtectionMiddleware::class, 'ownership:course,course'])
            ->name('my-courses.lesson.watch');
        Route::post('/my-courses/{course}/lessons/{lesson}/progress', [\App\Http\Controllers\Student\MyCourseController::class, 'updateLessonProgress'])
            ->middleware(['ownership:course,course'])
            ->name('my-courses.lesson.progress');
        
    });
    
    // الإحالات
    Route::get('/referrals', [\App\Http\Controllers\Student\ReferralController::class, 'index'])->name('referrals.index');
    Route::post('/referrals/copy-link', [\App\Http\Controllers\Student\ReferralController::class, 'copyLink'])->name('referrals.copy-link');
    
    // API للتحقق من الكوبون
    Route::post('/api/validate-coupon', [\App\Http\Controllers\Student\CouponController::class, 'validateCoupon'])->name('api.validate-coupon');
    
    // API لمعلومات الفيديو
    Route::post('/api/video/info', [\App\Http\Controllers\Api\VideoInfoController::class, 'getInfo'])->name('api.video.info');
    
    // API للدروس - محمية بالتأكد من التسجيل
    Route::get('/api/lessons/{lesson}', function(\App\Models\CourseLesson $lesson) {
        $user = auth()->user();
        
        // التحقق من أن المستخدم طالب
        if (!$user->isStudent()) {
            return response()->json(['error' => 'غير مصرح'], 403);
        }
        
        // التحقق من أن المستخدم مسجل في الكورس
        if (!$user->isEnrolledIn($lesson->advanced_course_id)) {
            return response()->json(['error' => 'غير مصرح - غير مسجل في الكورس'], 403);
        }
        
        $progress = $lesson->progress()->where('user_id', $user->id)->first();
        
        return response()->json([
            'id' => $lesson->id,
            'title' => $lesson->title,
            'description' => $lesson->description,
            'content' => $lesson->content,
            'type' => $lesson->type,
            'video_url' => $lesson->video_url ? trim($lesson->video_url) : null,
            'duration_minutes' => $lesson->duration_minutes,
            'attachments' => $lesson->attachments ? json_decode($lesson->attachments, true) : null,
            'progress' => $progress ? [
                'is_completed' => (bool) $progress->is_completed,
                'progress_percent' => (int) ($progress->progress_percent ?? 0),
                'watch_time' => (int) ($progress->watch_time ?? 0),
            ] : null
        ]);
    });

    // API للطلاب المسجلين في الكورس - محمية بـ role middleware
    Route::get('/api/courses/{course}/students', function($course) {
        $instructor = auth()->user();
        
        // التحقق من أن المستخدم مدرب
        if (!$instructor->isInstructor()) {
            return response()->json(['error' => 'غير مصرح'], 403);
        }
        
        // التحقق من أن الكورس يخص المدرب
        $advancedCourse = \App\Models\AdvancedCourse::where('id', $course)
            ->where('instructor_id', $instructor->id)
            ->firstOrFail();
        
        // جلب الطلاب المسجلين في الكورس
        $enrollments = \App\Models\StudentCourseEnrollment::where('advanced_course_id', $course)
            ->where('status', 'active')
            ->with('user')
            ->get();
        
        $students = $enrollments->map(function($enrollment) {
            $user = $enrollment->user;
            return [
                'id' => $user->id,
                'name' => $user->name ?? $user->full_name ?? ($user->first_name . ' ' . $user->last_name),
                'full_name' => $user->full_name ?? ($user->first_name . ' ' . $user->last_name),
                'first_name' => $user->first_name ?? '',
                'last_name' => $user->last_name ?? '',
                'email' => $user->email,
            ];
        });
        
        return response()->json([
            'students' => $students,
            'count' => $students->count()
        ]);
    })->middleware(['auth', 'role:instructor|teacher']);

    // نظام الطلبات - محمي للطلاب فقط
    Route::middleware(['role:student'])->group(function () {
        Route::post('/courses/{advancedCourse}/order', [\App\Http\Controllers\Student\OrderController::class, 'store'])->name('courses.order');
        Route::get('/orders', [\App\Http\Controllers\Student\OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [\App\Http\Controllers\Student\OrderController::class, 'show'])
            ->middleware(['ownership:order,order'])
            ->name('orders.show');
    });
    
    // امتحانات الطلاب - محمية للطلاب فقط
    Route::prefix('exams')->name('student.exams.')->middleware(['role:student'])->group(function () {
        Route::get('/', [\App\Http\Controllers\Student\ExamController::class, 'index'])->name('index');
        Route::get('/{exam}', [\App\Http\Controllers\Student\ExamController::class, 'show'])->name('show');
        Route::post('/{exam}/start', [\App\Http\Controllers\Student\ExamController::class, 'start'])->name('start');
        Route::get('/{exam}/attempts/{attempt}/take', [\App\Http\Controllers\Student\ExamController::class, 'take'])
            ->middleware(\App\Http\Middleware\VideoProtectionMiddleware::class)
            ->name('take');
        Route::post('/{exam}/attempts/{attempt}/save-answer', [\App\Http\Controllers\Student\ExamController::class, 'saveAnswer'])->name('save-answer');
        Route::post('/{exam}/attempts/{attempt}/submit', [\App\Http\Controllers\Student\ExamController::class, 'submit'])->name('submit');
        Route::get('/{exam}/attempts/{attempt}/result', [\App\Http\Controllers\Student\ExamController::class, 'result'])->name('result');
        Route::post('/{exam}/attempts/{attempt}/tab-switch', [\App\Http\Controllers\Student\ExamController::class, 'logTabSwitch'])->name('tab-switch');
    });

    // صفحات الطلاب - محمية للطلاب فقط
    Route::middleware(['role:student'])->group(function () {
        Route::get('/profile', [\App\Http\Controllers\Student\ProfileController::class, 'index'])->name('profile');
        Route::put('/profile', [\App\Http\Controllers\Student\ProfileController::class, 'update'])->name('profile.update');
        Route::get('/settings', [\App\Http\Controllers\Student\SettingsController::class, 'index'])->name('settings');
        Route::get('/notifications', [\App\Http\Controllers\Student\NotificationController::class, 'index'])->name('notifications');
        Route::get('/notifications/{notification}/go', [\App\Http\Controllers\Student\NotificationController::class, 'go'])
            ->name('notifications.go');
        Route::get('/notifications/{notification}', [\App\Http\Controllers\Student\NotificationController::class, 'show'])
            ->middleware(['ownership:user,user'])
            ->name('notifications.show');
        Route::post('/notifications/{notification}/mark-read', [\App\Http\Controllers\Student\NotificationController::class, 'markAsRead'])
            ->middleware(['ownership:user,user'])
            ->name('notifications.mark-read');
        Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Student\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
        Route::delete('/notifications/{notification}', [\App\Http\Controllers\Student\NotificationController::class, 'destroy'])
            ->middleware(['ownership:user,user'])
            ->name('notifications.destroy');
        Route::post('/notifications/cleanup', [\App\Http\Controllers\Student\NotificationController::class, 'cleanup'])->name('notifications.cleanup');
        Route::get('/api/notifications/unread-count', [\App\Http\Controllers\Student\NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
        Route::get('/api/notifications/recent', [\App\Http\Controllers\Student\NotificationController::class, 'getRecent'])->name('notifications.recent');
        Route::get('/calendar', [\App\Http\Controllers\Student\CalendarController::class, 'index'])->name('calendar');
        Route::get('/api/calendar/events', [\App\Http\Controllers\Student\CalendarController::class, 'getEvents'])->name('calendar.events');
        // التسويق الشخصي / البورتفوليو للمعلم (عرض فقط) — تم إلغاء صفحة /my-portfolio/create
        Route::prefix('my-portfolio')->name('student.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Student\PortfolioProjectController::class, 'index'])->name('portfolio.index');
        });
        // صفحة اشتراكي (عرض الباقة الحالية ومدة التفاعيل والانتهاء)
        Route::get('/my-subscription', [\App\Http\Controllers\Student\MySubscriptionController::class, 'show'])->name('student.my-subscription');
        // صفحات المزايا المرتبطة بالاشتراك (كل ميزة لها صفحة)
        Route::get('/features/{feature}', [\App\Http\Controllers\Student\SubscriptionFeatureController::class, 'show'])
            ->name('student.features.show')
            ->where('feature', 'library_access|ai_tools|classroom_access|zoom_access|support|visible_to_academies|can_apply_opportunities|full_ai_suite|teacher_evaluation|recommended_to_academies|priority_opportunities|direct_support');
        // مكتبة المناهج التفاعلية (محمية بميزة library_access)
        Route::get('/curriculum-library', [\App\Http\Controllers\Student\CurriculumLibraryController::class, 'index'])->name('curriculum-library.index');
        Route::get('/curriculum-library/{item:slug}', [\App\Http\Controllers\Student\CurriculumLibraryController::class, 'show'])->name('curriculum-library.show');
    });

    // لوحة الموظفين
    Route::prefix('employee')->name('employee.')->middleware(['auth'])->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Employee\EmployeeController::class, 'dashboard'])->name('dashboard');
        Route::get('/tasks', [\App\Http\Controllers\Employee\EmployeeTaskController::class, 'index'])->name('tasks.index');
        Route::get('/tasks/{task}', [\App\Http\Controllers\Employee\EmployeeTaskController::class, 'show'])->name('tasks.show');
        Route::put('/tasks/{task}/status', [\App\Http\Controllers\Employee\EmployeeTaskController::class, 'updateStatus'])->name('tasks.update-status');
        Route::post('/tasks/{task}/deliverables', [\App\Http\Controllers\Employee\EmployeeTaskController::class, 'submitDeliverable'])->name('tasks.submit-deliverable');
        
        // طلبات الإجازة
        Route::resource('leaves', \App\Http\Controllers\Employee\EmployeeLeaveController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
        
        // المحاسبة والراتب
        Route::get('/accounting', [\App\Http\Controllers\Employee\AccountingController::class, 'index'])->name('accounting.index');
        Route::post('/accounting/bank-account', [\App\Http\Controllers\Employee\AccountingController::class, 'updateBankAccount'])->name('accounting.update-bank');
        
        // اتفاقيات الموظف
        Route::get('/agreements', [\App\Http\Controllers\Employee\AgreementController::class, 'index'])->name('agreements.index');
        Route::get('/agreements/{agreement}', [\App\Http\Controllers\Employee\AgreementController::class, 'show'])->name('agreements.show');
        
        // الملف الشخصي
        Route::get('/profile', [\App\Http\Controllers\Employee\EmployeeProfileController::class, 'index'])->name('profile');
        Route::put('/profile', [\App\Http\Controllers\Employee\EmployeeProfileController::class, 'update'])->name('profile.update');
        
        // الإشعارات
        Route::get('/notifications', [\App\Http\Controllers\Employee\EmployeeNotificationController::class, 'index'])->name('notifications');
        Route::get('/notifications/{notification}/go', [\App\Http\Controllers\Employee\EmployeeNotificationController::class, 'go'])->name('notifications.go');
        Route::get('/notifications/{notification}', [\App\Http\Controllers\Employee\EmployeeNotificationController::class, 'show'])->name('notifications.show');
        Route::post('/notifications/{notification}/mark-read', [\App\Http\Controllers\Employee\EmployeeNotificationController::class, 'markAsRead'])->name('notifications.mark-read');
        Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Employee\EmployeeNotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
        
        // التقويم
        Route::get('/calendar', [\App\Http\Controllers\Employee\EmployeeCalendarController::class, 'index'])->name('calendar');
        Route::get('/api/calendar/events', [\App\Http\Controllers\Employee\EmployeeCalendarController::class, 'getEvents'])->name('calendar.events');
        
        // التقارير والإحصائيات
        Route::get('/reports', [\App\Http\Controllers\Employee\EmployeeReportController::class, 'index'])->name('reports');
        
        // الإعدادات
        Route::get('/settings', [\App\Http\Controllers\Employee\EmployeeSettingsController::class, 'index'])->name('settings');
        Route::put('/settings', [\App\Http\Controllers\Employee\EmployeeSettingsController::class, 'update'])->name('settings.update');
        
        // API للإشعارات
        Route::get('/api/notifications/unread', [\App\Http\Controllers\Employee\EmployeeNotificationController::class, 'getUnread'])->name('notifications.unread');
        Route::post('/api/notifications/{notification}/mark-read', [\App\Http\Controllers\Employee\EmployeeNotificationController::class, 'markAsRead'])->name('notifications.api.mark-read');
    });

    // مسارات الإدارة - محمية بالـ role middleware للإداريين فقط
    Route::prefix('admin')->name('admin.')->middleware(['role:admin|super_admin'])->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Admin\AdminController::class, 'dashboard'])->name('dashboard');

        // بروفايل الأدمن
        Route::get('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'index'])->name('profile');
        Route::put('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');

        // إدارة المستخدمين
        Route::get('/users', [\App\Http\Controllers\Admin\AdminController::class, 'users'])->name('users.index');
        Route::get('/users/create', [\App\Http\Controllers\Admin\AdminController::class, 'createUser'])->name('users.create');
        Route::post('/users', [\App\Http\Controllers\Admin\AdminController::class, 'storeUser'])
            ->middleware('throttle:20,1')
            ->name('users.store');
        Route::get('/users/{id}', [\App\Http\Controllers\Admin\AdminController::class, 'showUser'])->name('users.show')->where('id', '[0-9]+');
        Route::get('/users/{id}/edit', [\App\Http\Controllers\Admin\AdminController::class, 'editUser'])->name('users.edit')->where('id', '[0-9]+');
        Route::put('/users/{id}', [\App\Http\Controllers\Admin\AdminController::class, 'updateUser'])->name('users.update')->where('id', '[0-9]+');
        Route::delete('/users/{id}', [\App\Http\Controllers\Admin\AdminController::class, 'deleteUser'])->name('users.delete')->where('id', '[0-9]+');
        
        // السنوات الدراسية ملغاة — التوجيه إلى إدارة الكورسات
        Route::get('/academic-years', function () { return redirect()->route('admin.advanced-courses.index', [], 302); })->name('academic-years.index');
        Route::get('/academic-years/create', function () { return redirect()->route('admin.advanced-courses.index', [], 302); })->name('academic-years.create');
        Route::get('/academic-years/{academicYear}', function () { return redirect()->route('admin.advanced-courses.index', [], 302); })->name('academic-years.show');
        Route::get('/academic-years/{academicYear}/edit', function () { return redirect()->route('admin.advanced-courses.index', [], 302); })->name('academic-years.edit');
        Route::post('/academic-years', function () { return redirect()->route('admin.advanced-courses.index', [], 302); })->name('academic-years.store');
        Route::put('/academic-years/{academicYear}', function () { return redirect()->route('admin.advanced-courses.index', [], 302); })->name('academic-years.update');
        Route::delete('/academic-years/{academicYear}', function () { return redirect()->route('admin.advanced-courses.index', [], 302); })->name('academic-years.destroy');
        Route::post('/academic-years/{academicYear}/toggle-status', function () { return redirect()->route('admin.advanced-courses.index', [], 302); })->name('academic-years.toggle-status');
        Route::post('/academic-years/reorder', function () { return redirect()->route('admin.advanced-courses.index', [], 302); })->name('academic-years.reorder');
        Route::post('/academic-years/{academicYear}/add-course', function () { return redirect()->route('admin.advanced-courses.index', [], 302); })->name('academic-years.add-course');
        Route::delete('/academic-years/{academicYear}/remove-course/{course}', function () { return redirect()->route('admin.advanced-courses.index', [], 302); })->name('academic-years.remove-course');
        Route::post('/academic-years/{academicYear}/add-instructor', function () { return redirect()->route('admin.advanced-courses.index', [], 302); })->name('academic-years.add-instructor');
        Route::delete('/academic-years/{academicYear}/remove-instructor/{instructor}', function () { return redirect()->route('admin.advanced-courses.index', [], 302); })->name('academic-years.remove-instructor');

        // المسارات التعليمية ومجموعات المهارات ملغاة — التوجيه إلى الكورسات
        Route::get('/learning-paths/courses', function () { return redirect()->route('admin.advanced-courses.index', [], 302); })->name('learning-paths.courses.index');
        Route::get('/learning-paths/instructors', function () { return redirect()->route('admin.advanced-courses.index', [], 302); })->name('learning-paths.instructors.index');
        Route::get('/learning-paths/{any?}', function () { return redirect()->route('admin.advanced-courses.index', [], 302); })->where('any', '.*');
        Route::get('/academic-subjects', function () { return redirect()->route('admin.advanced-courses.index', [], 302); })->name('academic-subjects.index');
        Route::get('/academic-subjects/create', function () { return redirect()->route('admin.advanced-courses.index', [], 302); })->name('academic-subjects.create');
        Route::get('/academic-subjects/{academicSubject}', function () { return redirect()->route('admin.advanced-courses.index', [], 302); })->name('academic-subjects.show');
        Route::get('/academic-subjects/{academicSubject}/edit', function () { return redirect()->route('admin.advanced-courses.index', [], 302); })->name('academic-subjects.edit');
        Route::post('/academic-subjects', function () { return redirect()->route('admin.advanced-courses.index', [], 302); })->name('academic-subjects.store');
        Route::put('/academic-subjects/{academicSubject}', function () { return redirect()->route('admin.advanced-courses.index', [], 302); })->name('academic-subjects.update');
        Route::delete('/academic-subjects/{academicSubject}', function () { return redirect()->route('admin.advanced-courses.index', [], 302); })->name('academic-subjects.destroy');
        Route::post('/academic-subjects/{academicSubject}/toggle-status', function () { return redirect()->route('admin.advanced-courses.index', [], 302); })->name('academic-subjects.toggle-status');
        Route::post('/academic-subjects/reorder', function () { return redirect()->route('admin.advanced-courses.index', [], 302); })->name('academic-subjects.reorder');

        // إدارة الكورسات المتطورة
        Route::resource('advanced-courses', \App\Http\Controllers\Admin\AdvancedCourseController::class);
        Route::post('/advanced-courses/{advancedCourse}/activate-student', [\App\Http\Controllers\Admin\AdvancedCourseController::class, 'activateStudent'])->name('advanced-courses.activate-student');
        Route::get('/advanced-courses/{advancedCourse}/students', [\App\Http\Controllers\Admin\AdvancedCourseController::class, 'students'])->name('advanced-courses.students');
        Route::post('/advanced-courses/{advancedCourse}/toggle-status', [\App\Http\Controllers\Admin\AdvancedCourseController::class, 'toggleStatus'])->name('advanced-courses.toggle-status');
        Route::post('/advanced-courses/{advancedCourse}/toggle-featured', [\App\Http\Controllers\Admin\AdvancedCourseController::class, 'toggleFeatured'])->name('advanced-courses.toggle-featured');
        Route::get('/advanced-courses/{advancedCourse}/orders', [\App\Http\Controllers\Admin\AdvancedCourseController::class, 'orders'])->name('advanced-courses.orders');
        Route::get('/advanced-courses/{advancedCourse}/statistics', [\App\Http\Controllers\Admin\AdvancedCourseController::class, 'statistics'])->name('advanced-courses.statistics');
        Route::post('/advanced-courses/{advancedCourse}/duplicate', [\App\Http\Controllers\Admin\AdvancedCourseController::class, 'duplicate'])->name('advanced-courses.duplicate');
        Route::get('/get-subjects-by-year', [\App\Http\Controllers\Admin\AdvancedCourseController::class, 'getSubjectsByYear'])->name('advanced-courses.get-subjects-by-year');
        Route::get('/courses/{course}/lessons-list', function(\App\Models\AdvancedCourse $course) {
            return response()->json($course->lessons()->active()->select('id', 'title')->get());
        });

        // إدارة دروس الكورسات
        Route::prefix('courses/{course}/lessons')->name('courses.lessons.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\CourseLessonController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\CourseLessonController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\CourseLessonController::class, 'store'])->name('store');
            Route::get('/{lesson}', [\App\Http\Controllers\Admin\CourseLessonController::class, 'show'])->name('show');
            Route::get('/{lesson}/edit', [\App\Http\Controllers\Admin\CourseLessonController::class, 'edit'])->name('edit');
            Route::put('/{lesson}', [\App\Http\Controllers\Admin\CourseLessonController::class, 'update'])->name('update');
            Route::delete('/{lesson}', [\App\Http\Controllers\Admin\CourseLessonController::class, 'destroy'])->name('destroy');
            Route::post('/{lesson}/toggle-status', [\App\Http\Controllers\Admin\CourseLessonController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/reorder', [\App\Http\Controllers\Admin\CourseLessonController::class, 'reorder'])->name('reorder');
        });

        // إدارة بنك الأسئلة
        Route::prefix('question-bank')->name('question-bank.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\QuestionBankController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\QuestionBankController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\QuestionBankController::class, 'store'])->name('store');
            Route::get('/{question}', [\App\Http\Controllers\Admin\QuestionBankController::class, 'show'])->name('show');
            Route::get('/{question}/edit', [\App\Http\Controllers\Admin\QuestionBankController::class, 'edit'])->name('edit');
            Route::put('/{question}', [\App\Http\Controllers\Admin\QuestionBankController::class, 'update'])->name('update');
            Route::delete('/{question}', [\App\Http\Controllers\Admin\QuestionBankController::class, 'destroy'])->name('destroy');
            Route::post('/{question}/duplicate', [\App\Http\Controllers\Admin\QuestionBankController::class, 'duplicate'])->name('duplicate');
            Route::post('/export', [\App\Http\Controllers\Admin\QuestionBankController::class, 'export'])->name('export');
            Route::post('/import', [\App\Http\Controllers\Admin\QuestionBankController::class, 'import'])->name('import');
        });

        // إدارة تصنيفات الأسئلة
        Route::prefix('question-categories')->name('question-categories.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\QuestionCategoryController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\QuestionCategoryController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\QuestionCategoryController::class, 'store'])->name('store');
            Route::get('/{questionCategory}', [\App\Http\Controllers\Admin\QuestionCategoryController::class, 'show'])->name('show');
            Route::get('/{questionCategory}/edit', [\App\Http\Controllers\Admin\QuestionCategoryController::class, 'edit'])->name('edit');
            Route::put('/{questionCategory}', [\App\Http\Controllers\Admin\QuestionCategoryController::class, 'update'])->name('update');
            Route::delete('/{questionCategory}', [\App\Http\Controllers\Admin\QuestionCategoryController::class, 'destroy'])->name('destroy');
            Route::post('/reorder', [\App\Http\Controllers\Admin\QuestionCategoryController::class, 'reorder'])->name('reorder');
            Route::get('/subjects-by-year/{year}', [\App\Http\Controllers\Admin\QuestionCategoryController::class, 'getSubjectsByYear'])->name('subjects-by-year');
        });

        // إدارة الامتحانات (مسار الكورس قبل المسارات الأخرى لتفادي التعارض)
        Route::prefix('exams')->name('exams.')->group(function () {
            Route::get('/course/{course}', [\App\Http\Controllers\Admin\ExamController::class, 'indexByCourse'])->name('by-course');
            Route::get('/', [\App\Http\Controllers\Admin\ExamController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\ExamController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\ExamController::class, 'store'])->name('store');
            Route::get('/{exam}', [\App\Http\Controllers\Admin\ExamController::class, 'show'])->name('show');
            Route::get('/{exam}/edit', [\App\Http\Controllers\Admin\ExamController::class, 'edit'])->name('edit');
            Route::put('/{exam}', [\App\Http\Controllers\Admin\ExamController::class, 'update'])->name('update');
            Route::delete('/{exam}', [\App\Http\Controllers\Admin\ExamController::class, 'destroy'])->name('destroy');
            Route::get('/{exam}/questions', [\App\Http\Controllers\Admin\ExamController::class, 'manageQuestions'])->name('questions.manage');
            Route::post('/{exam}/questions', [\App\Http\Controllers\Admin\ExamController::class, 'addQuestion'])->name('questions.add');
            Route::delete('/{exam}/questions/{examQuestion}', [\App\Http\Controllers\Admin\ExamController::class, 'removeQuestion'])->name('questions.remove');
            Route::post('/{exam}/questions/reorder', [\App\Http\Controllers\Admin\ExamController::class, 'reorderQuestions'])->name('questions.reorder');
            Route::post('/{exam}/toggle-publish', [\App\Http\Controllers\Admin\ExamController::class, 'togglePublish'])->name('toggle-publish');
            Route::post('/{exam}/toggle-status', [\App\Http\Controllers\Admin\ExamController::class, 'toggleStatus'])->name('toggle-status');
            Route::get('/{exam}/statistics', [\App\Http\Controllers\Admin\ExamController::class, 'statistics'])->name('statistics');
            Route::get('/{exam}/preview', [\App\Http\Controllers\Admin\ExamController::class, 'preview'])->name('preview');
            Route::post('/{exam}/duplicate', [\App\Http\Controllers\Admin\ExamController::class, 'duplicate'])->name('duplicate');
        });

        // إدارة المواد الدراسية القديمة
        Route::resource('subjects', \App\Http\Controllers\Admin\SubjectController::class);

        // إدارة الكورسات القديمة
        Route::resource('courses', \App\Http\Controllers\Admin\CourseController::class);

        // سجل النشاطات
        Route::get('/activity-log', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('activity-log');
        Route::get('/activity-log/{activityLog}', [\App\Http\Controllers\Admin\ActivityLogController::class, 'show'])->name('activity-log.show');
        Route::post('/activity-log/clear', [\App\Http\Controllers\Admin\ActivityLogController::class, 'destroy'])->name('activity-log.destroy');

        // سجلات التحقق الثنائي (2FA)
        Route::get('/two-factor-logs', [\App\Http\Controllers\Admin\TwoFactorLogController::class, 'index'])->name('two-factor-logs.index');

        // الإحصائيات
        Route::get('/statistics', [\App\Http\Controllers\Admin\StatisticsController::class, 'index'])->name('statistics.index');
        Route::get('/statistics/users', [\App\Http\Controllers\Admin\StatisticsController::class, 'users'])->name('statistics.users');
        Route::get('/statistics/courses', [\App\Http\Controllers\Admin\StatisticsController::class, 'courses'])->name('statistics.courses');

        // إدارة الطلبات
        Route::get('/orders', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [\App\Http\Controllers\Admin\OrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/approve', [\App\Http\Controllers\Admin\OrderController::class, 'approve'])
            ->middleware('throttle:10,1')
            ->name('orders.approve');
        Route::post('/orders/{order}/reject', [\App\Http\Controllers\Admin\OrderController::class, 'reject'])
            ->middleware('throttle:10,1')
            ->name('orders.reject');

        // إدارة الصلاحيات والأدوار
        Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);
        Route::resource('permissions', \App\Http\Controllers\Admin\PermissionController::class);
        Route::post('/roles/{role}/permissions', [\App\Http\Controllers\Admin\RoleController::class, 'updatePermissions'])->name('roles.update-permissions');
        
        // إدارة صلاحيات المستخدمين
        Route::get('/user-permissions', [\App\Http\Controllers\Admin\UserPermissionController::class, 'index'])->name('user-permissions.index');
        Route::get('/user-permissions/{user}', [\App\Http\Controllers\Admin\UserPermissionController::class, 'show'])->name('user-permissions.show');
        Route::put('/user-permissions/{user}', [\App\Http\Controllers\Admin\UserPermissionController::class, 'update'])->name('user-permissions.update');
        Route::post('/user-permissions/{user}/attach', [\App\Http\Controllers\Admin\UserPermissionController::class, 'attachPermission'])->name('user-permissions.attach');
        Route::post('/user-permissions/{user}/detach', [\App\Http\Controllers\Admin\UserPermissionController::class, 'detachPermission'])->name('user-permissions.detach');

        // إدارة المحافظ الذكية
        Route::resource('wallets', \App\Http\Controllers\Admin\WalletController::class);
        Route::get('/wallets/{wallet}/transactions', [\App\Http\Controllers\Admin\WalletController::class, 'transactions'])->name('wallets.transactions');
        Route::get('/wallets/{wallet}/reports', [\App\Http\Controllers\Admin\WalletController::class, 'reports'])->name('wallets.reports');
        Route::post('/wallets/{wallet}/generate-report', [\App\Http\Controllers\Admin\WalletController::class, 'generateReport'])->name('wallets.generate-report');

        // إدارة المحاضرات والجروبات
        Route::resource('lectures', \App\Http\Controllers\Admin\LectureController::class);
        Route::post('/lectures/{lecture}/sync-teams-attendance', [\App\Http\Controllers\Admin\LectureController::class, 'syncTeamsAttendance'])->name('lectures.sync-teams-attendance');
        // إدارة الواجبات والمشاريع (مسار الكورس قبل المسارات الأخرى لتفادي التعارض)
        Route::get('/assignments/course/{course}', [\App\Http\Controllers\Admin\AssignmentController::class, 'indexByCourse'])->name('assignments.by-course');
        Route::resource('assignments', \App\Http\Controllers\Admin\AssignmentController::class);
        Route::get('/assignments/{assignment}/submissions', [\App\Http\Controllers\Admin\AssignmentController::class, 'submissions'])->name('assignments.submissions');
        Route::post('/assignments/{assignment}/grade/{submission}', [\App\Http\Controllers\Admin\AssignmentController::class, 'grade'])->name('assignments.grade');

        // إدارة المهام
        Route::resource('tasks', \App\Http\Controllers\Admin\TaskController::class);
        Route::post('/tasks/{task}/complete', [\App\Http\Controllers\Admin\TaskController::class, 'complete'])->name('tasks.complete');
        Route::post('/tasks/{task}/comments', [\App\Http\Controllers\Admin\TaskController::class, 'addComment'])->name('tasks.add-comment');
        Route::post('/tasks/{task}/deliverables/{deliverable}/review', [\App\Http\Controllers\Admin\TaskController::class, 'reviewDeliverable'])->name('tasks.review-deliverable');

        // البورتفوليو - الرقابة والجودة (الأدمن يرى الكل ويمكنه إخفاء مشروع)
        Route::get('portfolio', [\App\Http\Controllers\Admin\PortfolioController::class, 'index'])->name('portfolio.index');
        Route::get('portfolio/{project}', [\App\Http\Controllers\Admin\PortfolioController::class, 'show'])->name('portfolio.show');
        Route::post('portfolio/{project}/approve', [\App\Http\Controllers\Admin\PortfolioController::class, 'approve'])->name('portfolio.approve');
        Route::post('portfolio/{project}/reject', [\App\Http\Controllers\Admin\PortfolioController::class, 'reject'])->name('portfolio.reject');
        Route::post('portfolio/{project}/publish', [\App\Http\Controllers\Admin\PortfolioController::class, 'publish'])->name('portfolio.publish');
        Route::post('portfolio/{project}/toggle-visibility', [\App\Http\Controllers\Admin\PortfolioController::class, 'toggleVisibility'])->name('portfolio.toggle-visibility');

        // تم إيقاف مجتمع البيانات والذكاء الاصطناعي في لوحة الإدارة، لذا أزيلت جميع مساراته.

        // الإدارة العليا (من نحن وغيرها)
        Route::get('about', [\App\Http\Controllers\Admin\AboutPageController::class, 'index'])->name('about.index');
        Route::get('about/view', [\App\Http\Controllers\Admin\AboutPageController::class, 'viewPublic'])->name('about.view-public');

        Route::resource('contact-messages', \App\Http\Controllers\Admin\ContactMessageController::class);
        Route::post('/contact-messages/{contactMessage}/mark-as-read', [\App\Http\Controllers\Admin\ContactMessageController::class, 'markAsRead'])->name('contact-messages.mark-as-read');
        Route::post('/contact-messages/{contactMessage}/mark-as-unread', [\App\Http\Controllers\Admin\ContactMessageController::class, 'markAsUnread'])->name('contact-messages.mark-as-unread');
        
        // إدارة الأسعار والباقات
        Route::resource('packages', \App\Http\Controllers\Admin\PackageController::class);
        Route::post('/packages/{course}/update-price', [\App\Http\Controllers\Admin\PackageController::class, 'updatePrice'])->name('packages.update-price');
        Route::post('/packages/bulk-update', [\App\Http\Controllers\Admin\PackageController::class, 'updateBulkPrices'])->name('packages.bulk-update');

        // إدارة الإشعارات
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\NotificationController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\NotificationController::class, 'store'])
                ->middleware('throttle:20,5')
                ->name('store');
            Route::get('/{notification}', [\App\Http\Controllers\Admin\NotificationController::class, 'show'])->name('show');
            Route::delete('/{notification}', [\App\Http\Controllers\Admin\NotificationController::class, 'destroy'])
                ->middleware('throttle:30,1')
                ->name('destroy');
            Route::post('/quick-send', [\App\Http\Controllers\Admin\NotificationController::class, 'quickSend'])
                ->middleware('throttle:30,5')
                ->name('quick-send');
            Route::get('/target-count', [\App\Http\Controllers\Admin\NotificationController::class, 'getTargetCount'])
                ->middleware('throttle:60,1')
                ->name('target-count');
            Route::post('/mark-all-read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAllAsRead'])
                ->middleware('throttle:10,1')
                ->name('mark-all-read');
            Route::post('/cleanup', [\App\Http\Controllers\Admin\NotificationController::class, 'cleanup'])
                ->middleware('throttle:5,10')
                ->name('cleanup');
            Route::get('/statistics', [\App\Http\Controllers\Admin\NotificationController::class, 'statistics'])->name('statistics');
        });

        // إشعارات الموظفين
        Route::prefix('employee-notifications')->name('employee-notifications.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\EmployeeNotificationController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\EmployeeNotificationController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\EmployeeNotificationController::class, 'store'])
                ->middleware('throttle:10,1')
                ->name('store');
            Route::get('/{notification}', [\App\Http\Controllers\Admin\EmployeeNotificationController::class, 'show'])->name('show');
        });

        // إدارة تسجيل الطلاب في الكورسات الأونلاين
        Route::prefix('online-enrollments')->name('online-enrollments.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\StudentEnrollmentController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\StudentEnrollmentController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\StudentEnrollmentController::class, 'store'])->name('store');
            Route::post('/quick-activate', [\App\Http\Controllers\Admin\StudentEnrollmentController::class, 'quickActivate'])->name('quick-activate');
            Route::get('/{enrollment}', [\App\Http\Controllers\Admin\StudentEnrollmentController::class, 'show'])->name('show');
            Route::post('/{enrollment}/activate', [\App\Http\Controllers\Admin\StudentEnrollmentController::class, 'activate'])->name('activate');
            Route::post('/{enrollment}/deactivate', [\App\Http\Controllers\Admin\StudentEnrollmentController::class, 'deactivate'])->name('deactivate');
            Route::post('/{enrollment}/update-progress', [\App\Http\Controllers\Admin\StudentEnrollmentController::class, 'updateProgress'])->name('update-progress');
            Route::post('/{enrollment}/update-notes', [\App\Http\Controllers\Admin\StudentEnrollmentController::class, 'updateNotes'])->name('update-notes');
            Route::delete('/{enrollment}', [\App\Http\Controllers\Admin\StudentEnrollmentController::class, 'destroy'])->name('destroy');
            Route::get('/search/by-phone', [\App\Http\Controllers\Admin\StudentEnrollmentController::class, 'searchStudentByPhone'])->name('search-by-phone');
        });

        // إدارة مصادر الفيديو (Bunny وغيرها)
        Route::prefix('video-providers')->name('video-providers.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\VideoProviderController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Admin\VideoProviderController::class, 'store'])->name('store');
            Route::put('/{videoProvider}', [\App\Http\Controllers\Admin\VideoProviderController::class, 'update'])->name('update');
        });

        // تسجيل المسارات التعليمية ملغى — التوجيه إلى التسجيلات أونلاين
        Route::get('/learning-path-enrollments', function () { return redirect()->route('admin.online-enrollments.index', [], 302); })->name('learning-path-enrollments.index');
        Route::get('/learning-path-enrollments/create', function () { return redirect()->route('admin.online-enrollments.index', [], 302); })->name('learning-path-enrollments.create');
        Route::post('/learning-path-enrollments', function () { return redirect()->route('admin.online-enrollments.index', [], 302); })->name('learning-path-enrollments.store');
        Route::post('/learning-path-enrollments/{enrollment}/toggle-status', function () { return redirect()->route('admin.online-enrollments.index', [], 302); })->name('learning-path-enrollments.toggle-status');
        Route::delete('/learning-path-enrollments/{enrollment}', function () { return redirect()->route('admin.online-enrollments.index', [], 302); })->name('learning-path-enrollments.destroy');

        // إدارة الموظفين
        Route::resource('employees', \App\Http\Controllers\Admin\EmployeeController::class);
        Route::resource('employee-jobs', \App\Http\Controllers\Admin\EmployeeJobController::class);
        Route::resource('employee-tasks', \App\Http\Controllers\Admin\EmployeeTaskController::class);
        
        // إدارة الإجازات
        Route::get('/leaves', [\App\Http\Controllers\Admin\AdminLeaveController::class, 'index'])->name('leaves.index');
        Route::get('/leaves/{leave}', [\App\Http\Controllers\Admin\AdminLeaveController::class, 'show'])->name('leaves.show');
        Route::post('/leaves/{leave}/approve', [\App\Http\Controllers\Admin\AdminLeaveController::class, 'approve'])->name('leaves.approve');
        Route::post('/leaves/{leave}/reject', [\App\Http\Controllers\Admin\AdminLeaveController::class, 'reject'])->name('leaves.reject');

        // طلبات المدربين للإدارة
        Route::prefix('instructor-requests')->name('instructor-requests.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\InstructorRequestController::class, 'index'])->name('index');
            Route::get('/{instructorRequest}', [\App\Http\Controllers\Admin\InstructorRequestController::class, 'show'])->name('show');
            Route::post('/{instructorRequest}/respond', [\App\Http\Controllers\Admin\InstructorRequestController::class, 'respond'])->name('respond');
        });

        // الرقابة والجودة
        Route::prefix('quality-control')->name('quality-control.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\QualityControlController::class, 'index'])->name('index');
            Route::get('/students', [\App\Http\Controllers\Admin\QualityControlController::class, 'students'])->name('students');
            Route::get('/instructors', [\App\Http\Controllers\Admin\QualityControlController::class, 'instructors'])->name('instructors');
            Route::get('/instructors/{instructor}', [\App\Http\Controllers\Admin\QualityControlController::class, 'instructorShow'])->name('instructors.show');
            Route::get('/instructors/{instructor}/export', [\App\Http\Controllers\Admin\QualityControlController::class, 'instructorExport'])->name('instructors.export');
            Route::get('/employees', [\App\Http\Controllers\Admin\QualityControlController::class, 'employees'])->name('employees');
            Route::get('/operations', [\App\Http\Controllers\Admin\QualityControlController::class, 'operations'])->name('operations');
        });

        // إدارة الرسائل والتقارير
        Route::prefix('messages')->name('messages.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\MessagesController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\MessagesController::class, 'create'])->name('create');
            Route::post('/send-single', [\App\Http\Controllers\Admin\MessagesController::class, 'sendSingle'])->name('send-single');
            Route::post('/send-bulk', [\App\Http\Controllers\Admin\MessagesController::class, 'sendBulk'])->name('send-bulk');
            Route::get('/{message}', [\App\Http\Controllers\Admin\MessagesController::class, 'show'])->name('show');
            Route::post('/{message}/resend', [\App\Http\Controllers\Admin\MessagesController::class, 'resend'])->name('resend');
            Route::delete('/{message}', [\App\Http\Controllers\Admin\MessagesController::class, 'destroy'])->name('destroy');
            
            // التقارير الشهرية
            Route::get('/monthly-reports', [\App\Http\Controllers\Admin\MessagesController::class, 'monthlyReports'])->name('monthly-reports');
            Route::post('/generate-monthly-reports', [\App\Http\Controllers\Admin\MessagesController::class, 'generateMonthlyReports'])->name('generate-monthly-reports');
            
            // قوالب الرسائل
            Route::get('/templates', [\App\Http\Controllers\Admin\MessagesController::class, 'templates'])->name('templates');
            Route::post('/templates', [\App\Http\Controllers\Admin\MessagesController::class, 'storeTemplate'])->name('templates.store');
            Route::delete('/templates/{template}', [\App\Http\Controllers\Admin\MessagesController::class, 'destroyTemplate'])->name('templates.destroy');
            
            // إعدادات WhatsApp API
            Route::get('/settings', [\App\Http\Controllers\Admin\WhatsAppSettingsController::class, 'settings'])->name('settings');
            Route::post('/save-api-settings', [\App\Http\Controllers\Admin\WhatsAppSettingsController::class, 'saveApiSettings'])->name('save-api-settings');
            Route::post('/test-api', [\App\Http\Controllers\Admin\WhatsAppSettingsController::class, 'testApi'])->name('test-api');
        });

        // إدارة المحاسبة
        Route::resource('invoices', \App\Http\Controllers\Admin\InvoiceController::class)
            ->middleware('throttle:60,1')
            ->except(['update', 'destroy']);
        Route::post('/invoices/{invoice}', [\App\Http\Controllers\Admin\InvoiceController::class, 'update'])->middleware('throttle:20,5')->name('invoices.update');
        Route::delete('/invoices/{invoice}', [\App\Http\Controllers\Admin\InvoiceController::class, 'destroy'])->middleware('throttle:10,1')->name('invoices.destroy');
        
        Route::resource('payments', \App\Http\Controllers\Admin\PaymentController::class)
            ->middleware('throttle:60,1')
            ->except(['update', 'destroy']);
        Route::post('/payments/{payment}', [\App\Http\Controllers\Admin\PaymentController::class, 'update'])->middleware('throttle:20,5')->name('payments.update');
        Route::delete('/payments/{payment}', [\App\Http\Controllers\Admin\PaymentController::class, 'destroy'])->middleware('throttle:10,1')->name('payments.destroy');
        
        Route::resource('transactions', \App\Http\Controllers\Admin\TransactionController::class)
            ->middleware('throttle:60,1')
            ->except(['update', 'destroy']);
        Route::post('/transactions/{transaction}', [\App\Http\Controllers\Admin\TransactionController::class, 'update'])->middleware('throttle:20,5')->name('transactions.update');
        Route::delete('/transactions/{transaction}', [\App\Http\Controllers\Admin\TransactionController::class, 'destroy'])->middleware('throttle:10,1')->name('transactions.destroy');
        
        Route::resource('wallets', \App\Http\Controllers\Admin\WalletController::class)
            ->middleware('throttle:60,1')
            ->except(['update', 'destroy']);
        Route::post('/wallets/{wallet}', [\App\Http\Controllers\Admin\WalletController::class, 'update'])->middleware('throttle:20,5')->name('wallets.update');
        Route::delete('/wallets/{wallet}', [\App\Http\Controllers\Admin\WalletController::class, 'destroy'])->middleware('throttle:10,1')->name('wallets.destroy');
        
        Route::resource('expenses', \App\Http\Controllers\Admin\ExpenseController::class)->except(['destroy']);
        Route::post('/expenses/{expense}/approve', [\App\Http\Controllers\Admin\ExpenseController::class, 'approve'])->middleware('throttle:10,1')->name('expenses.approve');
        Route::post('/expenses/{expense}/reject', [\App\Http\Controllers\Admin\ExpenseController::class, 'reject'])->middleware('throttle:10,1')->name('expenses.reject');
        Route::post('/expenses/{expense}', [\App\Http\Controllers\Admin\ExpenseController::class, 'update'])->middleware('throttle:20,5')->name('expenses.update');
        Route::delete('/expenses/{expense}', [\App\Http\Controllers\Admin\ExpenseController::class, 'destroy'])->middleware('throttle:10,1')->name('expenses.destroy');
        
        Route::resource('subscriptions', \App\Http\Controllers\Admin\SubscriptionController::class)
            ->middleware('throttle:60,1');
        Route::get('/teacher-features', [\App\Http\Controllers\Admin\TeacherFeaturesController::class, 'index'])
            ->name('teacher-features.index');
        Route::post('/teacher-features', [\App\Http\Controllers\Admin\TeacherFeaturesController::class, 'update'])
            ->name('teacher-features.update');
        // مكتبة المناهج التفاعلية (إدارة)
        Route::get('/curriculum-library', [\App\Http\Controllers\Admin\CurriculumLibraryController::class, 'index'])->name('curriculum-library.index');
        Route::get('/curriculum-library/categories', [\App\Http\Controllers\Admin\CurriculumLibraryController::class, 'categories'])->name('curriculum-library.categories');
        Route::get('/curriculum-library/categories/create', [\App\Http\Controllers\Admin\CurriculumLibraryController::class, 'createCategory'])->name('curriculum-library.categories.create');
        Route::post('/curriculum-library/categories', [\App\Http\Controllers\Admin\CurriculumLibraryController::class, 'storeCategory'])->name('curriculum-library.categories.store');
        Route::get('/curriculum-library/categories/{category}/edit', [\App\Http\Controllers\Admin\CurriculumLibraryController::class, 'editCategory'])->name('curriculum-library.categories.edit');
        Route::put('/curriculum-library/categories/{category}', [\App\Http\Controllers\Admin\CurriculumLibraryController::class, 'updateCategory'])->name('curriculum-library.categories.update');
        Route::delete('/curriculum-library/categories/{category}', [\App\Http\Controllers\Admin\CurriculumLibraryController::class, 'destroyCategory'])->name('curriculum-library.categories.destroy');
        Route::get('/curriculum-library/items/create', [\App\Http\Controllers\Admin\CurriculumLibraryController::class, 'createItem'])->name('curriculum-library.items.create');
        Route::post('/curriculum-library/items', [\App\Http\Controllers\Admin\CurriculumLibraryController::class, 'storeItem'])->name('curriculum-library.items.store');
        Route::get('/curriculum-library/items/{item}/edit', [\App\Http\Controllers\Admin\CurriculumLibraryController::class, 'editItem'])->name('curriculum-library.items.edit');
        Route::put('/curriculum-library/items/{item}', [\App\Http\Controllers\Admin\CurriculumLibraryController::class, 'updateItem'])->name('curriculum-library.items.update');
        Route::delete('/curriculum-library/items/{item}', [\App\Http\Controllers\Admin\CurriculumLibraryController::class, 'destroyItem'])->name('curriculum-library.items.destroy');
        Route::post('/subscriptions/{subscription}', [\App\Http\Controllers\Admin\SubscriptionController::class, 'update'])->middleware('throttle:20,5')->name('subscriptions.update');
        Route::delete('/subscriptions/{subscription}', [\App\Http\Controllers\Admin\SubscriptionController::class, 'destroy'])->middleware('throttle:10,1')->name('subscriptions.destroy');
        Route::post('/subscription-requests/{subscriptionRequest}/approve', [\App\Http\Controllers\Admin\SubscriptionController::class, 'approveRequest'])->name('subscription-requests.approve');
        Route::post('/subscription-requests/{subscriptionRequest}/reject', [\App\Http\Controllers\Admin\SubscriptionController::class, 'rejectRequest'])->name('subscription-requests.reject');
        Route::get('/accounting/instructor-accounts', [\App\Http\Controllers\Admin\InstructorAccountController::class, 'index'])->name('accounting.instructor-accounts.index');
        Route::get('/accounting/instructor-accounts/{instructor}', [\App\Http\Controllers\Admin\InstructorAccountController::class, 'show'])->name('accounting.instructor-accounts.show');

        Route::get('/accounting/reports', [\App\Http\Controllers\Admin\AccountingReportsController::class, 'index'])->name('accounting.reports');
        Route::get('/accounting/reports/export', [\App\Http\Controllers\Admin\AccountingReportsController::class, 'export'])->name('accounting.reports.export');
        Route::get('/accounting/reports/invoices', [\App\Http\Controllers\Admin\AccountingReportsController::class, 'invoices'])->name('accounting.reports.invoices');
        Route::get('/accounting/reports/payments', [\App\Http\Controllers\Admin\AccountingReportsController::class, 'payments'])->name('accounting.reports.payments');
        Route::get('/accounting/reports/transactions', [\App\Http\Controllers\Admin\AccountingReportsController::class, 'transactions'])->name('accounting.reports.transactions');
        Route::get('/accounting/reports/expenses', [\App\Http\Controllers\Admin\AccountingReportsController::class, 'expenses'])->name('accounting.reports.expenses');
        Route::get('/accounting/reports/wallets', [\App\Http\Controllers\Admin\AccountingReportsController::class, 'wallets'])->name('accounting.reports.wallets');
        Route::get('/accounting/reports/orders', [\App\Http\Controllers\Admin\AccountingReportsController::class, 'orders'])->name('accounting.reports.orders');

        // الماليات الخاصة بالمدربين (قائمة المدربين ثم المطلوب دفعه لكل مدرب)
        Route::prefix('salaries')->name('salaries.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\SalaryController::class, 'index'])->name('index');
            Route::get('/instructor/{instructor}', [\App\Http\Controllers\Admin\SalaryController::class, 'instructor'])->name('instructor');
            Route::post('/instructor/{instructor}/pay-now/{agreement}', [\App\Http\Controllers\Admin\SalaryController::class, 'payNowFromAgreement'])->name('pay-now-from-agreement');
            Route::get('/pay/{payment}', [\App\Http\Controllers\Admin\SalaryController::class, 'pay'])->name('pay');
            Route::post('/pay/{payment}', [\App\Http\Controllers\Admin\SalaryController::class, 'markPaid'])->name('mark-paid');
        });

        // التقارير الشاملة
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\ReportsController::class, 'index'])->name('index');
            Route::get('/users', [\App\Http\Controllers\Admin\ReportsController::class, 'users'])->name('users');
            Route::get('/courses', [\App\Http\Controllers\Admin\ReportsController::class, 'courses'])->name('courses');
            Route::get('/financial', [\App\Http\Controllers\Admin\ReportsController::class, 'financial'])->name('financial');
            Route::get('/academic', [\App\Http\Controllers\Admin\ReportsController::class, 'academic'])->name('academic');
            Route::get('/activities', [\App\Http\Controllers\Admin\ReportsController::class, 'activities'])->name('activities');
            Route::get('/comprehensive', [\App\Http\Controllers\Admin\ReportsController::class, 'comprehensive'])->name('comprehensive');
            
            // تصدير التقارير
            Route::get('/export/users', [\App\Http\Controllers\Admin\ReportsController::class, 'exportUsers'])
                ->middleware('throttle:10,5')
                ->name('export.users');
            Route::get('/export/courses', [\App\Http\Controllers\Admin\ReportsController::class, 'exportCourses'])
                ->middleware('throttle:10,5')
                ->name('export.courses');
            Route::get('/export/financial', [\App\Http\Controllers\Admin\ReportsController::class, 'exportFinancial'])
                ->middleware('throttle:10,5')
                ->name('export.financial');
            Route::get('/export/comprehensive', [\App\Http\Controllers\Admin\ReportsController::class, 'exportComprehensive'])
                ->middleware('throttle:5,10')
                ->name('export.comprehensive');
        });
        Route::prefix('installments')->name('installments.')->group(function () {
            Route::resource('plans', \App\Http\Controllers\Admin\InstallmentPlanController::class);
            Route::resource('agreements', \App\Http\Controllers\Admin\InstallmentAgreementController::class);
            Route::post('/agreements/payments/{payment}/mark', [\App\Http\Controllers\Admin\InstallmentAgreementController::class, 'markPayment'])
                ->name('agreements.mark-payment');
        });

        // نظام الاتفاقيات للمدربين
        Route::prefix('agreements')->name('agreements.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\InstructorAgreementController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\InstructorAgreementController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\InstructorAgreementController::class, 'store'])
                ->middleware('throttle:20,5')
                ->name('store');
            Route::get('/{agreement}', [\App\Http\Controllers\Admin\InstructorAgreementController::class, 'show'])->name('show');
            Route::get('/{agreement}/edit', [\App\Http\Controllers\Admin\InstructorAgreementController::class, 'edit'])->name('edit');
            Route::put('/{agreement}', [\App\Http\Controllers\Admin\InstructorAgreementController::class, 'update'])
                ->middleware('throttle:20,5')
                ->name('update');
            Route::delete('/{agreement}', [\App\Http\Controllers\Admin\InstructorAgreementController::class, 'destroy'])
                ->middleware('throttle:10,1')
                ->name('destroy');
        });

        // نظام اتفاقيات الموظفين
        Route::prefix('employee-agreements')->name('employee-agreements.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\EmployeeAgreementController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\EmployeeAgreementController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\EmployeeAgreementController::class, 'store'])
                ->middleware('throttle:20,5')
                ->name('store');
            Route::post('payments/{payment}/mark-paid', [\App\Http\Controllers\Admin\EmployeeAgreementController::class, 'markPaymentPaid'])->name('payments.mark-paid');
            Route::post('{employeeAgreement}/payments', [\App\Http\Controllers\Admin\EmployeeAgreementController::class, 'storePayment'])->name('payments.store');
            Route::get('/{employeeAgreement}', [\App\Http\Controllers\Admin\EmployeeAgreementController::class, 'show'])->name('show');
            Route::get('/{employeeAgreement}/edit', [\App\Http\Controllers\Admin\EmployeeAgreementController::class, 'edit'])->name('edit');
            Route::put('/{employeeAgreement}', [\App\Http\Controllers\Admin\EmployeeAgreementController::class, 'update'])
                ->middleware('throttle:20,5')
                ->name('update');
            Route::delete('/{employeeAgreement}', [\App\Http\Controllers\Admin\EmployeeAgreementController::class, 'destroy'])
                ->middleware('throttle:10,1')
                ->name('destroy');
        });
        
        // إدارة طلبات السحب للمدربين
        Route::prefix('withdrawals')->name('withdrawals.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\WithdrawalRequestController::class, 'index'])->name('index');
            Route::get('/{withdrawal}', [\App\Http\Controllers\Admin\WithdrawalRequestController::class, 'show'])->name('show');
            Route::post('/{withdrawal}/approve', [\App\Http\Controllers\Admin\WithdrawalRequestController::class, 'approve'])
                ->middleware('throttle:10,1')
                ->name('approve');
            Route::post('/{withdrawal}/reject', [\App\Http\Controllers\Admin\WithdrawalRequestController::class, 'reject'])
                ->middleware('throttle:10,1')
                ->name('reject');
            Route::post('/{withdrawal}/complete', [\App\Http\Controllers\Admin\WithdrawalRequestController::class, 'complete'])
                ->middleware('throttle:10,1')
                ->name('complete');
        });

        // إدارة التسويق
        Route::get('/personal-branding', [\App\Http\Controllers\Admin\InstructorPersonalBrandingController::class, 'index'])->name('personal-branding.index');
        Route::resource('popup-ads', \App\Http\Controllers\Admin\PopupAdController::class)->except(['show']);
        Route::get('/personal-branding/{personal_branding}', [\App\Http\Controllers\Admin\InstructorPersonalBrandingController::class, 'show'])->name('personal-branding.show');
        Route::post('/personal-branding/{personal_branding}/approve', [\App\Http\Controllers\Admin\InstructorPersonalBrandingController::class, 'approve'])->name('personal-branding.approve');
        Route::post('/personal-branding/{personal_branding}/reject', [\App\Http\Controllers\Admin\InstructorPersonalBrandingController::class, 'reject'])->name('personal-branding.reject');
        Route::post('/personal-branding/{personal_branding}/send-back', [\App\Http\Controllers\Admin\InstructorPersonalBrandingController::class, 'sendBackForReview'])->name('personal-branding.send-back');
        Route::resource('coupons', \App\Http\Controllers\Admin\CouponController::class);
        // إدارة برامج الإحالات
        Route::resource('referral-programs', \App\Http\Controllers\Admin\ReferralProgramController::class);
        
        // إدارة الإحالات
        Route::prefix('referrals')->name('referrals.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\ReferralController::class, 'index'])->name('index');
            Route::get('/{referral}', [\App\Http\Controllers\Admin\ReferralController::class, 'show'])->name('show');
        });
        Route::prefix('loyalty')->name('loyalty.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\LoyaltyController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Admin\LoyaltyController::class, 'store'])->name('store');
            Route::get('/{loyaltyProgram}', [\App\Http\Controllers\Admin\LoyaltyController::class, 'show'])->name('show');
            Route::put('/{loyaltyProgram}', [\App\Http\Controllers\Admin\LoyaltyController::class, 'update'])->name('update');
        });

        // إدارة الشهادات والإنجازات
        Route::resource('certificates', \App\Http\Controllers\Admin\CertificateController::class);
        Route::resource('achievements', \App\Http\Controllers\Admin\AchievementController::class);
        Route::resource('badges', \App\Http\Controllers\Admin\BadgeController::class);
        Route::resource('reviews', \App\Http\Controllers\Admin\ReviewController::class);

        // إدارة المحاضرات (مسار الكورس قبل الـ resource لتفادي التعارض)
        Route::get('/lectures/course/{course}', [\App\Http\Controllers\Admin\LectureController::class, 'indexByCourse'])->name('lectures.by-course');
        Route::resource('lectures', \App\Http\Controllers\Admin\LectureController::class);

        // إدارة الحضور
        Route::prefix('attendance')->name('attendance.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\AttendanceController::class, 'index'])->name('index');
            Route::get('/lecture/{lecture}', [\App\Http\Controllers\Admin\AttendanceController::class, 'showLectureAttendance'])->name('lecture');
            Route::post('/lecture/{lecture}/upload-teams', [\App\Http\Controllers\Admin\AttendanceController::class, 'uploadTeamsFile'])->name('upload-teams');
        });

        // إدارة الأداء
        Route::prefix('performance')->name('performance.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\PerformanceController::class, 'index'])->name('index');
            Route::post('/clear-cache', [\App\Http\Controllers\Admin\PerformanceController::class, 'clearCache'])
                ->middleware('throttle:10,1')
                ->name('clear-cache');
            Route::post('/optimize-cache', [\App\Http\Controllers\Admin\PerformanceController::class, 'optimizeCache'])
                ->middleware('throttle:5,5')
                ->name('optimize-cache');
            Route::post('/clear-temp-files', [\App\Http\Controllers\Admin\PerformanceController::class, 'clearTempFiles'])
                ->middleware('throttle:5,5')
                ->name('clear-temp-files');
            Route::post('/optimize-database', [\App\Http\Controllers\Admin\PerformanceController::class, 'optimizeDatabase'])
                ->middleware('throttle:3,10')
                ->name('optimize-database');
        });

        // ===== نظام البث المباشر (Admin) =====
        Route::prefix('live-sessions')->name('live-sessions.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\LiveSessionController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\LiveSessionController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\LiveSessionController::class, 'store'])->name('store');
            Route::get('/{liveSession}', [\App\Http\Controllers\Admin\LiveSessionController::class, 'show'])->name('show');
            Route::get('/{liveSession}/edit', [\App\Http\Controllers\Admin\LiveSessionController::class, 'edit'])->name('edit');
            Route::put('/{liveSession}', [\App\Http\Controllers\Admin\LiveSessionController::class, 'update'])->name('update');
            Route::delete('/{liveSession}', [\App\Http\Controllers\Admin\LiveSessionController::class, 'destroy'])->name('destroy');
            Route::post('/{liveSession}/force-end', [\App\Http\Controllers\Admin\LiveSessionController::class, 'forceEnd'])->name('force-end');
            Route::post('/{liveSession}/cancel', [\App\Http\Controllers\Admin\LiveSessionController::class, 'cancel'])->name('cancel');
        });

        Route::prefix('live-servers')->name('live-servers.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\LiveServerController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\LiveServerController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\LiveServerController::class, 'store'])->name('store');
            Route::get('/{liveServer}/edit', [\App\Http\Controllers\Admin\LiveServerController::class, 'edit'])->name('edit');
            Route::put('/{liveServer}', [\App\Http\Controllers\Admin\LiveServerController::class, 'update'])->name('update');
            Route::delete('/{liveServer}', [\App\Http\Controllers\Admin\LiveServerController::class, 'destroy'])->name('destroy');
            Route::post('/{liveServer}/toggle-status', [\App\Http\Controllers\Admin\LiveServerController::class, 'toggleStatus'])->name('toggle-status');
        });

        Route::prefix('live-recordings')->name('live-recordings.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\LiveRecordingController::class, 'index'])->name('index');
            Route::get('/{liveRecording}', [\App\Http\Controllers\Admin\LiveRecordingController::class, 'show'])->name('show');
            Route::put('/{liveRecording}', [\App\Http\Controllers\Admin\LiveRecordingController::class, 'update'])->name('update');
            Route::post('/{liveRecording}/toggle-publish', [\App\Http\Controllers\Admin\LiveRecordingController::class, 'togglePublish'])->name('toggle-publish');
            Route::delete('/{liveRecording}', [\App\Http\Controllers\Admin\LiveRecordingController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('live-settings')->name('live-settings.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\LiveSettingController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Admin\LiveSettingController::class, 'update'])->name('update');
        });

    });

    // المهام (للجميع)
    Route::resource('tasks', \App\Http\Controllers\TaskController::class);

    // مسارات الطلاب - محمية للطلاب فقط
    Route::prefix('student')->name('student.')->middleware(['role:student'])->group(function () {
        Route::resource('invoices', \App\Http\Controllers\Student\InvoiceController::class)->only(['index', 'show']);
        Route::resource('wallet', \App\Http\Controllers\Student\WalletController::class)->only(['index', 'show']);
        Route::resource('certificates', \App\Http\Controllers\Student\CertificateController::class)->only(['index', 'show']);
        Route::resource('achievements', \App\Http\Controllers\Student\AchievementController::class)->only(['index', 'show']);
        Route::resource('assignments', \App\Http\Controllers\Student\AssignmentController::class)->only(['index', 'show']);
        Route::post('/assignments/{assignment}/submit', [\App\Http\Controllers\Student\AssignmentController::class, 'submit'])
            ->middleware(['ownership:assignment,assignment'])
            ->name('assignments.submit');
        Route::resource('tasks', \App\Http\Controllers\Student\TaskController::class);

        // ===== البث المباشر (Student) =====
        Route::prefix('live-sessions')->name('live-sessions.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Student\LiveSessionController::class, 'index'])->name('index');
            Route::get('/{liveSession}', [\App\Http\Controllers\Student\LiveSessionController::class, 'show'])->name('show');
            Route::post('/{liveSession}/join', [\App\Http\Controllers\Student\LiveSessionController::class, 'join'])->name('join');
            Route::post('/{liveSession}/leave', [\App\Http\Controllers\Student\LiveSessionController::class, 'leave'])->name('leave');
        });
    });

    // مسارات المدرسين
    Route::prefix('instructor')->name('instructor.')->middleware(['auth', 'role:instructor|teacher'])->group(function () {
        // بروفايل المدرب
        Route::get('/profile', [\App\Http\Controllers\Instructor\ProfileController::class, 'index'])->name('profile');
        Route::put('/profile', [\App\Http\Controllers\Instructor\ProfileController::class, 'update'])->name('profile.update');

        // التسويق الشخصي (البراندينغ) — ملف تعريفي للمدرب للمراجعة والنشر
        Route::get('/personal-branding', [\App\Http\Controllers\Instructor\PersonalBrandingController::class, 'edit'])->name('personal-branding.edit');
        Route::put('/personal-branding', [\App\Http\Controllers\Instructor\PersonalBrandingController::class, 'update'])->name('personal-branding.update');
        Route::post('/personal-branding/submit', [\App\Http\Controllers\Instructor\PersonalBrandingController::class, 'submit'])->name('personal-branding.submit');

        Route::resource('courses', \App\Http\Controllers\Instructor\CourseController::class)->only(['index', 'show']);
        Route::get('courses/{course}/curriculum', [\App\Http\Controllers\Instructor\CurriculumController::class, 'index'])->name('courses.curriculum');
        Route::post('courses/{course}/curriculum/exams', [\App\Http\Controllers\Instructor\CurriculumController::class, 'storeExamFromCurriculum'])->name('courses.curriculum.exams.store');
        Route::post('courses/{course}/curriculum/assignments', [\App\Http\Controllers\Instructor\CurriculumController::class, 'storeAssignmentFromCurriculum'])->name('courses.curriculum.assignments.store');
        Route::post('courses/{course}/sections', [\App\Http\Controllers\Instructor\CurriculumController::class, 'storeSection'])->name('courses.sections.store');
        Route::put('sections/{section}', [\App\Http\Controllers\Instructor\CurriculumController::class, 'updateSection'])->name('sections.update');
        Route::delete('sections/{section}', [\App\Http\Controllers\Instructor\CurriculumController::class, 'destroySection'])->name('sections.destroy');
        Route::post('sections/{section}/items', [\App\Http\Controllers\Instructor\CurriculumController::class, 'addItem'])->name('sections.items.store');
        Route::delete('curriculum-items/{item}', [\App\Http\Controllers\Instructor\CurriculumController::class, 'removeItem'])->name('curriculum-items.destroy');
        Route::post('curriculum-items/{item}/move', [\App\Http\Controllers\Instructor\CurriculumController::class, 'moveItem'])->name('curriculum-items.move');
        Route::post('courses/{course}/sections/order', [\App\Http\Controllers\Instructor\CurriculumController::class, 'updateSectionsOrder'])->name('courses.sections.order');
        Route::post('sections/{section}/items/order', [\App\Http\Controllers\Instructor\CurriculumController::class, 'updateItemsOrder'])->name('sections.items.order');
        Route::get('lectures/{lecture}/video-questions', [\App\Http\Controllers\Instructor\LectureVideoQuestionController::class, 'index'])->name('lectures.video-questions.index');
        Route::post('lectures/{lecture}/video-questions', [\App\Http\Controllers\Instructor\LectureVideoQuestionController::class, 'store'])->name('lectures.video-questions.store');
        Route::delete('lectures/{lecture}/video-questions/{videoQuestion}', [\App\Http\Controllers\Instructor\LectureVideoQuestionController::class, 'destroy'])->name('lectures.video-questions.destroy');
        
        // تم إلغاء نظام الدروس — الاعتماد على المحاضرات فقط (إعادة توجيه الروابط القديمة)
        Route::prefix('courses/{course}/lessons')->name('courses.lessons.')->group(function () {
            Route::get('/', fn($course) => redirect()->route('instructor.courses.curriculum', $course))->name('index');
            Route::get('/create', fn($course) => redirect()->route('instructor.lectures.index'))->name('create');
            Route::post('/', fn($course) => redirect()->route('instructor.courses.curriculum', $course)->with('info', 'تم إلغاء نظام الدروس؛ استخدم المحاضرات.'))->name('store');
            Route::get('/{lesson}', fn($course) => redirect()->route('instructor.lectures.index'))->name('show');
            Route::get('/{lesson}/edit', fn($course) => redirect()->route('instructor.lectures.index'))->name('edit');
            Route::put('/{lesson}', fn($course) => redirect()->route('instructor.courses.curriculum', $course))->name('update');
            Route::delete('/{lesson}', fn($course) => redirect()->route('instructor.courses.curriculum', $course))->name('destroy');
            Route::post('/{lesson}/toggle-status', fn($course) => redirect()->route('instructor.courses.curriculum', $course))->name('toggle-status');
            Route::post('/reorder', fn($course) => redirect()->route('instructor.courses.curriculum', $course))->name('reorder');
        });

        Route::get('/api/courses/{course}/lessons-list', fn($course) => response()->json([]));
        
        // API لدروس الكورس للمدرب
        Route::resource('lectures', \App\Http\Controllers\Instructor\LectureController::class);
        Route::post('/lectures/{lecture}/sync-teams-attendance', [\App\Http\Controllers\Instructor\LectureController::class, 'syncTeamsAttendance'])->name('lectures.sync-teams-attendance');
        Route::post('/lectures/{lecture}/update-attendance', [\App\Http\Controllers\Instructor\LectureController::class, 'updateAttendance'])->name('lectures.update-attendance');
        
        // المسار التعليمي ملغى — التوجيه إلى لوحة المدرب
        Route::get('/learning-path', function () { return redirect()->route('instructor.dashboard', [], 302); })->name('learning-path.index');
        Route::get('/learning-path/{slug}', function () { return redirect()->route('instructor.dashboard', [], 302); })->name('learning-path.show');
        Route::post('/lectures/{lecture}/update-status', [\App\Http\Controllers\Instructor\LectureController::class, 'updateStatus'])->name('lectures.update-status');
        Route::resource('assignments', \App\Http\Controllers\Instructor\AssignmentController::class);
        Route::get('/assignments/{assignment}/submissions', [\App\Http\Controllers\Instructor\AssignmentController::class, 'submissions'])->name('assignments.submissions');
        Route::post('/assignments/{assignment}/grade/{submission}', [\App\Http\Controllers\Instructor\AssignmentController::class, 'grade'])->name('assignments.grade');
        Route::resource('exams', \App\Http\Controllers\Instructor\ExamController::class);
        Route::get('exams/{exam}/questions', [\App\Http\Controllers\Instructor\ExamQuestionController::class, 'manage'])->name('exams.questions.manage');
        Route::post('exams/{exam}/questions/from-bank', [\App\Http\Controllers\Instructor\ExamQuestionController::class, 'addFromBank'])->name('exams.questions.add-from-bank');
        Route::post('exams/{exam}/questions/new', [\App\Http\Controllers\Instructor\ExamQuestionController::class, 'createNew'])->name('exams.questions.create-new');
        Route::delete('exams/{exam}/questions/{question}', [\App\Http\Controllers\Instructor\ExamQuestionController::class, 'remove'])->name('exams.questions.remove');
        Route::post('exams/{exam}/questions/reorder', [\App\Http\Controllers\Instructor\ExamQuestionController::class, 'reorder'])->name('exams.questions.reorder');
        
        // بنك الأسئلة
        Route::resource('question-banks', \App\Http\Controllers\Instructor\QuestionBankController::class);
        Route::post('question-banks/{questionBank}/questions', [\App\Http\Controllers\Instructor\QuestionController::class, 'store'])->name('question-banks.questions.store');
        Route::get('question-banks/{questionBank}/questions/create', [\App\Http\Controllers\Instructor\QuestionController::class, 'create'])->name('question-banks.questions.create');
        Route::resource('questions', \App\Http\Controllers\Instructor\QuestionController::class)->except(['create', 'store']);
        Route::get('/attendance', [\App\Http\Controllers\Instructor\AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('/attendance/lecture/{lecture}', [\App\Http\Controllers\Instructor\AttendanceController::class, 'showLecture'])->name('attendance.lecture');
        Route::resource('tasks', \App\Http\Controllers\Instructor\TaskController::class);
        Route::get('/tasks/lectures', [\App\Http\Controllers\Instructor\TaskController::class, 'getLectures'])->name('tasks.lectures');
        Route::post('/tasks/{task}/deliverables', [\App\Http\Controllers\Instructor\TaskController::class, 'submitDeliverable'])->name('tasks.submit-deliverable');
        Route::put('/tasks/{task}/progress', [\App\Http\Controllers\Instructor\TaskController::class, 'updateProgress'])->name('tasks.update-progress');

        // تقديم طلبات للإدارة
        Route::prefix('management-requests')->name('management-requests.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Instructor\ManagementRequestController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Instructor\ManagementRequestController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Instructor\ManagementRequestController::class, 'store'])->name('store');
            Route::get('/{managementRequest}', [\App\Http\Controllers\Instructor\ManagementRequestController::class, 'show'])->name('show');
        });
        
        // نظام الاتفاقيات للمدرب
        Route::prefix('agreements')->name('agreements.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Instructor\AgreementController::class, 'index'])->name('index');
            Route::get('/{agreement}/export-activations', [\App\Http\Controllers\Instructor\AgreementController::class, 'exportActivations'])->name('export-activations');
            Route::get('/{agreement}', [\App\Http\Controllers\Instructor\AgreementController::class, 'show'])->name('show');
        });

        // حساب التحويل (بيانات استلام المبالغ)
        Route::get('/transfer-account', [\App\Http\Controllers\Instructor\TransferAccountController::class, 'index'])->name('transfer-account.index');
        Route::post('/transfer-account', [\App\Http\Controllers\Instructor\TransferAccountController::class, 'store'])->name('transfer-account.store');
        
        // طلبات السحب للمدرب
        Route::prefix('withdrawals')->name('withdrawals.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Instructor\WithdrawalRequestController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Instructor\WithdrawalRequestController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Instructor\WithdrawalRequestController::class, 'store'])->name('store');
            Route::get('/{withdrawal}', [\App\Http\Controllers\Instructor\WithdrawalRequestController::class, 'show'])->name('show');
            Route::post('/{withdrawal}/cancel', [\App\Http\Controllers\Instructor\WithdrawalRequestController::class, 'cancel'])->name('cancel');
        });

        // ===== البث المباشر (Instructor) =====
        Route::prefix('live-sessions')->name('live-sessions.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Instructor\LiveSessionController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Instructor\LiveSessionController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Instructor\LiveSessionController::class, 'store'])->name('store');
            Route::get('/{liveSession}', [\App\Http\Controllers\Instructor\LiveSessionController::class, 'show'])->name('show');
            Route::post('/{liveSession}/start', [\App\Http\Controllers\Instructor\LiveSessionController::class, 'start'])->name('start');
            Route::get('/{liveSession}/room', [\App\Http\Controllers\Instructor\LiveSessionController::class, 'room'])->name('room');
            Route::post('/{liveSession}/end', [\App\Http\Controllers\Instructor\LiveSessionController::class, 'end'])->name('end');
        });

    });
});

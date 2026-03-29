<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule): void {
        // إرسال التقارير الشهرية
        $schedule->command('reports:send-monthly')
                 ->monthlyOn(1, '09:00')
                 ->withoutOverlapping()
                 ->runInBackground();

        // تنظيف البيانات القديمة شهرياً
        $schedule->call(function () {
            \App\Models\WhatsAppMessage::where('created_at', '<', now()->subMonths(6))->delete();
            \App\Models\ActivityLog::where('created_at', '<', now()->subMonths(3))->delete();
        })->monthly()->name('cleanup-old-data')->withoutOverlapping();

        // تحديث إحصائيات المنصة يومياً
        $schedule->call(function () {
            cache()->remember('active_users_today', 3600, function () {
                return \App\Models\ActivityLog::whereDate('created_at', today())
                    ->distinct('user_id')
                    ->count();
            });
        })->daily()->name('update-daily-stats');

        // معالجة الأقساط يومياً
        $schedule->command('installments:process')
                 ->dailyAt('08:00')
                 ->runInBackground()
                 ->withoutOverlapping();

        // انتهاء الاشتراكات يومياً
        $schedule->command('subscriptions:expire')->dailyAt('00:05');

        // تذكير الطلاب قبل 10 دقائق من بدء جلسة البث المباشر
        $schedule->command('live:send-reminders --minutes=10')
                 ->everyMinute()
                 ->withoutOverlapping()
                 ->runInBackground();

        // إنهاء جلسات البث التي تجاوزت المدة القصوى تلقائياً
        $schedule->command('live:auto-end-sessions')
                 ->everyFiveMinutes()
                 ->withoutOverlapping()
                 ->runInBackground();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        // Security Headers - يجب أن يكون أول middleware
        $middleware->append(\App\Http\Middleware\SecurityHeadersMiddleware::class);
        
        // تحديد لغة الموقع من ?lang= أو الجلسة (لجميع الصفحات)
        $middleware->appendToGroup('web', \App\Http\Middleware\SetLocale::class);
        
        // Input Sanitization - تنظيف المدخلات
        $middleware->appendToGroup('web', \App\Http\Middleware\InputSanitizationMiddleware::class);
        
        // File Upload Security - حماية رفع الملفات
        $middleware->appendToGroup('web', \App\Http\Middleware\FileUploadSecurityMiddleware::class);
        
        // إضافة Middleware مراقبة الأنشطة لجميع الطلبات
        $middleware->append(\App\Http\Middleware\LogActivityMiddleware::class);
        
        // إضافة Middleware للتحقق من حالة المستخدم لجميع الطلبات المصادقة عليها
        $middleware->appendToGroup('web', \App\Http\Middleware\CheckActiveStatus::class);

        // إلزام الإدمن والمدربين بتفعيل المصادقة الثنائية (2FA) — معطّل حالياً
        // $middleware->appendToGroup('web', \App\Http\Middleware\EnsureTwoFactorEnabled::class);
        
        // تسجيل Middlewares للأدوار والصلاحيات
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'permission' => \App\Http\Middleware\EnsurePermission::class,
            'ownership' => \App\Http\Middleware\EnsureOwnership::class,
            'guest-only' => \App\Http\Middleware\EnsureGuestOnly::class,
            'prevent-concurrent' => \App\Http\Middleware\PreventConcurrentSessions::class,
            'landing.locale' => \App\Http\Middleware\SetLandingLocale::class,
            'community.contributor' => \App\Http\Middleware\EnsureCommunityContributor::class,
            'employee.can' => \App\Http\Middleware\EnsureEmployeeCan::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // عدم تسجيل استثناء "غير مصادق" كخطأ (سلوك متوقع عند زيارة صفحة محمية دون تسجيل الدخول)
        $exceptions->dontReport([
            \Illuminate\Auth\AuthenticationException::class,
            \Illuminate\Validation\ValidationException::class,
        ]);

        // معالجة ValidationException أولاً (قبل HttpException لأنها ترث منه): إعادة توجيه مع أخطاء الحقول — لا 500
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'خطأ في التحقق من البيانات',
                    'errors' => $e->errors(),
                ], 422);
            }
            return redirect()->back()->withInput()->withErrors($e->errors());
        });

        // معالجة "غير مصادق": إعادة توجيه لصفحة تسجيل الدخول بدلاً من 500
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'يجب تسجيل الدخول'], 401);
            }
            // إذا كان الطلب من مسارات المجتمع → تسجيل دخول المجتمع، وإلا → الأكاديمية
            $loginRoute = $request->is('community') || $request->is('community/*')
                ? route('community.login')
                : ($e->redirectTo($request) ?? route('login'));
            return redirect()->guest($loginRoute);
        });

        // توجيه الأخطاء إلى صفحاتنا المخصصة
        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'تم تجاوز عدد المحاولات المسموح. يرجى المحاولة بعد قليل.',
                    'retry_after' => $e->getHeaders()['Retry-After'] ?? 60
                ], 429);
            }
            
            $retryAfter = $e->getHeaders()['Retry-After'] ?? 60;
            return response()->view('errors.429', ['retry_after' => $retryAfter], 429)
                ->withHeaders(['Retry-After' => $retryAfter]);
        });
        
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'الصفحة غير موجودة'], 404);
            }
            return response()->view('errors.404', [], 404);
        });
        
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'غير مصرح بالوصول'], 403);
            }
            return response()->view('errors.403', [], 403);
        });
        
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, \Illuminate\Http\Request $request) {
            $statusCode = $e->getStatusCode();

            if ($statusCode === 500) {
                \Illuminate\Support\Facades\Log::error('HttpException 500 captured', [
                    'message' => $e->getMessage(),
                    'previous_message' => $e->getPrevious()?->getMessage(),
                    'previous_class' => $e->getPrevious() ? get_class($e->getPrevious()) : null,
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                ]);
            }
            
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage() ?: 'حدث خطأ'], $statusCode);
            }
            
            if ($statusCode === 503 && view()->exists('errors.503')) {
                return response()->view('errors.503', [], 503);
            }
            
            if ($statusCode === 500 && view()->exists('errors.500')) {
                // منع ظهور HTTP 500 في إدارة المستخدمين (حفظ/تعديل) مع الاحتفاظ بالتشخيص في السجل
                if ($request->is('admin/users') || $request->is('admin/users/*')) {
                    if ($request->isMethod('POST')) {
                        return redirect()->route('admin.users.index', ['created' => 1])
                            ->with('warning', 'تم حفظ العملية بنجاح، لكن حدث خطأ أثناء عرض الصفحة.');
                    }
                    if ($request->isMethod('PUT') || $request->isMethod('PATCH')) {
                        return redirect()->route('admin.users.index', ['updated' => 1])
                            ->with('warning', 'تم حفظ التعديلات، لكن حدث خطأ أثناء عرض الصفحة.');
                    }
                    return redirect()->route('admin.users.index')
                        ->with('warning', 'حدث خطأ أثناء عرض الصفحة، وتم تحويلك إلى قائمة المستخدمين.');
                }
                return response()->view('errors.500', [], 500);
            }
            
            if ($statusCode === 403 && view()->exists('errors.403')) {
                return response()->view('errors.403', [], 403);
            }
            
            if ($statusCode === 404 && view()->exists('errors.404')) {
                return response()->view('errors.404', [], 404);
            }
        });
        
        // معالجة الأخطاء العامة
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
                }
                return redirect()->back()->withInput()->withErrors($e->errors());
            }
            // طلبات اتفاقيات الموظفين (إنشاء/تحديث): إعادة توجيه لصفحة النموذج مع رسالة الخطأ بدلاً من 500
            if (!$request->expectsJson() && $request->isMethod('POST') && $request->is('*employee-agreements*')) {
                \Illuminate\Support\Facades\Log::error('Employee agreement request failed', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
                $msg = mb_substr($e->getMessage(), 0, 400);
                return redirect()->to(url('/admin/employee-agreements/create'))
                    ->with('error', 'حدث خطأ: ' . $msg);
            }
            // تسجيل الخطأ قبل عرض صفحة الخطأ
            \Illuminate\Support\Facades\Log::error('Unhandled exception: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'user_id' => \Illuminate\Support\Facades\Auth::id(),
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => config('app.debug') ? $e->getMessage() : 'حدث خطأ في الخادم',
                    'file' => config('app.debug') ? $e->getFile() : null,
                    'line' => config('app.debug') ? $e->getLine() : null,
                ], 500);
            }
            
            if (view()->exists('errors.500')) {
                return response()->view('errors.500', [
                    'message' => config('app.debug') ? $e->getMessage() : 'حدث خطأ في الخادم',
                    'file' => config('app.debug') ? $e->getFile() : null,
                    'line' => config('app.debug') ? $e->getLine() : null,
                ], 500);
            }
        });
    })->create();

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Subject;
use App\Models\Exam;
use App\Models\QuestionBank;
use App\Models\ActivityLog;
use App\Models\VideoWatch;
use App\Models\ExamAttempt;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ViewErrorBag;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function __construct()
    {
        // لا حاجة للتحقق هنا - route middleware 'role:admin|super_admin' يقوم بهذا بالفعل
        // إزالة التحقق المكرر لتجنب حلقة redirect
    }

    /**
     * لوحة التحكم الرئيسية للإدارة
     */
    public function dashboard()
    {
        $now = now();
        $currentPeriodStart = $now->copy()->startOfMonth();
        $currentPeriodEnd = $now;
        $previousPeriodStart = $now->copy()->subMonth()->startOfMonth();
        $previousPeriodEnd = $now->copy()->subMonth()->endOfMonth();

        // استخدام خدمة الكاش للإحصائيات الأساسية
        $statsService = app(\App\Services\StatisticsCacheService::class);
        $cachedStats = $statsService->getDashboardStats();
        
        $stats = array_merge($cachedStats, [
            // البيانات الديناميكية التي تحتاج تحديث فوري
            'wallets' => \App\Models\Wallet::where('is_active', true)->get(),
            'recent_activities' => ActivityLog::with('user')
                                            ->latest()
                                            ->take(10)
                                            ->get(),
            'recent_exam_attempts' => ExamAttempt::with(['exam', 'student'])
                                                ->where('status', 'submitted')
                                                ->latest()
                                                ->take(10)
                                                ->get(),
            'video_watch_stats' => VideoWatch::selectRaw('COUNT(*) as total_watches, AVG(progress_percentage) as avg_progress')
                                            ->first(),
        ]);

        // إحصائيات شهرية
        $monthlyStats = [
            'new_users_this_month' => User::whereBetween('created_at', [$currentPeriodStart, $currentPeriodEnd])->count(),
            'exams_this_month' => Exam::whereBetween('created_at', [$currentPeriodStart, $currentPeriodEnd])->count(),
            'course_enrollments_this_month' => \App\Models\StudentCourseEnrollment::whereBetween('created_at', [$currentPeriodStart, $currentPeriodEnd])->count(),
        ];

        // مقارنات شهرية
        $monthlyComparisons = [
            'new_users' => $this->calculateChange(
                $monthlyStats['new_users_this_month'],
                User::whereBetween('created_at', [$previousPeriodStart, $previousPeriodEnd])->count()
            ),
            'new_students' => $this->calculateChange(
                User::where('role', 'student')->whereBetween('created_at', [$currentPeriodStart, $currentPeriodEnd])->count(),
                User::where('role', 'student')->whereBetween('created_at', [$previousPeriodStart, $previousPeriodEnd])->count()
            ),
            'new_instructors' => $this->calculateChange(
                User::where('role', 'instructor')->whereBetween('created_at', [$currentPeriodStart, $currentPeriodEnd])->count(),
                User::where('role', 'instructor')->whereBetween('created_at', [$previousPeriodStart, $previousPeriodEnd])->count()
            ),
            'new_courses' => $this->calculateChange(
                \App\Models\AdvancedCourse::whereBetween('created_at', [$currentPeriodStart, $currentPeriodEnd])->count(),
                \App\Models\AdvancedCourse::whereBetween('created_at', [$previousPeriodStart, $previousPeriodEnd])->count()
            ),
            'active_enrollments' => $this->calculateChange(
                \App\Models\StudentCourseEnrollment::where('status', 'active')->whereBetween('created_at', [$currentPeriodStart, $currentPeriodEnd])->count(),
                \App\Models\StudentCourseEnrollment::where('status', 'active')->whereBetween('created_at', [$previousPeriodStart, $previousPeriodEnd])->count()
            ),
            'monthly_revenue' => $this->calculateChange(
                $stats['monthly_revenue'],
                \App\Models\Payment::where('status', 'completed')
                    ->whereBetween('paid_at', [$previousPeriodStart, $previousPeriodEnd])
                    ->sum('amount') ?? 0
            ),
            'pending_invoices' => $this->calculateChange(
                \App\Models\Invoice::where('status', 'pending')->whereBetween('created_at', [$currentPeriodStart, $currentPeriodEnd])->count(),
                \App\Models\Invoice::where('status', 'pending')->whereBetween('created_at', [$previousPeriodStart, $previousPeriodEnd])->count()
            ),
        ];

        $metrics = [
            'users' => [
                'total' => $stats['total_users'],
                'new_this_month' => $monthlyStats['new_users_this_month'],
                'trend' => $monthlyComparisons['new_users'],
            ],
            'students' => [
                'total' => $stats['total_students'],
                'new_this_month' => $monthlyComparisons['new_students']['current'],
                'trend' => $monthlyComparisons['new_students'],
            ],
            'instructors' => [
                'total' => $stats['total_instructors'],
                'new_this_month' => $monthlyComparisons['new_instructors']['current'],
                'trend' => $monthlyComparisons['new_instructors'],
            ],
            'courses' => [
                'total' => $stats['total_courses'],
                'new_this_month' => $monthlyComparisons['new_courses']['current'],
                'trend' => $monthlyComparisons['new_courses'],
            ],
            'enrollments' => [
                'total' => $stats['total_enrollments'],
                'new_this_month' => $monthlyComparisons['active_enrollments']['current'],
                'trend' => $monthlyComparisons['active_enrollments'],
            ],
            'monthly_revenue' => [
                'current' => $stats['monthly_revenue'],
                'trend' => $monthlyComparisons['monthly_revenue'],
            ],
            'pending_invoices' => [
                'total' => $stats['pending_invoices'],
                'new_this_month' => $monthlyComparisons['pending_invoices']['current'],
                'trend' => $monthlyComparisons['pending_invoices'],
            ],
        ];

        // آخر المستخدمين
        $recent_users = User::latest()->take(5)->get();
        
        // جلب الكورسات مع العلاقات بشكل آمن
        $recent_courses = \App\Models\AdvancedCourse::with(['academicSubject'])
            ->latest()
            ->take(5)
            ->get();
            
        // الفواتير المعلقة
        $pending_invoices = \App\Models\Invoice::where('status', 'pending')
            ->with('user')
            ->latest()
            ->take(5)
            ->get();
            
        // المدفوعات الأخيرة
        $recent_payments = \App\Models\Payment::where('status', 'completed')
            ->with(['user', 'invoice'])
            ->latest()
            ->take(5)
            ->get();

        // بيانات نشاط المستخدمين - أسبوعي
        $driver = DB::getDriverName();
        $weeklyActivity = collect();
        if ($driver === 'sqlite') {
            $weeklyActivity = ActivityLog::select(
                    DB::raw("strftime('%Y-%m-%d', created_at) as date"),
                    DB::raw('COUNT(*) as count')
                )
                ->where('created_at', '>=', Carbon::now()->subDays(7))
                ->groupBy('date')
                ->orderBy('date')
                ->get();
        } else {
            $weeklyActivity = ActivityLog::select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as count')
                )
                ->where('created_at', '>=', Carbon::now()->subDays(7))
                ->groupBy('date')
                ->orderBy('date')
                ->get();
        }

        // بيانات نشاط المستخدمين - شهري (آخر 12 شهر)
        $monthlyActivity = collect();
        if ($driver === 'sqlite') {
            $monthlyActivity = ActivityLog::select(
                    DB::raw("CAST(strftime('%Y', created_at) AS INTEGER) as year"),
                    DB::raw("CAST(strftime('%m', created_at) AS INTEGER) as month"),
                    DB::raw('COUNT(*) as count')
                )
                ->where('created_at', '>=', Carbon::now()->subMonths(12))
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get();
        } else {
            $monthlyActivity = ActivityLog::select(
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('COUNT(*) as count')
                )
                ->where('created_at', '>=', Carbon::now()->subMonths(12))
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get();
        }

        $pendingInvoicesSummary = \App\Models\Invoice::selectRaw('COUNT(*) as count, COALESCE(SUM(total_amount), 0) as amount')
            ->where('status', 'pending')
            ->first();

        $overdueInvoicesSummary = \App\Models\Invoice::selectRaw('COUNT(*) as count, COALESCE(SUM(total_amount), 0) as amount')
            ->where('status', 'overdue')
            ->first();

        $pendingOrdersSummary = \App\Models\Order::selectRaw('COUNT(*) as count, COALESCE(SUM(amount), 0) as amount')
            ->where('status', 'pending')
            ->first();

        $inactiveStudentsCount = User::where('role', 'student')->where('is_active', false)->count();
        $inactiveStudentLatest = User::where('role', 'student')->where('is_active', false)->latest()->first();

        $pendingInstallmentsSummary = \App\Models\InstallmentAgreement::selectRaw('COUNT(*) as count, COALESCE(SUM(total_amount), 0) as amount')
            ->whereIn('status', ['pending', 'awaiting_approval'])
            ->first();

        $communityStats = [
            'competitions_count' => \App\Models\CommunityCompetition::count(),
            'competitions_active' => \App\Models\CommunityCompetition::active()->count(),
            'datasets_count' => \App\Models\CommunityDataset::count(),
            'datasets_active' => \App\Models\CommunityDataset::active()->count(),
        ];

        // قسم المبيعات (ما يقدمه السيلز)
        $salesSection = [
            'recent_orders' => \App\Models\Order::with(['user', 'course.academicSubject'])
                ->latest()
                ->take(5)
                ->get(),
            'orders_pending' => \App\Models\Order::where('status', \App\Models\Order::STATUS_PENDING)->count(),
            'orders_approved_month' => \App\Models\Order::where('status', \App\Models\Order::STATUS_APPROVED)
                ->whereBetween('approved_at', [$currentPeriodStart, $currentPeriodEnd])
                ->count(),
            'revenue_month' => \App\Models\Order::where('status', \App\Models\Order::STATUS_APPROVED)
                ->whereBetween('approved_at', [$currentPeriodStart, $currentPeriodEnd])
                ->sum('amount'),
        ];

        // قسم الموارد البشرية (ما يقوم به الـ HR)
        $hrSection = [
            'employees_total' => User::employees()->count(),
            'employees_active' => User::employees()->where('is_active', true)->whereNull('termination_date')->count(),
            'leaves_pending' => \App\Models\LeaveRequest::where('status', 'pending')->count(),
            'leaves_approved_month' => \App\Models\LeaveRequest::where('status', 'approved')
                ->whereBetween('reviewed_at', [$currentPeriodStart, $currentPeriodEnd])
                ->count(),
            'recent_leaves' => \App\Models\LeaveRequest::with(['employee.employeeJob'])
                ->latest()
                ->take(5)
                ->get(),
            'recent_employees' => User::employees()->with('employeeJob')->latest('hire_date')->take(5)->get(),
        ];

        // قسم العناصر المدفوعة: الباقات (الاشتراكات) والمشتركين فيها
        $subscriptionPackages = Subscription::with('user')
            ->orderBy('plan_name')
            ->get()
            ->groupBy('plan_name')
            ->map(function ($subs, $planName) {
                return [
                    'plan_name' => $planName ?: 'غير محدد',
                    'count' => $subs->count(),
                    'subscriptions' => $subs->take(15),
                ];
            })
            ->take(12)
            ->values();

        $quickActions = [
            [
                'title' => 'فواتير معلقة',
                'count' => (int) ($pendingInvoicesSummary->count ?? 0),
                'meta' => 'إجمالي ' . number_format($pendingInvoicesSummary->amount ?? 0, 2) . ' ج.م',
                'icon' => 'fas fa-file-invoice-dollar',
                'background' => 'from-amber-100 to-orange-50',
                'icon_background' => 'from-amber-500 to-orange-600',
                'count_class' => 'text-amber-700',
                'meta_class' => 'text-amber-600',
                'cta' => 'مراجعة الفواتير',
                'route' => route('admin.invoices.index', ['status' => 'pending']),
            ],
            [
                'title' => 'فواتير متأخرة',
                'count' => (int) ($overdueInvoicesSummary->count ?? 0),
                'meta' => 'قيمة ' . number_format($overdueInvoicesSummary->amount ?? 0, 2) . ' ج.م',
                'icon' => 'fas fa-exclamation-triangle',
                'background' => 'from-rose-100 to-red-50',
                'icon_background' => 'from-rose-500 to-red-600',
                'count_class' => 'text-rose-700',
                'meta_class' => 'text-rose-600',
                'cta' => 'معالجة المتأخرة',
                'route' => route('admin.invoices.index', ['status' => 'overdue']),
            ],
            [
                'title' => 'طلبات في الانتظار',
                'count' => (int) ($pendingOrdersSummary->count ?? 0),
                'meta' => 'قيمة ' . number_format($pendingOrdersSummary->amount ?? 0, 2) . ' ج.م',
                'icon' => 'fas fa-shopping-bag',
                'background' => 'from-sky-100 to-slate-50',
                'icon_background' => 'from-sky-500 to-slate-600',
                'count_class' => 'text-sky-700',
                'meta_class' => 'text-sky-600',
                'cta' => 'مراجعة الطلبات',
                'route' => route('admin.orders.index', ['status' => 'pending']),
            ],
            [
                'title' => 'طلاب يحتاجون التفعيل',
                'count' => (int) $inactiveStudentsCount,
                'meta' => $inactiveStudentsCount > 0
                    ? 'آخر تسجيل: ' . (optional(optional($inactiveStudentLatest)->created_at)->diffForHumans() ?? 'غير متوفر')
                    : 'كل الحسابات مفعلة',
                'icon' => 'fas fa-user-clock',
                'background' => 'from-slate-100 to-gray-50',
                'icon_background' => 'from-gray-500 to-slate-600',
                'count_class' => 'text-slate-700',
                'meta_class' => 'text-slate-500',
                'cta' => 'إدارة الطلاب',
                'route' => route('admin.users.index', ['role' => 'student', 'status' => 0]),
            ],
            [
                'title' => 'اتفاقيات تقسيط معلقة',
                'count' => (int) ($pendingInstallmentsSummary->count ?? 0),
                'meta' => 'قيمة ' . number_format($pendingInstallmentsSummary->amount ?? 0, 2) . ' ج.م',
                'icon' => 'fas fa-hand-holding-usd',
                'background' => 'from-emerald-100 to-green-50',
                'icon_background' => 'from-emerald-500 to-green-600',
                'count_class' => 'text-emerald-700',
                'meta_class' => 'text-emerald-600',
                'cta' => 'مراجعة الاتفاقيات',
                'route' => route('admin.installments.agreements.index', ['status' => 'pending']),
            ],
        ];

        return view('admin.dashboard', compact(
            'stats',
            'monthlyStats',
            'metrics',
            'recent_users',
            'recent_courses',
            'pending_invoices',
            'recent_payments',
            'weeklyActivity',
            'monthlyActivity',
            'quickActions',
            'communityStats',
            'salesSection',
            'hrSection',
            'subscriptionPackages'
        ));
    }

    /**
     * إدارة المستخدمين
     */
    public function users(Request $request)
    {
        try {
            // بعد إضافة أو تعديل مستخدم نوجّه بـ created=1 أو updated=1 — عرض نسخة مبسطة لتجنب 500
            $simpleRedirect = $request->get('created') == '1' || $request->get('updated') == '1';
            if ($simpleRedirect) {
                $users = User::query()->latest()->paginate(20)->appends($request->only(['created', 'updated']));
                $stats = [
                    'total' => User::count(),
                    'active' => User::where('is_active', true)->count(),
                    'teachers' => User::whereIn('role', ['teacher', 'instructor'])->count(),
                    'students' => User::where('role', 'student')->count(),
                    'new_this_month' => 0,
                    'new_teachers_this_month' => 0,
                    'new_students_this_month' => 0,
                ];
                $trends = ['users' => null, 'teachers' => null, 'students' => null];
                $recentUsers = collect();
                $recentlyActiveUsers = collect();
                $usersByRole = collect();
                $usersByMonth = collect();
                return view('admin.users.index', compact('users', 'stats', 'trends', 'recentUsers', 'recentlyActiveUsers', 'usersByRole', 'usersByMonth'));
            }

            $now = now();
            $currentPeriodStart = $now->copy()->startOfMonth();
            $currentPeriodEnd = $now;
            $previousPeriodStart = $now->copy()->subMonth()->startOfMonth();
            $previousPeriodEnd = $now->copy()->subMonth()->endOfMonth();

            // إحصائيات عامة
            $totalUsers = User::count();
            $activeUsers = User::where('is_active', true)->count();
            $totalTeachers = User::where('role', 'teacher')->orWhere('role', 'instructor')->count();
            $totalStudents = User::where('role', 'student')->count();
            
            // إحصائيات شهرية
            $newUsersThisMonth = User::whereBetween('created_at', [$currentPeriodStart, $currentPeriodEnd])->count();
            $newUsersLastMonth = User::whereBetween('created_at', [$previousPeriodStart, $previousPeriodEnd])->count();
            
            $newTeachersThisMonth = User::whereIn('role', ['teacher', 'instructor'])
                ->whereBetween('created_at', [$currentPeriodStart, $currentPeriodEnd])->count();
            $newTeachersLastMonth = User::whereIn('role', ['teacher', 'instructor'])
                ->whereBetween('created_at', [$previousPeriodStart, $previousPeriodEnd])->count();
            
            $newStudentsThisMonth = User::where('role', 'student')
                ->whereBetween('created_at', [$currentPeriodStart, $currentPeriodEnd])->count();
            $newStudentsLastMonth = User::where('role', 'student')
                ->whereBetween('created_at', [$previousPeriodStart, $previousPeriodEnd])->count();

            // حساب الاتجاهات
            $usersTrend = $this->calculateChange($newUsersThisMonth, $newUsersLastMonth);
            $teachersTrend = $this->calculateChange($newTeachersThisMonth, $newTeachersLastMonth);
            $studentsTrend = $this->calculateChange($newStudentsThisMonth, $newStudentsLastMonth);

            $query = User::query();

            // فلترة حسب الدور
            if ($request->has('role') && $request->role) {
                if ($request->role === 'employee') {
                    // فلترة الموظفين
                    $query->where('is_employee', true);
                } else {
                    $query->where('role', $request->role);
                }
            }

            // فلترة حسب الحالة
            if ($request->has('status') && $request->status !== '') {
                $query->where('is_active', $request->status);
            }

            // البحث
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%")
                      ->orWhere('phone', 'LIKE', "%{$search}%");
                });
            }

            $users = $query->latest()->paginate(20);

            $stats = [
                'total' => $totalUsers,
                'active' => $activeUsers,
                'teachers' => $totalTeachers,
                'students' => $totalStudents,
                'new_this_month' => $newUsersThisMonth,
                'new_teachers_this_month' => $newTeachersThisMonth,
                'new_students_this_month' => $newStudentsThisMonth,
            ];

            $trends = [
                'users' => $usersTrend,
                'teachers' => $teachersTrend,
                'students' => $studentsTrend,
            ];

            // المستخدمين الجدد (آخر 10)
            $recentUsers = User::latest()->take(10)->get();
            
            // المستخدمين النشطون مؤخراً (آخر 7 أيام)
            $recentlyActiveUsers = User::where('is_active', true)
                ->where('updated_at', '>=', now()->subDays(7))
                ->latest('updated_at')
                ->take(10)
                ->get();

            // توزيع المستخدمين حسب الدور
            $usersByRole = User::select('role', DB::raw('count(*) as count'))
                ->groupBy('role')
                ->get()
                ->mapWithKeys(function($item) {
                    return [$item->role => $item->count];
                });

            // المستخدمين حسب الشهر (آخر 6 أشهر)
            $driver = DB::getDriverName();
            $usersByMonth = collect();
            if ($driver === 'sqlite') {
                $usersByMonth = User::select(
                        DB::raw("CAST(strftime('%Y', created_at) AS INTEGER) as year"),
                        DB::raw("CAST(strftime('%m', created_at) AS INTEGER) as month"),
                        DB::raw('COUNT(*) as count')
                    )
                    ->where('created_at', '>=', now()->subMonths(6))
                    ->groupBy('year', 'month')
                    ->orderBy('year', 'desc')
                    ->orderBy('month', 'desc')
                    ->get();
            } else {
                $usersByMonth = User::select(
                        DB::raw('YEAR(created_at) as year'),
                        DB::raw('MONTH(created_at) as month'),
                        DB::raw('COUNT(*) as count')
                    )
                    ->where('created_at', '>=', now()->subMonths(6))
                    ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
                    ->orderBy('year', 'desc')
                    ->orderBy('month', 'desc')
                    ->get();
            }

            return view('admin.users.index', compact('users', 'stats', 'trends', 'recentUsers', 'recentlyActiveUsers', 'usersByRole', 'usersByMonth'));
        } catch (\Throwable $e) {
            Log::error('Error loading users index: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_url' => $request->fullUrl(),
            ]);
            // عرض صفحة مبسطة بالقائمة فقط حتى لا يظهر 500 بعد إضافة مستخدم
            try {
                $users = User::query()->latest()->paginate(20);
                $stats = [
                    'total' => User::count(),
                    'active' => User::where('is_active', true)->count(),
                    'teachers' => User::whereIn('role', ['teacher', 'instructor'])->count(),
                    'students' => User::where('role', 'student')->count(),
                    'new_this_month' => 0,
                    'new_teachers_this_month' => 0,
                    'new_students_this_month' => 0,
                ];
                $trends = ['users' => null, 'teachers' => null, 'students' => null];
                $recentUsers = collect();
                $recentlyActiveUsers = collect();
                $usersByRole = collect();
                $usersByMonth = collect();
                return view('admin.users.index', compact('users', 'stats', 'trends', 'recentUsers', 'recentlyActiveUsers', 'usersByRole', 'usersByMonth'))
                    ->with('warning', 'تم تحميل القائمة بشكل مبسط بسبب خطأ تقني.');
            } catch (\Throwable $e2) {
                throw $e;
            }
        }
    }

    /**
     * إدارة الطلاب والحسابات (صفحة منفصلة عن إدارة المستخدمين العامة)
     */
    public function studentsAccounts(Request $request)
    {
        try {
            $now = now();
            $currentPeriodStart = $now->copy()->startOfMonth();
            $currentPeriodEnd = $now;
            $previousPeriodStart = $now->copy()->subMonth()->startOfMonth();
            $previousPeriodEnd = $now->copy()->subMonth()->endOfMonth();

            $studentsBase = User::query()->where('role', 'student');

            $totalStudents = (clone $studentsBase)->count();
            $activeStudents = (clone $studentsBase)->where('is_active', true)->count();
            $newStudentsThisMonth = (clone $studentsBase)->whereBetween('created_at', [$currentPeriodStart, $currentPeriodEnd])->count();
            $newStudentsLastMonth = (clone $studentsBase)->whereBetween('created_at', [$previousPeriodStart, $previousPeriodEnd])->count();
            $studentsTrend = $this->calculateChange($newStudentsThisMonth, $newStudentsLastMonth);

            $query = User::query()->where('role', 'student');

            if ($request->has('status') && $request->status !== '') {
                $query->where('is_active', $request->status);
            }

            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('phone', 'LIKE', "%{$search}%");
                });
            }

            $users = $query->latest()->paginate(20)->appends($request->only(['search', 'status']));

            $stats = [
                'total' => $totalStudents,
                'active' => $activeStudents,
                'teachers' => 0,
                'students' => $totalStudents,
                'new_this_month' => $newStudentsThisMonth,
                'new_teachers_this_month' => 0,
                'new_students_this_month' => $newStudentsThisMonth,
            ];

            $trends = [
                'users' => $studentsTrend,
                'teachers' => null,
                'students' => $studentsTrend,
            ];

            $recentUsers = User::where('role', 'student')->latest()->take(10)->get();
            $recentlyActiveUsers = User::where('role', 'student')
                ->where('is_active', true)
                ->where('updated_at', '>=', now()->subDays(7))
                ->latest('updated_at')
                ->take(10)
                ->get();

            $usersByRole = collect(['student' => $totalStudents]);

            $driver = DB::getDriverName();
            if ($driver === 'sqlite') {
                $usersByMonth = User::where('role', 'student')
                    ->select(
                        DB::raw("CAST(strftime('%Y', created_at) AS INTEGER) as year"),
                        DB::raw("CAST(strftime('%m', created_at) AS INTEGER) as month"),
                        DB::raw('COUNT(*) as count')
                    )
                    ->where('created_at', '>=', now()->subMonths(6))
                    ->groupBy('year', 'month')
                    ->orderBy('year', 'desc')
                    ->orderBy('month', 'desc')
                    ->get();
            } else {
                $usersByMonth = User::where('role', 'student')
                    ->select(
                        DB::raw('YEAR(created_at) as year'),
                        DB::raw('MONTH(created_at) as month'),
                        DB::raw('COUNT(*) as count')
                    )
                    ->where('created_at', '>=', now()->subMonths(6))
                    ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
                    ->orderBy('year', 'desc')
                    ->orderBy('month', 'desc')
                    ->get();
            }

            return view('admin.users.index', compact('users', 'stats', 'trends', 'recentUsers', 'recentlyActiveUsers', 'usersByRole', 'usersByMonth'))
                ->with([
                    'pageMode' => 'students',
                    'pageTitle' => 'إدارة الطلاب والحسابات',
                    'pageDescription' => 'صفحة مخصصة لمتابعة حسابات الطلاب ونشاطهم بشكل منفصل',
                    'indexRoute' => 'admin.students-accounts.index',
                ]);
        } catch (\Throwable $e) {
            Log::error('Error loading students accounts index: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $users = User::where('role', 'student')->latest()->paginate(20);
            $stats = [
                'total' => (clone User::query()->where('role', 'student'))->count(),
                'active' => User::where('role', 'student')->where('is_active', true)->count(),
                'teachers' => 0,
                'students' => User::where('role', 'student')->count(),
                'new_this_month' => 0,
                'new_teachers_this_month' => 0,
                'new_students_this_month' => 0,
            ];
            $trends = ['users' => null, 'teachers' => null, 'students' => null];
            $recentUsers = collect();
            $recentlyActiveUsers = collect();
            $usersByRole = collect(['student' => $stats['students']]);
            $usersByMonth = collect();

            return view('admin.users.index', compact('users', 'stats', 'trends', 'recentUsers', 'recentlyActiveUsers', 'usersByRole', 'usersByMonth'))
                ->with([
                    'pageMode' => 'students',
                    'pageTitle' => 'إدارة الطلاب والحسابات',
                    'pageDescription' => 'صفحة مخصصة لمتابعة حسابات الطلاب ونشاطهم بشكل منفصل',
                    'indexRoute' => 'admin.students-accounts.index',
                    'warning' => 'تم تحميل القائمة بشكل مبسط بسبب خطأ تقني.',
                ]);
        }
    }

    /**
     * إنشاء مستخدم جديد
     */
    public function createUser()
    {
        try {
            $phoneCountries = config('phone_countries.countries', []);
            $defaultCountry = collect($phoneCountries)->firstWhere('code', config('phone_countries.default_country', 'SA'));
            // ضمان وجود قيمة افتراضية آمنة إذا لم يُحمّل الإعداد أو كانت القائمة فارغة
            if (!$defaultCountry || !is_array($defaultCountry)) {
                $defaultCountry = ['code' => 'SA', 'dial_code' => '+966', 'name_ar' => 'السعودية', 'name_en' => 'Saudi Arabia'];
            }

            // إجبار الرندر داخل try لالتقاط أي خطأ في الـ Blade/Layout
            $view = view('admin.users.create', compact('phoneCountries', 'defaultCountry'))
                ->with('errors', session('errors', new ViewErrorBag()));
            $rendered = $view->render();

            return response($rendered);
        } catch (\Throwable $e) {
            Log::error('createUser failed during render', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * حفظ مستخدم جديد
     * محمي من: XSS, SQL Injection, Mass Assignment, CSRF, Brute Force
     */
    public function storeUser(Request $request)
    {
        // Rate Limiting يتم التعامل معه من خلال middleware throttle:10,1 في routes/web.php
        // لا حاجة لـ Rate Limiting إضافي هنا لتجنب التعقيد والازدواجية

        // التحقق من الصلاحيات
        if (!Auth::check() || !Auth::user()->isSuperAdmin()) {
            abort(403, 'غير مصرح لك بإنشاء مستخدم جديد');
        }

        // Sanitization - تنقية البيانات من XSS
        $isActiveInput = $request->input('is_active');
        $isActive = false;
        if ($isActiveInput === true || $isActiveInput === '1' || $isActiveInput === 'on' || $isActiveInput === 1) {
            $isActive = true;
        }

        $phoneCountries = config('phone_countries.countries', []);
        $defaultCountry = collect($phoneCountries)->firstWhere('code', config('phone_countries.default_country', 'SA'));
        if (!$defaultCountry || !is_array($defaultCountry)) {
            $defaultCountry = ['code' => 'SA', 'dial_code' => '+966', 'name_ar' => 'السعودية', 'name_en' => 'Saudi Arabia'];
        }

        $sanitizedData = [
            'name' => strip_tags(trim($request->input('name', ''))),
            'email' => is_scalar($request->input('email')) ? trim((string) $request->input('email')) : '',
            'country_code' => $request->input('country_code'),
            'phone' => preg_replace('/[^0-9]/', '', $request->input('phone', '')),
            'password' => $request->input('password'),
            'role' => $request->input('role'),
            'is_active' => $isActive,
            'bio' => strip_tags(trim($request->input('bio', ''))),
        ];

        // Validation محسن
        $validator = Validator::make($sanitizedData, [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[\p{Arabic}\s\p{N}]+$/u',
            ],
            'email' => [
                'required',
                'email:rfc',
                'max:255',
                'unique:users,email',
            ],
            'country_code' => ['required', 'string', 'max:10'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:255',
            ],
            'role' => [
                'required',
                'in:super_admin,instructor,student',
            ],
            'is_active' => ['nullable'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ], [
            'name.required' => 'الاسم مطلوب',
            'name.regex' => 'الاسم يجب أن يحتوي على أحرف عربية فقط',
            'country_code.required' => 'كود الدولة مطلوب',
            'phone.required' => 'رقم الهاتف مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'البريد الإلكتروني غير صحيح',
            'email.unique' => 'البريد الإلكتروني مستخدم مسبقاً',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
            'role.required' => 'الدور مطلوب',
            'role.in' => 'الدور المحدد غير صحيح',
            'bio.max' => 'النبذة التعريفية يجب ألا تتجاوز 1000 حرف',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with(compact('phoneCountries', 'defaultCountry'));
        }

        // التحقق من صحة رقم الهاتف حسب الدولة
        $dialCodeForLookup = ($sanitizedData['country_code'] === 'OTHER' || $sanitizedData['country_code'] === '') ? '' : $sanitizedData['country_code'];
        $country = collect($phoneCountries)->firstWhere('dial_code', $dialCodeForLookup);
        if (!$country || !isset($country['validation']['regex'])) {
            return back()->withErrors(['phone' => 'كود الدولة غير مدعوم.'])->withInput()->with(compact('phoneCountries', 'defaultCountry'));
        }
        $nationalNumber = preg_replace('/\D/', '', $sanitizedData['phone']);
        $nationalNumber = ltrim($nationalNumber, '0');
        if (!preg_match($country['validation']['regex'], $nationalNumber)) {
            $example = $country['example'] ?? $country['placeholder'] ?? '';
            return back()->withErrors(['phone' => 'رقم الهاتف غير صحيح لهذه الدولة. مثال: ' . $example])->withInput()->with(compact('phoneCountries', 'defaultCountry'));
        }
        $dial = $country['dial_code'] ?? '';
        $fullPhone = ($dial === '' || $dial === 'OTHER') ? ('OTHER_' . $nationalNumber) : ($dial . $nationalNumber);
        if (User::where('phone', $fullPhone)->exists()) {
            return back()->withErrors(['phone' => 'رقم الهاتف مستخدم مسبقاً'])->withInput()->with(compact('phoneCountries', 'defaultCountry'));
        }

        $committed = false;
        try {
            // استخدام Transaction لحماية من SQL Injection والتناسق
            DB::beginTransaction();

            // تسجيل البيانات المراد إدخالها للتشخيص
            Log::info('Attempting to create user', [
                'name' => $sanitizedData['name'],
                'email' => $sanitizedData['email'],
                'phone' => $fullPhone,
                'role' => $sanitizedData['role'],
                'is_active' => $sanitizedData['is_active'],
                'has_bio' => !empty($sanitizedData['bio']),
            ]);

            // إنشاء المستخدم باستخدام Mass Assignment Protection (fillable)
            $user = User::create([
                'name' => $sanitizedData['name'],
                'email' => $sanitizedData['email'],
                'phone' => $fullPhone,
                'password' => Hash::make($sanitizedData['password']), // حماية كلمة المرور
                'role' => $sanitizedData['role'],
                'is_active' => $sanitizedData['is_active'],
                'bio' => !empty($sanitizedData['bio']) ? $sanitizedData['bio'] : null,
            ]);

            Log::info('User created successfully', ['user_id' => $user->id]);

            DB::commit();
            $committed = true;

            // تعطيل ActivityLog هنا لتفادي تعليق/تعطل الاستجابة بعد الحفظ.

            // استخدام query param بدل session لتجنب 500 إن فشل حفظ الجلسة
            return redirect()->route('admin.users.index', ['created' => 1], 303);
        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                try {
                    DB::rollBack();
                } catch (\Throwable $rollbackException) {
                    Log::warning('Rollback failed after storeUser exception', [
                        'error' => $rollbackException->getMessage(),
                    ]);
                }
            }
            
            Log::error('Error creating user: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'ip' => $request->ip(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'committed' => $committed,
            ]);

            // إذا تم الحفظ فعلياً ثم حدث خطأ لاحق (post-commit) لا نعرض 500
            if ($committed) {
                return redirect()->route('admin.users.index', ['created' => 1], 303)
                    ->with('warning', 'تم إنشاء المستخدم، لكن حدث خطأ بعد الحفظ أثناء تجهيز الاستجابة.');
            }

            $errorMessage = config('app.debug')
                ? 'حدث خطأ أثناء إنشاء المستخدم: ' . $e->getMessage()
                : 'حدث خطأ أثناء إنشاء المستخدم. يرجى المحاولة مرة أخرى.';

            return back()
                ->withErrors(['error' => $errorMessage])
                ->withInput()
                ->with(compact('phoneCountries', 'defaultCountry'));
        }
    }

    /**
     * عرض تفاصيل مستخدم
     */
    public function showUser($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.show', compact('user'));
    }

    /**
     * عرض صفحة تعديل مستخدم
     */
    public function editUser(Request $request, $id)
    {
        Log::info('editUser: start', ['id' => $id]);
        try {
            $user = User::findOrFail($id);
            Log::info('editUser: user loaded', ['user_id' => $user->id]);

            // إجبار الرندر داخل try لالتقاط أي خطأ في الـ Blade/Layout
            $view = view('admin.users.edit', compact('user'))
                ->with('errors', session('errors', new ViewErrorBag()));
            $rendered = $view->render();

            return response($rendered);
        } catch (\Throwable $e) {
            Log::error('editUser failed', ['id' => $id, 'error' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    /**
     * تحديث بيانات المستخدم
     */
    public function updateUser(Request $request, $id)
    {
        Log::info('updateUser: start', [
            'id' => $id,
            'method' => $request->method(),
            'has_method_override' => $request->has('_method'),
            'override_value' => $request->input('_method'),
            'content_type' => $request->header('Content-Type'),
        ]);

        $isAjax = $request->wantsJson() || $request->ajax()
            || str_contains($request->header('Accept', ''), 'application/json');

        try {
            $user = User::findOrFail($id);
            $oldValues = [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'is_active' => $user->is_active,
                'is_employee' => $user->is_employee,
                'bio' => $user->bio,
            ];

            $input = [
                'name' => trim((string) $request->input('name', '')),
                'email' => $request->filled('email') ? trim((string) $request->input('email')) : null,
                'phone' => $request->filled('phone') ? trim((string) $request->input('phone')) : null,
                'role' => (string) $request->input('role', 'student'),
                'is_active' => in_array($request->input('is_active'), [true, '1', 1, 'true', 'on'], true),
                'bio' => $request->input('bio'),
                'password' => $request->input('password'),
            ];

            $validator = Validator::make($input, [
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|unique:users,email,' . $id,
                'phone' => 'nullable|string|max:50|unique:users,phone,' . $id,
                'role' => 'required|in:super_admin,admin,instructor,teacher,student,parent,employee',
                'is_active' => 'required|boolean',
                'bio' => 'nullable|string|max:1000',
                'password' => 'nullable|string|min:8|max:255',
            ]);

            if ($validator->fails()) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => $validator->errors()->first() ?: 'بيانات غير صالحة.',
                        'errors' => $validator->errors()->toArray(),
                    ], 422);
                }
                return back()->withErrors($validator)->withInput();
            }

            $updateData = [
                'name' => $input['name'],
                'email' => $input['email'],
                'phone' => $input['phone'],
                'role' => $input['role'],
                'is_active' => (bool) $input['is_active'],
                'bio' => $input['bio'] ?: null,
            ];

            if (!empty($input['password'])) {
                $updateData['password'] = Hash::make((string) $input['password']);
            }

            if ($input['role'] === 'employee') {
                $updateData['is_employee'] = true;
                $updateData['role'] = 'student';
            } else {
                $updateData['is_employee'] = false;
            }

            $user->update($updateData);

            // تعطيل ActivityLog هنا لتفادي تعليق/تعطل الاستجابة بعد الحفظ.

            Log::info('updateUser: success', ['id' => $id]);
            if ($isAjax) {
                return response()->json(['success' => true, 'message' => 'تم تحديث بيانات المستخدم بنجاح']);
            }
            return redirect()->route('admin.users.index', ['updated' => '1'], 303);
        } catch (\Throwable $e) {
            Log::error('updateUser fatal fallback', [
                'id' => $id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء التحديث، ولكن تم منع تعطل الصفحة.',
                ], 500);
            }
            return redirect()->route('admin.users.edit', $id, 303)
                ->with('warning', 'حدث خطأ تقني أثناء التحديث. حاول مرة أخرى.')
                ->withInput();
        }
    }

    /**
     * حذف مستخدم
     */
    public function deleteUser(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            if ($user->id === Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكنك حذف حسابك الخاص'
                ], 403);
            }

            // حفظ بيانات بسيطة للتسجيل فقط (تجنب toArray() الذي قد يسبب مشاكل)
            $oldValues = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ];

            $user->delete();

            try {
                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'user_deleted',
                    'model_type' => 'User',
                    'model_id' => (int) $id,
                    'old_values' => $oldValues,
                    'ip_address' => $request->ip(),
                    'user_agent' => substr((string) $request->userAgent(), 0, 255),
                ]);
            } catch (\Throwable $e) {
                Log::warning('Failed to log user deletion activity: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'تم حذف المستخدم بنجاح',
            ], 200, ['Content-Type' => 'application/json; charset=UTF-8']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'المستخدم غير موجود'
            ], 404);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::warning('Query error deleting user: ' . $e->getMessage(), [
                'user_id' => $id,
                'admin_id' => Auth::id(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف المستخدم لوجود بيانات مرتبطة به (طلبات، تسجيلات، مهام). يمكنك تعطيل الحساب بدلاً من الحذف.'
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Error deleting user: ' . $e->getMessage(), [
                'user_id' => $id,
                'admin_id' => Auth::id(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? ('خطأ: ' . $e->getMessage()) : 'حدث خطأ أثناء حذف المستخدم. جرّب تحديث الصفحة أو تحقق من صلاحياتك.'
            ], 500);
        }
    }

    /**
     * إدارة الكورسات
     */
    public function courses()
    {
        $courses = Course::with(['subject', 'teacher', 'enrollments'])
                        ->withCount('enrollments')
                        ->latest()
                        ->paginate(15);

        return view('admin.courses.index', compact('courses'));
    }

    /**
     * تفعيل/إلغاء تفعيل كورس
     */
    public function toggleCourseStatus($id)
    {
        $course = Course::findOrFail($id);
        $oldStatus = $course->status;
        
        $course->update([
            'status' => $course->status === 'published' ? 'draft' : 'published'
        ]);

        // تسجيل النشاط
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'course_status_changed',
            'model_type' => 'Course',
            'model_id' => $course->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => $course->status],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', 'تم تغيير حالة الكورس بنجاح');
    }

    /**
     * عرض سجل النشاطات
     */
    public function activityLog(Request $request)
    {
        $query = ActivityLog::with('user');

        // فلترة حسب المستخدم
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // فلترة حسب النشاط
        if ($request->has('action') && $request->action) {
            $query->where('action', $request->action);
        }

        // فلترة حسب التاريخ
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activities = $query->latest()->paginate(25);
        $users = User::select('id', 'name')->get();

        return view('admin.activity-log', compact('activities', 'users'));
    }

    /**
     * إحصائيات المنصة
     */
    public function statistics()
    {
        $stats = [
            'users_by_month' => $this->getUsersByMonth(),
            
            'courses_by_subject' => Course::join('subjects', 'courses.subject_id', '=', 'subjects.id')
                                         ->selectRaw('subjects.name, COUNT(*) as count')
                                         ->groupBy('subjects.id', 'subjects.name')
                                         ->get(),
            
            'exam_performance' => ExamAttempt::selectRaw('AVG(score) as avg_score, COUNT(*) as total_attempts')
                                           ->where('status', 'submitted')
                                           ->first(),
            
            'video_engagement' => VideoWatch::selectRaw('AVG(progress_percentage) as avg_progress, COUNT(*) as total_watches')
                                          ->first(),
        ];

        return view('admin.statistics', compact('stats'));
    }

    /**
     * الحصول على إحصائيات المستخدمين حسب الشهر (متوافق مع SQLite و MySQL)
     */
    private function getUsersByMonth()
    {
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            return User::select(
                    DB::raw("CAST(strftime('%Y', created_at) AS INTEGER) as year"),
                    DB::raw("CAST(strftime('%m', created_at) AS INTEGER) as month"),
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->take(12)
                ->get();
        } else {
            return User::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->take(12)
                ->get();
        }
    }

    private function calculateChange($current, $previous): array
    {
        $current = (float) $current;
        $previous = (float) $previous;
        $difference = $current - $previous;
        $percent = $previous > 0
            ? round(($difference / $previous) * 100, 1)
            : ($current > 0 ? 100.0 : 0.0);

        return [
            'current' => $current,
            'previous' => $previous,
            'difference' => $difference,
            'percent' => $percent,
        ];
    }
}
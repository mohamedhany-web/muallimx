<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\EmployeeAgreement;
use App\Models\EmployeeSalaryPayment;
use App\Models\EmployeeTask;
use App\Models\LeaveRequest;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{
    /**
     * لوحة تحكم الموظف — إحصائيات عامة + لمحة حسب الوظيفة
     */
    public function dashboard()
    {
        $user = Auth::user();

        if (!$user->isEmployee()) {
            abort(403, 'غير مصرح لك بالوصول إلى هذه الصفحة');
        }

        // الموظفون الذين لديهم دور RBAC مخصص → لوحة الأدمن المفلترة
        if ($user->roles()->exists()) {
            return redirect()->route('admin.dashboard');
        }

        if (!$user->employeeCan('dashboard')) {
            abort(403, 'لوحة التحكم غير متاحة لوظيفتك الحالية.');
        }

        $user->load('employeeJob');

        $tasks = $user->employeeTasks()
            ->with(['assigner', 'deliverables'])
            ->latest()
            ->take(10)
            ->get();

        $stats = [
            'total_tasks' => $user->employeeTasks()->count(),
            'pending_tasks' => $user->employeeTasks()->where('status', 'pending')->count(),
            'in_progress_tasks' => $user->employeeTasks()->where('status', 'in_progress')->count(),
            'completed_tasks' => $user->employeeTasks()->where('status', 'completed')->count(),
            'overdue_tasks' => $user->employeeTasks()
                ->where('deadline', '<', now())
                ->whereIn('status', ['pending', 'in_progress'])
                ->count(),
        ];

        $jobCode = $user->employeeJob?->code;
        $jobInsights = $this->buildJobInsights($user, $jobCode);

        return view('employee.dashboard', compact('user', 'tasks', 'stats', 'jobCode', 'jobInsights'));
    }

    /**
     * أرقام سريعة تظهر في لوحة التحكم حسب نوع الوظيفة
     */
    private function buildJobInsights($user, ?string $jobCode): array
    {
        if (!$jobCode) {
            return [];
        }

        return match ($jobCode) {
            'accountant' => [
                'label' => 'لمحة المحاسب',
                'items' => [
                    ['text' => 'طلبات دفع معلّقة', 'value' => Order::where('status', Order::STATUS_PENDING)->count(), 'color' => 'amber'],
                    ['text' => 'اتفاقيات موظفين نشطة', 'value' => EmployeeAgreement::where('status', 'active')->count(), 'color' => 'emerald'],
                    ['text' => 'دفعات رواتب بانتظار الصرف', 'value' => EmployeeSalaryPayment::where('status', 'pending')->count(), 'color' => 'sky'],
                ],
            ],
            'sales' => [
                'label' => 'لمحة المبيعات',
                'items' => [
                    ['text' => 'طلبات قيد المراجعة', 'value' => Order::where('status', Order::STATUS_PENDING)->count(), 'color' => 'amber'],
                    ['text' => 'طلبات مُعتمدة هذا الشهر', 'value' => Order::where('status', Order::STATUS_APPROVED)->where('approved_at', '>=', now()->startOfMonth())->count(), 'color' => 'emerald'],
                ],
            ],
            'hr' => [
                'label' => 'لمحة الموارد البشرية',
                'items' => [
                    ['text' => 'موظفون نشطون', 'value' => User::where('is_employee', true)->where('is_active', true)->count(), 'color' => 'indigo'],
                    ['text' => 'طلبات إجازة معلّقة', 'value' => LeaveRequest::where('status', 'pending')->count(), 'color' => 'rose'],
                ],
            ],
            'general_supervision' => $this->supervisionInsight(),
            'supervisor' => $this->supervisionInsight(),
            default => [],
        };
    }

    private function supervisionInsight(): array
    {
        return [
            'label' => 'لمحة الإشراف',
            'items' => [
                ['text' => 'مهام مفتوحة (كل الفريق)', 'value' => EmployeeTask::whereIn('status', ['pending', 'in_progress'])->count(), 'color' => 'blue'],
                ['text' => 'مهام متأخرة', 'value' => EmployeeTask::whereIn('status', ['pending', 'in_progress'])
                    ->whereNotNull('deadline')
                    ->where('deadline', '<', now()->toDateString())
                    ->count(), 'color' => 'red'],
            ],
        ];
    }
}

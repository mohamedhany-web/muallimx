<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class EmployeeHrDeskController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        abort_unless($user->isEmployee() && $user->employeeCan('hr_desk'), 403);

        $stats = [
            'employees' => User::where('is_employee', true)->where('is_active', true)->count(),
            'employees_inactive' => User::where('is_employee', true)->where('is_active', false)->count(),
            'leaves_pending' => LeaveRequest::where('status', 'pending')->count(),
            'leaves_month_approved' => LeaveRequest::where('status', 'approved')
                ->where('reviewed_at', '>=', now()->startOfMonth())
                ->count(),
        ];

        $pendingLeaves = LeaveRequest::query()
            ->pending()
            ->with(['employee:id,name,email,employee_code'])
            ->latest()
            ->take(15)
            ->get();

        $upcomingApproved = LeaveRequest::query()
            ->where('status', 'approved')
            ->where('end_date', '>=', now()->startOfDay())
            ->with(['employee:id,name'])
            ->orderBy('start_date')
            ->take(12)
            ->get();

        $recentHires = User::query()
            ->employees()
            ->where('is_active', true)
            ->whereNotNull('hire_date')
            ->where('hire_date', '>=', now()->subMonths(6))
            ->with('employeeJob:id,name')
            ->orderByDesc('hire_date')
            ->take(8)
            ->get(['id', 'name', 'employee_code', 'hire_date', 'employee_job_id']);

        return view('employee.hr-desk.index', compact('stats', 'pendingLeaves', 'upcomingApproved', 'recentHires'));
    }
}

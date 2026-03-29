<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\EmployeeTask;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class EmployeeSupervisionDeskController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        abort_unless($user->isEmployee() && $user->employeeCan('supervision_desk'), 403);

        $stats = [
            'employees_active' => User::where('is_employee', true)->where('is_active', true)->count(),
            'tasks_open' => EmployeeTask::whereIn('status', ['pending', 'in_progress'])->count(),
            'tasks_overdue' => EmployeeTask::query()
                ->whereIn('status', ['pending', 'in_progress'])
                ->whereNotNull('deadline')
                ->where('deadline', '<', now()->toDateString())
                ->count(),
            'tasks_done_week' => EmployeeTask::where('status', 'completed')
                ->where('completed_at', '>=', now()->subWeek())
                ->count(),
        ];

        $atRiskTasks = EmployeeTask::query()
            ->whereIn('status', ['pending', 'in_progress'])
            ->whereNotNull('deadline')
            ->where('deadline', '<', now()->toDateString())
            ->with(['employee:id,name', 'assigner:id,name'])
            ->orderBy('deadline')
            ->take(25)
            ->get();

        return view('employee.supervision-desk.index', compact('stats', 'atRiskTasks'));
    }
}

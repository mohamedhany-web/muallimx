<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\HrInterview;
use App\Models\HrJobApplication;
use App\Models\HrJobOpening;
use Illuminate\Support\Facades\Auth;

class EmployeeHrRecruitmentController extends Controller
{
    private function gate(): void
    {
        $u = Auth::user();
        abort_unless($u && $u->isEmployee() && $u->employeeCan('hr_desk'), 403);
    }

    public function index()
    {
        $this->gate();

        $stats = [
            'openings_open' => HrJobOpening::where('status', HrJobOpening::STATUS_OPEN)->count(),
            'applications_active' => HrJobApplication::whereNotIn('status', [
                HrJobApplication::STATUS_HIRED,
                HrJobApplication::STATUS_REJECTED,
                HrJobApplication::STATUS_WITHDRAWN,
            ])->count(),
            'interviews_upcoming' => HrInterview::query()
                ->where('status', HrInterview::STATUS_SCHEDULED)
                ->where('scheduled_at', '>=', now()->startOfDay())
                ->where('scheduled_at', '<=', now()->addDays(14))
                ->count(),
        ];

        $recentApplications = HrJobApplication::query()
            ->with(['opening:id,title', 'candidate:id,full_name,email'])
            ->latest('applied_at')
            ->take(12)
            ->get();

        $upcomingInterviews = HrInterview::query()
            ->where('status', HrInterview::STATUS_SCHEDULED)
            ->where('scheduled_at', '>=', now()->startOfDay())
            ->with([
                'application.opening:id,title',
                'application.candidate:id,full_name',
                'interviewer:id,name',
            ])
            ->orderBy('scheduled_at')
            ->take(15)
            ->get();

        return view('employee.hr.recruitment.index', compact('stats', 'recentApplications', 'upcomingInterviews'));
    }
}

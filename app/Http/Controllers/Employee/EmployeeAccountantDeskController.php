<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\EmployeeAgreement;
use App\Models\EmployeeSalaryPayment;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class EmployeeAccountantDeskController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        abort_unless($user->isEmployee() && $user->employeeCan('desk_accountant'), 403);

        $stats = [
            'orders_pending' => Order::where('status', Order::STATUS_PENDING)->count(),
            'agreements_active' => EmployeeAgreement::where('status', 'active')->count(),
            'salary_payments_pending' => EmployeeSalaryPayment::where('status', 'pending')->count(),
            'salary_payments_paid_month' => EmployeeSalaryPayment::where('status', 'paid')
                ->where('payment_date', '>=', now()->startOfMonth()->toDateString())
                ->count(),
        ];

        $recentPendingOrders = Order::query()
            ->where('status', Order::STATUS_PENDING)
            ->with(['user:id,name,email', 'course:id,title'])
            ->latest()
            ->take(10)
            ->get();

        return view('employee.accountant-desk.index', compact('stats', 'recentPendingOrders'));
    }
}

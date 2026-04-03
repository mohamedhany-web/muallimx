<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmployeeCan
{
    /**
     * يتحقق من أن المستخدم موظف وأن وظيفته تسمح بمفتاح القائمة (permissions في employee_jobs).
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();
        if (!$user || !$user->isEmployee()) {
            abort(403, 'غير مصرح لك بالوصول إلى هذه الصفحة');
        }

        // الموظفون ذوو الدور RBAC المخصص → يعملون من لوحة الأدمن فقط
        if ($user->roles()->exists()) {
            return redirect()->route('admin.dashboard');
        }

        if (!$user->employeeCan($permission)) {
            abort(403, 'هذه الصفحة غير متاحة لوظيفتك الحالية.');
        }

        return $next($request);
    }
}

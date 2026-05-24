<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * يمنع الوصول لخدمات باقة المعلم عند انتهاء الاشتراك (بما فيها التجربة المجانية).
 */
class EnsureActiveTeacherSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('login');
        }

        $user->expireOverdueSubscriptionsIfAny();

        if ($user->activeSubscription()) {
            return $next($request);
        }

        $message = 'انتهت مدة اشتراكك أو التجربة المجانية. لا يمكنك استخدام هذه الخدمة حتى تشترك في باقة أو تُفعّل تجربة جديدة.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 403);
        }

        return redirect()
            ->route('public.pricing')
            ->with('error', $message);
    }
}

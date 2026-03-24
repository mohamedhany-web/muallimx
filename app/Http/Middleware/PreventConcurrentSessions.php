<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware لمنع تسجيل الدخول المتزامن من أجهزة/جلسات متعددة
 * يحافظ على جلسة واحدة نشطة فقط لكل مستخدم
 */
class PreventConcurrentSessions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        $sessionId = Session::getId();
        
        // المفتاح في الكاش لتخزين معرف الجلسة النشطة
        $cacheKey = "user_session_{$user->id}";

        // تعطيل المنع الصارم افتراضياً لأنه قد يسبب طرداً غير متوقع أثناء الحفظ/التعديل.
        // يمكن تفعيله عبر SESSION_ENFORCE_SINGLE=true في .env عند الحاجة.
        if (!((bool) env('SESSION_ENFORCE_SINGLE', false))) {
            Cache::put($cacheKey, $sessionId, now()->addDays(7));
            return $next($request);
        }
        
        // الحصول على معرف الجلسة المخزن
        $storedSessionId = Cache::get($cacheKey);
        
        // إذا لم تكن هناك جلسة مخزنة أو كانت هذه هي الجلسة النشطة
        if (!$storedSessionId || $storedSessionId === $sessionId) {
            // حفظ معرف الجلسة الحالية كجلسة نشطة (مدة أطول لتجنب المشاكل)
            Cache::put($cacheKey, $sessionId, now()->addDays(7));
            return $next($request);
        }
        
        // عند اختلاف الجلسة لا نكسر الطلبات الحساسة (مثل حفظ المستخدمين)؛ نحدّث الجلسة النشطة ونكمل.
        \Log::warning('Concurrent session detected; refreshing active session instead of forced logout', [
            'user_id' => $user->id,
            'current_session' => $sessionId,
            'stored_session' => $storedSessionId,
            'url' => $request->fullUrl(),
        ]);
        Cache::put($cacheKey, $sessionId, now()->addDays(7));
        return $next($request);
    }
}

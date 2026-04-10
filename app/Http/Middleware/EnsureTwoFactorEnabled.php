<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * جعل المصادقة الثنائية (2FA) إلزامية للإدمن ومدير العام فقط.
 * من لم يفعّل 2FA بعد يُوجّه لصفحة الإعداد ولا يستطيع الوصول لأي صفحة أخرى.
 */
class EnsureTwoFactorEnabled
{
    /** المسارات المسموح بها قبل تفعيل 2FA */
    protected array $except = [
        'two-factor.setup',
        'two-factor.enable',
        'logout',
        'admin.system-settings.two-factor.confirm',
        'admin.system-settings.two-factor.confirm.submit',
        'admin.system-settings.two-factor.resend',
        'admin.system-settings.two-factor.enable-request',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return $next($request);
        }

        // مسارات مجتمع الذكاء الاصطناعي: لا نفرض 2FA
        if ($request->is('community') || $request->is('community/*')) {
            return $next($request);
        }

        $user = auth()->user();

        // إذا كان إلزام 2FA معطّلاً من إعدادات المنصة أو المستخدم غير مشمول
        if (! $user->requiresTwoFactor()) {
            return $next($request);
        }

        // من لديه 2FA بتطبيق (TOTP) مفعّلة يمرّ بشكل طبيعي
        if ($user->hasTwoFactorEnabled()) {
            return $next($request);
        }

        // من يستخدم 2FA عبر البريد فقط (أدمن/مدرب بدون TOTP) لا يحتاج صفحة إعداد — يتم إرسال الرمز عند كل دخول
        if ($user->usesEmailTwoFactor()) {
            return $next($request);
        }

        // مسموح له فقط بزيارة إعداد 2FA أو التفعيل أو تسجيل الخروج
        if ($request->routeIs($this->except)) {
            return $next($request);
        }

        return redirect()->route('two-factor.setup')
            ->with('warning', 'المصادقة الثنائية إلزامية للدخول إلى النظام. يرجى تفعيلها الآن.');
    }
}

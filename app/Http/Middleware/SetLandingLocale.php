<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * يحدد لغة واجهة الصفحة الرئيسية (Landing) من المعامل ?lang= أو من الجلسة.
 * يُطبّق فقط على المسارات التي نحددها (الصفحة الرئيسية والصفحات العامة) دون التأثير على لوحة التحكم.
 */
class SetLandingLocale
{
    public const ALLOWED_LOCALES = ['ar', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->query('lang');

        if ($locale && in_array($locale, self::ALLOWED_LOCALES, true)) {
            App::setLocale($locale);
            session(['landing_locale' => $locale]);
        } elseif (session()->has('landing_locale')) {
            $saved = session('landing_locale');
            if (in_array($saved, self::ALLOWED_LOCALES, true)) {
                App::setLocale($saved);
            }
        } else {
            // أول زيارة: افتراضي عربي للصفحة الرئيسية
            App::setLocale('ar');
        }

        return $next($request);
    }
}

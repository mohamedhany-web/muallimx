<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * يحدد لغة الموقع من المعامل ?lang= أو من الجلسة (locale).
 * يُطبّق على جميع مسارات الويب لتفعيل الترجمة في كل الصفحات.
 */
class SetLocale
{
    public const ALLOWED_LOCALES = ['ar', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->query('lang');

        if ($locale && in_array($locale, self::ALLOWED_LOCALES, true)) {
            App::setLocale($locale);
            session(['locale' => $locale]);
            // توحيد مع الصفحة الرئيسية إن وُجد
            session(['landing_locale' => $locale]);
        } elseif (session()->has('locale')) {
            $saved = session('locale');
            if (in_array($saved, self::ALLOWED_LOCALES, true)) {
                App::setLocale($saved);
            }
        } elseif (session()->has('landing_locale')) {
            $saved = session('landing_locale');
            if (in_array($saved, self::ALLOWED_LOCALES, true)) {
                App::setLocale($saved);
                session(['locale' => $saved]);
            }
        } else {
            // الافتراضي دائماً العربية (اللغة الأساسية للموقع)
            App::setLocale('ar');
            session(['locale' => 'ar', 'landing_locale' => 'ar']);
        }

        return $next($request);
    }
}

<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/**
 * روابط وعناوين أزرار صفحات الأخطاء حسب مسار الطلب والمستخدم.
 */
final class ErrorPageContext
{
    public static function homeUrl(?Request $request = null): string
    {
        $request = $request ?? request();
        $path = $request->path();

        if (self::pathStartsWithSegment($path, 'admin')) {
            if (Route::has('admin.dashboard')) {
                return route('admin.dashboard');
            }
        }

        if (self::pathStartsWithSegment($path, 'employee')) {
            if (Route::has('employee.dashboard')) {
                return route('employee.dashboard');
            }
        }

        if (self::pathStartsWithSegment($path, 'community')) {
            foreach (['community.dashboard', 'community.login', 'home'] as $routeName) {
                if (Route::has($routeName)) {
                    return route($routeName);
                }
            }
        }

        if (Auth::check() && Route::has('dashboard')) {
            return route('dashboard');
        }

        if (Route::has('home')) {
            return route('home');
        }

        return url('/');
    }

    public static function homeLabel(): string
    {
        $path = request()->path();

        if (self::pathStartsWithSegment($path, 'admin')) {
            return __('errors.back_admin_home');
        }

        if (self::pathStartsWithSegment($path, 'employee')) {
            return __('errors.back_employee_home');
        }

        if (self::pathStartsWithSegment($path, 'community')) {
            return __('errors.back_community_home');
        }

        if (Auth::check()) {
            return __('errors.back_my_account');
        }

        return __('errors.back_site_home');
    }

    private static function pathStartsWithSegment(string $path, string $segment): bool
    {
        return $path === $segment || str_starts_with($path, $segment.'/');
    }
}

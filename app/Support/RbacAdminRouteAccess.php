<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Route;

final class RbacAdminRouteAccess
{
    /**
     * صلاحيات مطلوبة (واحدة تكفي) لهذا المسار، أو null = غير مسموح لموظف RBAC
     *
     * @return list<string>|null
     */
    public static function permissionsForRoute(?string $routeName): ?array
    {
        if ($routeName === null || $routeName === '' || ! str_starts_with($routeName, 'admin.')) {
            return null;
        }

        $exacts = config('rbac_admin_route_access.exacts', []);
        if (isset($exacts[$routeName]) && is_array($exacts[$routeName])) {
            return $exacts[$routeName];
        }

        $parts = explode('.', $routeName);
        $resource = $parts[1] ?? '';

        if ($resource === '') {
            return null;
        }

        $map = config('rbac_admin_route_access.by_resource', []);
        if (! array_key_exists($resource, $map)) {
            return null;
        }

        $perms = $map[$resource];
        if ($perms === false || ! is_array($perms)) {
            return null;
        }

        return $perms;
    }

    public static function userMayAccessAdminRoute(User $user, ?string $routeName): bool
    {
        $required = self::permissionsForRoute($routeName);
        if ($required === null) {
            return false;
        }
        foreach ($required as $perm) {
            if ($user->hasPermission($perm)) {
                return true;
            }
        }

        return false;
    }

    /**
     * أول مسار admin يصلح كصفحة دخول لموظف RBAC، أو null
     */
    public static function firstPostLoginAdminRouteName(User $user): ?string
    {
        foreach (config('rbac_admin_route_access.post_login_admin_routes', []) as $row) {
            $perm = $row['permission'] ?? null;
            $route = $row['route'] ?? null;
            if (! is_string($perm) || ! is_string($route)) {
                continue;
            }
            if ($user->hasPermission($perm) && Route::has($route)) {
                return $route;
            }
        }

        return null;
    }
}

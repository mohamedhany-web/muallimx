<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Route;

final class EmployeeSidebar
{
    /**
     * @return list<array{title: ?string, links: list<array<string, mixed>}>
     */
    public static function sectionsFor(User $user): array
    {
        $code = $user->employeeJob?->code;
        $sections = config('employee_sidebar.menus_by_job.'.$code)
            ?? config('employee_sidebar.fallback_sections', []);
        $items = config('employee_sidebar.items', []);

        $out = [];
        foreach ($sections as $section) {
            $links = [];
            foreach ($section['keys'] ?? [] as $key) {
                $meta = $items[$key] ?? null;
                $perm = (is_array($meta) && array_key_exists('permission', $meta) && $meta['permission'] !== null)
                    ? $meta['permission']
                    : $key;
                if (! $user->employeeCan($perm)) {
                    continue;
                }
                if ($meta === null || empty($meta['route'])) {
                    continue;
                }
                if (! Route::has($meta['route'])) {
                    continue;
                }
                $links[] = array_merge($meta, ['key' => $key]);
            }
            if ($links !== []) {
                $out[] = [
                    'title' => $section['title'] ?? null,
                    'links' => $links,
                ];
            }
        }

        return $out;
    }

    public static function linkIsActive(array $link): bool
    {
        foreach ($link['route_patterns'] ?? [] as $pattern) {
            if (request()->routeIs($pattern)) {
                return true;
            }
        }

        return false;
    }
}

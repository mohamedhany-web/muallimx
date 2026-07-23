<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class ClassroomSlugService
{
    public static function ensureFor(User $user): string
    {
        if (is_string($user->classroom_slug) && $user->classroom_slug !== '') {
            return $user->classroom_slug;
        }

        $slug = self::generateUnique($user);
        $user->forceFill(['classroom_slug' => $slug])->save();

        return $slug;
    }

    public static function generateUnique(User $user, ?string $preferred = null): string
    {
        $base = self::normalize($preferred ?: $user->name);
        if ($base === '') {
            $base = 't'.$user->id;
        }

        $slug = $base;
        $n = 1;
        while (User::query()
            ->where('classroom_slug', $slug)
            ->when($user->id, fn ($q) => $q->where('id', '!=', $user->id))
            ->exists()) {
            $slug = $base.'-'.$n++;
            if ($n > 500) {
                $slug = $base.'-'.Str::lower(Str::random(4));
                break;
            }
        }

        return $slug;
    }

    public static function normalize(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        $slug = Str::slug($value, '-', 'en');
        if ($slug === '') {
            // أسماء عربية بالكامل — استخدم معرفًا ثابتًا من الحروف المسموحة
            $slug = preg_replace('/[^a-zA-Z0-9]+/', '-', $value) ?? '';
            $slug = strtolower(trim($slug, '-'));
        }

        $slug = Str::limit($slug, 60, '');

        return $slug === '' ? '' : $slug;
    }

    public static function fixedJoinUrl(User $user): string
    {
        $slug = self::ensureFor($user);

        return url('classroom/join/t/'.$slug);
    }

    public static function isValidFormat(string $slug): bool
    {
        return (bool) preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)
            && strlen($slug) >= 2
            && strlen($slug) <= 80;
    }
}
